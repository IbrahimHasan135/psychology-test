# Demo Addon

Addon contoh untuk membuktikan fondasi modular NovaBase:

- manifest: `addon.php`
- route: `routes/web.php`
- controller: `app/Http/Controllers/DemoController.php`
- dashboard card: `app/Dashboard/DemoOverviewCard.php`
- views: `resources/views`
- permission: `demo.view`

## Tenant Management

The Demo addon is also the tenancy reference implementation. Platform Super Admin can create tenants from the addon management screen. The reusable provisioning service creates the tenant, owner account, tenant membership, and tenant Home page in one transaction.

The public `Tenant Account Signup` Web Editor block is automatically added to the default Home page when the addon migration runs. It renders a real signup form on the public website and posts to `demo.tenant-accounts.store`. The owner receives the tenant role `super_admin` through `tenant_memberships`, then signs in at `/{tenant-slug}/login`.

## Web Editor Blocks

This addon registers demo.promo-card through addon.php. The definition lives in app/PageBuilder/DemoPromoBlock.php. Its defaults become the block data, its fields become inspector inputs, and the shared addon-card renderer displays it in the editor and public preview.

The block requires demo.view. Disable the Demo addon and the block will no longer be available for new pages; existing state must use the unavailable fallback behavior.

`demo.tenant-signup` is the public signup card. Its copy is editable from the inspector while its account fields and provisioning behavior remain controlled by the addon controller/service.
