<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DiskonController extends Controller
{
    // SK2 — HTML Table
    public function html()
    {
        return view('pages.diskon.html');
    }

    // SK2 — DataTables
    public function datatables()
    {
        return view('pages.diskon.datatables');
    }
}
