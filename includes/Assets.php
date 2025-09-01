<?php

namespace WeLabs\WeCatering;

class Assets {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_all_scripts' ), 10 );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10 );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_scripts' ) );
		}
	}

	/**
	 * Register all Dokan scripts and styles.
	 *
	 * @return void
	 */
	public function register_all_scripts() {
		$this->register_styles();
		$this->register_scripts();
	}

	/**
	 * Register scripts.
	 *
	 * @param array $scripts
	 *
	 * @return void
	 */
	public function register_scripts() {
		$admin_script    = WE_CATERING_PLUGIN_ADMIN_ASSET . '/js/script.js';
		$frontend_script = WE_CATERING_PLUGIN_PUBLIC_ASSET . '/js/script.js';

		// Register Select2 for admin
		wp_register_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '4.1.0', true );
		wp_register_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0' );

		wp_register_script( 'we_catering_admin_script', $admin_script, array( 'jquery', 'select2' ), WE_CATERING_PLUGIN_VERSION, true );
		wp_register_script( 'we_catering_script', $frontend_script, array( 'jquery' ), WE_CATERING_PLUGIN_VERSION, true );
	}

	/**
	 * Register styles.
	 *
	 * @return void
	 */
	public function register_styles() {
		$admin_style    = WE_CATERING_PLUGIN_ADMIN_ASSET . '/css/style.css';
		$frontend_style = WE_CATERING_PLUGIN_PUBLIC_ASSET . '/css/style.css';

		wp_register_style( 'we_catering_admin_style', $admin_style, array(), WE_CATERING_PLUGIN_VERSION );
		wp_register_style( 'we_catering_style', $frontend_style, array(), WE_CATERING_PLUGIN_VERSION );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_style( 'select2' );
		wp_enqueue_style( 'we_catering_admin_style' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'we_catering_admin_script' );
		wp_localize_script(
			'we_catering_admin_script',
			'We_Catering_Admin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'we_catering_admin_nonce' ),
			)
		);
	}

	/**
	 * Enqueue front-end scripts.
	 *
	 * @return void
	 */
	public function enqueue_front_scripts() {
		wp_enqueue_style( 'we_catering_style' );
		wp_enqueue_script( 'we_catering_script' );

		wp_localize_script(
			'we_catering_script',
			'We_Catering',
			array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'we_catering_public_nonce' ),
				'current_user_id'  => get_current_user_id(),
			)
		);
	}
}
