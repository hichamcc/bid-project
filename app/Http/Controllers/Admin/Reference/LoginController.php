<?php

namespace App\Http\Controllers\Admin\Reference;

use App\Http\Controllers\Controller;
use App\Models\ReferenceLogin;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        $query = ReferenceLogin::ordered();

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(fn ($q) => $q->where('platform', 'like', $term)->orWhere('email', 'like', $term));
        }

        $logins = $query->get();

        return view('admin.reference.logins.index', compact('logins'));
    }

    public function create()
    {
        return view('admin.reference.logins.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = $data['sort_order'] ?? (ReferenceLogin::max('sort_order') + 1);

        ReferenceLogin::create($data);

        return redirect()->route('admin.reference.logins.index')->with('success', 'Login saved.');
    }

    public function edit(ReferenceLogin $login)
    {
        return view('admin.reference.logins.edit', compact('login'));
    }

    public function update(Request $request, ReferenceLogin $login)
    {
        $data = $this->validated($request);

        // Leave the stored password untouched if the field was left blank on edit.
        if (($data['password'] ?? '') === '' || $data['password'] === null) {
            unset($data['password']);
        }

        $login->update($data);

        return redirect()->route('admin.reference.logins.index')->with('success', 'Login updated.');
    }

    public function destroy(ReferenceLogin $login)
    {
        $login->delete();

        return redirect()->route('admin.reference.logins.index')->with('success', 'Login deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'platform'   => 'required|string|max:255',
            'email'      => 'nullable|string|max:255',
            'password'   => 'nullable|string|max:1000',
            'notes'      => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}
