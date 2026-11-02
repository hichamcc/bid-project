<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Reads the email details (sent date, from, to, subject, body preview, attachment names)
 * out of an uploaded .eml / .msg file. Other file types (pdf, images) return only the
 * file type. Never throws: a file that can't be read comes back with parse_error set.
 *
 * Needs: composer require zbateson/mail-mime-parser hfig/mapi
 */
class EmailFileParser
{
    private const PREVIEW_LENGTH = 500;

    /** Inline images bigger than this aren't embedded in the preview */
    private const MAX_INLINE_IMAGE = 3 * 1024 * 1024;

    /** Keys only used by the full preview (not stored in gc_emails) */
    private const FULL_ONLY = ['cc', 'body_html', 'body_text', 'inline_images'];

    /**
     * Parse a file stored on the public disk (as saved by the proposal form) into the
     * gc_emails columns.
     */
    public static function parseStoredFile(string $path): array
    {
        return array_diff_key(self::readStoredFile($path), array_flip(self::FULL_ONLY));
    }

    /**
     * Everything needed to show the email: the gc_emails fields plus cc, the full
     * body (body_html with inline images embedded, or body_text).
     */
    public static function readStoredFile(string $path): array
    {
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $data = ['file_type' => $type, 'parse_error' => null];

        if (!in_array($type, ['eml', 'msg'], true)) {
            return $data;
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            return $data + ['parse_error' => 'File not found'];
        }

        try {
            $parsed = $type === 'eml'
                ? self::parseEml($disk->path($path))
                : self::parseMsg($disk->path($path));
        } catch (Throwable $e) {
            return $data + ['parse_error' => Str::limit($e->getMessage(), 250)];
        }

        // Point cid: references (logos, pasted screenshots) at the embedded images
        if (!empty($parsed['body_html']) && !empty($parsed['inline_images'])) {
            $parsed['body_html'] = preg_replace_callback('/cid:([^"\'\s>)]+)/i', function ($m) use ($parsed) {
                return $parsed['inline_images'][strtolower(trim(urldecode($m[1]), '<>'))] ?? $m[0];
            }, $parsed['body_html']) ?? $parsed['body_html'];
        }
        unset($parsed['inline_images']);

        return array_merge($data, $parsed);
    }

