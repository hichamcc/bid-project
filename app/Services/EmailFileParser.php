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

    /**
     * Parse a file stored on the public disk (as saved by the proposal form).
     */
    public static function parseStoredFile(string $path): array
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

        return array_merge($data, $parsed);
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

        $body = $message->getTextContent();
        if ($body === null || trim($body) === '') {
            $body = strip_tags((string) $message->getHtmlContent());
        }

        $attachments = [];
        foreach ($message->getAllAttachmentParts() as $part) {
            if ($name = $part->getFilename()) {
                $attachments[] = $name;
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
        ];
    }

    private static function parseMsg(string $file): array
    {
        if (!class_exists(\Hfig\MAPI\MapiMessageFactory::class)) {
            throw new \RuntimeException('hfig/mapi is not installed');
        }

        $ole = (new \Hfig\MAPI\OLE\Pear\DocumentFactory())->createFromFile($file);
        $message = (new \Hfig\MAPI\MapiMessageFactory())->parseMessage($ole);

        $prop = function (string $name) use ($message) {
            try {
                return $message->properties[$name] ?? null;
            } catch (Throwable $e) {
                return null;
            }
        };

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
        foreach ($message->getRecipients() as $recipient) {
            $type = method_exists($recipient, 'getType') ? strtolower((string) $recipient->getType()) : 'to';
            if ($type !== 'to') {
                continue;
            }
            $email = method_exists($recipient, 'getEmail') ? self::smtpAddress($recipient->getEmail()) : null;
            $name = method_exists($recipient, 'getName') ? $recipient->getName() : null;
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

        $body = $message->getBody();
        if ($body === null || trim((string) $body) === '') {
            $body = method_exists($message, 'getBodyHTML') ? strip_tags((string) $message->getBodyHTML()) : '';
        }

        $attachments = [];
        foreach ($message->getAttachments() as $attachment) {
            if ($name = $attachment->getFilename()) {
                $attachments[] = $name;
            }
        }

        return [
            'sent_at'      => $sentAt,
            'from_name'    => $prop('sender_name') ?: null,
            'from_email'   => $fromEmail,
            'to_name'      => self::joinList($toNames),
            'to_email'     => self::joinList($toEmails),
            'subject'      => self::clip($prop('subject') ?? ($headers['Subject'] ?? null), 500),
            'body_preview' => self::preview($body),
            'attachments'  => $attachments ?: null,
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
            if (is_int($value) || (is_string($value) && ctype_digit($value))) {
                return Carbon::createFromTimestamp((int) $value);
            }
            if (is_string($value) && trim($value) !== '') {
                return Carbon::parse($value);
            }
        } catch (Throwable $e) {
        }

        return null;
    }

    private static function preview($body): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode((string) $body, ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        return $text === '' ? null : Str::limit($text, self::PREVIEW_LENGTH);
    }

    private static function joinList(array $values): ?string
    {
        $values = array_values(array_filter(array_map('trim', array_map('strval', $values))));

        return $values ? implode(', ', $values) : null;
    }

    private static function clip($value, int $length): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return $value ? Str::limit($value, $length - 3) : null;
    }
}
