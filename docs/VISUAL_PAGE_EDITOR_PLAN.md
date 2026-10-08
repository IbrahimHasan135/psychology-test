# Visual Page Editor Integration Plan

This document summarizes the `/home/ibrohim/Documents/python/web-editor` proof of concept and how NovaBase should adopt its Page Management workflow.

## Source Studied

The POC is a static HTML/CSS/JavaScript visual editor:

```text
index.html
assets/css/editor.css
assets/css/templates.css
assets/js/main.js
assets/js/editor.js
assets/js/state.js
assets/js/siteRenderer.js
assets/js/inspectorRenderer.js
assets/js/blockRegistry.js
assets/js/blockFactory.js
assets/js/templates.js
assets/js/objectPath.js
assets/js/siteInteractions.js
```

The important concept is not the static storage itself, but the editor architecture:

- a page has ordered blocks
- each block has a type and JSON data
- the same renderer powers edit preview and public view
- edit mode adds toolbars, selection, `contenteditable`, inspector fields, and block actions
- preview/public mode hides editing controls and renders the final website

## How The POC Works

### 1. State Shape

`assets/js/state.js` stores the whole website as JSON in localStorage.

Core state:

```json
{
  "template": "template-studio",
  "activePageId": "home",
  "selectedBlockId": null,
  "mode": "edit",
  "pages": [
    {
      "id": "home",
      "label": "Home",
      "path": "/",
      "blocks": []
    }
  ]
}
```

Important details:

- `template` selects the global design theme.
- `activePageId` decides which page is edited.
- `selectedBlockId` decides which block opens in the inspector.
- `mode` toggles edit/preview.
- `pages[].blocks[]` is the main page content.

### 2. Block Shape

Blocks come from `blockFactory.js`.

Example:

```json
{
  "id": "block_xxx",
  "navEnabled": true,
  "navLabel": "Home",
  "type": "hero",
  "data": {
    "kicker": "Template 1",
    "title": "Editable hero title",
    "text": "Editable copy",
    "buttonLabel": "Start now",
    "buttonUrl": "https://example.com",
    "image": "https://..."
  }
}
```

The POC supports these block types:

- `hero`
- `cards`
- `split`
- `gallery`
- `video`
- `logos`
- `testimonials`
- `pricing`
- `cta`

Each type owns its own `data` structure. For example, `cards` has `items`, `gallery` has `images`, `logos` has `logos`, and `pricing` has `plans`.

### 3. Rendering Model

`siteRenderer.js` is the central renderer.

It has two important modes:

- `renderWebsite(state)` for editor/preview.
- `renderPublicPage(siteData)` for public website rendering.

This means the edit canvas and the real public page use the same template rules. Edit mode only adds metadata and controls.

The renderer:

- renders navigation
- renders selected page blocks
- renders each block by `type`
- injects `data-edit="blockId:path"` on editable text
- injects `data-block-id="..."` on block wrappers
- shows toolbar buttons only in editable mode

### 4. Editor Controller

`editor.js` owns the full editor interaction loop.

Main responsibilities:

- render pages sidebar
- render design templates sidebar
- render website canvas
- render inspector panel
- bind canvas clicks
- bind toolbar actions
- bind inspector inputs
- save state
- export JSON
- reset state
- toggle edit/preview mode
- toggle desktop/mobile preview

The render loop is simple:

```js
saveState(state)
applyMode()
renderPages()
renderTemplates()
siteCanvas.innerHTML = renderWebsite(state)
renderInspector()
bindCanvasEvents()
```

### 5. Canvas Editing

In edit mode:

- clicking a block selects it
- selected block receives visual highlight
- toolbar can move, add after, duplicate, or delete
- contenteditable text writes directly into `block.data`
- links are prevented from navigating

Text edit uses:

```html
data-edit="blockId:data.path"
```

The editor parses the path and writes the value with `setByPath`.

### 6. Inspector Editing

`inspectorRenderer.js` renders controls based on selected block shape.

The inspector handles:

- nav label
- show/hide block in navigation
- image URL
- image upload converted to base64
- button URL
- video URL
- item links
- split image position
- add card
- add gallery image
- add logo
- add testimonial
- add pricing plan
- duplicate section
- delete section

The key idea is generic path-based mutation:

```text
data-text-field="blockId:items.0.title"
data-image-field="blockId:image"
data-root-field="blockId:navLabel"
```

### 7. Templates

`templates.js` registers design themes:

- `template-studio`
- `template-bold`
- `template-editorial`
- `template-neon`
- `template-earth`

The selected template is applied as a CSS class:

```html
<div class="website-theme template-studio">
```

