import { renderPublicPage } from './siteRenderer.js';

const canvas = document.querySelector('#publicSiteCanvas');
const state = window.NOVABASE_BUILDER_STATE;
if (!canvas || !state) throw new Error('Published page data was not provided.');
const basePath = new URL(window.NOVABASE_BASE_URL || '/', window.location.origin).pathname.replace(/\/$/, '');
state.pages.forEach((page) => {
  page.path = page.path === '/' ? `${basePath}/` : `${basePath}${page.path}`;
});

function render() {
  canvas.innerHTML = renderPublicPage(state, { auth: window.NOVABASE_AUTH });
  canvas.querySelectorAll('[data-site-menu-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const nav = button.closest('[data-site-navbar]');
      const isOpen = nav.classList.toggle('menu-open');
      button.setAttribute('aria-expanded', String(isOpen));
    });
  });
  canvas.querySelectorAll('[data-page-id]').forEach((item) => {
    item.addEventListener('click', (event) => {
      const targetPage = state.pages.find((page) => page.id === item.dataset.pageId);
      if (!targetPage) return;
      const blockId = item.dataset.scrollBlock;
      if (!blockId && item.classList.contains('dropdown-trigger')) {
        event.preventDefault();
        item.closest('.site-nav-dropdown')?.classList.toggle('dropdown-open');
        return;
      }
      event.preventDefault();
      const pageChanged = targetPage.path !== window.location.pathname;
      state.activePageId = targetPage.id;
      if (targetPage.path && pageChanged) {
        window.history.pushState({}, '', `${targetPage.path}${blockId ? `#${blockId}` : ''}`);
      } else if (blockId) {
        window.history.replaceState({}, '', `${window.location.pathname}#${blockId}`);
      }
      render();
      const target = blockId ? document.getElementById(blockId) : null;
      target?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      if (!target && pageChanged) window.scrollTo({ top: 0, behavior: 'smooth' });
      canvas.querySelector('[data-site-navbar]')?.classList.remove('menu-open');
    });
  });
}

window.addEventListener('popstate', () => {
  const page = state.pages.find((item) => item.path === window.location.pathname);
  if (page) state.activePageId = page.id;
  render();
  if (window.location.hash) document.querySelector(window.location.hash)?.scrollIntoView();
});

render();
