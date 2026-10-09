# Demo Addon

Addon contoh untuk membuktikan fondasi modular NovaBase:

- manifest: `addon.php`
- route: `routes/web.php`
- controller: `app/Http/Controllers/DemoController.php`
- dashboard card: `app/Dashboard/DemoOverviewCard.php`
- views: `resources/views`
- permission: `demo.view`

## Web Editor Block

This addon registers demo.promo-card through addon.php. The definition lives in app/PageBuilder/DemoPromoBlock.php. Its defaults become the block data, its fields become inspector inputs, and the shared addon-card renderer displays it in the editor and public preview.

The block requires demo.view. Disable the Demo addon and the block will no longer be available for new pages; existing state must use the unavailable fallback behavior.
