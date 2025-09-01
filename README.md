# We Catering Plugin

A comprehensive WordPress plugin for managing catering services, orders, and organizations.

## Features

### Admin Features
- **Dashboard**: Overview of orders, revenue, and statistics
- **Menu Management**: Add, edit, and manage menu items
- **Order Management**: View and manage orders by organization
- **Organizations**: Manage client organizations and users
- **Reports**: Generate detailed reports and analytics
- **Settings**: Configure order windows, notifications, and limits

### Client Features
- **Menu Display**: View available menu items
- **Order Placement**: Place orders with quantity selection
- **Order Management**: View and edit orders before cutoff time
- **Order History**: Track past orders and spending

## Installation

### Development Environment

1. Clone the repository
2. Run the following command for development environment:
```bash
composer update
```

### Production Environment

Run the following command for production environment to ignore the dev dependencies:
```bash
composer update --no-dev
```

### Build Release

Set execution permission to the script file by `chmod +x bin/build.sh` command. Now, Run the following bash script:
```bash
bin/build.sh
```

## Plugin Setup

1. **Activate the Plugin**: Go to WordPress Admin → Plugins → We Catering → Activate
   - This will automatically create all necessary database tables
   - Default settings will be configured automatically

2. **Access Admin Menu**: Navigate to "We Catering" in the WordPress admin menu
   - Dashboard: Overview with statistics and quick actions
   - Menu Management: Add, edit, and manage menu items
   - Order Management: View and manage orders (coming soon)
   - Organizations: Manage client organizations and users
   - Reports: Generate analytics and export data (coming soon)
   - Settings: Configure plugin settings and order windows

3. **Configure Settings**: Go to Settings page to set up:
   - Order window times (default: 10:00 AM - 11:30 AM)
   - Currency settings (USD, EUR, GBP, BDT)
   - Email notifications
   - Order limits (max items per order, max quantity per item)

4. **Add Organizations**: Create organizations that will use the catering service
   - Organization name, description, contact person
   - Email and phone information
   - Status management (active/inactive)

5. **Create Menu Items**: Add menu items with prices and categories
   - Item name, description, price
   - Category (Breakfast, Lunch, Dinner, Snacks, Beverages)
   - Dietary type information
   - Status management (active/inactive)

6. **Set Up Daily Menus**: Configure which menu items are available each day (coming soon)

## Current Implementation Status

### ✅ **Completed Features:**
- **Admin Menu System**: Complete with all submenus
- **Database Structure**: All tables created with proper relationships
- **Menu Item Management**: CRUD operations for menu items
- **Organization Management**: CRUD operations for organizations
- **AJAX Handlers**: Backend functionality for dynamic operations
- **Settings System**: Plugin configuration options
- **Professional UI**: Modern, responsive admin interface
- **Security**: Nonce verification, capability checks, data sanitization

### 🔄 **In Progress:**
- **Order Management**: Database structure ready, UI in progress
- **Daily Menu Setup**: Interface ready, backend logic in progress
- **Reports System**: Interface ready, data aggregation in progress

### 📋 **Next Steps:**
- **Order System**: Complete order creation, management, and processing
- **Frontend Client Interface**: User-facing order placement system
- **Email Notifications**: Order confirmations and reminders
- **Advanced Reports**: Detailed analytics and export functionality

## Database Tables

The plugin will create the following custom tables on activation:
- `wp_we_catering_organizations` - Organization information
- `wp_we_catering_menu_items` - Menu items and pricing
- `wp_we_catering_orders` - Order records
- `wp_we_catering_order_items` - Individual items in orders

## Hooks and Filters

### Available Hooks
- `we_catering_loaded` - Fired when plugin is fully loaded
- `we-catering_template` - For template customization
- `we-catering_admin_template` - For admin template customization

### Available Filters
- `we_catering_order_window_start` - Modify order window start time
- `we_catering_order_window_end` - Modify order window end time
- `we_catering_max_items_per_order` - Modify maximum items per order
- `we_catering_max_quantity_per_item` - Modify maximum quantity per item

## Development

### Code Standards
This plugin follows WordPress coding standards. Run the following commands:

```bash
# Check code standards
composer phpcs

# Fix code standards automatically
composer phpcbf
```

### Testing
```bash
# Run tests
composer test
```

## Support

For support and questions, please contact: labbiahmed.rucse35@gmail.com

## License

GPL2