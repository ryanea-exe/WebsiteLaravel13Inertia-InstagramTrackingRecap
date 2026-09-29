<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Seksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('seksi');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('seksi_id')) {
            $query->where('seksi_id', $request->input('seksi_id'));
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status', 'seksi_id']),
            'seksis' => Seksi::orderBy('nama')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create', [
            'seksis' => Seksi::orderBy('nama')->get(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $validated['photo'] = $path;
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user->only(['id', 'name', 'email', 'photo', 'role', 'status', 'seksi_id']),
            'seksis' => Seksi::orderBy('nama')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();
        $currentUser = $request->user();

        // Self-protection checks
        if ($user->id === $currentUser->id) {
            if ($validated['role'] === 'Staff' && $user->role === 'Administrator') {
                throw ValidationException::withMessages(['role' => 'Anda tidak dapat mengubah role Anda sendiri menjadi Staff.']);
            }
            if ($validated['status'] === 'Tidak Aktif' && $user->status === 'Aktif') {
                throw ValidationException::withMessages(['status' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
            }
        }

        DB::beginTransaction();

        try {
            // Minimum active administrator protection
            if ($user->role === 'Administrator' && $user->status === 'Aktif' && 
                ($validated['role'] === 'Staff' || $validated['status'] === 'Tidak Aktif')) {
                
                $query = User::where('role', 'Administrator')
                    ->where('status', 'Aktif')
                    ->where('id', '!=', $user->id);
                
                if (DB::getDriverName() !== 'sqlite') {
                    $query->lockForUpdate();
                }
                
                $activeAdminsCount = $query->count();

                if ($activeAdminsCount < 1) {
                    throw ValidationException::withMessages([
                        'role' => 'Sistem harus memiliki minimal 1 Administrator Aktif.',
                    ]);
                }
            }

            if ($request->hasFile('photo')) {
                if ($user->photo) {
                    Storage::disk('public')->delete($user->photo);
                }
                $path = $request->file('photo')->store('photos', 'public');
                $validated['photo'] = $path;
            } else {
                unset($validated['photo']);
            }

            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }
}
