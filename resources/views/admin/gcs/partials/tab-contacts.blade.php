{{-- People at this GC. Add / edit in a popup; filters work in the browser. --}}
@php
    $roles = $contacts->pluck('role')->filter()->unique()->sort()->values();
    $contactFields = ['first_name', 'last_name', 'role', 'office_number', 'mobile', 'email'];
    $emptyContact = array_fill_keys($contactFields, '');

    // After a failed submit, reopen the popup with what was typed
    $reopen = old('_contact_form') ? [
        'id' => old('_contact_id') ?: null,
        'values' => array_merge($emptyContact, array_map(fn ($v) => (string) $v, array_filter(old(), fn ($k) => in_array($k, $contactFields), ARRAY_FILTER_USE_KEY))),
    ] : null;
@endphp

<div data-filter-scope="contacts"
     x-data="{
        open: false,
        id: null,
        form: @js($emptyContact),
        storeUrl: @js(route('admin.gcs.contacts.store', $gc)),
        updateUrl: @js(route('admin.gcs.contacts.update', [$gc, '__ID__'])),
        get action() { return this.id ? this.updateUrl.replace('__ID__', this.id) : this.storeUrl },
        openCreate() { this.id = null; this.form = @js($emptyContact); this.open = true },
        openEdit(contact) { this.id = contact.id; this.form = Object.assign(@js($emptyContact), contact.values); this.open = true },
     }"
     x-init="@if($reopen) id = @js($reopen['id']); form = @js($reopen['values']); open = true; @endif"
     @keydown.escape.window="open = false">

    <div class="flex items-center justify-between mb-2 text-sm">
        <div class="flex items-center gap-3">
            <span class="text-gray-600" data-filter-count></span>
            <button type="button" class="text-gray-600 hover:text-gray-900 underline" data-filter-clear style="display:none">Clear filters</button>
        </div>
        <button type="button" @click="openCreate()" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-md">
            Add Contact
        </button>
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="gc-table">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Role</th>
                    <th>Office Number</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th></th>
                </tr>
                <tr class="filters">
                    <th><input class="gc-filter" data-filter-col="0" placeholder="Filter…"></th>
                    <th><input class="gc-filter" data-filter-col="1" placeholder="Filter…"></th>
                    <th>
                        <select class="gc-filter" data-filter-col="2">
                            <option value="">All</option>
                            @foreach($roles as $role)
                                <option value="{{ strtolower($role) }}">{{ $role }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th><input class="gc-filter" data-filter-col="3" placeholder="Filter…"></th>
                    <th><input class="gc-filter" data-filter-col="4" placeholder="Filter…"></th>
                    <th><input class="gc-filter" data-filter-col="5" placeholder="Filter…"></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $contact)
                    <tr data-row>
                        <td>{{ $contact->first_name }}</td>
                        <td>{{ $contact->last_name ?? '-' }}</td>
                        <td data-value="{{ strtolower($contact->role ?? '') }}">{{ $contact->role ?? '-' }}</td>
                        <td>
                            @if($contact->office_number)
                                <a href="tel:{{ $contact->office_number }}" class="text-blue-600 hover:text-blue-800">{{ $contact->office_number }}</a>
                            @else - @endif
                        </td>
                        <td>
                            @if($contact->mobile)
                                <a href="tel:{{ $contact->mobile }}" class="text-blue-600 hover:text-blue-800">{{ $contact->mobile }}</a>
                            @else - @endif
                        </td>
                        <td>
                            @if($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="text-blue-600 hover:text-blue-800">{{ $contact->email }}</a>
                            @else - @endif
                        </td>
                        <td class="text-right">
                            <button type="button" class="text-blue-600 hover:text-blue-800 mr-2"
                                    @click="openEdit(@js(['id' => $contact->id, 'values' => array_map(fn ($v) => (string) $v, $contact->only($contactFields))]))">Edit</button>
                            <form method="POST" action="{{ route('admin.gcs.contacts.destroy', [$gc, $contact]) }}" class="inline"
                                  onsubmit="return confirm('Delete {{ addslashes($contact->full_name) }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-gray-500 py-6">No contacts yet. Use “Add Contact” to add one.</td></tr>
                @endforelse
                <tr data-empty-filtered style="display:none"><td colspan="7" class="text-center text-gray-500 py-6">No contacts match these filters.</td></tr>
            </tbody>
        </table>
    </div>

    {{-- Add / edit popup --}}
    <div x-show="open" x-cloak style="position: fixed; inset: 0; z-index: 50; background: rgba(0,0,0,0.5); overflow-y: auto;">
      <div style="display: flex; align-items: center; justify-content: center; min-height: 100%; padding: 1rem;" @click.self="open = false">
        <div class="bg-white rounded-lg shadow-xl w-full" style="max-width: 36rem;">
            <form method="POST" :action="action">
                @csrf
                <template x-if="id"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="_contact_form" value="1">
                <input type="hidden" name="_contact_id" :value="id ?? ''">

                <div class="px-5 py-3 border-b flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900" x-text="id ? 'Edit Contact' : 'Add Contact'"></h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>

                <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach([
                        'first_name'    => ['First Name *', 'text'],
                        'last_name'     => ['Last Name', 'text'],
                        'role'          => ['Role', 'text'],
                        'office_number' => ['Office Number', 'tel'],
                        'mobile'        => ['Mobile', 'tel'],
                        'email'         => ['Email', 'email'],
                    ] as $field => [$label, $type])
                        <div>
                            <label for="contact_{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                            <input type="{{ $type }}" id="contact_{{ $field }}" name="{{ $field }}" x-model="form.{{ $field }}"
                                   @if($field === 'first_name') required @endif
                                   @if($field === 'role') list="contact-roles" @endif
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @if(old('_contact_form')) @error($field) border-red-500 @enderror @endif">
                            @if(old('_contact_form'))
                                @error($field)<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            @endif
                        </div>
                    @endforeach
                    <datalist id="contact-roles">
                        @foreach($roles as $role)<option value="{{ $role }}">@endforeach
                    </datalist>
                </div>

                <div class="px-5 py-3 border-t flex justify-end gap-2">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-md">Save</button>
                </div>
            </form>
        </div>
      </div>
    </div>
</div>
