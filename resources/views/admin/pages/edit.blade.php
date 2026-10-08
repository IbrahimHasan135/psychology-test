@extends('layouts.app', ['title' => 'Edit Page'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content page-studio">
        <div class="studio-topbar">
            <div>
                <div class="eyebrow">Page Studio</div>
                <h2>{{ $page->name }}</h2>
                <p>Build content from sections, tabs, and cards. Visual card templates stay separate from content data.</p>
            </div>
            <div class="studio-actions">
                <a class="button button-soft" href="{{ route('home') }}">Preview</a>
                <a class="button button-soft" href="{{ route('admin.pages.index') }}">Pages</a>
            </div>
        </div>

        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="notice error-notice">{{ $errors->first() }}</div>
        @endif

        <section class="studio-board">
            <aside class="studio-settings">
                <h3>Page Setting</h3>
                <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="studio-form">
                    @csrf
                    @method('PUT')
                    <label for="name">Page Name</label>
                    <input id="name" name="name" value="{{ old('name', $page->name) }}" required>

                    <label for="display_mode">Display Mode</label>
                    <select id="display_mode" name="display_mode">
                        <option value="sections" @selected($page->display_mode === 'sections')>Section scroll</option>
                        <option value="tabs" @selected($page->display_mode === 'tabs')>Tabs</option>
                    </select>

                    <label class="checkbox-row compact-check" for="is_published">
                        <input id="is_published" type="checkbox" name="is_published" value="1" @checked($page->is_published)>
                        <span>Published</span>
                    </label>

                    <button class="button button-primary full-button" type="submit">Save Page</button>
                </form>

                <div class="studio-divider"></div>

                <h3>Add Section</h3>
                <form method="POST" action="{{ route('admin.pages.sections.store', $page) }}" class="studio-form">
                    @csrf
                    <label>Title</label>
                    <input name="title" required>
                    <label>Anchor</label>
                    <input name="anchor" placeholder="auto when empty">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                    <label>Order</label>
                    <input name="sort_order" type="number" min="0" value="0">
                    <label class="checkbox-row compact-check">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Active</span>
                    </label>
                    <button class="button button-primary full-button" type="submit">Add Section</button>
                </form>
            </aside>

            <section class="studio-canvas">
                @forelse ($page->sections as $section)
                    <article class="studio-section">
                        <div class="studio-section-head">
                            <div>
                                <span class="section-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $section->title }}</h3>
                                <p>#{{ $section->anchor }} · {{ $section->cards->count() }} card · {{ $section->is_active ? 'active' : 'inactive' }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.sections.delete', $section) }}" onsubmit="return confirm('Delete this section and all cards inside it?')">
                                @csrf
                                <button class="button danger-button" type="submit">Delete Section</button>
                            </form>
                        </div>

                        <form method="POST" action="{{ route('admin.sections.update', $section) }}" class="section-quick-edit">
                            @csrf
                            @method('PUT')
                            <input name="title" value="{{ $section->title }}" required aria-label="Section title">
                            <input name="anchor" value="{{ $section->anchor }}" aria-label="Anchor section">
                            <input name="sort_order" type="number" min="0" value="{{ $section->sort_order }}" aria-label="Section order">
                            <label class="checkbox-row compact-check"><input type="checkbox" name="is_active" value="1" @checked($section->is_active)> <span>Active</span></label>
                            <textarea name="description" rows="2" aria-label="Section description">{{ $section->description }}</textarea>
                            <button class="button button-soft" type="submit">Update Section</button>
                        </form>

                        <div class="card-stack">
                            @foreach ($section->cards as $card)
                                <article class="visual-card-editor">
                                    <div class="visual-card-head">
                                        <div>
                                            <span class="card-type">{{ $templates[$card->template] ?? $card->template }}</span>
                                            <h4>{{ $card->title }}</h4>
                                            <p>{{ $card->is_active ? 'Active' : 'Inactive' }} · order {{ $card->sort_order }}</p>
                                        </div>
                                        <span class="card-delete-hint">Delete button is below the preview</span>
                                    </div>

                                    <div class="card-live-preview">
                                        @includeIf('website.card-templates.'.$card->template, ['card' => $card])
                                    </div>

                                    <form method="POST" action="{{ route('admin.cards.delete', $card) }}" class="always-delete-card" onsubmit="return confirm('Delete this card?')">
                                        @csrf
                                        <button class="button danger-button delete-card-button" type="submit">Delete Card</button>
                                    </form>

                                    <details class="card-edit-panel">
                                        <summary>Edit content</summary>
                                    <form method="POST" action="{{ route('admin.cards.update', $card) }}" class="card-editor-form">
                                        @csrf
                                        @method('PUT')
                                        @include('admin.pages.partials.card-fields', ['card' => $card, 'templates' => $templates, 'imagePositions' => $imagePositions])
                                        <div class="card-actions">
                                            <button class="button button-soft" type="submit">Update Card</button>
                                        </div>
                                    </form>
                                    </details>
                                </article>
                            @endforeach
                        </div>

                        <details class="studio-card new-studio-card" open>
                            <summary><strong>Add New Card</strong><small>Choose a card type and fill the content</small></summary>
                            <form method="POST" action="{{ route('admin.sections.cards.store', $section) }}" class="card-editor-form">
                                @csrf
                                @include('admin.pages.partials.card-fields', ['card' => null, 'templates' => $templates, 'imagePositions' => $imagePositions])
                                <div class="card-actions"><button class="button button-primary" type="submit">Add Card</button></div>
                            </form>
                        </details>
                    </article>
                @empty
                    <section class="empty-state">Home is still empty. Add the first section from the left panel.</section>
                @endforelse
            </section>
        </section>
    </main>
</div>
@endsection
