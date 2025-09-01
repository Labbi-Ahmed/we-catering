<?php
/**
 * Test script for menu item modal functionality
 */

// Try to include WordPress core with multiple possible paths
$wp_load_paths = array(
    '../../../wp-load.php', // From plugin directory
    '../../../../wp-load.php', // From deeper subdirectory
    dirname( __DIR__, 3 ) . '/wp-load.php', // Relative to current file
);

$wp_loaded = false;
foreach ( $wp_load_paths as $wp_load_path ) {
    if ( file_exists( $wp_load_path ) ) {
        require_once $wp_load_path;
        $wp_loaded = true;
        break;
    }
}

if ( ! $wp_loaded ) {
    die( '❌ Could not load WordPress. Make sure this script is run from within the WordPress plugin directory.' . "\n" );
}

// Check if WordPress functions are available
if ( ! function_exists( 'admin_url' ) || ! function_exists( 'wp_create_nonce' ) ) {
    die( '❌ WordPress functions not available. Make sure WordPress loaded correctly.' . "\n" );
}

// Test the AJAX endpoint
$url = admin_url( 'admin-ajax.php' );
$data = array(
    'action' => 'we_catering_get_menu_item_modal',
    'nonce'  => wp_create_nonce( 'we_catering_admin_nonce' ),
);

echo 'Testing menu item modal AJAX endpoint...' . "\n";
echo 'URL: ' . $url . "\n";
echo 'Data: ' . print_r( $data, true ) . "\n";

// Make the request using cURL
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
curl_setopt( $ch, CURLOPT_FAILONERROR, true );
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

$response = curl_exec( $ch );

if ( curl_errno( $ch ) ) {
    $error_msg = curl_error( $ch );
    curl_close( $ch );
    die( '❌ cURL Error: ' . $error_msg . "\n" );
}

$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
curl_close( $ch );

echo 'HTTP Status: ' . $http_code . "\n";

if ( $http_code !== 200 ) {
    echo '❌ Non-200 HTTP response. The AJAX endpoint may not be accessible.' . "\n";
    echo 'Response: ' . $response . "\n";
    exit( 1 );
}

echo 'Response: ' . $response . "\n";

// Check if response is valid JSON
$json_response = json_decode( $response, true );
if ( json_last_error() === JSON_ERROR_NONE ) {
    echo 'JSON decoded successfully!' . "\n";
    if ( isset( $json_response['success'] ) && $json_response['success'] ) {
        echo '✅ Modal template loaded successfully!' . "\n";
        echo 'HTML content length: ' . strlen( $json_response['data']['html'] ) . ' characters' . "\n";
    } else {
        echo '❌ Error: ' . ( $json_response['data']['message'] ?? 'Unknown error' ) . "\n";
        if ( isset( $json_response['data']['error'] ) ) {
            echo 'Error details: ' . $json_response['data']['error'] . "\n";
        }
    }
} else {
    echo '❌ Invalid JSON response' . "\n";
    echo 'JSON error: ' . json_last_error_msg() . "\n";
    echo 'Raw response: ' . $response . "\n";
}
