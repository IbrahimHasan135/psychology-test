import { createBlock } from './blockFactory.js';
import { uid } from './id.js';
import { renderInspector } from './inspectorRenderer.js';
import { escapeHtml, renderWebsite } from './siteRenderer.js';
import { setByPath } from './objectPath.js';
import { createInitialState, findBlock, getActivePage, saveState } from './state.js';
import { slugify } from './slug.js';
import { designTemplates } from './templates.js';
import { blockDefinitions } from './blockRegistry.js';

export class VisualEditor {
  constructor(state) {
    this.state = state;
    this.nodes = {
      pageList: document.querySelector('#pageList'),
      templateList: document.querySelector('#templateList'),
      blockPalette: document.querySelector('#blockPalette'),
      siteCanvas: document.querySelector('#siteCanvas'),
      addPageBtn: document.querySelector('#addPageBtn'),
      inspectorContent: document.querySelector('#inspectorContent'),
      emptyInspector: document.querySelector('#emptyInspector'),
      canvasFrame: document.querySelector('#canvasFrame'),
      exportBtn: document.querySelector('#exportBtn'),
      resetBtn: document.querySelector('#resetBtn'),
      jsonOutput: document.querySelector('#jsonOutput'),
      appShell: document.querySelector('.app-shell'),
      editModeBtn: document.querySelector('#editModeBtn'),
      previewModeBtn: document.querySelector('#previewModeBtn'),
      saveBtn: document.querySelector('#saveChangesBtn'),
      saveStatus: document.querySelector('#saveStatus'),
    };
    this.jsonModal = new bootstrap.Modal(document.querySelector('#jsonModal'));
    this.initialized = false;
    this.dirty = false;
    this.saving = false;
    this.saveTimer = null;
  }

  init() {
    this.bindGlobalEvents();
    this.render();
  }

  render() {
    if (this.initialized && !this.saving) {
      this.markDirty();
      this.queueSave();
    }
    this.applyMode();
    this.renderPages();
    this.renderTemplates();
    this.renderBlockPalette();
    this.nodes.siteCanvas.innerHTML = renderWebsite(this.state);
    this.renderInspector();
    this.bindCanvasEvents();
    this.initialized = true;
  }

  renderPages() {
    this.nodes.pageList.innerHTML = this.state.pages.map((page) => `
      <button class="page-item ${page.id === this.state.activePageId ? 'active' : ''}" data-select-page="${page.id}">
        <i class="bi bi-file-earmark"></i>
        <span>${escapeHtml(page.label)}</span>
      </button>
    `).join('');
  }

  renderTemplates() {
    this.nodes.templateList.innerHTML = designTemplates.map((template) => `
      <button class="template-item ${template.id === this.state.template ? 'active' : ''}" data-select-template="${template.id}">
        <span class="template-swatch ${template.id}"></span>
        <span>
          <strong>${template.name}</strong>
          <small>${template.description}</small>
        </span>
      </button>
    `).join('');
  }

  renderBlockPalette() {
    if (!this.nodes.blockPalette) return;
    this.nodes.blockPalette.innerHTML = Object.entries(blockDefinitions).map(([type, definition]) => `
      <button class="palette-item" data-add-block="${escapeHtml(type)}">
        <i class="bi ${escapeHtml(definition.icon || 'bi-layout-text-window')}"></i>
        <span>${escapeHtml(definition.label || type)}</span>
      </button>
    `).join('');
  }

  renderInspector() {
    const block = findBlock(this.state);
    this.nodes.inspectorContent.innerHTML = renderInspector(block);
    this.nodes.inspectorContent.classList.toggle('d-none', !block);
    this.nodes.emptyInspector.classList.toggle('d-none', Boolean(block));
  }

  applyMode() {
    const isPreview = this.state.mode === 'preview';
    this.nodes.appShell.classList.toggle('preview-mode', isPreview);
    this.nodes.siteCanvas.classList.toggle('is-preview', isPreview);
    this.nodes.editModeBtn.classList.toggle('active', !isPreview);
    this.nodes.previewModeBtn.classList.toggle('active', isPreview);
  }

