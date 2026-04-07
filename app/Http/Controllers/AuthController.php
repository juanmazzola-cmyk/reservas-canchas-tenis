<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $user = \App\Models\User::where('dni', trim($request->dni))->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['dni' => 'DNI o contraseña incorrectos.'])->withInput(['dni' => $request->dni]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        if (Auth::user()->must_change_password) {
            session()->flash('recordar_cambiar_password', true);
        }
        return redirect()->route('agenda');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            \App\Models\Reserva::where('estado', 'DRAFT')
                ->where('creador_id', Auth::id())
                ->update(['estado' => 'PENDING']);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
