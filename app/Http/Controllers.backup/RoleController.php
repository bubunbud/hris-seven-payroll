<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Role::with('permissions')->orderBy('name');

        // Filter by status aktif jika ada
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        // Search by name atau description
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $roles = $query->paginate(20);

        return view('settings.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil semua permissions yang dikelompokkan berdasarkan module
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

        return view('settings.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'slug' => 'nullable|string|max:100|unique:roles,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => 'Nama role harus diisi',
            'name.unique' => 'Nama role sudah digunakan',
            'slug.unique' => 'Slug role sudah digunakan',
            'permissions.*.exists' => 'Permission tidak valid',
        ]);

        // Generate slug jika tidak diisi
        $slug = $request->slug ?: Str::slug($request->name);

        $role = Role::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Assign permissions jika ada
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::with(['permissions', 'users'])->findOrFail($id);
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

        return view('settings.roles.show', compact('role', 'permissions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

        return view('settings.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles')->ignore($role->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('roles')->ignore($role->id),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => 'Nama role harus diisi',
            'name.unique' => 'Nama role sudah digunakan',
            'slug.unique' => 'Slug role sudah digunakan',
            'permissions.*.exists' => 'Permission tidak valid',
        ]);

        // Generate slug jika tidak diisi
        $slug = $request->slug ?: Str::slug($request->name);

        $role->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Sync permissions
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        // Prevent delete if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Tidak dapat menghapus role yang masih memiliki user. Hapus user terlebih dahulu.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil dihapus');
    }
}
