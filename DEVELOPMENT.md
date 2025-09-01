# We Catering – Development Guide

This document explains the project structure, coding patterns, extension points, and step-by-step workflows so any engineer can continue building the We Catering plugin confidently.

## Overview
- WordPress plugin providing organization-wise catering management.
- Admin app features: dashboard, menu management, daily menus, orders, organizations, reports, settings.
- Backend: custom tables + models (CRUD), secure AJAX endpoints, asset management.

## Tech & Standards
- PHP 7.4+ compatible, WordPress 5.4+
- PSR-4 autoloading via Composer (`WeLabs\WeCatering\` → `includes/`)
- WordPress Coding Standards (WPCS) configured via `phpcs.xml`
- Follow project architecture and patterns below

## Project Structure
```
we-catering/
  we-catering.php                // Plugin bootstrap
  includes/
    WeCatering.php               // Main plugin container (entry point for classes)
    Assets.php                   // Script/style registration & enqueue
    Admin.php                    // Admin menus & page routing
    Database.php                 // DB schema (create/update/drop) + defaults
    Ajax.php                     // Admin AJAX endpoints
    Models/
      MenuItem.php               // Menu item CRUD and queries
      Organization.php           // Organization CRUD and relations
      Order.php                  // Order & order items CRUD, status, summaries
      DailyMenu.php              // Daily menu setup & availability
  templates/
    admin/*.php                  // Admin page templates
  assets/
    admin/{css,js}/              // Admin UI assets
    public/{css,js}/             // Frontend assets (future)
  README.md                      // User-level instructions
  DEVELOPMENT.md                 // This file – engineering guide
  uninstall.php                  // Cleanup data on uninstall
  composer.json                  // Autoload + dev tooling
```

## Core Architecture Rules
- Instantiate new classes only in `includes/WeCatering.php` within `init_classes()` using the container:
  ```php
  $this->container['your_key'] = new YourClass();
  ```
- All scripts/styles are registered/enqueued in `includes/Assets.php` only.
- Admin pages load PHP templates from `templates/admin/`.
- Use models under `includes/Models/` for all data access (CRUD, queries).
- Use secure AJAX endpoints in `includes/Ajax.php` for dynamic actions.
- Keep business logic out of templates; templates are for display.

## Activation / DB Lifecycle
- Activation (`WeCatering::activate()`):
  - Creates/updates DB tables via `Database::create_tables()`
  - Sets default options
  - Flushes rewrite rules
- Uninstall (`uninstall.php`): drops plugin tables and deletes options

### Custom Tables (prefix `wp_we_catering_`)
- `organizations` (id, name, contact, email, phone, status)
- `menu_items` (id, name, description, price, category, dietary_type, status)
- `daily_menus` (id, menu_date, menu_item_id, is_available)
- `orders` (id, order_number, user_id, organization_id, order_date, total_amount, status, notes)
- `order_items` (id, order_id, menu_item_id, quantity, unit_price, total_price)
- `user_organizations` (user_id, organization_id, role, status)

## Key Classes
- `WeLabs\WeCatering\WeCatering`
  - Defines constants
  - Wires activation/deactivation
  - Initializes classes in container: `assets`, `admin`, `database`, `ajax`
- `WeLabs\WeCatering\Assets`
  - Registers and enqueues admin/public assets
  - Localizes `We_Catering_Admin` for AJAX (`ajax_url`, `nonce`)
- `WeLabs\WeCatering\Admin`
  - Registers admin menu: Dashboard, Menu Management, Orders, Organizations, Reports, Settings
  - Loads templates from `templates/admin/*.php`
- `WeLabs\WeCatering\Database`
  - Table creation with `dbDelta`
  - Default options and helpers
- `WeLabs\WeCatering\Ajax`
  - Handles admin AJAX actions (see list below)
- Models (`includes/Models/`)
  - `MenuItem`: CRUD, filters, categories, daily menu lookup
  - `Organization`: CRUD, user assign/remove, counts
  - `Order`: CRUD with transaction, number generator, items, status, summaries
  - `DailyMenu`: set/copy daily menus, availability, stats, upcoming dates

## Templates (Admin)
- `dashboard.php` – KPI cards (orders, revenue, orgs, items)
- `menu-management.php` – items list + daily menu form (UI scaffolding)
- `order-management.php` – filters, summary, orders table
- `organizations.php` – org list and org users (UI scaffolding)
- `reports.php` – filters + results shell (to be wired to data)
- `settings.php` – form scaffolding (register settings pending)

## Admin Assets
- `assets/admin/css/style.css` – Dashboard cards, tables, modals, responsive
- `assets/admin/js/script.js` – UI interactions, modals, AJAX calls scaffolding

## AJAX Endpoints (Admin)
Action names (POST to `admin-ajax.php`):
- Menu Items
  - `we_catering_save_menu_item` (name, description, price, category)
  - `we_catering_get_menu_items` (filters)
  - `we_catering_delete_menu_item` (menu_item_id)
- Organizations
  - `we_catering_save_organization` (name, contact, email, phone)
  - `we_catering_get_organizations` ()
  - `we_catering_delete_organization` (organization_id)
- Orders
  - `we_catering_create_order` (user_id, organization_id, order_date, total_amount, order_items[])
  - `we_catering_get_orders` (status, organization_id, order_date)
  - `we_catering_update_order_status` (order_id, status)
  - `we_catering_delete_order` (order_id)
- Daily Menu
  - `we_catering_set_daily_menu` (date, menu_items[])
  - `we_catering_get_daily_menu` (date)
  - `we_catering_update_item_availability` (menu_item_id, date, is_available)

All endpoints require:
- Capability: `manage_options`
- Nonce: `we_catering_admin_nonce` (see `Assets.php` localization)

## Extension Points
- Hooks
  - Do action after plugin loaded: `do_action( 'we_catering_loaded' );`
  - Filter template path: `apply_filters( 'we-catering_template', $template, $name );`
  - Filter admin template path: `apply_filters( 'we-catering_admin_template', $template, $template_name );`
- Add your own filters/actions in new features; keep namespaced and prefixed `we_catering_...`

## Coding Patterns
- Always introduce new classes in `WeCatering::init_classes()`:
  ```php
  // includes/WeCatering.php
  $this->container['feature'] = new FeatureClass();
  ```
- Place DB logic in a Model (CRUD + queries). Avoid SQL in controllers/templates.
- Sanitize and validate all request data. Use `$wpdb->prepare()` for queries.
- Keep templates free of heavy logic. Fetch data in the controller or at top of the template.
- Use `current_time()` (not `date()`) for WP-aware dates.

## Local Dev & QA
```bash
# Install deps
composer update

# Lint (WPCS)
composer phpcs

# Auto-fix (where possible)
composer phpcbf
```

## How To Implement a New Feature
1. Create a Model under `includes/Models/` for data storage/retrieval.
2. Add a Controller/Service class if needed; register it in `WeCatering::init_classes()`.
3. Add admin submenu or UI pieces in `includes/Admin.php`.
4. Create templates under `templates/admin/`.
5. Register/enqueue scripts in `includes/Assets.php` and localize data.
6. Add secure AJAX endpoints in `includes/Ajax.php` (nonce + capability checks).
7. Update `README.md` and this `DEVELOPMENT.md` if needed.
8. Run linters and validate pages.

## Current Completion Status (Engineering)
- Core container, constants, activation/deactivation: DONE
- DB schema + defaults: DONE
- Admin menu, templates, assets: DONE (UI scaffolding complete)
- Models: `MenuItem`, `Organization`, `Order`, `DailyMenu`: DONE
- AJAX: Admin endpoints for items, orgs, orders, daily menu: DONE
- Dashboard KPIs: DONE (orders, revenue, orgs, items)
- Order Management page: DONE (filters, summary, list – backend wired)
- Settings page: UI scaffolding ONLY (needs settings registration)
- Reports page: UI scaffolding ONLY (needs data wiring)

## Next Steps (Prioritized)
1. Frontend Client Experience
   - Public shortcodes/templates to display daily menu and accept orders
   - Validate order window and quantities on the frontend
   - Persist orders using `Models\Order` and `Models\DailyMenu`
   - Scripts/styles via `assets/public/`; enqueue from `Assets.php`
2. Settings API Wiring
   - Register settings with `register_setting`, `add_settings_section`, `add_settings_field`
   - Persist/read settings currently displayed in `templates/admin/settings.php`
3. Reports Engine
   - Implement data providers using `Models\Order` summaries (daily/org/menu/user)
   - Add CSV export endpoints
4. Email Notifications
   - On order create/confirm: send confirmation emails
   - Reminder emails before cutoff (WP Cron)
5. UX Enhancements (Admin)
   - Replace placeholder tables with real lists (pagination, search, filters)
   - Edit/delete operations for items/orgs via AJAX
6. Security & Hardening
   - Ensure all AJAX inputs use `wp_unslash()` + sanitize + validate
   - Audit templates for escaping/escaping context
7. Tests & Tooling
   - Add unit tests for Models (where feasible)
   - Smoke tests for critical flows

## Examples & Snippets
- Accessing container instances from anywhere in plugin scope:
  ```php
  $plugin = \WeLabs\WeCatering\WeCatering::init();
  $db     = $plugin->database; // from container
  ```
- Getting a template path:
  ```php
  $template = \WeLabs\WeCatering\WeCatering::init()->get_template( 'admin/dashboard.php' );
  ```
- Enqueueing assets (admin):
  ```php
  // Already handled by Assets; add new handles there if needed
  ```

## Contact
- Primary maintainer: Labbi Ahmed (welabs.dev)
- For support/issues, see `README.md`
