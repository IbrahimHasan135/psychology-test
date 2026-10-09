const fallbackTypes = {
  hero: 'Hero',
  cards: 'Card Grid',
  split: 'Gambar + Teks',
  gallery: 'Gallery',
  video: 'Video YouTube',
  logos: 'Logo List',
  testimonials: 'Testimonial',
  pricing: 'Pricing',
  cta: 'CTA',
};

export const blockDefinitions = window.NOVABASE_BLOCK_DEFINITIONS
  ? window.NOVABASE_BLOCK_DEFINITIONS
  : Object.fromEntries(Object.entries(fallbackTypes).map(([type, label]) => [type, { label, category: "Core" }]));

export const blockTypes = Object.fromEntries(Object.entries(blockDefinitions).map(([type, definition]) => [type, definition.label]));
