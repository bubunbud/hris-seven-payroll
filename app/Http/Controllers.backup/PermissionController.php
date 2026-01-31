<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Permission::orderBy('module')->orderBy('name');

        // Filter by module jika ada
        if ($request->has('module') && $request->module) {
            $query->where('module', $request->module);
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

        $permissions = $query->paginate(20);

        // Ambil semua module untuk filter
        $modules = Permission::distinct()->pluck('module')->filter()->sort()->values();

        return view('settings.permissions.index', compact('permissions', 'modules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name',
            'slug' => 'nullable|string|max:100|unique:permissions,slug',
            'description' => 'nullable|string',
            'module' => 'nullable|string|max:50',
        ], [
            'name.required' => 'Nama permission harus diisi',
            'name.unique' => 'Nama permission sudah digunakan',
            'slug.unique' => 'Slug permission sudah digunakan',
        ]);

        // Generate slug jika tidak diisi
        $slug = $request->slug ?: Str::slug($request->name);

        Permission::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'module' => $request->module,
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $permission = Permission::with('roles')->findOrFail($id);
        return view('settings.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $permission = Permission::findOrFail($id);
        return view('settings.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('permissions')->ignore($permission->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('permissions')->ignore($permission->id),
            ],
            'description' => 'nullable|string',
            'module' => 'nullable|string|max:50',
        ], [
            'name.required' => 'Nama permission harus diisi',
            'name.unique' => 'Nama permission sudah digunakan',
            'slug.unique' => 'Slug permission sudah digunakan',
        ]);

        // Generate slug jika tidak diisi
        $slug = $request->slug ?: Str::slug($request->name);

        $permission->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'module' => $request->module,
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permission = Permission::findOrFail($id);

        // Prevent delete if permission has roles
        if ($permission->roles()->count() > 0) {
            return redirect()->route('permissions.index')
                ->with('error', 'Tidak dapat menghapus permission yang masih digunakan oleh role. Hapus dari role terlebih dahulu.');
        }

        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil dihapus');
    }
}
