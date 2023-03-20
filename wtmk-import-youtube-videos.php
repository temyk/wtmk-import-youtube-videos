<?php
/**
 * Plugin Name: ASU: Импорт YouTube плейлистов
 * Description: WP плагин для импорта плейлистов Youtube в пользовательский тип записи
 * Version:     1.0.0
 * Author:      Webtemyk <webtemyk@yandex.ru>
 * Author URI:  https://temyk.ru
 * Text Domain: wtmk-import-youtube-videos
 * Domain Path: /languages/
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = get_file_data( __FILE__, [ 'ver' => 'Version' ] );

// WYV = MyPluginName
define( 'WYV_PLUGIN_DIR', __DIR__ );
define( 'WYV_PLUGIN_SLUG', 'wtmk-import-youtube-videos' );
define( 'WYV_PLUGIN_VERSION', $data['ver'] );
define( 'WYV_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'WYV_PLUGIN_URL', plugins_url( null, __FILE__ ) );
define( 'WYV_PLUGIN_PREFIX', 'wyv' );

load_plugin_textdomain( WYV_PLUGIN_SLUG, false, dirname( WYV_PLUGIN_BASE ) );

require_once WYV_PLUGIN_DIR . '/includes/boot.php';
if ( is_admin() ) {
	require_once WYV_PLUGIN_DIR . '/admin/boot.php';
}

try {
	new \WYV\Plugin();
} catch ( Exception $e ) {
	$wyv_plugin_error_func = function () use ( $e ) {
		$error = sprintf( __( 'The %1$s plugin has stopped. <b>Error:</b> %2$s Code: %3$s', 'wtmk-import-youtube-videos' ), 'My Plugin Name', $e->getMessage(), $e->getCode() );
		echo '<div class="notice notice-error"><p>' . $error . '</p></div>'; // @codingStandardsIgnoreLine
	};

	add_action( 'admin_notices', $wyv_plugin_error_func );
	add_action( 'network_admin_notices', $wyv_plugin_error_func );
}
