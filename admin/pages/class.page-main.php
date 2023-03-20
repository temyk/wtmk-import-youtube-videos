<?php

namespace WYV;

use ASU\Video_Post_type;

class Page_Main extends PageBase {

	private $log = [];

	/**
	 * Settings constructor.
	 *
	 * @param $plugin Plugin Plugin class
	 */
	public function __construct( $plugin ) {
		parent::__construct( $plugin );

		$this->id                 = 'main';
		$this->page_menu_dashicon = 'dashicons-superhero-alt';
		$this->page_menu_position = 20;
		$this->page_title         = __( 'Импорт плейлистов YouTube', 'wtmk-import-youtube-videos' );
		$this->page_menu_title    = __( 'Импорт плейлистов', 'wtmk-import-youtube-videos' );
	}

	public function add_page_to_menu() {
		add_submenu_page( 'edit.php?post_type=video', $this->page_title, $this->page_menu_title, 'manage_options', WYV_PLUGIN_PREFIX . '_' . $this->id, [
			$this,
			'page_action',
		], $this->page_menu_dashicon );
	}

	public function page_action() {
		//$channel        = 'UC0UNQu3TLxBUKaPOT5jSfyg';

		if ( isset( $_POST['wyv-form-link'] ) && $_POST['wyv-form-link'] ) {
			$youtube_parser = new YoutubeParser( $_POST['wyv-form-link'] );
			if ( $youtube_parser->channel ) {
				$args['link'] = $_POST['wyv-form-link'];
				$playlists    = get_transient( 'wyv_playlists' );
				if ( $playlists === false ) {
					$playlists = $youtube_parser->getPlaylists();
					set_transient( 'wyv_playlists', $playlists, 365 * HOUR_IN_SECONDS );
				}

				//обработка видео
				$youtube_parser->parse_playlists( $playlists );

				foreach ( $youtube_parser->getLog() as $this->log => $items ) {
					echo "<p><b>{$this->log}:</b></p>";
					foreach ( $items as $title ) {
						echo "		- {$title}<br>";
					}
				}
			}

		}

		$this->plugin->render_template( 'admin/import', $args );
	}

}