  bindGlobalEvents() {
    this.nodes.addPageBtn.addEventListener('click', () => this.addPage());
    this.nodes.pageList.addEventListener('click', (event) => {
      const button = event.target.closest('[data-select-page]');
      if (!button) return;
      this.state.activePageId = button.dataset.selectPage;
      this.state.selectedBlockId = null;
      this.render();
    });

    this.nodes.templateList.addEventListener('click', (event) => {
      const button = event.target.closest('[data-select-template]');
      if (!button) return;
      this.state.template = button.dataset.selectTemplate;
      this.render();
    });

    this.nodes.blockPalette?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-add-block]');
      if (!button) return;
      this.addBlock(button.dataset.addBlock);
    });

    document.querySelectorAll('[data-viewport]').forEach((button) => {
      button.addEventListener('click', () => {
        document.querySelectorAll('[data-viewport]').forEach((item) => item.classList.remove('active'));
        button.classList.add('active');
        this.nodes.canvasFrame.classList.toggle('mobile', button.dataset.viewport === 'mobile');
      });
    });

    document.querySelectorAll('[data-mode]').forEach((button) => {
      button.addEventListener('click', () => {
        this.state.mode = button.dataset.mode;
        if (this.state.mode === 'preview') {
          this.state.selectedBlockId = null;
        }
        this.render();
      });
    });

    this.nodes.exportBtn.addEventListener('click', () => {
      this.nodes.jsonOutput.value = JSON.stringify(this.state, null, 2);
      this.jsonModal.show();
    });

    this.nodes.resetBtn.addEventListener('click', () => {
      if (!confirm('Discard unsaved changes and reload the saved page state?')) return;
      window.location.reload();
    });

    this.nodes.saveBtn.addEventListener('click', () => this.save());
    window.addEventListener('novabase:builder-state-change', () => {
      this.markDirty();
      this.queueSave();
    });
    window.addEventListener('beforeunload', (event) => {
      if (!this.dirty) return;
      event.preventDefault();
      event.returnValue = '';
    });

    this.nodes.inspectorContent.addEventListener('input', (event) => this.handleInspectorInput(event));
    this.nodes.inspectorContent.addEventListener('change', (event) => this.handleInspectorChange(event));
    this.nodes.inspectorContent.addEventListener('click', (event) => this.handleInspectorClick(event));
  }

  bindCanvasEvents() {
    this.nodes.siteCanvas.querySelectorAll('[data-site-menu-toggle]').forEach((button) => {
      button.addEventListener('click', () => {
        const nav = button.closest('[data-site-navbar]');
        const open = nav.classList.toggle('menu-open');
        button.setAttribute('aria-expanded', String(open));
      });
    });

    this.nodes.siteCanvas.querySelectorAll('[data-page-id]').forEach((navItem) => {
      navItem.addEventListener('click', (event) => {
        event.preventDefault();
        if (navItem.classList.contains('dropdown-trigger') && !navItem.dataset.scrollBlock) {
          const dropdown = navItem.closest('.site-nav-dropdown');
          this.nodes.siteCanvas.querySelectorAll('.site-nav-dropdown.dropdown-open').forEach((item) => {
            if (item !== dropdown) item.classList.remove('dropdown-open');
          });
          dropdown?.classList.toggle('dropdown-open');
          return;
        }

        const targetPageId = navItem.dataset.pageId;
        const targetBlockId = navItem.dataset.scrollBlock;
        const pageChanged = this.state.activePageId !== targetPageId;

        this.state.activePageId = targetPageId;
        this.state.selectedBlockId = targetBlockId ?? null;

        if (pageChanged) {
          this.render();
          this.scrollToBlock(targetBlockId);
        } else if (targetBlockId) {
          this.renderSelectionOnly();
          this.scrollToBlock(targetBlockId);
          this.closeSiteMenu();
        } else {
          this.render();
        }
      });
    });

    if (this.state.mode === 'preview') {
      this.nodes.siteCanvas.querySelectorAll('a[href="#"]').forEach((link) => {
        link.addEventListener('click', (event) => event.preventDefault());
      });
      return;
    }

    this.nodes.siteCanvas.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', (event) => event.preventDefault());
    });

    this.nodes.siteCanvas.querySelectorAll('[data-block-id]').forEach((blockNode) => {
      blockNode.addEventListener('click', (event) => {
        const toolbarButton = event.target.closest('[data-action]');
        if (toolbarButton) {
          event.preventDefault();
          event.stopPropagation();
          this.handleBlockAction(toolbarButton.dataset.action, blockNode.dataset.blockId);
          return;
        }

        this.state.selectedBlockId = blockNode.dataset.blockId;
        this.renderSelectionOnly();
      });
    });

    this.nodes.siteCanvas.querySelectorAll('[data-edit]').forEach((editable) => {
      editable.addEventListener('input', () => {
        const [blockId, path] = editable.dataset.edit.split(':');
        const block = findBlock(this.state, blockId);
        if (!block) return;
        setByPath(block.data, path, editable.innerText.trim());
        saveState(this.state);
      });

      editable.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        const [blockId] = editable.dataset.edit.split(':');
        this.state.selectedBlockId = blockId;
        this.renderSelectionOnly();
      });
    });
  }

  renderSelectionOnly() {
    this.nodes.siteCanvas.querySelectorAll('[data-block-id]').forEach((node) => {
      node.classList.toggle('selected', node.dataset.blockId === this.state.selectedBlockId);
    });
    this.renderInspector();
  }

  markDirty() {
    this.dirty = true;
    this.nodes.saveStatus.textContent = 'Unsaved changes';
    this.nodes.saveStatus.classList.remove('text-danger');
    this.nodes.saveStatus.classList.add('text-warning');
  }

  queueSave() {
    window.clearTimeout(this.saveTimer);
    this.saveTimer = window.setTimeout(() => this.save(), 800);
  }

  async save() {
    window.clearTimeout(this.saveTimer);
    if (this.saving) return;
    this.nodes.saveBtn.disabled = true;
    this.nodes.saveStatus.textContent = 'Saving…';
    this.nodes.appShell.inert = true;
    this.saving = true;
    try {
      const response = await fetch(this.nodes.saveBtn.dataset.url, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': this.nodes.saveBtn.dataset.csrf,
        },
        body: JSON.stringify(this.state),
      });
      const result = await response.json();
      if (!response.ok) {
        throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Save failed.');
      }
      const selectedBlockId = this.state.selectedBlockId;
      const mode = this.state.mode;
      this.state = result.state;
      this.state.mode = mode;
      this.state.selectedBlockId = selectedBlockId;
      this.render();
      this.dirty = false;
      this.nodes.saveStatus.textContent = 'All changes saved';
      this.nodes.saveStatus.classList.remove('text-warning', 'text-danger');
    } catch (error) {
      this.nodes.saveStatus.textContent = error.message;
      this.nodes.saveStatus.classList.add('text-danger');
      this.dirty = true;
    } finally {
      this.saving = false;
      this.nodes.appShell.inert = false;
      this.nodes.saveBtn.disabled = false;
    }
  }

  addPage() {
    const label = prompt('Nama tab/page baru:', `Page ${this.state.pages.length + 1}`);
    if (!label) return;

    const page = {
      id: uid('page'),
      label,
      path: `/${slugify(label)}`,
      blocks: [createBlock('hero')],
    };

    this.state.pages.push(page);
    this.state.activePageId = page.id;
    this.state.selectedBlockId = page.blocks[0].id;
    this.render();
  }

  addBlock(type) {
    const page = getActivePage(this.state);
    const block = createBlock(type);
    page.blocks.push(block);
    this.state.selectedBlockId = block.id;
    this.render();
    this.scrollToBlock(block.id);
  }

  addBlockAfter(blockId, type = null) {
    const selectedType = type ?? prompt('Jenis section baru: hero, cards, split, gallery, video, logos, testimonials, pricing, cta', 'cards');
    if (!selectedType) return;

    const page = getActivePage(this.state);
    const index = page.blocks.findIndex((block) => block.id === blockId);
    const block = createBlock(selectedType.trim());
    page.blocks.splice(index < 0 ? page.blocks.length : index + 1, 0, block);
    this.state.selectedBlockId = block.id;
    this.render();
    this.scrollToBlock(block.id);
  }

  handleBlockAction(action, blockId = this.state.selectedBlockId) {
    const page = getActivePage(this.state);
    const index = page.blocks.findIndex((block) => block.id === blockId);
    if (index < 0) return;

    if (action === 'delete') {
      page.blocks.splice(index, 1);
      this.state.selectedBlockId = page.blocks[index]?.id ?? page.blocks[index - 1]?.id ?? null;
    }

    if (action === 'duplicate') {
      const clone = structuredClone(page.blocks[index]);
      clone.id = uid('block');
      page.blocks.splice(index + 1, 0, clone);
      this.state.selectedBlockId = clone.id;
    }

    if (action === 'add-after') {
      this.addBlockAfter(blockId);
      return;
    }

    if (action === 'move-up' && index > 0) {
      [page.blocks[index - 1], page.blocks[index]] = [page.blocks[index], page.blocks[index - 1]];
    }

    if (action === 'move-down' && index < page.blocks.length - 1) {
      [page.blocks[index], page.blocks[index + 1]] = [page.blocks[index + 1], page.blocks[index]];
    }

    this.render();
    this.scrollToBlock(this.state.selectedBlockId);
  }

  handleInspectorInput(event) {
    const imageInput = event.target.closest('[data-image-field]');
    const textInput = event.target.closest('[data-text-field]');
    const rootInput = event.target.closest('[data-root-field]');

    if (rootInput) {
      const [blockId, path] = rootInput.dataset.rootField.split(':');
      const block = findBlock(this.state, blockId);
      if (!block) return;

      block[path] = rootInput.value;
      saveState(this.state);
      return;
    }

    if (textInput) {
      const [blockId, path] = textInput.dataset.textField.split(':');
      const block = findBlock(this.state, blockId);
      if (!block) return;

      setByPath(block.data, path, textInput.value);
      saveState(this.state);
      return;
    }

    if (!imageInput) return;

    const [blockId, path] = imageInput.dataset.imageField.split(':');
    const block = findBlock(this.state, blockId);
    if (!block) return;

    setByPath(block.data, path, imageInput.value);
    this.render();
  }

  handleInspectorChange(event) {
    const uploadInput = event.target.closest('[data-upload-field]');
    const fieldInput = event.target.closest('[data-inspector-field]');
    const textInput = event.target.closest('[data-text-field]');
    const rootInput = event.target.closest('[data-root-field]');

    if (rootInput) {
      const [blockId, path] = rootInput.dataset.rootField.split(':');
      const block = findBlock(this.state, blockId);
      if (!block) return;

      block[path] = rootInput.value;
      this.render();
      return;
    }

    if (textInput) {
      const [blockId, path] = textInput.dataset.textField.split(':');
      const block = findBlock(this.state, blockId);
      if (!block) return;

      setByPath(block.data, path, textInput.value);
      this.render();
      return;
    }

    if (fieldInput) {
      const block = findBlock(this.state);
      if (!block) return;
      block.data[fieldInput.dataset.inspectorField] = fieldInput.value;
      this.render();
    }

    if (!uploadInput || !uploadInput.files?.[0]) return;

    const [blockId, path] = uploadInput.dataset.uploadField.split(':');
    const block = findBlock(this.state, blockId);
    if (!block) return;

    const reader = new FileReader();
    reader.onload = () => {
      setByPath(block.data, path, reader.result);
      this.render();
    };
    reader.readAsDataURL(uploadInput.files[0]);
  }

  handleInspectorClick(event) {
    const button = event.target.closest('[data-inspector-action]');
    if (!button) return;

    const action = button.dataset.inspectorAction;
    const block = findBlock(this.state);
    if (!block) return;

    if (action === 'add-nav-section') {
      block.navEnabled = true;
      block.navLabel = block.navLabel || block.data.title || block.data.kicker || block.type;
      this.render();
      return;
    }

    if (action === 'remove-nav-section') {
      block.navEnabled = false;
      this.render();
      return;
    }

    if (action === 'add-after') {
      this.addBlockAfter(block.id);
      return;
    }

    if (action === 'add-card' && block.type === 'cards') {
      block.data.items.push({
        icon: 'bi-check2-circle',
        title: 'Card baru',
        text: 'Isi card ini bisa diedit langsung di preview.',
        link: 'https://example.com/card-baru',
      });
      this.render();
      return;
    }

    if (action === 'add-gallery-image' && block.type === 'gallery') {
      block.data.images.push('https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1200&q=80');
      this.render();
      return;
    }

    if (action === 'add-logo' && block.type === 'logos') {
      block.data.logos.push({
        name: 'Logo Baru',
        image: 'https://dummyimage.com/240x100/f8fafc/111827&text=Logo+Baru',
        link: 'https://example.com/logo-baru',
      });
      this.render();
      return;
    }

    if (action === 'add-testimonial' && block.type === 'testimonials') {
      block.data.items.push({
        name: 'Nama Client',
        role: 'Role client',
        quote: 'Tulis testimoni atau kutipan client di sini.',
        link: 'https://example.com/testimonial',
      });
      this.render();
      return;
    }

    if (action === 'add-plan' && block.type === 'pricing') {
      block.data.plans.push({
        name: 'Paket Baru',
        price: 'Rp0',
        text: 'Deskripsi singkat paket.',
        buttonLabel: 'Pilih Paket',
        link: 'https://example.com/paket-baru',
      });
      this.render();
      return;
    }

    this.handleBlockAction(action);
  }

  scrollToBlock(blockId) {
    if (!blockId) return;
    requestAnimationFrame(() => {
      this.nodes.siteCanvas.querySelector(`[data-block-id="${blockId}"]`)?.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
      });
      this.closeSiteMenu();
    });
  }

  closeSiteMenu() {
    this.nodes.siteCanvas.querySelectorAll('[data-site-navbar].menu-open').forEach((nav) => {
      nav.classList.remove('menu-open');
      nav.querySelector('[data-site-menu-toggle]')?.setAttribute('aria-expanded', 'false');
    });
  }
}
