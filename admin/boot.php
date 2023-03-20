<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Libs
require_once WYV_PLUGIN_DIR . '/libs/youtube-api/load.php';

require_once WYV_PLUGIN_DIR . '/admin/includes/base/class.page-base.php';
require_once WYV_PLUGIN_DIR . '/admin/includes/base/class.page-settings-base.php';
require_once WYV_PLUGIN_DIR . '/admin/includes/class.youtube-api.php';
require_once WYV_PLUGIN_DIR . '/admin/includes/class.youtube-parser.php';

$pages_dir = WYV_PLUGIN_DIR . '/admin/pages/';
foreach ( scandir( $pages_dir ) as $page ) {
	if ( '.' === $page || '..' === $page ) {
		continue;
	}

	require_once $pages_dir . $page;
}
