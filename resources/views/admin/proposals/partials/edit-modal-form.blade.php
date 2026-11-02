{{-- Edit form as shown in the list page's popup. Posts normally so attachments upload. --}}
<form method="POST" action="{{ route('admin.proposals.update', $proposal) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" name="_modal" value="edit">
    {{-- Where to go after a successful save: 'grid' or the list (empty). Set by the page's JS. --}}
    <input type="hidden" name="_return" value="{{ old('_return') }}">
    <input type="hidden" name="_proposal_id" value="{{ $proposal->id }}">

    @include('admin.proposals.partials.edit-fields')

    <div class="flex justify-end space-x-4">
        <button type="button" onclick="pnForm.close()"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
            Cancel
        </button>
        <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Update Proposal
        </button>
    </div>
</form>
