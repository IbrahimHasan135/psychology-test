import { DEFAULT_TEMPLATE_ID, normalizeTemplateId } from './templates.js';

export function renderWebsite(state, options = {}) {
  const page = resolvePage(state, options.pageId);
  const isEditable = Boolean(options.editable ?? state.mode !== 'preview');
  const selectedBlockId = options.selectedBlockId ?? state.selectedBlockId;
  const templateId = normalizeTemplateId(state.template || DEFAULT_TEMPLATE_ID);

  return `
    <div class="website-theme ${escapeHtml(templateId)}">
      ${renderSiteNavigation(state, page, isEditable, options.auth)}
      <div>
        ${page.blocks.map((block, index) => renderEditableBlock(block, selectedBlockId === block.id, index, page.blocks.length, isEditable)).join('')}
      </div>
    </div>
  `;
}

export function renderPublicPage(siteData, options = {}) {
  return renderWebsite(
    {
      ...siteData,
      selectedBlockId: null,
      mode: 'preview',
    },
    {
      ...options,
      editable: false,
      selectedBlockId: null,
    },
  );
}

export function renderSiteNavigation(state, page, isEditable = false, auth = null) {
  const singlePage = state.pages.length === 1;

  return `
    <nav class="site-navbar" data-site-navbar>
      <div class="site-brand-row">
        <div class="site-logo" contenteditable="${isEditable}" data-site-field="brand">Template Studio</div>
        <button class="site-menu-toggle" data-site-menu-toggle aria-label="Toggle menu" aria-expanded="false">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
      <div class="site-nav" data-site-menu>
        ${singlePage ? renderSinglePageSectionNav(page) : renderMultiPageNav(state, page)}
        ${!isEditable && auth?.url ? `<a class="site-nav-auth" href="${escapeHtml(auth.url)}">${escapeHtml(auth.label || 'Login')}</a>` : ''}
      </div>
    </nav>
  `;
}

export function renderEditableBlock(block, selected, index, total, isEditable = true) {
  return `
    <section id="${escapeHtml(block.id)}" class="editable-block ${selected ? 'selected' : ''}" data-block-id="${block.id}">
      ${isEditable ? `
        <div class="block-toolbar" contenteditable="false">
          <button data-action="move-up" title="Naik" ${index === 0 ? 'disabled' : ''}><i class="bi bi-arrow-up"></i></button>
          <button data-action="move-down" title="Turun" ${index === total - 1 ? 'disabled' : ''}><i class="bi bi-arrow-down"></i></button>
          <button data-action="add-after" title="Tambah Section"><i class="bi bi-plus-lg"></i></button>
          <button data-action="duplicate" title="Duplikat"><i class="bi bi-copy"></i></button>
          <button data-action="delete" title="Hapus"><i class="bi bi-trash"></i></button>
        </div>
      ` : ''}
      ${renderBlock(block, isEditable)}
    </section>
  `;
}

function renderSinglePageSectionNav(page) {
  const navBlocks = getNavBlocks(page);

  if (!navBlocks.length) return '';

  return navBlocks.map((block) => `
    <a class="site-nav-link" href="#${escapeHtml(block.id)}" data-page-id="${page.id}" data-scroll-block="${block.id}">
      ${escapeHtml(getSectionLabel(block))}
    </a>
  `).join('');
}

function renderMultiPageNav(state, activePage) {
  return state.pages.map((page) => {
    const navBlocks = getNavBlocks(page);

    if (!navBlocks.length) {
      return `
        <button class="site-nav-link ${page.id === activePage.id ? 'active' : ''}" data-page-id="${page.id}">
          ${escapeHtml(page.label)}
        </button>
      `;
    }

    return `
      <div class="site-nav-dropdown ${page.id === activePage.id ? 'active' : ''}">
        <button class="site-nav-link dropdown-trigger ${page.id === activePage.id ? 'active' : ''}" data-page-id="${page.id}">
          ${escapeHtml(page.label)}
          <i class="bi bi-chevron-down"></i>
        </button>
        <div class="site-dropdown-menu">
          ${navBlocks.map((block) => `
            <a href="${escapeHtml(page.path || '#')}#${escapeHtml(block.id)}" data-page-id="${page.id}" data-scroll-block="${block.id}">
              ${escapeHtml(getSectionLabel(block))}
            </a>
          `).join('')}
        </div>
      </div>
    `;
  }).join('');
}

