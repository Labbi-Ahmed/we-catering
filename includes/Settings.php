<?php

namespace WeLabs\WeCatering;

class Settings {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register settings, sections and fields
	 */
	public function register_settings() {
		$option_group = 'we_catering_settings';
		$option_name  = 'we_catering_settings';

		register_setting( $option_group, $option_name, array( $this, 'sanitize_settings' ) );

		// General section
		add_settings_section(
			'we_catering_settings_general',
			__( 'General Settings', 'we-catering' ),
			'__return_false',
			$option_group
		);

		add_settings_field(
			'order_window_start',
			__( 'Order Window Start Time', 'we-catering' ),
			array( $this, 'render_time_field' ),
			$option_group,
			'we_catering_settings_general',
			array(
				'label_for' => 'order_window_start',
				'option'    => 'order_window_start',
				'placeholder' => '10:00',
			)
		);

		add_settings_field(
			'order_window_end',
			__( 'Order Window End Time', 'we-catering' ),
			array( $this, 'render_time_field' ),
			$option_group,
			'we_catering_settings_general',
			array(
				'label_for' => 'order_window_end',
				'option'    => 'order_window_end',
				'placeholder' => '11:30',
			)
		);

		add_settings_field(
			'currency',
			__( 'Currency', 'we-catering' ),
			array( $this, 'render_currency_field' ),
			$option_group,
			'we_catering_settings_general',
			array(
				'label_for' => 'currency',
				'option'    => 'currency',
			)
		);

		// Limits section
		add_settings_section(
			'we_catering_settings_limits',
			__( 'Order Limits', 'we-catering' ),
			'__return_false',
			$option_group
		);

		add_settings_field(
			'max_items_per_order',
			__( 'Maximum Items Per Order', 'we-catering' ),
			array( $this, 'render_number_field' ),
			$option_group,
			'we_catering_settings_limits',
			array(
				'label_for' => 'max_items_per_order',
				'option'    => 'max_items_per_order',
				'attrs'     => array( 'min' => 1, 'max' => 100, 'class' => 'small-text' ),
			)
		);

		add_settings_field(
			'max_quantity_per_item',
			__( 'Maximum Quantity Per Item', 'we-catering' ),
			array( $this, 'render_number_field' ),
			$option_group,
			'we_catering_settings_limits',
			array(
				'label_for' => 'max_quantity_per_item',
				'option'    => 'max_quantity_per_item',
				'attrs'     => array( 'min' => 1, 'max' => 50, 'class' => 'small-text' ),
			)
		);
	}

	/**
	 * Sanitize settings
	 */
	public function sanitize_settings( $input ) {
		$defaults = $this->get_defaults();
		$clean = array();

		$clean['order_window_start']   = isset( $input['order_window_start'] ) ? sanitize_text_field( $input['order_window_start'] ) : $defaults['order_window_start'];
		$clean['order_window_end']     = isset( $input['order_window_end'] ) ? sanitize_text_field( $input['order_window_end'] ) : $defaults['order_window_end'];
		$clean['currency']             = isset( $input['currency'] ) ? sanitize_text_field( $input['currency'] ) : $defaults['currency'];
		$clean['max_items_per_order']  = isset( $input['max_items_per_order'] ) ? absint( $input['max_items_per_order'] ) : $defaults['max_items_per_order'];
		$clean['max_quantity_per_item']= isset( $input['max_quantity_per_item'] ) ? absint( $input['max_quantity_per_item'] ) : $defaults['max_quantity_per_item'];
		
		return $clean;
	}

	/**
	 * Defaults used when options are missing
	 */
	private function get_defaults() {
		return array(
			'order_window_start'     => '10:00',
			'order_window_end'       => '11:30',
			'currency'               => 'USD',
			'max_items_per_order'    => 10,
			'max_quantity_per_item'  => 5,
		);
	}

	/**
	 * Helpers to render fields
	 */
	public function render_time_field( $args ) {
		$defaults = $this->get_defaults();
		$opts     = get_option( 'we_catering_settings', array() );
		$key      = $args['option'];
		$val      = isset( $opts[ $key ] ) && $opts[ $key ] !== '' ? $opts[ $key ] : ( isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
		printf( '<input type="time" id="%1$s" name="we_catering_settings[%1$s]" value="%2$s" placeholder="%3$s" class="regular-text"/>', esc_attr( $key ), esc_attr( $val ), esc_attr( isset( $args['placeholder'] ) ? $args['placeholder'] : '' ) );
	}

	public function render_number_field( $args ) {
		$defaults = $this->get_defaults();
		$opts     = get_option( 'we_catering_settings', array() );
		$key      = $args['option'];
		$val      = isset( $opts[ $key ] ) && $opts[ $key ] !== '' ? $opts[ $key ] : ( isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
		$attrs = '';
		if ( ! empty( $args['attrs'] ) && is_array( $args['attrs'] ) ) {
			foreach ( $args['attrs'] as $a => $v ) {
				$attrs .= sprintf( ' %s="%s"', esc_attr( $a ), esc_attr( $v ) );
			}
		}
		printf( '<input type="number" id="%1$s" name="we_catering_settings[%1$s]" value="%2$s" %3$s/>', esc_attr( $key ), esc_attr( $val ), $attrs );
	}

	public function render_currency_field() {
		$opts = get_option( 'we_catering_settings', $this->get_defaults() );
		$val  = isset( $opts['currency'] ) ? $opts['currency'] : 'USD';
		echo '<select id="currency" name="we_catering_settings[currency]" class="regular-text">';
		$currencies = array( 'USD' => 'US Dollar ($)', 'EUR' => 'Euro (€)', 'GBP' => 'British Pound (£)', 'BDT' => 'Bangladeshi Taka (৳)' );
		foreach ( $currencies as $code => $label ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $code ), selected( $val, $code, false ), esc_html( $label ) );
		}
		echo '</select>';
	}
}
