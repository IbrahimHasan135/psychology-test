<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteCard;
use App\Models\SitePage;
use App\Models\SiteSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageManagementController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => SitePage::query()->withCount('sections')->orderBy('name')->get(),
        ]);
    }

    public function edit(SitePage $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page->load(['sections.cards']),
            'templates' => SiteCard::TEMPLATES,
            'imagePositions' => SiteCard::IMAGE_POSITIONS,
        ]);
    }

    public function update(Request $request, SitePage $page): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_mode' => ['required', Rule::in(['sections', 'tabs'])],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $page->update([...$data, 'is_published' => $request->boolean('is_published')]);

        return back()->with('status', 'Page setting disimpan.');
    }

    public function storeSection(Request $request, SitePage $page): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'anchor' => ['nullable', 'alpha_dash', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $page->sections()->create([
            ...$data,
            'anchor' => $data['anchor'] ?: str($data['title'])->slug()->toString(),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Section/tab ditambahkan.');
    }

    public function updateSection(Request $request, SiteSection $section): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'anchor' => ['nullable', 'alpha_dash', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $section->update([
            ...$data,
            'anchor' => $data['anchor'] ?: str($data['title'])->slug()->toString(),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Section/tab diperbarui.');
    }

    public function destroySection(SiteSection $section): RedirectResponse
    {
        $section->delete();

        return back()->with('status', 'Section dan semua card di dalamnya dihapus.');
    }

    public function storeCard(Request $request, SiteSection $section): RedirectResponse
    {
        $section->cards()->create($this->validateCard($request));

        return back()->with('status', 'Card ditambahkan.');
    }

    public function updateCard(Request $request, SiteCard $card): RedirectResponse
    {
        $card->update($this->validateCard($request));

        return back()->with('status', 'Card diperbarui.');
    }

    public function destroyCard(SiteCard $card): RedirectResponse
    {
        $card->delete();

        return back()->with('status', 'Card dihapus.');
    }

    private function validateCard(Request $request): array
    {
        $data = $request->validate([
            'template' => ['required', Rule::in(array_keys(SiteCard::TEMPLATES))],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:2000'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'image_position' => ['required', Rule::in(array_keys(SiteCard::IMAGE_POSITIONS))],
            'button_label' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [...$data, 'sort_order' => $data['sort_order'] ?? 0, 'is_active' => $request->boolean('is_active', true)];
    }
}
