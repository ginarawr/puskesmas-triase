<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [], ['login' => 'NIP atau email']);

        $login = trim($data['login']);
        $user = User::where('nip', $login)
            ->orWhere('email', $login)
            ->orWhere('phone', $login)
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()
                ->withErrors(['login' => 'NIP/email atau kata sandi tidak cocok.'])
                ->onlyInput('login');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect($user->homeUrl());
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $contact = trim((string) $request->input('contact'));
        $isEmail = str_contains($contact, '@');

        $data = $request->validate([
            'role' => ['required', 'in:perawat,dokter'],
            'name' => ['required', 'string', 'max:120'],
            'nip' => ['required', 'string', 'max:40', 'unique:users,nip'],
            'contact' => $isEmail
                ? ['required', 'email', 'max:120', 'unique:users,email']
                : ['required', 'regex:/^(\+62|62|0)8\d{7,12}$/', 'unique:users,phone'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
        ], [
            'contact.regex' => 'Isi email yang valid atau nomor WhatsApp (contoh 08123456789).',
        ], [
            'role' => 'peran',
            'name' => 'nama lengkap',
            'nip' => 'nomor NIP/STR',
            'contact' => 'email / nomor WhatsApp',
            'password' => 'kata sandi',
        ]);

        $user = User::create([
            'role' => $data['role'],
            'name' => $data['name'],
            'nip' => $data['nip'],
            'email' => $isEmail ? $contact : null,
            'phone' => $isEmail ? null : $contact,
            'password' => $data['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($user->homeUrl())
            ->with('status', 'Akun berhasil dibuat. Selamat datang, ' . $user->name . '.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda sudah keluar.');
    }
}
