import { blockTypes } from './blockRegistry.js';
import { escapeHtml } from './siteRenderer.js';

export function renderInspector(block) {
  if (!block) return '';

  const imageFields = [];

  if (block.data.image !== undefined) {
    imageFields.push(imageField(block.id, 'image', block.data.image));
  }

  if (Array.isArray(block.data.images)) {
    block.data.images.forEach((image, index) => {
      imageFields.push(imageField(block.id, `images.${index}`, image, `Gallery image ${index + 1}`));
    });
  }

  if (Array.isArray(block.data.logos)) {
    block.data.logos.forEach((logo, index) => {
      imageFields.push(imageField(block.id, `logos.${index}.image`, logo.image, `Logo ${index + 1}`));
      imageFields.push(textField(block.id, `logos.${index}.link`, logo.link, `Logo ${index + 1} link`));
    });
  }

  const buttonLink = block.data.buttonUrl !== undefined
    ? textField(block.id, 'buttonUrl', block.data.buttonUrl, 'Button link')
    : '';

  const videoLink = block.data.videoUrl !== undefined
    ? textField(block.id, 'videoUrl', block.data.videoUrl, 'YouTube link')
    : '';

  const itemLinks = Array.isArray(block.data.items)
    ? block.data.items.map((item, index) => item.link !== undefined
      ? textField(block.id, `items.${index}.link`, item.link, `Item ${index + 1} link`)
      : '').join('')
    : '';

  const planLinks = Array.isArray(block.data.plans)
    ? block.data.plans.map((plan, index) => textField(block.id, `plans.${index}.link`, plan.link, `Plan ${index + 1} link`)).join('')
    : '';

  const cardsControl = block.type === 'cards'
    ? `<button class="btn btn-outline-primary btn-sm w-100" data-inspector-action="add-card"><i class="bi bi-plus-lg"></i> Tambah Card</button>`
    : '';

  const galleryControl = block.type === 'gallery'
    ? `<button class="btn btn-outline-primary btn-sm w-100" data-inspector-action="add-gallery-image"><i class="bi bi-image"></i> Tambah Gambar</button>`
    : '';

  const logoControl = block.type === 'logos'
    ? `<button class="btn btn-outline-primary btn-sm w-100" data-inspector-action="add-logo"><i class="bi bi-plus-lg"></i> Tambah Logo</button>`
    : '';

  const testimonialControl = block.type === 'testimonials'
    ? `<button class="btn btn-outline-primary btn-sm w-100" data-inspector-action="add-testimonial"><i class="bi bi-plus-lg"></i> Tambah Testimonial</button>`
    : '';

  const pricingControl = block.type === 'pricing'
    ? `<button class="btn btn-outline-primary btn-sm w-100" data-inspector-action="add-plan"><i class="bi bi-plus-lg"></i> Tambah Paket</button>`
    : '';

  const splitControl = block.type === 'split'
    ? `
      <label class="form-label">Posisi gambar</label>
      <select class="form-select form-select-sm" data-inspector-field="imagePosition">
        <option value="left" ${block.data.imagePosition === 'left' ? 'selected' : ''}>Kiri</option>
        <option value="right" ${block.data.imagePosition === 'right' ? 'selected' : ''}>Kanan</option>
      </select>
    `
    : '';

  return `
    <div class="inspector-card">
      <div class="fw-bold mb-1">${blockTypes[block.type] ?? block.type}</div>
      <div class="text-secondary small">ID: ${block.id}</div>
    </div>
    <div class="inspector-card">
      ${navControl(block)}
      ${block.navEnabled ? rootTextField(block.id, 'navLabel', block.navLabel ?? block.data.title ?? block.type, 'Nama section di navbar') : ''}
      ${splitControl}
      ${splitControl ? '<hr>' : ''}
      ${buttonLink}
      ${videoLink}
      ${itemLinks}
      ${planLinks}
      ${imageFields.join('')}
      ${cardsControl}
      ${galleryControl}
      ${logoControl}
      ${testimonialControl}
      ${pricingControl}
    </div>
    <div class="inspector-card d-grid gap-2">
      <button class="btn btn-outline-primary btn-sm" data-inspector-action="add-after"><i class="bi bi-plus-lg"></i> Tambah Section Setelah Ini</button>
      <button class="btn btn-outline-dark btn-sm" data-inspector-action="duplicate"><i class="bi bi-copy"></i> Duplikat Section</button>
      <button class="btn btn-outline-danger btn-sm" data-inspector-action="delete"><i class="bi bi-trash"></i> Hapus Section</button>
    </div>
  `;
}

function imageField(blockId, path, value, label = 'Gambar') {
  return `
    <div class="mb-3">
      <label class="form-label">${label} URL</label>
      <input class="form-control form-control-sm" value="${escapeHtml(value)}" data-image-field="${blockId}:${path}">
      <label class="btn btn-outline-secondary btn-sm w-100 mt-2">
        <i class="bi bi-upload"></i>
        Upload file
        <input type="file" accept="image/*" class="d-none" data-upload-field="${blockId}:${path}">
      </label>
    </div>
  `;
}

function textField(blockId, path, value, label) {
  return `
    <div class="mb-3">
      <label class="form-label">${label}</label>
      <input class="form-control form-control-sm" value="${escapeHtml(value)}" data-text-field="${blockId}:${path}">
    </div>
  `;
}

function rootTextField(blockId, path, value, label) {
  return `
    <div class="mb-3">
      <label class="form-label">${label}</label>
      <input class="form-control form-control-sm" value="${escapeHtml(value)}" data-root-field="${blockId}:${path}">
    </div>
  `;
}

function navControl(block) {
  if (block.navEnabled) {
    return `
      <div class="mb-3 d-grid gap-2">
        <div class="small text-secondary">Section ini tampil di navbar.</div>
        <button class="btn btn-outline-warning btn-sm" data-inspector-action="remove-nav-section">
          <i class="bi bi-dash-circle"></i>
          Hapus dari Daftar Section
        </button>
      </div>
    `;
  }

  return `
    <div class="mb-3 d-grid gap-2">
      <div class="small text-secondary">Block ini belum masuk daftar section navbar.</div>
      <button class="btn btn-outline-primary btn-sm" data-inspector-action="add-nav-section">
        <i class="bi bi-list-ul"></i>
        Tambah ke Daftar Section
      </button>
    </div>
  `;
}
