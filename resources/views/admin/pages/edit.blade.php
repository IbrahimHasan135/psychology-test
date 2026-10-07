@extends('layouts.app', ['title' => 'Edit Page'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        <div class="split">
            <div>
                <div class="eyebrow">Page builder</div>
                <h2>{{ $page->name }}</h2>
                <p>Konten website diatur sebagai data: section/tab, card, template card, posisi gambar, dan teks.</p>
            </div>
            <a class="button button-soft" href="{{ route('home') }}">Preview</a>
        </div>

        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <section class="admin-panel">
            <h3>Page Setting</h3>
            <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="form-grid">
                @csrf
                @method('PUT')
                <div>
                    <label for="name">Nama Page</label>
                    <input id="name" name="name" value="{{ old('name', $page->name) }}" required>
                </div>
                <div>
                    <label for="display_mode">Mode Tampilan</label>
                    <select id="display_mode" name="display_mode">
                        <option value="sections" @selected($page->display_mode === 'sections')>Section scroll</option>
                        <option value="tabs" @selected($page->display_mode === 'tabs')>Tabs</option>
                    </select>
                </div>
                <label class="checkbox-row compact-check" for="is_published">
                    <input id="is_published" type="checkbox" name="is_published" value="1" @checked($page->is_published)>
                    <span>Published</span>
                </label>
                <div class="form-actions"><button class="button button-primary" type="submit">Simpan</button></div>
            </form>
        </section>

        <section class="admin-panel">
            <h3>Tambah Section/Tab</h3>
            <form method="POST" action="{{ route('admin.pages.sections.store', $page) }}" class="form-grid">
                @csrf
                <div>
                    <label>Judul</label>
                    <input name="title" required>
                </div>
                <div>
                    <label>Anchor</label>
                    <input name="anchor" placeholder="otomatis kalau kosong">
                </div>
                <div class="form-wide">
                    <label>Deskripsi</label>
                    <textarea name="description" rows="2"></textarea>
                </div>
                <div>
                    <label>Urutan</label>
                    <input name="sort_order" type="number" min="0" value="0">
                </div>
                <label class="checkbox-row compact-check">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span>Aktif</span>
                </label>
                <div class="form-actions"><button class="button button-primary" type="submit">Tambah Section</button></div>
            </form>
        </section>

        @forelse ($page->sections as $section)
            <section class="admin-panel">
                <div class="split compact-split">
                    <div>
                        <h3>{{ $section->title }}</h3>
                        <p class="muted">#{{ $section->anchor }} · {{ $section->cards->count() }} card</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.sections.update', $section) }}" class="form-grid section-editor">
                    @csrf
                    @method('PUT')
                    <input name="title" value="{{ $section->title }}" required>
                    <input name="anchor" value="{{ $section->anchor }}">
                    <input name="sort_order" type="number" min="0" value="{{ $section->sort_order }}">
                    <label class="checkbox-row compact-check"><input type="checkbox" name="is_active" value="1" @checked($section->is_active)> <span>Aktif</span></label>
                    <textarea class="form-wide" name="description" rows="2">{{ $section->description }}</textarea>
                    <div class="form-actions"><button class="button button-soft" type="submit">Update Section</button></div>
                </form>

                <div class="builder-list">
                    @foreach ($section->cards as $card)
                        <article class="builder-card">
                            <form method="POST" action="{{ route('admin.cards.update', $card) }}">
                                @csrf
                                @method('PUT')
                                @include('admin.pages.partials.card-fields', ['card' => $card, 'templates' => $templates, 'imagePositions' => $imagePositions])
                                <div class="form-actions row-actions">
                                    <button class="button button-soft" type="submit">Update Card</button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('admin.cards.destroy', $card) }}" class="delete-card-form">
                                @csrf
                                @method('DELETE')
                                <button class="button danger-button" type="submit">Hapus Card</button>
                            </form>
                        </article>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('admin.sections.cards.store', $section) }}" class="builder-card new-card">
                    @csrf
                    <h3>Tambah Card</h3>
                    @include('admin.pages.partials.card-fields', ['card' => null, 'templates' => $templates, 'imagePositions' => $imagePositions])
                    <div class="form-actions"><button class="button button-primary" type="submit">Tambah Card</button></div>
                </form>
            </section>
        @empty
            <section class="empty-state">Home masih kosong. Tambahkan section pertama untuk mulai menyusun website.</section>
        @endforelse
    </main>
</div>
@endsection