function getNavBlocks(page) {
  return page.blocks.filter((block) => block.navEnabled === true);
}

function getSectionLabel(block) {
  return block.navLabel
    || block.data?.title
    || block.data?.kicker
    || block.type;
}

export function renderBlock(block, isEditable = false) {
  const definition = window.NOVABASE_BLOCK_DEFINITIONS?.[block.type];
  if (definition?.renderer === 'tenant-signup') return renderTenantSignup(block, isEditable);
  if (definition?.renderer === 'addon-card') return renderAddonCard(block, isEditable);
  if (block.type === 'hero') return renderHero(block, isEditable);
  if (block.type === 'cards') return renderCards(block, isEditable);
  if (block.type === 'split') return renderSplit(block, isEditable);
  if (block.type === 'gallery') return renderGallery(block, isEditable);
  if (block.type === 'video') return renderVideo(block, isEditable);
  if (block.type === 'logos') return renderLogos(block, isEditable);
  if (block.type === 'testimonials') return renderTestimonials(block, isEditable);
  if (block.type === 'pricing') return renderPricing(block, isEditable);
  return renderCta(block, isEditable);
}

function resolvePage(state, pageId) {
  return state.pages.find((item) => item.id === (pageId ?? state.activePageId)) ?? state.pages[0];
}

function editAttr(isEditable, block, path) {
  if (!isEditable) return 'contenteditable="false"';
  return `contenteditable="true" data-edit="${block.id}:${path}"`;
}

function hrefAttr(value) {
  const href = String(value || '#').trim() || '#';
  return `href="${escapeHtml(href)}"`;
}

function renderAddonCard(block, isEditable) {
  const { data } = block;
  return `
    <div class="section-pad addon-card-section">
      <div class="addon-card-inner">
        <div class="addon-card-badge">${escapeHtml(data.badge)}</div>
        <h2 class="section-heading" ${editAttr(isEditable, block, 'title')}>${escapeHtml(data.title)}</h2>
        <p class="section-copy" ${editAttr(isEditable, block, 'text')}>${escapeHtml(data.text)}</p>
        <a ${hrefAttr(data.buttonUrl)} class="btn btn-primary" ${editAttr(isEditable, block, 'buttonLabel')}>${escapeHtml(data.buttonLabel)} <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
  `;
}

function renderTenantSignup(block, isEditable) {
  const { data } = block;
  const action = window.NOVABASE_TENANT_SIGNUP_URL || '#';
  const previewAttributes = isEditable ? 'onsubmit="return false" data-editor-preview="true"' : '';

  return `
    <div class="section-pad tenant-signup-section">
      <div class="tenant-signup-card">
        <div class="tenant-signup-copy">
          <div class="addon-card-badge" ${editAttr(isEditable, block, 'badge')}>${escapeHtml(data.badge)}</div>
          <h2 class="section-heading" ${editAttr(isEditable, block, 'title')}>${escapeHtml(data.title)}</h2>
          <p class="section-copy" ${editAttr(isEditable, block, 'text')}>${escapeHtml(data.text)}</p>
          <div class="tenant-signup-note"><i class="bi bi-link-45deg"></i> Your website will be available at <strong>/your-slug</strong>.</div>
          ${isEditable ? '<div class="tenant-signup-editor-note"><i class="bi bi-eye"></i> Editor preview only. The live website will accept registrations.</div>' : ''}
        </div>
        <form class="tenant-signup-form" method="POST" action="${escapeHtml(action)}" ${previewAttributes}>
          <input type="hidden" name="_token" value="${escapeHtml(window.NOVABASE_CSRF_TOKEN || '')}">
          <label>Workspace name<input name="tenant_name" placeholder="My organization" required ${isEditable ? 'disabled' : ''}></label>
          <label>Workspace URL<input name="tenant_slug" placeholder="my-organization" pattern="[A-Za-z0-9_-]+" required ${isEditable ? 'disabled' : ''}></label>
          <label>Owner name<input name="owner_name" placeholder="Your full name" required ${isEditable ? 'disabled' : ''}></label>
          <label>Username<input name="owner_username" placeholder="your.username" pattern="[A-Za-z0-9._-]+" required ${isEditable ? 'disabled' : ''}></label>
          <label>Email<input type="email" name="owner_email" placeholder="you@example.com" required ${isEditable ? 'disabled' : ''}></label>
          <label>Password<input type="password" name="owner_password" minlength="8" required ${isEditable ? 'disabled' : ''}></label>
          <label>Confirm password<input type="password" name="owner_password_confirmation" minlength="8" required ${isEditable ? 'disabled' : ''}></label>
          <button class="btn btn-primary" type="submit" ${isEditable ? 'disabled' : ''}>Create account <i class="bi bi-arrow-right"></i></button>
        </form>
      </div>
    </div>
  `;
}

