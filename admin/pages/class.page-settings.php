<?php

namespace WYV;

class Page_Settings extends PageSettingsBase {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * Settings constructor.
	 *
	 * @param $plugin Plugin object
	 */
	public function __construct( $plugin ) {
		parent::__construct( $plugin );

		$this->id                 = 'settings';
		$this->page_menu_position = 20;
		$this->page_title         = __( 'Настройки YouTube', 'wtmk-import-youtube-videos' );
		$this->page_menu_title    = __( 'Настройки YouTube', 'wtmk-import-youtube-videos' );

		$this->settings = $this->settings();

		add_action( 'admin_init', [ $this, 'init_settings' ] );
	}

	/**
	 * Array of the settings
	 *
	 * @return array
	 */
	public function settings(): array {
		return [
			'settings_group' => [
				'sections' => [
					[
						//'title'   => __( 'Настройки API', 'wtmk-import-youtube-videos' ),
						//'slug'    => 'section_api',
						'options' => [
							'youtube_token' => [
								'title'   => __( 'API ключ YouTube', 'wtmk-import-youtube-videos' ),
								'type'    => 'text',
								'default' => '',
							],
							/*
							'check_option' => [
									'title'             => __( 'Checkbox', 'wtmk-import-youtube-videos' ),
									'type'  			=> 'checkbox',
									'default'			=> '',
							],
							*/
						],
					],
				],
			],
		];
	}

	public function add_page_to_menu() {
		add_submenu_page( 'edit.php?post_type=video', $this->page_title, $this->page_menu_title, 'manage_options', WYV_PLUGIN_PREFIX . '_' . $this->id, [
			$this,
			'page_action',
		], 10 );
	}


	public function page_action() {
		$this->plugin->render_template( 'admin/settings-page', [ 'settings' => $this->settings ] );
	}
}
