@extends('components.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ $scopeReview->project_name }}
                </h2>
                <a href="{{ route('scope-review.index') }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back
                </a>
            </div>
        </div>

        @if($scopeReview->project_number)
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                Project Number: <strong>{{ $scopeReview->project_number }}</strong>
            </div>
        @endif

        <form method="POST" action="{{ route('scope-review.update', $scopeReview) }}">
            @csrf
            @method('PUT')

            @if(auth()->user()->isAdmin())
                <!-- Admin: intake fields + assignment -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Entry Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="entry_date" value="{{ old('entry_date', $scopeReview->entry_date->format('Y-m-d')) }}" required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('entry_date') border-red-500 @enderror">
                                @error('entry_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Source</label>
                                <input type="text" name="source" value="{{ old('source', $scopeReview->source) }}"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('source') border-red-500 @enderror">
                                @error('source') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Platform</label>
                                <input type="text" name="platform" value="{{ old('platform', $scopeReview->platform) }}"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('platform') border-red-500 @enderror">
                                @error('platform') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Project Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="project_name" value="{{ old('project_name', $scopeReview->project_name) }}" required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('project_name') border-red-500 @enderror">
                                @error('project_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bid Due Date</label>
                                <input type="date" name="due_date" value="{{ old('due_date', optional($scopeReview->due_date)->format('Y-m-d')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('due_date') border-red-500 @enderror">
                                @error('due_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location</label>
                                <input type="text" name="location" value="{{ old('location', $scopeReview->location) }}"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('location') border-red-500 @enderror">
                                @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project Link</label>
                                <input type="url" name="project_link" value="{{ old('project_link', $scopeReview->project_link) }}"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('project_link') border-red-500 @enderror">
                                @error('project_link') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign Estimator</label>
                                <select name="assigned_estimator_id"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('assigned_estimator_id') border-red-500 @enderror">
                                    <option value="">-- Not assigned yet --</option>
                                    @foreach($estimators as $estimator)
                                        <option value="{{ $estimator->id }}" {{ old('assigned_estimator_id', $scopeReview->assigned_estimator_id) == $estimator->id ? 'selected' : '' }}>
                                            {{ $estimator->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_estimator_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                                <textarea name="notes" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('notes') border-red-500 @enderror">{{ old('notes', $scopeReview->notes) }}</textarea>
                                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                @if($scopeReview->decision)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6 p-6">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Estimator Review (read-only)</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div><dt class="text-gray-500 dark:text-gray-400">Project Type</dt><dd class="text-gray-900 dark:text-gray-100">{{ $scopeReview->project_type ?? '—' }}</dd></div>
                            <div><dt class="text-gray-500 dark:text-gray-400">Decision</dt><dd class="text-gray-900 dark:text-gray-100">{{ $scopeReview->decision }}</dd></div>
                            <div><dt class="text-gray-500 dark:text-gray-400">Duration</dt><dd class="text-gray-900 dark:text-gray-100">{{ $scopeReview->duration ?? '—' }}</dd></div>
                            <div><dt class="text-gray-500 dark:text-gray-400">Uploaded in OH</dt><dd class="text-gray-900 dark:text-gray-100">{{ $scopeReview->uploaded_in_oh ? 'Yes' : 'No' }}</dd></div>
                            <div class="md:col-span-2"><dt class="text-gray-500 dark:text-gray-400">Estimator Notes</dt><dd class="text-gray-900 dark:text-gray-100">{{ $scopeReview->estimator_notes ?? '—' }}</dd></div>
                        </dl>
                    </div>
                @endif

            @else
                <!-- Estimator: review fields only -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                     x-data="{ decision: '{{ old('decision', $scopeReview->decision) }}' }">
                    <div class="p-6">
                        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <p><strong>Project:</strong> {{ $scopeReview->project_name }}</p>
                            <p><strong>Platform:</strong> {{ $scopeReview->platform ?? '—' }} &middot; <strong>Location:</strong> {{ $scopeReview->location ?? '—' }}</p>
                            <p><strong>Bid Due:</strong> {{ optional($scopeReview->due_date)->format('M d, Y') ?? '—' }}</p>
                            @if($scopeReview->project_link)
                                <p><a href="{{ $scopeReview->project_link }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">View Project Link</a></p>
                            @endif
                            @if($scopeReview->notes)
                                <p><strong>Admin Notes:</strong> {{ $scopeReview->notes }}</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div x-show="decision === 'approved'">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Project Type <span class="text-red-500">*</span>
                                </label>
                                <select name="project_type" :required="decision === 'approved'"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('project_type') border-red-500 @enderror">
                                    <option value="">-- Select --</option>
                                    <option value="MU" {{ old('project_type', $scopeReview->project_type) === 'MU' ? 'selected' : '' }}>MU</option>
                                    <option value="NON_MU" {{ old('project_type', $scopeReview->project_type) === 'NON_MU' ? 'selected' : '' }}>NON MU</option>
                                </select>
                                @error('project_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Duration</label>
                                <input type="text" name="duration" value="{{ old('duration', $scopeReview->duration) }}" placeholder="e.g. 2 days"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('duration') border-red-500 @enderror">
                                @error('duration') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Bid Decision <span class="text-red-500">*</span>
                                </label>
                                <input type="hidden" name="decision" :value="decision">
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <button type="button" @click="decision = 'approved'"
                                            :class="decision === 'approved' ? 'bg-green-600 text-white border-green-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600'"
                                            class="px-4 py-2 rounded-md border text-sm font-medium">
                                        Approve
                                    </button>
                                    <button type="button" @click="decision = 'rfi_requested'"
                                            :class="decision === 'rfi_requested' ? 'bg-yellow-500 text-white border-yellow-500' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600'"
                                            class="px-4 py-2 rounded-md border text-sm font-medium">
                                        Send RFI
                                    </button>
                                    <button type="button" @click="decision = 'not_in_scope'"
                                            :class="decision === 'not_in_scope' ? 'bg-red-600 text-white border-red-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600'"
                                            class="px-4 py-2 rounded-md border text-sm font-medium">
                                        Not In Scope
                                    </button>
                                    <button type="button" @click="decision = 'skipped'"
                                            :class="decision === 'skipped' ? 'bg-gray-600 text-white border-gray-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600'"
                                            class="px-4 py-2 rounded-md border text-sm font-medium">
                                        Skip
                                    </button>
                                </div>
                                @error('decision') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                @if($scopeReview->decision === 'approved')
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Already approved — a project number has been generated.</p>
                                @endif
                                <p x-show="decision === 'skipped'" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use this if the project was approved but you're no longer pursuing it. Add a note below explaining why.</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-center gap-2">
                                    <input type="hidden" name="uploaded_in_oh" value="0">
                                    <input type="checkbox" name="uploaded_in_oh" value="1" {{ old('uploaded_in_oh', $scopeReview->uploaded_in_oh) ? 'checked' : '' }}
                                           class="rounded border-gray-300 dark:border-gray-600">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Uploaded in OH</span>
                                </label>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                                <textarea name="estimator_notes" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('estimator_notes') border-red-500 @enderror">{{ old('estimator_notes', $scopeReview->estimator_notes) }}</textarea>
                                @error('estimator_notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('scope-review.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
