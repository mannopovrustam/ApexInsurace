<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    // controller resource methods laravel

    public function index()
    {
        return 'client index';
    }

    public function create()
    {
        return view('view.client.create');
    }

}
