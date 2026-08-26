<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user()->load('rol');

        return view('panel', compact('usuario'));
    }
}
