<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function home(): View
    {
        $page = SitePage::query()
            ->where('slug', 'home')
            ->where('is_published', true)
            ->with(['activeSections.activeCards'])
            ->first();

        return view('website.home', [
            'page' => $page,
        ]);
    }
}