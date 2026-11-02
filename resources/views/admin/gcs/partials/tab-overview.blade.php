{{-- Overview: proposal results (MU / NON MU), GC info, project counts. --}}
@php
    $resultTables = ['art' => 'ART Result', 'gc' => 'GC Result'];
    // Clicking a number opens the Proposals tab with matching filters
    $typeFilter = ['MU' => 'mu', 'NON MU' => 'non mu', 'Total' => null];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @foreach($resultTables as $key => $title)
        <div class="border border-gray-200 rounded-lg">
            <div class="px-3 py-2 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                <h3 class="text-sm font-semibold text-gray-900">Proposals sent · {{ $title }}</h3>
            </div>
            <table class="gc-stat-table w-full">
                <thead>
                    <tr>
                        <th></th>
                        <th>Sent</th>
                        <th>Won</th>
                        <th>Lost</th>
                        <th>Pending</th>
                        <th>Win %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats[$key] as $label => $row)
                        @php
                            $base = array_filter(['type' => $typeFilter[$label]]);
                            $resultName = $key === 'art' ? 'art' : 'gc_result';
                        @endphp
                        <tr class="{{ $label === 'Total' ? 'total' : '' }}">
                            <td>{{ $label }}</td>
                            <td><a class="gc-stat-link" @click="tab = 'proposals'; gcTableFilter.set('proposals', @js($base))">{{ $row['sent'] }}</a></td>
                            <td class="text-green-700"><a class="gc-stat-link" @click="tab = 'proposals'; gcTableFilter.set('proposals', @js($base + [$resultName => 'win']))">{{ $row['won'] }}</a></td>
                            <td class="text-red-700"><a class="gc-stat-link" @click="tab = 'proposals'; gcTableFilter.set('proposals', @js($base + [$resultName => 'loss']))">{{ $row['lost'] }}</a></td>
                            <td class="text-yellow-700"><a class="gc-stat-link" @click="tab = 'proposals'; gcTableFilter.set('proposals', @js($base + [$resultName => 'pending']))">{{ $row['pending'] }}</a></td>
                            <td>{{ is_null($row['win_rate']) ? '-' : $row['win_rate'] . '%' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</div>
<p class="text-xs text-gray-500 mt-2">Win % = won ÷ (won + lost). Click a number to see those proposals.</p>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
    {{-- GC information --}}
    <div class="border border-gray-200 rounded-lg p-4 text-sm space-y-3">
        <h3 class="text-sm font-semibold text-gray-900">GC Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <div class="text-xs text-gray-500">Email</div>
                @if($gc->email)
                    <a href="mailto:{{ $gc->email }}" class="text-blue-600 hover:text-blue-800">{{ $gc->email }}</a>
                @else
                    <span class="text-gray-400">-</span>
                @endif
            </div>
            <div>
                <div class="text-xs text-gray-500">Phone</div>
                @if($gc->phone)
                    <a href="tel:{{ $gc->phone }}" class="text-blue-600 hover:text-blue-800">{{ $gc->formatted_phone }}</a>
                @else
                    <span class="text-gray-400">-</span>
                @endif
            </div>
        </div>
        @if($gc->address)
            <div>
                <div class="text-xs text-gray-500">Address</div>
                <div class="text-gray-900 whitespace-pre-line">{{ $gc->address }}</div>
            </div>
        @endif
        @if($gc->notes)
            <div>
                <div class="text-xs text-gray-500">Notes</div>
                <div class="text-gray-900 whitespace-pre-line">{{ $gc->notes }}</div>
            </div>
        @endif
        <div class="text-xs text-gray-500 border-t pt-3">
            GC #{{ $gc->id }} · Created {{ $gc->created_at->format('M d, Y') }} · Updated {{ $gc->updated_at->format('M d, Y') }}
        </div>
    </div>

    {{-- Project counts (all time, unique project numbers) --}}
    <div class="border border-gray-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Projects (all time)</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-center">
            <div class="bg-blue-50 rounded-lg p-3">
                <div class="text-xl font-bold text-blue-900">{{ $totalProjects }}</div>
                <div class="text-xs text-blue-700">Total</div>
            </div>
            <div class="bg-green-50 rounded-lg p-3">
                <div class="text-xl font-bold text-green-900">{{ $activeProjects }}</div>
                <div class="text-xs text-green-700">Active</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <div class="text-xl font-bold text-gray-900">{{ $completedProjects }}</div>
                <div class="text-xs text-gray-700">Completed</div>
            </div>
            <div class="bg-indigo-50 rounded-lg p-3">
                <div class="text-xl font-bold text-indigo-900">{{ $muProjects }}</div>
                <div class="text-xs text-indigo-700">MU</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-3">
                <div class="text-xl font-bold text-purple-900">{{ $nonMuProjects }}</div>
                <div class="text-xs text-purple-700">NON MU</div>
            </div>
        </div>

        <div class="mt-4 pt-3 border-t flex flex-wrap items-center gap-3 text-sm">
            @if($totalProjects === 0)
                <form method="POST" action="{{ route('admin.gcs.destroy', $gc) }}"
                      onsubmit="return confirm('Are you sure you want to delete this GC? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-semibold py-1.5 px-3 rounded">Delete GC</button>
                </form>
            @else
                <span class="text-gray-500 text-xs">This GC can't be deleted: {{ $totalProjects }} project(s) use it.</span>
            @endif
        </div>
    </div>
</div>
