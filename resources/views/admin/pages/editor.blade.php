@extends('layouts.app', ['title' => 'NovaBase'])

@section('content')
<div class="app-shell">
    <aside class="editor-sidebar">
        <div class="brand-lockup">
            <span class="brand-mark"><i class="bi bi-layout-text-window"></i></span>
            <div><strong>NovaBase</strong><small>Visual page builder</small></div>
        </div>

        <section class="sidebar-section">
            <div class="section-title">Pages</div>
            <div id="pageList" class="page-list"></div>
            <button id="addPageBtn" class="btn btn-outline-primary btn-sm w-100 mt-2"><i class="bi bi-plus-lg"></i> Add page</button>
        </section>

        <section class="sidebar-section">
            <div class="section-title">Design templates</div>
            <div id="templateList" class="template-list"></div>
        </section>

        <section class="sidebar-section">
            <div class="section-title">Add section</div>
            <div id="blockPalette" class="block-palette"></div>
        </section>

        <section class="sidebar-section">
            <div class="section-title">Page data</div>
            <div class="d-grid gap-2">
                <button id="exportBtn" class="btn btn-dark btn-sm"><i class="bi bi-code-square"></i> Export JSON</button>
                <button id="resetBtn" class="btn btn-outline-danger btn-sm"><i class="bi bi-arrow-counterclockwise"></i> Discard unsaved changes</button>
            </div>
        </section>
    </aside>

    <main class="editor-main">
        <header class="topbar">
            <a class="back-admin" href="{{ route('admin.dashboard') }}" aria-label="Admin Panel">
                <i class="bi bi-arrow-left"></i>
                <span>Admin Panel</span>
            </a>
            <div class="topbar-actions">
                <span id="saveStatus" class="save-status">All changes saved</span>
                <div class="mode-switch" role="group" aria-label="Editor mode">
                    <button id="editModeBtn" class="mode-btn active" data-mode="edit" title="Edit mode"><i class="bi bi-pencil-square"></i> Edit</button>
                    <button id="previewModeBtn" class="mode-btn" data-mode="preview" title="Preview mode"><i class="bi bi-eye"></i> Preview</button>
                </div>
                <div class="viewport-switch" role="group" aria-label="Preview viewport">
                    <button class="btn btn-light btn-sm active" data-viewport="desktop" title="Desktop preview"><i class="bi bi-display"></i></button>
                    <button class="btn btn-light btn-sm" data-viewport="mobile" title="Mobile preview"><i class="bi bi-phone"></i></button>
                </div>
                <button id="saveChangesBtn" class="btn btn-success btn-sm editor-save-button" data-url="{{ route('admin.pages.builder.site-save') }}" data-csrf="{{ csrf_token() }}"><i class="bi bi-cloud-check"></i> Save</button>
            </div>
        </header>

        <div id="canvasFrame" class="canvas-frame">
            <div class="browser-bar"><span></span><span></span><span></span><div class="address">{{ request()->getHost() }}{{ request()->getBaseUrl() }}</div></div>
            <div id="siteCanvas" class="site-canvas"></div>
        </div>
    </main>

    <aside class="inspector-panel">
        <div class="section-title">Inspector</div>
        <div id="emptyInspector" class="empty-state"><i class="bi bi-cursor"></i><p>Select a section in the preview to edit its content and media.</p></div>
        <div id="inspectorContent" class="inspector-content d-none"></div>
    </aside>
</div>

<div class="modal fade" id="jsonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title fs-5">Current page data</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body"><textarea id="jsonOutput" class="form-control json-output" readonly></textarea></div>
    </div></div>
</div>

<script>
    window.NOVABASE_BUILDER_STATE = @json($builderState, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    window.NOVABASE_BLOCK_DEFINITIONS = @json($blockDefinitions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    window.NOVABASE_DESIGN_TEMPLATES = @json($designTemplates, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="{{ asset('js/page-builder/editor/main.js') }}"></script>
@endsection