Theme styling lives in `assets/css/templates.css`.

### 8. UI Layout

`index.html` uses a three-panel editor:

```text
left sidebar   -> pages, design templates, add section, export/reset
center canvas  -> live website canvas with edit/preview + desktop/mobile
right inspector -> selected block controls
```

This is the main UX NovaBase should copy for Page Management.

## Current NovaBase Gap

NovaBase Page Management currently uses database tables like:

```text
site_pages
site_sections
site_cards
```

This is good for simple section/card management, but not ideal for the visual editor:

- it separates section and card too rigidly
- every card has a fixed set of columns
- nested repeaters like logos, gallery images, pricing plans, and testimonials are awkward
- content editing is form-first, not canvas-first
- public rendering and editor preview are not yet the same renderer

The POC suggests moving Page Management toward a block-based JSON model.

## Recommended NovaBase Data Model

Keep `site_pages`, but change how page content is stored.

Recommended tables:

```text
site_pages
  id
  name
  slug
  path
  template
  status
  published_at
  created_at
  updated_at

site_blocks
  id
  site_page_id
  block_uid
  type
  nav_enabled
  nav_label
  sort_order
  data_json
  created_at
  updated_at
```

Optional later:

```text
site_revisions
  id
  site_page_id
  snapshot_json
  created_by
  note
  created_at

media_assets
  id
  disk
  path
  original_name
  mime_type
  size
  created_by
  created_at
```

Why this model:

- each block type can store different JSON
- galleries/pricing/testimonials work naturally
- adding new block types does not require new database columns
- public renderer and editor renderer can read the same block data
- future addon blocks can be registered without altering core tables

## Laravel Implementation Shape

### Models

```text
app/Models/SitePage.php
app/Models/SiteBlock.php
```

`SiteBlock` should cast:

```php
protected function casts(): array
{
    return [
        'nav_enabled' => 'boolean',
        'data_json' => 'array',
    ];
}
```

### Block Registry

Create a Laravel-side block registry:

```text
app/Core/PageBuilder/BlockRegistry.php
app/Core/PageBuilder/BlockDefinition.php
app/Core/PageBuilder/BlockFactory.php
```

Responsibilities:

- list available block types
- provide default block data
- define labels/icons
- define allowed inspector fields
- let addons register new block types later

### Views

Recommended Blade structure:

```text
resources/views/admin/pages/editor.blade.php
resources/views/admin/pages/partials/editor-sidebar.blade.php
resources/views/admin/pages/partials/editor-canvas.blade.php
resources/views/admin/pages/partials/editor-inspector.blade.php

resources/views/website/page.blade.php
resources/views/website/blocks/hero.blade.php
resources/views/website/blocks/cards.blade.php
resources/views/website/blocks/split.blade.php
resources/views/website/blocks/gallery.blade.php
resources/views/website/blocks/video.blade.php
resources/views/website/blocks/logos.blade.php
resources/views/website/blocks/testimonials.blade.php
resources/views/website/blocks/pricing.blade.php
resources/views/website/blocks/cta.blade.php
```

The public website should render the same block partials as the editor preview.

### JavaScript

Move/adapt POC modules into NovaBase:

```text
resources/js/page-builder/editor.js
resources/js/page-builder/state.js
resources/js/page-builder/siteRenderer.js
resources/js/page-builder/inspectorRenderer.js
resources/js/page-builder/blockFactory.js
resources/js/page-builder/blockRegistry.js
resources/js/page-builder/objectPath.js
```

But storage changes:

- POC uses localStorage
- NovaBase should load initial state from Laravel JSON
- NovaBase should save through API endpoints

### API Endpoints

Recommended routes:

```text
GET    /admin/pages/{page}/editor
GET    /admin/pages/{page}/builder-state
PUT    /admin/pages/{page}/builder-state
POST   /admin/pages/{page}/publish
POST   /admin/pages/{page}/revisions
POST   /admin/media
```

For first implementation, keep it simpler:

```text
GET /admin/pages/{page}/edit
PUT /admin/pages/{page}/builder-state
```

The editor can debounce save or use a clear Save button.

## Proposed Editor State For NovaBase

Laravel should return this JSON:

```json
{
  "template": "template-studio",
  "activePageId": "home",
  "selectedBlockId": null,
  "mode": "edit",
  "pages": [
    {
      "id": "home",
      "label": "Home",
      "path": "/",
      "blocks": [
        {
          "id": "block_abc",
          "navEnabled": true,
          "navLabel": "Home",
          "type": "hero",
          "data": {}
        }
      ]
    }
  ]
}
```

Mapping to database:

