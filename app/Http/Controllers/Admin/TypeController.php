<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TypeController extends Controller
{
    public function index(Request $request)
    {
        $query = Type::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by active status
        if ($request->filled('active_filter')) {
            $query->where('is_active', $request->active_filter === 'active');
        }

        $types = $query->ordered()->paginate(15);

        return view('admin.types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:types,name',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Auto-assign sort order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Type::max('sort_order') + 1;
        }

        Type::create($validated);

        return redirect()->route('admin.types.index')
            ->with('success', 'Type created successfully.');
    }

    public function show(Type $type)
    {
        $type->load('projects');
        
        return view('admin.types.show', compact('type'));
    }

    public function edit(Type $type)
    {
        return view('admin.types.edit', compact('type'));
    }

    public function update(Request $request, Type $type)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('types')->ignore($type->id)],
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $type->update($validated);

        return redirect()->route('admin.types.index')
            ->with('success', 'Type updated successfully.');
    }

    public function destroy(Type $type)
    {
        // Check if type is being used by projects
        $projectCount = $type->projects()->count();
        
        if ($projectCount > 0) {
            return redirect()->route('admin.types.index')
                ->with('error', "Cannot delete type '{$type->name}' because it is being used by {$projectCount} project(s).");
        }

        $type->delete();

        return redirect()->route('admin.types.index')
            ->with('success', 'Type deleted successfully.');
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'types' => 'required|array',
            'types.*.id' => 'required|exists:types,id',
            'types.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['types'] as $typeData) {
            Type::where('id', $typeData['id'])
                  ->update(['sort_order' => $typeData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}