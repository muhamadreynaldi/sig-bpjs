<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest; // Gunakan Form Request yang baru dibuat
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; // Untuk mengelola file
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan form edit profil.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(), // Mengambil user yang sedang terautentikasi
        ]);
    }

    /**
     * Update profil user.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user(); // Mengambil user yang sedang terautentikasi

        // Menggunakan data yang sudah divalidasi oleh Form Request
        $validatedData = $request->validated();

        // Update nama dan email
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        // Update password jika diisi
        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        // Handle upload avatar
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Simpan avatar baru dan dapatkan path-nya
            $avatarPath = $request->file('avatar')->store('avatars', 'public'); // Simpan di storage/app/public/avatars
            $user->avatar = $avatarPath;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui!');
    }
}