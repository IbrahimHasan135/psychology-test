<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\SitePage;
use App\Models\User;
use App\Core\PageBuilder\BlockRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_page_management(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.pages.index'))
            ->assertOk()
            ->assertSee('Web Editor')
            ->assertSee('Demo Promo Card');
    }

    public function test_admin_can_add_section_and_card_to_home(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $page = SitePage::query()->where('slug', 'home')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.pages.sections.store', $page), [
                'title' => 'Intro',
                'anchor' => 'intro',
                'description' => 'Awal konten website.',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $section = $page->sections()->where('anchor', 'intro')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.sections.cards.store', $section), [
                'template' => 'feature',
                'title' => 'Card pertama',
                'body' => 'Konten card modular.',
                'image_position' => 'left',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Intro')
            ->assertSee('Card pertama');
    }

    public function test_admin_can_delete_card(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $page = SitePage::query()->where('slug', 'home')->firstOrFail();
        $section = $page->sections()->create([
            'title' => 'Intro',
            'anchor' => 'intro',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $card = $section->cards()->create([
            'template' => 'feature',
            'title' => 'Card hapus',
            'image_position' => 'left',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.cards.delete', $card))
            ->assertRedirect();

        $this->assertDatabaseMissing('site_cards', ['id' => $card->id]);
    }

    public function test_admin_can_delete_section_with_cards(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $page = SitePage::query()->where('slug', 'home')->firstOrFail();
        $section = $page->sections()->create([
            'title' => 'Section hapus',
            'anchor' => 'section-hapus',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $card = $section->cards()->create([
            'template' => 'feature',
            'title' => 'Card ikut hapus',
            'image_position' => 'left',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.sections.delete', $section))
            ->assertRedirect();

        $this->assertDatabaseMissing('site_sections', ['id' => $section->id]);
        $this->assertDatabaseMissing('site_cards', ['id' => $card->id]);
    }

    public function test_stale_editor_save_is_rejected(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $page = SitePage::query()->where('slug', 'home')->firstOrFail();
        $payload = [
            'template' => 'template-studio',
            'activePageId' => (string) $page->id,
            'pages' => [[
                'id' => (string) $page->id,
                'label' => 'Home',
                'path' => '/',
                'version' => 1,
                'blocks' => [[
                    'id' => 'home-hero',
                    'type' => 'hero',
                    'navEnabled' => true,
                    'navLabel' => 'Home',
                    'data' => BlockRegistry::definitions()['hero']['initial'],
                ]],
            ]],
        ];

        $this->actingAs($admin)
            ->postJson(route('admin.pages.builder.site-save'), $payload)
            ->assertOk();

        $this->actingAs($admin)
            ->postJson(route('admin.pages.builder.site-save'), $payload)
            ->assertStatus(409)
            ->assertJsonFragment(['message' => 'This page was changed by another administrator. Reload before saving.']);
    }
}
