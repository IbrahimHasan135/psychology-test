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

export const blockTypes = window.NOVABASE_BLOCK_DEFINITIONS
  ? Object.fromEntries(Object.entries(window.NOVABASE_BLOCK_DEFINITIONS).map(([type, definition]) => [type, definition.label]))
  : fallbackTypes;
