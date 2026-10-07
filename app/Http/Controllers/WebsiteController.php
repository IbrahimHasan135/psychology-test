<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function home(): View
    {
        return view('website.home');
    }
}