function renderHero(block, isEditable) {
  const { data } = block;

  return `
    <div class="hero-section">
      <div class="hero-inner">
        <div>
          <div class="hero-kicker" ${editAttr(isEditable, block, 'kicker')}>${escapeHtml(data.kicker)}</div>
          <h2 class="hero-title" ${editAttr(isEditable, block, 'title')}>${escapeHtml(data.title)}</h2>
          <p class="hero-copy" ${editAttr(isEditable, block, 'text')}>${escapeHtml(data.text)}</p>
          <a ${hrefAttr(data.buttonUrl)} class="btn btn-primary btn-lg" ${editAttr(isEditable, block, 'buttonLabel')}>${escapeHtml(data.buttonLabel)}</a>
        </div>
        <div class="hero-media" style="background-image: url('${safeUrl(data.image)}')"></div>
      </div>
    </div>
  `;
}

function renderCards(block, isEditable) {
  const { data } = block;

  return `
    <div class="section-pad">
      <h2 class="section-heading" ${editAttr(isEditable, block, 'title')}>${escapeHtml(data.title)}</h2>
      <p class="section-copy" ${editAttr(isEditable, block, 'text')}>${escapeHtml(data.text)}</p>
      <div class="card-grid">
        ${data.items.map((item, index) => `
          <article class="feature-card">
            <span class="feature-icon"><i class="bi ${escapeHtml(item.icon)}"></i></span>
            <h3 class="h5" ${editAttr(isEditable, block, `items.${index}.title`)}>${escapeHtml(item.title)}</h3>
            <p class="text-secondary mb-0" ${editAttr(isEditable, block, `items.${index}.text`)}>${escapeHtml(item.text)}</p>
            <a class="card-link" ${hrefAttr(item.link)}>Detail <i class="bi bi-arrow-right"></i></a>
          </article>
        `).join('')}
      </div>
    </div>
  `;
}

function renderSplit(block, isEditable) {
  const { data } = block;
  const image = `<div class="split-media" style="background-image: url('${safeUrl(data.image)}')"></div>`;
  const copy = `
    <div>
      <h2 class="section-heading" ${editAttr(isEditable, block, 'title')}>${escapeHtml(data.title)}</h2>
      <p class="section-copy" ${editAttr(isEditable, block, 'text')}>${escapeHtml(data.text)}</p>
      <a ${hrefAttr(data.buttonUrl)} class="btn btn-outline-dark" ${editAttr(isEditable, block, 'buttonLabel')}>${escapeHtml(data.buttonLabel)}</a>
    </div>
  `;

  return `
    <div class="section-pad">
      <div class="split-section">
        ${data.imagePosition === 'right' ? `${copy}${image}` : `${image}${copy}`}
      </div>
    </div>
  `;
}

function renderGallery(block, isEditable) {
  const { data } = block;

  return `
    <div class="section-pad gallery-section">
      <h2 class="section-heading" ${editAttr(isEditable, block, 'title')}>${escapeHtml(data.title)}</h2>
      <p class="section-copy" ${editAttr(isEditable, block, 'text')}>${escapeHtml(data.text)}</p>
      <div class="gallery-strip" aria-label="Gallery slider">
        ${data.images.map((image) => `<div class="gallery-tile" style="background-image: url('${safeUrl(image)}')"></div>`).join('')}
      </div>
    </div>
  `;
}

function renderVideo(block, isEditable) {
  const { data } = block;
  const embedUrl = youtubeEmbedUrl(data.videoUrl);

  return `
    <div class="section-pad video-section">
      <div>
        <h2 class="section-heading" ${editAttr(isEditable, block, 'title')}>${escapeHtml(data.title)}</h2>
        <p class="section-copy" ${editAttr(isEditable, block, 'text')}>${escapeHtml(data.text)}</p>
      </div>
      <div class="video-frame">
        <iframe src="${escapeHtml(embedUrl)}" title="${escapeHtml(data.title)}" allowfullscreen loading="lazy"></iframe>
      </div>
    </div>
  `;
}

