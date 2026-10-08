<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use App\Core\PageBuilder\BlockRegistry;
use App\Core\PageBuilder\PageBuilderService;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function home(PageBuilderService $builder): View
    {
        $page = SitePage::query()
            ->where('slug', 'home')
            ->where('is_published', true)
            ->with(['activeSections.activeCards', 'blocks'])
            ->first();

        $builderState = $page && ($page->builder_initialized || $page->blocks->isNotEmpty())
            ? $builder->editorState($page, true)
            : null;

        return view('website.home', [
            'page' => $page,
            'builderState' => $builderState,
            'designTemplates' => BlockRegistry::templates(),
        ]);
    }

    public function page(SitePage $page, PageBuilderService $builder): View
    {
        abort_unless($page->is_published, 404);

        return view('website.home', [
            'page' => $page->load(['activeSections.activeCards', 'blocks']),
            'builderState' => $builder->editorState($page, true),
            'designTemplates' => BlockRegistry::templates(),
        ]);
    }
}
