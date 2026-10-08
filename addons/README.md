# NovaBase Addons

This folder is reserved for future Novalynk product features. Each addon should live in its own folder so product-specific code does not mix with NovaBase core.

Recommended structure:

```text
addons/
  AddonName/
    addon.php
    README.md
    routes/
      web.php
    database/
      migrations/
      seeders/
    app/
      Http/Controllers/
      Models/
      Services/
      Dashboard/
      Reports/
      Listeners/
    resources/
      views/
```

Simple contract:

- NovaBase core owns auth, roles, admin shell, page management, addon registry, and website rendering.
- Addons own product-specific features.
- If an addon needs tables, create migrations in `addons/AddonName/database/migrations`.
- If an addon needs admin menu items, register them in `addon.php`.
- If an addon needs dashboard cards, register card classes in `dashboard_cards`.
- If an addon needs permissions, register them in `permissions` using the `slug.action` format.
- If an addon needs website-facing visuals, add card templates in `resources/views/website/card-templates` or create an addon renderer.

The active example is `addons/Demo`. New addons are enabled from `config/addons.php`.
