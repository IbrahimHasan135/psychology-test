<?php

namespace App\Core\PageBuilder;

use App\Models\SitePage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Core\Tenancy\TenantContext;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PageBuilderService
{
    public function editorState(SitePage $activePage, bool $public = false): array
    {
        if ($public) {
            $cacheKey = 'novabase:public-builder-state:'.($activePage->tenant_id ?? 0).':'.$activePage->id.':'.($activePage->updated_at?->getTimestamp() ?? 0);

            return Cache::remember($cacheKey, now()->addMinutes(5), fn (): array => $this->buildEditorState($activePage, true));
        }

        return $this->buildEditorState($activePage, false);
    }

    private function buildEditorState(SitePage $activePage, bool $public): array
    {
        $pageQuery = SitePage::query()
            ->orderByRaw('CASE WHEN slug = ? THEN 0 ELSE 1 END', ['home'])
            ->orderBy('id')
            ->with('blocks');
        if ($public) {
            $pageQuery->where('is_published', true);
        }
        $pages = $pageQuery->get();
        $statePages = [];
        $template = 'template-studio';
        $tenantPrefix = app(TenantContext::class)->isScopedRequest()
            ? '/'.app(TenantContext::class)->tenant()->slug
            : '';

        foreach ($pages as $page) {
            $pageState = $this->state($page);
            $template = $page->template_id ?: $template;
            $statePages[] = [
                'id' => (string) $page->id,
                'label' => $page->name,
                'path' => $page->slug === 'home' ? ($tenantPrefix ?: '/') : $tenantPrefix.'/'.$page->slug,
                'blocks' => $pageState['blocks'],
                'version' => $pageState['page']['builderVersion'],
            ];
        }

        return [
            'template' => $template,
            'activePageId' => (string) $activePage->id,
            'selectedBlockId' => null,
            'mode' => 'edit',
            'pages' => $statePages,
        ];
    }

    public function state(SitePage $page): array
    {
        if (! $page->builder_initialized) {
            DB::transaction(function () use ($page): void {
                $lockedPage = SitePage::query()->lockForUpdate()->findOrFail($page->id);
                if (! $lockedPage->builder_initialized) {
                    $this->importLegacyContent($lockedPage);
                    $lockedPage->update(['builder_initialized' => true]);
                }
            });
            $page->refresh();
        }

        return [
            'page' => ['id' => $page->id, 'name' => $page->name, 'slug' => $page->slug, 'displayMode' => $page->display_mode, 'published' => $page->is_published, 'builderVersion' => $page->builder_version ?: 1],
            'blocks' => $page->loadMissing('blocks')->blocks->map(fn ($block) => [
                'id' => $block->block_uid,
                'type' => $block->type,
                'navEnabled' => $block->nav_enabled,
                'navLabel' => $block->nav_label,
                'data' => $block->data_json,
            ])->values()->all(),
        ];
    }

    public function save(SitePage $page, array $state): void
    {
        DB::transaction(function () use ($page, $state): void {
            $page = SitePage::query()->lockForUpdate()->findOrFail($page->id);
            $this->assertVersion($page, $state['page']['builderVersion'] ?? null);
            $page->update([
                'name' => $state['page']['name'],
                'display_mode' => $state['page']['displayMode'],
                'is_published' => $state['page']['published'],
                'builder_initialized' => true,
                'builder_version' => ($page->builder_version ?: 1) + 1,
            ]);

            $page->blocks()->delete();
            foreach ($state['blocks'] as $order => $block) {
                $page->blocks()->create([
                    'block_uid' => $block['id'], 'type' => $block['type'],
                    'nav_enabled' => $block['navEnabled'], 'nav_label' => $block['navLabel'] ?: null,
                    'sort_order' => $order, 'data_json' => $block['data'],
                ]);
            }
        });
    }

    public function saveSiteState(array $state): array
    {
        $idMap = [];
        DB::transaction(function () use ($state, &$idMap): void {
            foreach ($state['pages'] as $pageState) {
                $page = is_numeric($pageState['id'])
                    ? SitePage::query()->lockForUpdate()->find($pageState['id'])
                    : null;
                $page ??= new SitePage();

                $this->assertVersion($page, $pageState['version'] ?? null);

                $slug = $this->normalizePageSlug($pageState['path']);
                $page->fill([
                    'name' => $pageState['label'],
                    'slug' => $slug === '' ? 'home' : $slug,
                    'display_mode' => $page->display_mode ?: 'sections',
                    'is_published' => $page->exists ? $page->is_published : true,
                    'builder_initialized' => true,
                    'builder_version' => $page->exists ? (($page->builder_version ?: 1) + 1) : 1,
                    'template_id' => $state['template'],
                ]);
                $page->save();
                $idMap[(string) $pageState['id']] = (string) $page->id;

                $page->blocks()->delete();
                foreach ($pageState['blocks'] as $order => $block) {
                    $page->blocks()->create([
                        'block_uid' => $block['id'],
                        'type' => $block['type'],
                        'nav_enabled' => (bool) $block['navEnabled'],
                        'nav_label' => $block['navLabel'] ?: null,
                        'sort_order' => $order,
                        'data_json' => $block['data'],
                    ]);
                }
            }
        });

        $activeId = $idMap[(string) $state['activePageId']] ?? array_values($idMap)[0] ?? null;
        $activePage = SitePage::query()->findOrFail($activeId);

        return $this->editorState($activePage);
    }

    private function assertVersion(SitePage $page, mixed $expectedVersion): void
    {
        if ($page->exists && $expectedVersion !== null && (int) $expectedVersion !== (int) ($page->builder_version ?: 1)) {
            throw new ConflictHttpException('This page was changed by another administrator. Reload before saving.');
        }
    }

    private function normalizePageSlug(string $path): string
    {
        $slug = trim($path, '/');
        $context = app(TenantContext::class);
        $tenantSlug = $context->isScopedRequest() ? $context->tenant()?->slug : null;

        if ($tenantSlug && ($slug === $tenantSlug || str_starts_with($slug, $tenantSlug.'/'))) {
            $slug = ltrim(substr($slug, strlen($tenantSlug)), '/');
        }

        return $slug === '' ? 'home' : $slug;
    }

    private function importLegacyContent(SitePage $page): void
    {
        $page->loadMissing('sections.cards');
        $blocks = [];
        foreach ($page->sections as $section) {
            $items = $section->cards->map(fn ($card) => [
                'icon' => 'bi-check2-circle',
                'title' => $card->title,
                'text' => $card->body ?? '',
                'link' => $card->button_url ?? '#',
            ])->values()->all();
            $data = ['title' => $section->title, 'text' => $section->description ?? '', 'items' => $items];
            $blocks[] = [
                'block_uid' => (string) Str::uuid(), 'type' => 'cards', 'nav_enabled' => true,
                'nav_label' => $section->title, 'sort_order' => count($blocks), 'data_json' => $data,
            ];
        }

        if ($blocks !== []) {
            $page->blocks()->createMany($blocks);
        } else {
            $page->blocks()->create([
                'block_uid' => (string) Str::uuid(),
                'type' => 'hero',
                'nav_enabled' => true,
                'nav_label' => $page->name,
                'sort_order' => 0,
                'data_json' => BlockRegistry::definitions()['hero']['initial'],
            ]);
        }
    }
}