function renderLogos(block, isEditable) {
  const { data } = block;

  return `
    <div class="section-pad logos-section">
      <h2 class="section-heading" ${editAttr(isEditable, block, 'title')}>${escapeHtml(data.title)}</h2>
      <p class="section-copy" ${editAttr(isEditable, block, 'text')}>${escapeHtml(data.text)}</p>
      <div class="logo-grid">
        ${data.logos.map((logo, index) => `
          <a class="logo-card" ${hrefAttr(logo.link)} aria-label="${escapeHtml(logo.name)}">
            <img src="${safeUrl(logo.image)}" alt="${escapeHtml(logo.name)}">
            <span ${editAttr(isEditable, block, `logos.${index}.name`)}>${escapeHtml(logo.name)}</span>
          </a>
        `).join('')}
      </div>
    </div>
  `;
}

function renderTestimonials(block, isEditable) {
  const { data } = block;

  return `
    <div class="section-pad testimonials-section">
      <h2 class="section-heading" ${editAttr(isEditable, block, 'title')}>${escapeHtml(data.title)}</h2>
      <p class="section-copy" ${editAttr(isEditable, block, 'text')}>${escapeHtml(data.text)}</p>
      <div class="testimonial-grid">
        ${data.items.map((item, index) => `
          <article class="testimonial-card">
            <i class="bi bi-quote"></i>
            <p ${editAttr(isEditable, block, `items.${index}.quote`)}>${escapeHtml(item.quote)}</p>
            <div>
              <strong ${editAttr(isEditable, block, `items.${index}.name`)}>${escapeHtml(item.name)}</strong>
              <span ${editAttr(isEditable, block, `items.${index}.role`)}>${escapeHtml(item.role)}</span>
            </div>
            <a class="card-link" ${hrefAttr(item.link)}>Lihat cerita <i class="bi bi-arrow-right"></i></a>
          </article>
        `).join('')}
      </div>
    </div>
  `;
}

function renderPricing(block, isEditable) {
  const { data } = block;

  return `
    <div class="section-pad pricing-section">
      <h2 class="section-heading" ${editAttr(isEditable, block, 'title')}>${escapeHtml(data.title)}</h2>
      <p class="section-copy" ${editAttr(isEditable, block, 'text')}>${escapeHtml(data.text)}</p>
      <div class="pricing-grid">
        ${data.plans.map((plan, index) => `
          <article class="pricing-card">
            <h3 ${editAttr(isEditable, block, `plans.${index}.name`)}>${escapeHtml(plan.name)}</h3>
            <div class="price" ${editAttr(isEditable, block, `plans.${index}.price`)}>${escapeHtml(plan.price)}</div>
            <p ${editAttr(isEditable, block, `plans.${index}.text`)}>${escapeHtml(plan.text)}</p>
            <a class="btn btn-dark w-100" ${hrefAttr(plan.link)} ${editAttr(isEditable, block, `plans.${index}.buttonLabel`)}>${escapeHtml(plan.buttonLabel)}</a>
          </article>
        `).join('')}
      </div>
    </div>
  `;
}

function renderCta(block, isEditable) {
  const { data } = block;

  return `
    <div class="cta-band">
      <div>
        <h2 class="h1 mb-2" ${editAttr(isEditable, block, 'title')}>${escapeHtml(data.title)}</h2>
        <p ${editAttr(isEditable, block, 'text')}>${escapeHtml(data.text)}</p>
      </div>
      <a ${hrefAttr(data.buttonUrl)} class="btn btn-light btn-lg" ${editAttr(isEditable, block, 'buttonLabel')}>${escapeHtml(data.buttonLabel)}</a>
    </div>
  `;
}

export function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function safeUrl(value) {
  return String(value ?? '').replaceAll("'", '%27');
}

function youtubeEmbedUrl(value) {
  const raw = String(value || '').trim();
  const fallback = 'https://www.youtube.com/embed/dQw4w9WgXcQ';

  if (!raw) return fallback;

  try {
    const url = new URL(raw);
    if (url.hostname.includes('youtu.be')) {
      const id = url.pathname.split('/').filter(Boolean)[0];
      return id ? `https://www.youtube.com/embed/${id}` : fallback;
    }
    if (url.hostname.includes('youtube.com')) {
      const id = url.searchParams.get('v') || url.pathname.split('/').filter(Boolean).at(-1);
      return id ? `https://www.youtube.com/embed/${id}` : fallback;
    }
  } catch {
    return fallback;
  }

  return raw;
}
