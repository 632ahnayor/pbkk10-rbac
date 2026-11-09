<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    public function __invoke(Request $request, string $account): RedirectResponse
    {
        abort_unless(app()->environment('local'), 404);

        $demoAccounts = [
            'admin' => 'admin@example.com',
            'staf' => 'staf@example.com',
            'andi' => 'andi@example.com',
            'budi' => 'budi@example.com',
            'asdos' => 'asdos@example.com',
            'dosen' => 'dosen@example.com',
            'week6-mahasiswa-01' => 'week6.mahasiswa.01@student.its.ac.id',
        ];

        abort_unless(isset($demoAccounts[$account]), 404);

        $user = User::where('email', $demoAccounts[$account])->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route($user->homeRoute(), absolute: false));
    }
}
