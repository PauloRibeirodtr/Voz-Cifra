<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PainelMembroController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $usuario = Auth::user();
        $igreja = $usuario?->igreja;

        return view('member.dashboard', compact('usuario', 'igreja'));
    }
}
