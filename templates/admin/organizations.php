<?php
/**
 * Organizations Template
 *
 * @package WeCatering
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$org_model = new \WeLabs\WeCatering\Models\Organization();
$orgs = $org_model->get_all( array( 'orderby' => 'name', 'order' => 'ASC' ) );
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Organizations', 'we-catering' ); ?></h1>
    <a href="#" class="page-title-action" id="add-organization"><?php esc_html_e( 'Add New Organization', 'we-catering' ); ?></a>
    
    <div class="we-catering-organizations">
        <!-- Organizations List -->
        <div class="we-catering-organizations-list">
            <h2><?php esc_html_e( 'Organizations', 'we-catering' ); ?></h2>
            <div class="we-catering-table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Description', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Contact Person', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Email', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Phone', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'we-catering' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $orgs ) ) : ?>
                            <?php foreach ( $orgs as $o ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $o->name ); ?></td>
                                    <td><?php echo esc_html( wp_trim_words( (string) $o->description, 18 ) ); ?></td>
                                    <td><?php echo esc_html( (string) $o->contact_person ); ?></td>
                                    <td><?php echo esc_html( (string) $o->email ); ?></td>
                                    <td><?php echo esc_html( (string) $o->phone ); ?></td>
                                    <td><span class="we-catering-status <?php echo esc_attr( $o->status ); ?>"><?php echo esc_html( ucfirst( (string) $o->status ) ); ?></span></td>
                                    <td>
                                        <a href="#" class="button button-small we-catering-confirm delete-organization" data-confirm="<?php esc_attr_e( 'Delete this organization?', 'we-catering' ); ?>" data-id="<?php echo esc_attr( $o->id ); ?>"><?php esc_html_e( 'Delete', 'we-catering' ); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7"><?php esc_html_e( 'No organizations found.', 'we-catering' ); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Organization Users Management -->
        <div class="we-catering-organization-users">
            <h2><?php esc_html_e( 'Organization Users Management', 'we-catering' ); ?></h2>
            
            <!-- Organization Selection -->
            <div class="we-catering-user-selection">
                <label for="organization-select"><?php esc_html_e( 'Select Organization:', 'we-catering' ); ?></label>
                <select id="organization-select" name="organization_id">
                    <option value=""><?php esc_html_e( '-- Select Organization --', 'we-catering' ); ?></option>
                    <?php foreach ( $orgs as $o ) : ?>
                        <option value="<?php echo esc_attr( $o->id ); ?>"><?php echo esc_html( $o->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Users List (will be populated via AJAX) -->
            <div id="organization-users-list" class="we-catering-table-container" style="display: none; margin-top: 20px;">
                <h3><?php esc_html_e( 'Current Users', 'we-catering' ); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'User', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Email', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Role', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'we-catering' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'we-catering' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="users-list-body">
                        <!-- Users will be loaded here via AJAX -->
                    </tbody>
                </table>
            </div>

            <!-- Add User Form -->
            <div id="add-user-section" class="we-catering-add-user" style="display: none; margin-top: 30px;">
                <h3><?php esc_html_e( 'Add User to Organization', 'we-catering' ); ?></h3>
                
                <!-- Tabs for Add Existing User vs Create New User -->
                <h2 class="nav-tab-wrapper">
                    <a href="#add-existing-user" class="nav-tab nav-tab-active"><?php esc_html_e( 'Add Existing User', 'we-catering' ); ?></a>
                    <a href="#create-new-user" class="nav-tab"><?php esc_html_e( 'Create New User', 'we-catering' ); ?></a>
                </h2>
                
                <!-- Add Existing User Tab -->
                <div id="add-existing-user" class="we-catering-tab-content">
                    <form id="add-user-form" class="we-catering-form">
                        <input type="hidden" name="organization_id" id="add-user-org-id" value="" />
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="user-select"><?php esc_html_e( 'Select User', 'we-catering' ); ?></label>
                                </th>
                                <td>
                                    <select id="user-select" name="user_id" required>
                                        <option value=""><?php esc_html_e( '-- Select User --', 'we-catering' ); ?></option>
                                        <!-- Users will be populated via AJAX -->
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="user-role"><?php esc_html_e( 'Role', 'we-catering' ); ?></label>
                                </th>
                                <td>
                                    <select id="user-role" name="role" required>
                                        <option value="user"><?php esc_html_e( 'User', 'we-catering' ); ?></option>
                                        <option value="admin"><?php esc_html_e( 'Admin', 'we-catering' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php esc_html_e( 'Add User', 'we-catering' ); ?></button>
                        </p>
                    </form>
                </div>
                
                <!-- Create New User Tab -->
                <div id="create-new-user" class="we-catering-tab-content" style="display: none;">
                    <form id="create-user-form" class="we-catering-form">
                        <input type="hidden" name="organization_id" id="create-user-org-id" value="" />
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="new-user-username"><?php esc_html_e( 'Username', 'we-catering' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="new-user-username" name="username" class="regular-text" required />
                                    <p class="description"><?php esc_html_e( 'Username for login', 'we-catering' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="new-user-password"><?php esc_html_e( 'Password', 'we-catering' ); ?></label>
                                </th>
                                <td>
                                    <input type="password" id="new-user-password" name="password" class="regular-text" required />
                                    <p class="description"><?php esc_html_e( 'Password for login', 'we-catering' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="new-user-email"><?php esc_html_e( 'Email', 'we-catering' ); ?></label>
                                </th>
                                <td>
                                    <input type="email" id="new-user-email" name="email" class="regular-text" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="new-user-first-name"><?php esc_html_e( 'First Name', 'we-catering' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="new-user-first-name" name="first_name" class="regular-text" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="new-user-last-name"><?php esc_html_e( 'Last Name', 'we-catering' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="new-user-last-name" name="last_name" class="regular-text" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="new-user-role"><?php esc_html_e( 'Organization Role', 'we-catering' ); ?></label>
                                </th>
                                <td>
                                    <select id="new-user-role" name="role" required>
                                        <option value="user"><?php esc_html_e( 'User', 'we-catering' ); ?></option>
                                        <option value="admin"><?php esc_html_e( 'Admin', 'we-catering' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php esc_html_e( 'Create User & Add to Organization', 'we-catering' ); ?></button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
