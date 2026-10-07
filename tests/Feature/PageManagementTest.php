<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\SitePage;
use App\Models\User;
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
            ->assertSee('Page Management');
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
}
