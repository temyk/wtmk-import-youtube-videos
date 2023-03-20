<?php

namespace WYV;

use ASU\Video_Post_type;
use Madcoda\Youtube\Youtube;

class YoutubeParser {
	public $exclude_playlists = [
		'PLTUM8FhqJF61a-WBU0e7pCmc0gW0Gq40N',
		'PLTUM8FhqJF636mnWi9xgqWi8_psdHm6-W',
		'PLTUM8FhqJF636A795SCTVo3eqJChy6qSQ',
		'PLTUM8FhqJF62WuGgG7ceRtxUNNyKZMiz',
		'PLTUM8FhqJF63ifYiorHf5fm4UaHJviAib',
		'PLTUM8FhqJF62fAv74m4telxTULV2ho69Z',
		'PLTUM8FhqJF6290rVqSt9dWfkPpAF3GZ4c',
		'PLTUM8FhqJF62slIUv_MDq8JemqcPuiZUl',
	];

	/**
	 * @var
	 */
	public $channel;

	/**
	 * @var YoutubeApi
	 */
	public $youtube_api;

	/**
	 * @var array
	 */
	private $log = [];

	/**
	 * @param $url
	 */
	public function __construct( $url = '' ) {
		$this->youtube_api = new YoutubeApi();

		if ( $url ) {
			try {
				$this->channel = $this->youtube_api->getChannelFromURL( $url );
			} catch ( \Exception $e ) {
				echo $e->getMessage();
			}
		}
	}

	/**
	 * @return mixed
	 */
	public function getPlaylists() {
		$playlists = [];
		try {
			$result = $this->youtube_api->getPlaylistsByChannelId( $this->channel, [ 'maxResults' => 50 ] );
			if ( ! empty( $result ) ) {
				$playlists = array_merge( $playlists, $result );
			}
			$next_page = $this->youtube_api->page_info['nextPageToken'];
			while ( $next_page ) {
				$result = $this->youtube_api->getPlaylistsByChannelId( $this->channel, [
					'maxResults' => 50,
					'pageToken'  => $next_page,
				] );
				if ( ! empty( $result ) ) {
					$playlists = array_merge( $playlists, $result );
				}
				$next_page = $this->youtube_api->page_info['nextPageToken'];
			}
		} catch ( \Exception $e ) {
			echo $e->getMessage();
		}

		return $this->exclude_playlists( $playlists );
	}

	/**
	 * @param $playlists
	 *
	 */
	public function parse_playlists( $playlists ) {
		foreach ( $playlists as $playlist ) {
			$title = $playlist->snippet->title ?? '';
			$terms = [];
			//echo "{$title}<br>";
			preg_match( '/^(.*?)(\.|$)(.*)/m', $title, $matches );
			$category = $matches[1];
			//$sub_category = $matches[3];

			//главная категория
			if ( $category ) {
				$term = term_exists( $category, Video_Post_type::$POST_TYPE_TAX );
				if ( is_null( $term ) ) {
					$inserted_term     = wp_insert_term( $category, Video_Post_type::$POST_TYPE_TAX );
					$cats[ $category ] = $inserted_term['term_id'] ?? '';
				} else {
					$cats[ $category ] = $term['term_id'] ?? '';
				}

				$terms[] = $cats[ $category ];
			}

			//подкатегория
			/*if ( $sub_category ) {
				$term = term_exists( $sub_category, Video_Post_type::$POST_TYPE_TAX );
				if ( is_null( $term ) ) {
					$inserted_term         = wp_insert_term( $sub_category, Video_Post_type::$POST_TYPE_TAX, [
						'description' => $sub_category,
						'parent'      => $cats[ $category ],
					] );
					$cats[ $sub_category ] = $inserted_term['term_id'] ?? '';
				} else {
					$cats[ $sub_category ] = $term['term_id'] ?? '';
				}

				$terms[] = $cats[ $sub_category ];
			}*/

			$playlist_title = $this->insert_playlist( $playlist, $terms );

			if ( $category && $playlist_title ) {
				$this->log[ $category ][] = $playlist_title;
			}
		}
	}

