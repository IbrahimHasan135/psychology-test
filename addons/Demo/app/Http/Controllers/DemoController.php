<?php

namespace Addons\Demo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DemoController extends Controller
{
    public function __invoke(): View
    {
        return view('demo::index');
    }
}