    /**
     * Collects an inline image as a data: URI keyed by its content id.
     */
    private static function addInlineImage(array &$images, ?string $contentId, ?string $mime, ?string $data): void
    {
        $contentId = strtolower(trim((string) $contentId, " <>"));
        if ($contentId === '' || !$data || strlen($data) > self::MAX_INLINE_IMAGE || !str_starts_with((string) $mime, 'image/')) {
            return;
        }

        $images[$contentId] = 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    private static function parseEml(string $file): array
    {
        if (!class_exists(\ZBateson\MailMimeParser\Message::class)) {
            throw new \RuntimeException('zbateson/mail-mime-parser is not installed');
        }

        $handle = fopen($file, 'r');
        $message = \ZBateson\MailMimeParser\Message::from($handle, true);

        $from = $message->getHeader('From');
        $to = $message->getHeader('To');
        $date = $message->getHeader('Date');

        $toNames = [];
        $toEmails = [];
        foreach ($to ? $to->getAddresses() : [] as $address) {
            $toNames[] = $address->getName() ?: $address->getEmail();
            $toEmails[] = $address->getEmail();
        }

        $cc = [];
        $ccHeader = $message->getHeader('Cc');
        foreach ($ccHeader && method_exists($ccHeader, 'getAddresses') ? $ccHeader->getAddresses() : [] as $address) {
            $cc[] = $address->getName() ? $address->getName() . ' <' . $address->getEmail() . '>' : $address->getEmail();
        }

        $text = $message->getTextContent();
        $html = $message->getHtmlContent();
        $body = ($text === null || trim($text) === '') ? self::htmlToText((string) $html) : $text;

        $attachments = [];
        $inline = [];
        foreach ($message->getAllAttachmentParts() as $part) {
            if ($name = $part->getFilename()) {
                $attachments[] = $name;
            }
            try {
                $stream = $part->getContentId() ? $part->getBinaryContentStream() : null;
                self::addInlineImage($inline, $part->getContentId(), $part->getContentType(), $stream ? $stream->getContents() : null);
            } catch (Throwable $e) {
            }
        }

        $sentAt = $date && method_exists($date, 'getDateTime') ? $date->getDateTime() : null;

        return [
            'sent_at'      => $sentAt ? Carbon::instance($sentAt) : null,
            'from_name'    => $from ? ($from->getPersonName() ?: null) : null,
            'from_email'   => $from ? ($from->getEmail() ?: null) : null,
            'to_name'      => self::joinList($toNames),
            'to_email'     => self::joinList($toEmails),
            'subject'      => self::clip($message->getHeaderValue('Subject'), 500),
            'body_preview' => self::preview($body),
            'attachments'  => $attachments ?: null,
            'cc'           => self::joinList($cc),
            'body_html'    => $html ? self::utf8($html) : null,
            'body_text'    => $text ? self::utf8($text) : null,
            'inline_images' => $inline,
        ];
    }

    private static function parseMsg(string $file): array
    {
        if (!class_exists(\Hfig\MAPI\MapiMessageFactory::class)) {
            throw new \RuntimeException('hfig/mapi is not installed');
        }

        // Same as Pear\DocumentFactory::createFromFile(), but PEAR's OLE reports a bad file
        // by returning an error object instead of throwing, so check it here.
        $ole = new \OLE();
        $result = $ole->read($file);
        if ($result instanceof \PEAR_Error || empty($ole->root)) {
            throw new \RuntimeException('Not a valid Outlook .msg file' . ($result instanceof \PEAR_Error ? ': ' . $result->getMessage() : ''));
        }
        $message = (new \Hfig\MAPI\MapiMessageFactory())->parseMessage(new \Hfig\MAPI\OLE\Pear\DocumentElement($ole, $ole->root));

        // Each piece is read on its own: hfig/mapi throws on some messages (e.g. no
        // plain-text body), and one missing part shouldn't lose the rest.
        $try = function (callable $read, $default = null) {
            try {
                return $read();
            } catch (Throwable $e) {
                return $default;
            }
        };
        $prop = fn (string $name) => $try(fn () => $message->properties[$name] ?? null);

        // Outlook keeps the original internet headers on received mail; use them as a
        // fallback for anything the MAPI properties don't give us.
        $headers = [];
        if ($raw = $prop('transport_message_headers')) {
            $headers = @iconv_mime_decode_headers($raw, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8') ?: [];
        }

        $fromEmail = self::smtpAddress($prop('sender_smtp_address'))
            ?? self::smtpAddress($prop('sender_email_address'))
            ?? self::smtpAddress($prop('sent_representing_smtp_address'))
            ?? self::emailFromHeader($headers['From'] ?? null);

        $toNames = [];
        $toEmails = [];
        $cc = [];
        foreach ($try(fn () => $message->getRecipients(), []) as $recipient) {
            $type = strtolower((string) $try(fn () => $recipient->getType(), 'to'));
            $email = self::smtpAddress($try(fn () => $recipient->getEmail()));
            $name = $try(fn () => $recipient->getName());
            if ($type === 'cc') {
                $cc[] = $name && $email ? "{$name} <{$email}>" : ($email ?: $name);
            }
            if ($type !== 'to') {
                continue;
            }
            $toNames[] = $name ?: $email;
            $toEmails[] = $email;
        }
        if (!array_filter($toEmails) && isset($headers['To'])) {
            $toEmails = [self::emailFromHeader($headers['To'])];
        }
        if (!array_filter($toNames) && ($displayTo = $prop('display_to'))) {
            $toNames = array_map('trim', explode(';', $displayTo));
        }

        $sentAt = self::toDate($prop('client_submit_time'))
            ?? self::toDate($prop('message_delivery_time'))
            ?? self::toDate($headers['Date'] ?? null);

        // Plain-text body, else the HTML body (getBodyHTML also pulls HTML out of RTF bodies)
        $text = $prop('body');
        $text = is_string($text) && trim($text) !== '' ? $text : null;
        $html = $try(fn () => $message->getBodyHTML());
        $html = is_string($html) && trim($html) !== '' ? $html : null;
        $body = $text ?? self::htmlToText((string) $html);

        $attachments = [];
        $inline = [];
        foreach ($try(fn () => $message->getAttachments(), []) as $attachment) {
            if ($name = $try(fn () => $attachment->getFilename())) {
                $attachments[] = $name;
            }
            $contentId = $try(fn () => $attachment->getContentId());
            if ($contentId) {
                $data = $try(fn () => $attachment->getData());
                self::addInlineImage($inline, $contentId, $try(fn () => $attachment->getMimeType()), is_string($data) ? $data : null);
            }
        }

        return [
            'sent_at'      => $sentAt,
            'from_name'    => self::clip($prop('sender_name'), 255),
            'from_email'   => $fromEmail,
            'to_name'      => self::joinList($toNames),
            'to_email'     => self::joinList($toEmails),
            'subject'      => self::clip($prop('subject') ?? ($headers['Subject'] ?? null), 500),
            'body_preview' => self::preview($body),
            'attachments'  => $attachments ?: null,
            'cc'           => self::joinList($cc),
            'body_html'    => $html ? self::utf8($html) : null,
            'body_text'    => $text ? self::utf8($text) : null,
            'inline_images' => $inline,
        ];
    }

    /**
     * Exchange stores internal senders as an X.500 path (/O=EXCHANGE/...); only keep real addresses.
     */
    private static function smtpAddress($value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return $value && str_contains($value, '@') && !str_starts_with($value, '/') ? $value : null;
    }

    private static function emailFromHeader($value): ?string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        return is_string($value) && preg_match('/[\w.+\-]+@[\w\-]+\.[\w.\-]+/', $value, $m) ? $m[0] : null;
    }

    private static function toDate($value): ?Carbon
    {
        try {
            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value);
            }
            // hfig/mapi gives times as a unix timestamp float (0 when unset)
            if (is_int($value) || is_float($value) || (is_string($value) && ctype_digit($value))) {
                return (int) $value > 0 ? Carbon::createFromTimestamp((int) $value) : null;
            }
            if (is_string($value) && trim($value) !== '') {
                return Carbon::parse($value);
            }
        } catch (Throwable $e) {
        }

