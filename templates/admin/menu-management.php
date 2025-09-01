<?php
/**
 * Menu Management Template
 *
 * @package WeCatering
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$menu_model = new \WeLabs\WeCatering\Models\MenuItem();
$menu_items = $menu_model->get_all( array( 'orderby' => 'name', 'order' => 'ASC' ) );
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Menu Management', 'we-catering' ); ?></h1>
    <a href="#" class="page-title-action" id="add-menu-item"><?php esc_html_e( 'Add New Menu Item', 'we-catering' ); ?></a>
    
    <div class="we-catering-menu-management">
        <!-- Menu Items List -->
        <div class="we-catering-menu-items">
            <h2><?php esc_html_e( 'Menu Items', 'we-catering' ); ?></h2>
            <div class="we-catering-table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Description', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Items', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Price', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Category', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'we-catering' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $menu_items ) ) : ?>
                            <?php foreach ( $menu_items as $item ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $item->name ); ?></td>
                                    <td><?php echo esc_html( wp_trim_words( (string) $item->description, 18 ) ); ?></td>
                                    <td><?php echo esc_html( wp_trim_words( (string) $item->items, 10 ) ); ?></td>
                                    <td>$<?php echo esc_html( number_format( (float) $item->price, 2 ) ); ?></td>
                                    <td><?php echo esc_html( (string) $item->category ); ?></td>
                                    <td>
                                        <span class="we-catering-status <?php echo esc_attr( $item->status ); ?>"><?php echo esc_html( ucfirst( (string) $item->status ) ); ?></span>
                                    </td>
                                    <td>
                                        <a href="#" class="button button-small edit-menu-item" data-id="<?php echo esc_attr( $item->id ); ?>"><?php esc_html_e( 'Edit', 'we-catering' ); ?></a>
                                        <a href="#" class="button button-small we-catering-confirm delete-menu-item" data-confirm="<?php esc_attr_e( 'Delete this menu item?', 'we-catering' ); ?>" data-id="<?php echo esc_attr( $item->id ); ?>"><?php esc_html_e( 'Delete', 'we-catering' ); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8"><?php esc_html_e( 'No menu items found.', 'we-catering' ); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Daily Menu Setup -->
        <div class="we-catering-daily-menu">
            <h2><?php esc_html_e( 'Daily Menu Setup', 'we-catering' ); ?></h2>
            <div class="we-catering-daily-menu-form">
                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="menu_date"><?php esc_html_e( 'Menu Date', 'we-catering' ); ?></label>
                            </th>
                            <td>
                                <input type="date" id="menu_date" name="menu_date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" class="regular-text" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="menu_items"><?php esc_html_e( 'Select Menu Items', 'we-catering' ); ?></label>
                            </th>
                            <td>
                                <div class="we-catering-menu-items-selector">
                                    <?php if ( ! empty( $menu_items ) ) : ?>
                                        <?php foreach ( $menu_items as $item ) : ?>
                                            <label style="display:inline-block;margin:0 12px 8px 0;">
                                                <input type="checkbox" name="daily_menu_items[]" value="<?php echo esc_attr( $item->id ); ?>" />
                                                <?php echo esc_html( $item->name ); ?> ($<?php echo esc_html( number_format( (float) $item->price, 2 ) ); ?>)
                                            </label>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <p><?php esc_html_e( 'No menu items available. Please add menu items first.', 'we-catering' ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="button" id="save-daily-menu" class="button button-primary"><?php esc_html_e( 'Save Daily Menu', 'we-catering' ); ?></button>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
