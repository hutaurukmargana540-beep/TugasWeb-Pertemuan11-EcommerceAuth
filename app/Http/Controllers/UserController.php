<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Khusus admin (route diproteksi role:admin): kelola role pengguna. */
class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        // Cegah admin menurunkan role akunnya sendiri (bisa terkunci dari sistem).
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat mengubah role akun sendiri.');
        }

        $user->forceFill(['role' => $data['role']])->save(); // role tidak fillable => forceFill disengaja

        return back()->with('success', "Role {$user->name} diubah menjadi {$data['role']}.");
    }
}
