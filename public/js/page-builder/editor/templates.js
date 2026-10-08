export const DEFAULT_TEMPLATE_ID = 'template-studio';

const fallbackTemplates = [
  {
    id: 'template-studio',
    name: 'Studio Clean',
    description: 'SaaS modern, putih bersih',
  },
  {
    id: 'template-bold',
    name: 'Bold Launch',
    description: 'Kontras kuat, campaign',
  },
  {
    id: 'template-editorial',
    name: 'Editorial Luxe',
    description: 'Serif premium, magazine',
  },
  {
    id: 'template-neon',
    name: 'Neon Night',
    description: 'Dark tech, energetic',
  },
  {
    id: 'template-earth',
    name: 'Earth Calm',
    description: 'Natural, hangat, organik',
  },
];

export const designTemplates = window.NOVABASE_DESIGN_TEMPLATES
  ? Object.entries(window.NOVABASE_DESIGN_TEMPLATES).map(([id, template]) => ({ id, ...template }))
  : fallbackTemplates;

export function normalizeTemplateId(templateId) {
  return designTemplates.some((template) => template.id === templateId)
    ? templateId
    : DEFAULT_TEMPLATE_ID;
}
