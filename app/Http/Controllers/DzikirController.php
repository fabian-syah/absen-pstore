<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DzikirController extends Controller
{
    public function index()
    {
        return view('dzikir.index');
    }
}