	/**
	 * @param \stdClass $playlist
	 * @param array $terms
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function insert_playlist( $playlist, $terms = [] ) {
		$meta_videos = $this->getPlaylistVideoUrls( $playlist );
		if ( empty( $meta_videos ) ) {
			return '';
		}

		$post_time = strtotime( $playlist->snippet->publishedAt ?? '' );

		preg_match( '/^(.*?)(\.|$)(.*)/m', $playlist->snippet->title ?? '', $matches );
		$title = $matches[3] ?? '';
		$title = $title ?: $playlist->snippet->title;

		$user = wp_get_current_user();

		$post_data = [
			'post_title'   => $title,
			'post_content' => $playlist->snippet->description,
			'post_type'    => Video_Post_type::$POST_TYPE,
			'post_author'  => $user->ID,
			'post_date'    => wp_date( 'Y-m-d H:i:s', $post_time ),
			'post_status'  => 'publish',
			'tax_input'    => [ Video_Post_type::$POST_TYPE_TAX => $terms ],
			'meta_input'   => [
				Video_Post_type::$VIDEO_META       => $meta_videos,
				Video_Post_type::$PLAYLIST_META    => $playlist,
				Video_Post_type::$VIDEO_THUMB_META => 1,
			],
		];

		if ( ! post_exists( $title, '', '', Video_Post_type::$POST_TYPE ) ) {
			$post_id  = wp_insert_post( wp_slash( $post_data ) );
			$image    = $playlist->snippet->thumbnails->maxres->url ?? $playlist->snippet->thumbnails->high->url;
			$thumb_id = media_sideload_image( $image, $post_id, $playlist->snippet->title ?? '', 'id' );
			if ( ! is_wp_error( $thumb_id ) ) {
				update_post_meta( $post_id, '_thumbnail_id', $thumb_id );
			}

			return $playlist->snippet->title;
		}

		return '';
	}

	/**
	 * @param $playlist
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getPlaylistVideoUrls( $playlist ) {
		$videos = [];

		try {
			$result = $this->youtube_api->getPlaylistItemsByPlaylistIdAdvanced( [
				'playlistId' => $playlist->id,
				'maxResults' => 10,
				'part'       => 'id, contentDetails',
			], true );

			if ( ! empty( $result ) ) {
				$videos = array_merge( $videos, $result );
			}

			$next_page = $result['page_info']['nextPageToken'] ?? '';
			while ( $next_page ) {
				$result = $this->youtube_api->getPlaylistItemsByPlaylistIdAdvanced( [
					'playlistId' => $playlist->id,
					'maxResults' => 10,
					'part'       => 'id, contentDetails',
					'pageToken'  => $next_page,
				], true );
				if ( ! empty( $result ) ) {
					$videos = array_merge( $videos, $result );
				}

				$next_page = $this->youtube_api->page_info['nextPageToken'] ?? '';
			}
		} catch ( \Exception $e ) {
			echo $e->getMessage();
		}

		$video_ids = [];
		foreach ( $videos['results'] as $result ) {
			$video_ids[] = $result->contentDetails->videoId;
		}
		$result = $this->youtube_api->getVideosInfo( $video_ids );

		$meta_videos = [];
		foreach ( $result as $item ) {
			$meta_videos[] = "https://www.youtube.com/watch?v={$item->id}";
		}

		return $meta_videos;
	}

	/**
	 * @param $playlists
	 *
	 * @return mixed
	 */
	public function exclude_playlists( $playlists ) {
		foreach ( $playlists as $key => $playlist ) {
			if ( in_array( $playlist->id, $this->exclude_playlists ) ) {
				unset( $playlists[ $key ] );
			}
		}

		return $playlists;
	}

	public function getLog() {
		return $this->log;
	}

}