        return null;
    }

    /**
     * HTML email body → plain text (drops <style>/<script>/<head> so CSS doesn't end up in the preview).
     */
    private static function htmlToText(string $html): string
    {
        $html = preg_replace('#<(style|script|head)\b[^>]*>.*?</\1>#is', ' ', self::utf8($html)) ?? $html;
        $html = preg_replace('#<(br|/p|/div|/tr)\b[^>]*>#i', "\n", $html) ?? $html;

        return strip_tags($html);
    }

    private static function preview($body): ?string
    {
        $text = html_entity_decode(self::utf8((string) $body), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        return $text === '' ? null : Str::limit($text, self::PREVIEW_LENGTH);
    }

    private static function joinList(array $values): ?string
    {
        $values = array_values(array_filter(array_map(fn ($v) => trim(self::utf8((string) $v)), $values)));

        return $values ? implode(', ', $values) : null;
    }

    private static function clip($value, int $length): ?string
    {
        $value = is_string($value) ? trim(self::utf8($value)) : null;

        return $value ? Str::limit($value, $length - 3) : null;
    }

    /**
     * Older Outlook files can hold non-UTF-8 text, which MySQL would reject.
     */
    private static function utf8(string $value): string
    {
        return mb_check_encoding($value, 'UTF-8') ? $value : mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    }
}
