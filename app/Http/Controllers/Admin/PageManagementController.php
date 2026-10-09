<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteCard;
use App\Models\SitePage;
use App\Models\SiteSection;
use App\Core\PageBuilder\BlockRegistry;
use App\Core\PageBuilder\PageBuilderService;
use App\Core\PageBuilder\PageBuilderRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageManagementController extends Controller
{
    public function index(PageBuilderService $builder, PageBuilderRegistry $registry): View
    {
        $page = SitePage::query()->where('slug', 'home')->firstOrFail();

        return $this->editorView($page, $builder, $registry);
    }

    public function edit(SitePage $page, PageBuilderService $builder, PageBuilderRegistry $registry): View
    {
        return $this->editorView($page, $builder, $registry);
    }

    private function editorView(SitePage $page, PageBuilderService $builder, PageBuilderRegistry $registry): View
    {
        return view('admin.pages.editor', [
            'page' => $page,
            'builderState' => $builder->editorState($page),
            'blockDefinitions' => $registry->definitions(request()->user()),
            'designTemplates' => BlockRegistry::templates(),
        ]);
    }

    public function saveSiteBuilder(Request $request, PageBuilderService $builder, PageBuilderRegistry $registry): JsonResponse
    {
        $validated = $request->validate([
            'template' => ['required', Rule::in(array_keys(BlockRegistry::templates()))],
            'activePageId' => ['required', 'string', 'max:80'],
            'pages' => ['required', 'array', 'min:1', 'max:30'],
            'pages.*.id' => ['required', 'string', 'alpha_dash', 'max:80'],
            'pages.*.label' => ['required', 'string', 'max:120'],
            'pages.*.path' => ['required', 'string', 'max:121', 'regex:/^\/(?:[A-Za-z0-9][A-Za-z0-9-]*)?$/', 'distinct'],
            'pages.*.blocks' => ['present', 'array', 'max:100'],
            'pages.*.blocks.*.id' => ['required', 'string', 'alpha_dash', 'max:80', 'distinct'],
            'pages.*.blocks.*.type' => ['required', Rule::in(array_keys($registry->definitions($request->user())))],
            'pages.*.blocks.*.navEnabled' => ['required', 'boolean'],
            'pages.*.blocks.*.navLabel' => ['nullable', 'string', 'max:120'],
            'pages.*.blocks.*.data' => ['required', 'array'],
        ]);

        foreach ($validated['pages'] as $pageState) {
            foreach ($pageState['blocks'] as $block) {
                $this->validateBlockData($block['type'], $block['data'], $registry, $request->user());
            }
        }

        return response()->json([
            'saved' => true,
            'state' => $builder->saveSiteState($validated),
            'savedAt' => now()->toIso8601String(),
        ]);
    }

    public function saveBuilder(Request $request, SitePage $page, PageBuilderService $builder, PageBuilderRegistry $registry): JsonResponse
    {
        $validated = $request->validate([
            'page.name' => ['required', 'string', 'max:255'],
            'page.displayMode' => ['required', Rule::in(['sections', 'tabs'])],
            'page.published' => ['required', 'boolean'],
            'blocks' => ['present', 'array', 'max:100'],
            'blocks.*.id' => ['required', 'string', 'max:80', 'distinct'],
            'blocks.*.type' => ['required', Rule::in(array_keys($registry->definitions($request->user())))],
            'blocks.*.navEnabled' => ['required', 'boolean'],
            'blocks.*.navLabel' => ['nullable', 'string', 'max:120'],
            'blocks.*.data' => ['required', 'array'],
        ]);

        foreach ($validated['blocks'] as $block) {
            $this->validateBlockData($block['type'], $block['data'], $registry, $request->user());
        }

        $builder->save($page, $validated);

        return response()->json(['saved' => true, 'savedAt' => now()->toIso8601String()]);
    }

    private function validateBlockData(string $type, array $data, PageBuilderRegistry $registry, ?\App\Models\User $user): void
    {
        $defaults = $registry->defaults($type, $user);
        $rules = [];
        foreach ($defaults as $key => $default) {
            $maxLength = preg_match('/image/i', $key) ? 5_500_000 : 5000;
            $rules[$key] = is_array($default) ? ['nullable', 'array', 'max:100'] : ['nullable', 'string', 'max:'.$maxLength];
        }
        validator($data, $rules)->validate();

        foreach ($data as $key => $value) {
            if (! array_key_exists($key, $defaults)) {
                abort(422, 'Unknown field for page block.');
            }
            if (! is_array($defaults[$key])) {
                $this->validateSafeUrl($key, $value);
                continue;
            }
            if ($value === null) {
                continue;
            }
            foreach ($value as $item) {
                if ($key === 'images') {
                    validator(['value' => $item], ['value' => ['nullable', 'string', 'max:5500000']])->validate();
                    $this->validateSafeUrl('image', $item);
                    continue;
                }
                if (! is_array($item)) {
                    abort(422, 'Invalid repeatable block item.');
                }
                foreach ($item as $itemKey => $itemValue) {
                    if (! array_key_exists($itemKey, $defaults[$key][0] ?? [])) {
                        abort(422, 'Unknown repeatable block field.');
                    }
                    $maxLength = preg_match('/image/i', $itemKey) ? 5_500_000 : 5000;
                    validator(['value' => $itemValue], ['value' => ['nullable', 'string', 'max:'.$maxLength]])->validate();
                    $this->validateSafeUrl($itemKey, $itemValue);
                }
            }
        }
    }

    private function validateSafeUrl(string $field, mixed $value): void
    {
        if (is_string($value) && str_starts_with($value, 'data:image/')) {
            if (preg_match('/image/i', $field) && preg_match('/^data:image\/(?:png|jpe?g|gif|webp);base64,[A-Za-z0-9+\/=]+$/', $value)) {
                return;
            }
            abort(422, 'Only supported image data can be embedded.');
        }
        if ($value === null || $value === '' || ! preg_match('/url|image|link/i', $field)) {
            return;
        }
        if ((str_starts_with($value, '/') && ! str_starts_with($value, '//')) || str_starts_with($value, '#')) {
            if (preg_match('/^[A-Za-z0-9._~\/%?#=&+-]+$/', $value)) {
                return;
            }
            abort(422, 'This relative URL contains unsupported characters.');
        }
        $isImage = preg_match('/image/i', $field) === 1;
        if ($isImage && strpbrk($value, "'\"()\\\r\n") !== false) {
            abort(422, 'This image URL contains unsupported characters.');
        }
        if (! $isImage && preg_match('/^[A-Za-z0-9._~\/%?#=&+-]+$/', $value)) {
            return;
        }
        $scheme = parse_url($value, PHP_URL_SCHEME);
        if ($scheme === null) abort(422, 'This URL must use a supported local or web address.');
        $allowedSchemes = preg_match('/buttonUrl|link/i', $field) ? ['http', 'https', 'mailto', 'tel'] : ['http', 'https'];
        if (! in_array(strtolower((string) $scheme), $allowedSchemes, true)) {
            abort(422, 'This URL scheme is not allowed for this field.');
        }
        if (in_array(strtolower((string) $scheme), ['http', 'https'], true) && filter_var($value, FILTER_VALIDATE_URL) === false) {
            abort(422, 'This web URL is invalid.');
        }
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
