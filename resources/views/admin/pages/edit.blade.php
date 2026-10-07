@extends('layouts.app', ['title' => 'Edit Page'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content page-studio">
        <div class="studio-topbar">
            <div>
                <div class="eyebrow">Page studio</div>
                <h2>{{ $page->name }}</h2>
                <p>Susun konten dari section/tab dan card. Template visual card tetap terpisah dari data konten.</p>
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
                    <label for="name">Nama Page</label>
                    <input id="name" name="name" value="{{ old('name', $page->name) }}" required>

                    <label for="display_mode">Mode Tampilan</label>
                    <select id="display_mode" name="display_mode">
                        <option value="sections" @selected($page->display_mode === 'sections')>Section scroll</option>
                        <option value="tabs" @selected($page->display_mode === 'tabs')>Tabs</option>
                    </select>

                    <label class="checkbox-row compact-check" for="is_published">
                        <input id="is_published" type="checkbox" name="is_published" value="1" @checked($page->is_published)>
                        <span>Published</span>
                    </label>

                    <button class="button button-primary full-button" type="submit">Simpan Page</button>
                </form>

                <div class="studio-divider"></div>

                <h3>Tambah Section</h3>
                <form method="POST" action="{{ route('admin.pages.sections.store', $page) }}" class="studio-form">
                    @csrf
                    <label>Judul</label>
                    <input name="title" required>
                    <label>Anchor</label>
                    <input name="anchor" placeholder="otomatis kalau kosong">
                    <label>Deskripsi</label>
                    <textarea name="description" rows="3"></textarea>
                    <label>Urutan</label>
                    <input name="sort_order" type="number" min="0" value="0">
                    <label class="checkbox-row compact-check">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Aktif</span>
                    </label>
                    <button class="button button-primary full-button" type="submit">Tambah Section</button>
                </form>
            </aside>

            <section class="studio-canvas">
                @forelse ($page->sections as $section)
                    <article class="studio-section">
                        <div class="studio-section-head">
                            <div>
                                <span class="section-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $section->title }}</h3>
                                <p>#{{ $section->anchor }} · {{ $section->cards->count() }} card · {{ $section->is_active ? 'aktif' : 'nonaktif' }}</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.sections.update', $section) }}" class="section-quick-edit">
                            @csrf
                            @method('PUT')
                            <input name="title" value="{{ $section->title }}" required aria-label="Judul section">
                            <input name="anchor" value="{{ $section->anchor }}" aria-label="Anchor section">
                            <input name="sort_order" type="number" min="0" value="{{ $section->sort_order }}" aria-label="Urutan section">
                            <label class="checkbox-row compact-check"><input type="checkbox" name="is_active" value="1" @checked($section->is_active)> <span>Aktif</span></label>
                            <textarea name="description" rows="2" aria-label="Deskripsi section">{{ $section->description }}</textarea>
                            <button class="button button-soft" type="submit">Update Section</button>
                        </form>

                        <div class="card-stack">
                            @foreach ($section->cards as $card)
                                <details class="studio-card" open>
                                    <summary>
                                        <span>
                                            <strong>{{ $card->title }}</strong>
                                            <small>{{ $templates[$card->template] ?? $card->template }} · {{ $card->is_active ? 'aktif' : 'nonaktif' }}</small>
                                        </span>
                                    </summary>
                                    <form method="POST" action="{{ route('admin.cards.update', $card) }}" class="card-editor-form">
                                        @csrf
                                        @method('PUT')
                                        @include('admin.pages.partials.card-fields', ['card' => $card, 'templates' => $templates, 'imagePositions' => $imagePositions])
                                        <div class="card-actions">
                                            <button class="button button-soft" type="submit">Update Card</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('admin.cards.destroy', $card) }}" class="card-delete-form" onsubmit="return confirm('Hapus card ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger-button" type="submit">Hapus Card</button>
                                    </form>
                                </details>
                            @endforeach
                        </div>

                        <details class="studio-card new-studio-card">
                            <summary><strong>Tambah card baru</strong><small>Feature, media split, atau compact</small></summary>
                            <form method="POST" action="{{ route('admin.sections.cards.store', $section) }}" class="card-editor-form">
                                @csrf
                                @include('admin.pages.partials.card-fields', ['card' => null, 'templates' => $templates, 'imagePositions' => $imagePositions])
                                <div class="card-actions"><button class="button button-primary" type="submit">Tambah Card</button></div>
                            </form>
                        </details>
                    </article>
                @empty
                    <section class="empty-state">Home masih kosong. Tambahkan section pertama dari panel kiri.</section>
                @endforelse
            </section>
        </section>
    </main>
</div>
@endsection
