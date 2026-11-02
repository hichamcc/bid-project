{{-- Create form as shown in the list page's popup. Posts normally so attachments upload. --}}
<form method="POST" action="{{ route('admin.proposals.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="_modal" value="create">
    {{-- Where to go after a successful save: 'grid' or the list (empty). Set by the page's JS. --}}
    <input type="hidden" name="_return" value="{{ old('_return') }}">

    @include('admin.proposals.partials.create-fields')

    <div class="flex justify-end space-x-4">
        <button type="button" onclick="pnForm.close()"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
            Cancel
        </button>
        <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Create Proposal
        </button>
    </div>
</form>