```text
state.pages[]         -> site_pages
state.pages[].blocks  -> site_blocks
block.id              -> site_blocks.block_uid
block.type            -> site_blocks.type
block.navEnabled      -> site_blocks.nav_enabled
block.navLabel        -> site_blocks.nav_label
block.data            -> site_blocks.data_json
array order           -> site_blocks.sort_order
```

## Editor Workflow In NovaBase

### Page Management Index

Clicking `Page Management` should open a page list similar to today, but the primary action should open the visual editor.

Suggested actions:

- Edit visually
- Preview public page
- Publish/unpublish

### Visual Editor

When opening a page:

1. Load page + blocks from DB as JSON.
2. Hydrate JavaScript editor state.
3. Render canvas.
4. User edits text directly on canvas or uses inspector.
5. User saves JSON state to Laravel.
6. Laravel validates block types and persists blocks.
7. Public website renders from the same block records.

### Public View

The public front page should not read old `site_sections/site_cards` long term. It should:

1. Find page by slug/path.
2. Load active blocks ordered by `sort_order`.
3. Render website blocks with selected page template.
4. Never include editor toolbar or contenteditable attributes.

## Migration Strategy From Current Page Management

Because NovaBase already has `site_sections` and `site_cards`, use a staged migration.

### Phase 1 - Add Block Tables Without Removing Old Tables

- Add `site_blocks`.
- Keep old tables untouched.
- Create one default `hero` block if Home has no blocks.
- Public renderer can prefer blocks if present, otherwise fallback to old sections/cards.

### Phase 2 - Add Visual Editor

- Replace `/admin/pages/{page}/edit` with visual editor UI.
- Load/save `site_blocks`.
- Keep existing old page builder code available only as fallback during transition.

### Phase 3 - Public Renderer Uses Blocks

- Public Home renders from `site_blocks`.
- Existing section/card templates can be converted into block partials.

### Phase 4 - Optional Cleanup

- Once stable, old `site_sections` and `site_cards` can be deprecated.
- Do not delete old tables immediately, because existing content might still need migration.

## Backend Validation Rules

When saving editor state:

- page IDs must map to existing `site_pages`
- block type must exist in `BlockRegistry`
- block UID must be string and unique within page
- nav label length max 120
- block data must be array
- image/video URLs should be strings
- uploaded files should go through Laravel storage, not raw base64 long term

## Media Handling

The POC supports file upload by converting files to base64 in the browser. NovaBase should not store large base64 blobs in page JSON for production.

Recommended:

- first version: allow image URL fields
- next version: add `POST /admin/media`
- upload to `storage/app/public/page-builder`
- save returned URL/path into block `data`

## What Should Be Reused Directly

Good to reuse/adapt:

- three-panel layout
- edit/preview mode switch
- desktop/mobile canvas frame
- page list
- template list
- block palette
- block toolbar actions
- `data-edit` path approach
- object path mutation
- block registry/factory idea
- shared renderer for edit/public

Needs backend adaptation:

- localStorage persistence
- prompt-based adding page/block
- base64 image upload
- full-state mutation without validation
- Indonesian labels

## First Implementation Scope For NovaBase

Recommended first implementation:

- English UI.
- `site_blocks` table.
- visual editor replacing current page edit screen.
- block types: hero, cards, split, gallery, video, logos, testimonials, pricing, cta.
- save button to persist full page state.
- public renderer reads the same blocks.
- fallback to old section/card renderer when no blocks exist.
- simple URL image fields first; upload endpoint can follow after.

## Files Likely To Change In NovaBase

```text
database/migrations/*_create_site_blocks_table.php
app/Models/SiteBlock.php
app/Models/SitePage.php
app/Http/Controllers/Admin/PageManagementController.php
app/Http/Controllers/WebsiteController.php
app/Core/PageBuilder/*
resources/views/admin/pages/index.blade.php
resources/views/admin/pages/edit.blade.php
resources/views/website/home.blade.php
resources/views/website/blocks/*
resources/js/page-builder/*
resources/views/layouts/app.blade.php
```

## Open Decisions Before Implementation

1. Should the editor autosave or require a Save button?
2. Should public pages support multiple pages/routes immediately, or only Home first?
3. Should uploaded images be implemented now or start with image URLs?
4. Should old `site_sections/site_cards` be migrated into blocks automatically?
5. Should addons be allowed to register custom page-builder blocks in this phase?

## Recommendation

Implement the block-based editor in phases. Start with Home page visual editing using `site_blocks`, keep old tables as fallback, and reuse the POC editor architecture with Laravel persistence.

This keeps NovaBase modular and future-proof: the admin editor becomes a true visual builder, while the public website renders the exact same block data without editor controls.
