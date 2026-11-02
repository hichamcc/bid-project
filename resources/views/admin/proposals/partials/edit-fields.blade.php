{{-- Edit-proposal form fields, shared by the edit page and the list page's popup. Needs $proposal, $projects. --}}
                    <!-- Project Selection -->
                    <div class="mb-6">
                        <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Project <span class="text-red-500">*</span>
                        </label>
                        <select id="project_id" 
                                name="project_id" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('project_id') border-red-500 @enderror"
                                onchange="showProjectInfo(this.value)">
                            <option value="">Select a project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ (old('project_id', $proposal->project_id) == $project->id) ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Project Information Panel -->
                    <div id="projectInfo" class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Primary GC</label>
                                <p id="primaryGC" class="text-sm text-gray-900">-</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Other GCs</label>
                                <p id="otherGCs" class="text-sm text-gray-900">-</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Estimator</label>
                                <p id="estimator" class="text-sm text-gray-900">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Job Number -->
                    <div class="mb-6">
                        <label for="job_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Job Number
                        </label>
                        <input type="text" 
                               id="job_number" 
                               name="job_number" 
                               value="{{ old('job_number', $proposal->job_number) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('job_number') border-red-500 @enderror">
                        @error('job_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="mb-6">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select id="status"
                                name="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('status') border-red-500 @enderror">
                            <option value="">Not set</option>
                            @foreach(\App\Models\Proposal::STATUSES as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" {{ old('status', $proposal->status) === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Set to Inactive once the 4th follow-up has been reached with no response.</p>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submission Date -->
                    <div class="mb-6">
                        <label for="submission_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Submission Date
                        </label>
                        <input type="date" 
                               id="submission_date" 
                               name="submission_date" 
                               value="{{ old('submission_date', $proposal->submission_date?->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('submission_date') border-red-500 @enderror">
                        @error('submission_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- GC confirmed receipt (colours the submitted date green/red on the list) -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="hidden" name="responded" value="no">
                            <input type="checkbox" 
                                   name="responded" 
                                   value="yes"
                                   {{ old('responded', $proposal->responded) == 'yes' ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">GC Confirmed Receipt</span>
                        </label>
                        @error('responded')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Follow-up Section -->
                    <div class="mb-6 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Follow-up Tracking</h3>
                        
                        <!-- First Follow-up -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label for="first_follow_up_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    First Follow-up Date
                                </label>
                                <input type="date" 
                                       id="first_follow_up_date" 
                                       name="first_follow_up_date" 
                                       value="{{ old('first_follow_up_date', $proposal->first_follow_up_date?->format('Y-m-d')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('first_follow_up_date') border-red-500 @enderror">
                                @error('first_follow_up_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center">
                                <label class="flex items-center">
                                    <input type="hidden" name="first_follow_up_respond" value="no">
                                    <input type="checkbox" 
                                           name="first_follow_up_respond" 
                                           value="yes"
                                           {{ old('first_follow_up_respond', $proposal->first_follow_up_respond) == 'yes' ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-700">First Follow-up Response</span>
                                </label>
                                @error('first_follow_up_respond')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="first_follow_up_attachment" class="block text-sm font-medium text-gray-700 mb-2">
                                    1st Follow-up Attachment
                                </label>
                                <input type="file" 
                                       id="first_follow_up_attachment" 
                                       name="first_follow_up_attachment" 
                                       accept=".eml,.msg,.pdf,.png,.jpg,.jpeg"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('first_follow_up_attachment') border-red-500 @enderror">
                                @if($proposal->first_follow_up_attachment)
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($proposal->first_follow_up_attachment) }}" data-email-preview="{{ route('admin.proposals.follow-up-preview', [$proposal, 1]) }}" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 text-sm">
                                            Current: 1st Follow-up Email
                                        </a>
                                    </div>
                                @endif
                                <p class="mt-1 text-xs text-gray-500">Supported formats: .eml, .msg, .pdf, .png, .jpg, .jpeg</p>
                                @error('first_follow_up_attachment')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Second Follow-up -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label for="second_follow_up_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Second Follow-up Date
                                </label>
                                <input type="date" 
                                       id="second_follow_up_date" 
                                       name="second_follow_up_date" 
                                       value="{{ old('second_follow_up_date', $proposal->second_follow_up_date?->format('Y-m-d')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('second_follow_up_date') border-red-500 @enderror">
                                @error('second_follow_up_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center">
                                <label class="flex items-center">
                                    <input type="hidden" name="second_follow_up_respond" value="no">
                                    <input type="checkbox" 
                                           name="second_follow_up_respond" 
                                           value="yes"
                                           {{ old('second_follow_up_respond', $proposal->second_follow_up_respond) == 'yes' ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-700">Second Follow-up Response</span>
                                </label>
                                @error('second_follow_up_respond')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="second_follow_up_attachment" class="block text-sm font-medium text-gray-700 mb-2">
                                    2nd Follow-up Attachment
                                </label>
                                <input type="file" 
                                       id="second_follow_up_attachment" 
                                       name="second_follow_up_attachment" 
                                       accept=".eml,.msg,.pdf,.png,.jpg,.jpeg"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('second_follow_up_attachment') border-red-500 @enderror">
                                @if($proposal->second_follow_up_attachment)
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($proposal->second_follow_up_attachment) }}" data-email-preview="{{ route('admin.proposals.follow-up-preview', [$proposal, 2]) }}" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 text-sm">
                                            Current: 2nd Follow-up Email
                                        </a>
                                    </div>
                                @endif
                                <p class="mt-1 text-xs text-gray-500">Supported formats: .eml, .msg, .pdf, .png, .jpg, .jpeg</p>
                                @error('second_follow_up_attachment')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Third Follow-up -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label for="third_follow_up_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Third Follow-up Date
                                </label>
                                <input type="date" 
                                       id="third_follow_up_date" 
                                       name="third_follow_up_date" 
                                       value="{{ old('third_follow_up_date', $proposal->third_follow_up_date?->format('Y-m-d')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('third_follow_up_date') border-red-500 @enderror">
                                @error('third_follow_up_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center">
                                <label class="flex items-center">
                                    <input type="hidden" name="third_follow_up_respond" value="no">
                                    <input type="checkbox" 
                                           name="third_follow_up_respond" 
                                           value="yes"
                                           {{ old('third_follow_up_respond', $proposal->third_follow_up_respond) == 'yes' ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-700">Third Follow-up Response</span>
                                </label>
                                @error('third_follow_up_respond')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="third_follow_up_attachment" class="block text-sm font-medium text-gray-700 mb-2">
                                    3rd Follow-up Attachment
                                </label>
                                <input type="file" 
                                       id="third_follow_up_attachment" 
                                       name="third_follow_up_attachment" 
                                       accept=".eml,.msg,.pdf,.png,.jpg,.jpeg"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('third_follow_up_attachment') border-red-500 @enderror">
                                @if($proposal->third_follow_up_attachment)
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($proposal->third_follow_up_attachment) }}" data-email-preview="{{ route('admin.proposals.follow-up-preview', [$proposal, 3]) }}" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 text-sm">
                                            Current: 3rd Follow-up Email
                                        </a>
                                    </div>
                                @endif
                                <p class="mt-1 text-xs text-gray-500">Supported formats: .eml, .msg, .pdf, .png, .jpg, .jpeg</p>
                                @error('third_follow_up_attachment')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Fourth Follow-up -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="fourth_follow_up_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Fourth Follow-up Date
                                </label>
                                <input type="date" 
                                       id="fourth_follow_up_date" 
                                       name="fourth_follow_up_date" 
                                       value="{{ old('fourth_follow_up_date', $proposal->fourth_follow_up_date?->format('Y-m-d')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fourth_follow_up_date') border-red-500 @enderror">
                                @error('fourth_follow_up_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center">
                                <label class="flex items-center">
                                    <input type="hidden" name="fourth_follow_up_respond" value="no">
                                    <input type="checkbox" 
                                           name="fourth_follow_up_respond" 
                                           value="yes"
                                           {{ old('fourth_follow_up_respond', $proposal->fourth_follow_up_respond) == 'yes' ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-700">Fourth Follow-up Response</span>
                                </label>
                                @error('fourth_follow_up_respond')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="fourth_follow_up_attachment" class="block text-sm font-medium text-gray-700 mb-2">
                                    4th Follow-up Attachment
                                </label>
                                <input type="file" 
                                       id="fourth_follow_up_attachment" 
                                       name="fourth_follow_up_attachment" 
                                       accept=".eml,.msg,.pdf,.png,.jpg,.jpeg"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fourth_follow_up_attachment') border-red-500 @enderror">
                                @if($proposal->fourth_follow_up_attachment)
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($proposal->fourth_follow_up_attachment) }}" data-email-preview="{{ route('admin.proposals.follow-up-preview', [$proposal, 4]) }}" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 text-sm">
                                            Current: 4th Follow-up Email
                                        </a>
                                    </div>
                                @endif
                                <p class="mt-1 text-xs text-gray-500">Supported formats: .eml, .msg, .pdf, .png, .jpg, .jpeg</p>
                                @error('fourth_follow_up_attachment')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Price Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label for="price_original" class="block text-sm font-medium text-gray-700 mb-2">
                                ARTELYE Price ($)
                            </label>
                            <input type="number" 
                                   id="price_original" 
                                   name="price_original" 
                                   step="0.01"
                                   min="0"
                                   value="{{ old('price_original', $proposal->price_original) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price_original') border-red-500 @enderror">
                            @error('price_original')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price_ve" class="block text-sm font-medium text-gray-700 mb-2">
                                ARTELYE VE Price ($)
                            </label>
                            <input type="number" 
                                   id="price_ve" 
                                   name="price_ve" 
                                   step="0.01"
                                   min="0"
                                   value="{{ old('price_ve', $proposal->price_ve) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price_ve') border-red-500 @enderror">
                            @error('price_ve')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="gc_price" class="block text-sm font-medium text-gray-700 mb-2">
                                GC Price ($)
                            </label>
                            <input type="number" 
                                   id="gc_price" 
                                   name="gc_price" 
                                   step="0.01"
                                   min="0"
                                   value="{{ old('gc_price', $proposal->gc_price) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('gc_price') border-red-500 @enderror">
                            @error('gc_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Results -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="result_gc" class="block text-sm font-medium text-gray-700 mb-2">
                                Result GC
                            </label>
                            <select id="result_gc" 
                                    name="result_gc" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('result_gc') border-red-500 @enderror">
                                <option value="">Select result</option>
                                <option value="pending" {{ old('result_gc', $proposal->result_gc) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="win" {{ old('result_gc', $proposal->result_gc) == 'win' ? 'selected' : '' }}>Win</option>
                                <option value="loss" {{ old('result_gc', $proposal->result_gc) == 'loss' ? 'selected' : '' }}>Loss</option>
                            </select>
                            @error('result_gc')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="result_art" class="block text-sm font-medium text-gray-700 mb-2">
                                Result ART
                            </label>
                            <select id="result_art" 
                                    name="result_art" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('result_art') border-red-500 @enderror">
                                <option value="">Select result</option>
                                <option value="pending" {{ old('result_art', $proposal->result_art) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="win" {{ old('result_art', $proposal->result_art) == 'win' ? 'selected' : '' }}>Win</option>
                                <option value="loss" {{ old('result_art', $proposal->result_art) == 'loss' ? 'selected' : '' }}>Loss</option>
                            </select>
                            @error('result_art')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
