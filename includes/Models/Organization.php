<?php

namespace WeLabs\WeCatering\Models;

/**
 * Organization Model
 *
 * @class Organization The class that handles organization operations
 */
class Organization {

    /**
     * Database table name
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'we_catering_organizations';
    }

    /**
     * Get all organizations
     *
     * @param array $args
     * @return array
     */
    public function get_all( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status' => 'active',
            'orderby' => 'name',
            'order' => 'ASC',
            'limit' => 0,
            'offset' => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $where_conditions = array();
        $where_values = array();

        if ( ! empty( $args['status'] ) ) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $args['status'];
        }

        $where_clause = '';
        if ( ! empty( $where_conditions ) ) {
            $where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );
        }

        $order_clause = 'ORDER BY ' . esc_sql( $args['orderby'] ) . ' ' . esc_sql( $args['order'] );

        $limit_clause = '';
        if ( $args['limit'] > 0 ) {
            $limit_clause = 'LIMIT ' . intval( $args['limit'] );
            if ( $args['offset'] > 0 ) {
                $limit_clause .= ' OFFSET ' . intval( $args['offset'] );
            }
        }

        $sql = "SELECT * FROM {$this->table_name} {$where_clause} {$order_clause} {$limit_clause}";

        if ( ! empty( $where_values ) ) {
            $sql = $wpdb->prepare( $sql, $where_values );
        }

        return $wpdb->get_results( $sql );
    }

    /**
     * Get organization by ID
     *
     * @param int $id
     * @return object|null
     */
    public function get_by_id( $id ) {
        global $wpdb;

        $sql = $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id );
        return $wpdb->get_row( $sql );
    }

    /**
     * Create new organization
     *
     * @param array $data
     * @return int|false
     */
    public function create( $data ) {
        global $wpdb;

        $defaults = array(
            'name' => '',
            'description' => '',
            'contact_person' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'status' => 'active',
        );

        $data = wp_parse_args( $data, $defaults );

        // Validate required fields
        if ( empty( $data['name'] ) ) {
            return false;
        }

        // Sanitize data
        $insert_data = array(
            'name' => sanitize_text_field( $data['name'] ),
            'description' => sanitize_textarea_field( $data['description'] ),
            'contact_person' => sanitize_text_field( $data['contact_person'] ),
            'email' => sanitize_email( $data['email'] ),
            'phone' => sanitize_text_field( $data['phone'] ),
            'address' => sanitize_textarea_field( $data['address'] ),
            'status' => sanitize_text_field( $data['status'] ),
        );

        $result = $wpdb->insert( $this->table_name, $insert_data );

        if ( $result === false ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update organization
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update( $id, $data ) {
        global $wpdb;

        $update_data = array();

        if ( isset( $data['name'] ) ) {
            $update_data['name'] = sanitize_text_field( $data['name'] );
        }

        if ( isset( $data['description'] ) ) {
            $update_data['description'] = sanitize_textarea_field( $data['description'] );
        }

        if ( isset( $data['contact_person'] ) ) {
            $update_data['contact_person'] = sanitize_text_field( $data['contact_person'] );
        }

        if ( isset( $data['email'] ) ) {
            $update_data['email'] = sanitize_email( $data['email'] );
        }

        if ( isset( $data['phone'] ) ) {
            $update_data['phone'] = sanitize_text_field( $data['phone'] );
        }

        if ( isset( $data['address'] ) ) {
            $update_data['address'] = sanitize_textarea_field( $data['address'] );
        }

        if ( isset( $data['status'] ) ) {
            $update_data['status'] = sanitize_text_field( $data['status'] );
        }

        if ( empty( $update_data ) ) {
            return false;
        }

        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array( 'id' => $id ),
            null,
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Delete organization
     *
     * @param int $id
     * @return bool
     */
    public function delete( $id ) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table_name,
            array( 'id' => $id ),
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Get organization users
     *
     * @param int $organization_id
     * @return array
     */
    public function get_users( $organization_id ) {
        global $wpdb;

        $user_organizations_table = $wpdb->prefix . 'we_catering_user_organizations';

        $sql = $wpdb->prepare(
            "SELECT u.ID, u.display_name, u.user_email, uo.role, uo.status 
             FROM {$wpdb->users} u 
             INNER JOIN {$user_organizations_table} uo ON u.ID = uo.user_id 
             WHERE uo.organization_id = %d 
             ORDER BY u.display_name",
            $organization_id
        );

        return $wpdb->get_results( $sql );
    }

    /**
     * Add user to organization
     *
     * @param int $user_id
     * @param int $organization_id
     * @param string $role
     * @return bool
     */
    public function add_user( $user_id, $organization_id, $role = 'user' ) {
        global $wpdb;

        $user_organizations_table = $wpdb->prefix . 'we_catering_user_organizations';

        $result = $wpdb->insert(
            $user_organizations_table,
            array(
                'user_id' => $user_id,
                'organization_id' => $organization_id,
                'role' => $role,
                'status' => 'active',
            ),
            array( '%d', '%d', '%s', '%s' )
        );

        return $result !== false;
    }

    /**
     * Remove user from organization
     *
     * @param int $user_id
     * @param int $organization_id
     * @return bool
     */
    public function remove_user( $user_id, $organization_id ) {
        global $wpdb;

        $user_organizations_table = $wpdb->prefix . 'we_catering_user_organizations';

        $result = $wpdb->delete(
            $user_organizations_table,
            array(
                'user_id' => $user_id,
                'organization_id' => $organization_id,
            ),
            array( '%d', '%d' )
        );

        return $result !== false;
    }

    /**
     * Get user's organization ID
     *
     * @param int $user_id
     * @return int|null
     */
    public function get_user_organization_id( $user_id ) {
        global $wpdb;

        $user_organizations_table = $wpdb->prefix . 'we_catering_user_organizations';

        // Use direct query with table name since prepare doesn't support table names as placeholders
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = $wpdb->prepare(
            "SELECT organization_id 
             FROM {$user_organizations_table} 
             WHERE user_id = %d AND status = 'active' 
             LIMIT 1",
            $user_id
        );

        $organization_id = $wpdb->get_var( $sql );

        return $organization_id ? (int) $organization_id : null;
    }

    /**
     * Count organizations
     *
     * @param array $args
     * @return int
     */
    public function count( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status' => 'active',
        );

        $args = wp_parse_args( $args, $defaults );

        $where_conditions = array();
        $where_values = array();

        if ( ! empty( $args['status'] ) ) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $args['status'];
        }

        $where_clause = '';
        if ( ! empty( $where_conditions ) ) {
            $where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );
        }

        $sql = "SELECT COUNT(*) FROM {$this->table_name} {$where_clause}";

        if ( ! empty( $where_values ) ) {
            $sql = $wpdb->prepare( $sql, $where_values );
        }

        return (int) $wpdb->get_var( $sql );
    }
}
