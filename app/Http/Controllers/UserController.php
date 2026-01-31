<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with(['karyawan', 'roles'])->orderBy('created_at', 'desc');

        // Ambil semua role aktif untuk filter
        $availableRoles = Role::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        // Filter by role jika ada
        if ($request->filled('role_id')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role_id);
            });
        }

        // Filter by status aktif jika ada
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        // Search by name atau email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        return view('settings.users.index', [
            'users' => $users,
            'availableRoles' => $availableRoles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil semua karyawan aktif untuk dropdown
        $karyawans = Karyawan::where('vcAktif', '1')
            ->orderBy('Nama')
            ->get(['Nik', 'Nama']);

        // Ambil semua role aktif untuk dropdown
        $roles = Role::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return view('settings.users.create', compact('karyawans', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'nik' => 'nullable|string|max:8|exists:m_karyawan,Nik',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role_id.required' => 'Role harus dipilih',
            'role_id.exists' => 'Role tidak valid',
            'nik.exists' => 'NIK tidak ditemukan di master karyawan',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $role = Role::findOrFail($request->role_id);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role->slug,
            'nik' => $request->nik ?: null,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Assign role dari relasi many-to-many
        $user->roles()->attach($request->role_id);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('karyawan')->findOrFail($id);
        $user->loadMissing('roles');
        return view('settings.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::with('roles')->findOrFail($id);

        // Ambil semua karyawan aktif untuk dropdown
        $karyawans = Karyawan::where('vcAktif', '1')
            ->orderBy('Nama')
            ->get(['Nik', 'Nama']);

        // Ambil semua role aktif untuk dropdown
        $roles = Role::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return view('settings.users.edit', compact('user', 'karyawans', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'nik' => 'nullable|string|max:8|exists:m_karyawan,Nik',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role_id.required' => 'Role harus dipilih',
            'role_id.exists' => 'Role tidak valid',
            'nik.exists' => 'NIK tidak ditemukan di master karyawan',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $role = Role::findOrFail($request->role_id);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $role->slug,
            'nik' => $request->nik ?: null,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        // Update password hanya jika diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sync role dari relasi many-to-many
        $user->roles()->sync([$request->role_id]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Prevent delete admin user
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus user admin terakhir');
        }

        // Prevent delete own account
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus akun sendiri');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus');
    }
}
