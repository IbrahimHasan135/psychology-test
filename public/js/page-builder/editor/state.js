import { createInitialHeroBlock } from './blockFactory.js';
import { DEFAULT_TEMPLATE_ID, normalizeTemplateId } from './templates.js';

export function createInitialState() {
  return {
    template: DEFAULT_TEMPLATE_ID,
    activePageId: 'home',
    selectedBlockId: null,
    mode: 'edit',
    pages: [
      {
        id: 'home',
        label: 'Home',
        path: '/',
        blocks: [createInitialHeroBlock()],
      },
    ],
  };
}

export function loadState() {
  const state = window.NOVABASE_BUILDER_STATE;
  if (!state) return createInitialState();
  return normalizeState({
    ...structuredClone(state),
    template: normalizeTemplateId(state.template),
    mode: state.mode === 'preview' ? 'preview' : 'edit',
  });
}

export function saveState(state) {
  window.dispatchEvent(new CustomEvent('novabase:builder-state-change', { detail: { state } }));
}

export function getActivePage(state) {
  return state.pages.find((page) => page.id === state.activePageId) ?? state.pages[0];
}

export function findBlock(state, blockId = state.selectedBlockId) {
  const page = getActivePage(state);
  return page?.blocks.find((block) => block.id === blockId) ?? null;
}

function normalizeState(state) {
  state.pages?.forEach((page) => {
    const hasNavSection = page.blocks?.some((block) => block.navEnabled === true);
    if (!hasNavSection && page.blocks?.[0]) {
      page.blocks[0].navEnabled = true;
      page.blocks[0].navLabel = page.blocks[0].navLabel || page.blocks[0].data?.title || page.label;
    }
  });

  return state;
}
