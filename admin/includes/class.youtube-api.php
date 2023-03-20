<?php

namespace WYV;

use Madcoda\Youtube\Youtube;

class YoutubeApi extends Youtube {

	public function __construct() {
		$key = Plugin::instance()->getOption( 'youtube_token' );
		parent::__construct( [ 'key' => $key ] );
	}

	/**
	 * Get the channel object by supplying the URL of the channel page
	 *
	 * @param string $youtube_url
	 *
	 * @return object Channel object
	 * @throws \Exception
	 */
	public function getChannelFromURL( $youtube_url ) {
		if ( strpos( $youtube_url, 'youtube.com' ) === false ) {
			throw new \Exception( 'Указанный URL-адрес не похож на URL-адрес Youtube' );
		}

		$path = static::_parse_url_path( $youtube_url );
		if ( strpos( $path, '/channel' ) === 0 ) {
			$segments  = explode( '/', $path );
			$channelId = $segments[ count( $segments ) - 1 ];
			$channel   = $this->getChannelById( $channelId );
		} else if ( strpos( $path, '/user' ) === 0 ) {
			$segments = explode( '/', $path );
			$username = $segments[ count( $segments ) - 1 ];
			$channel  = $this->getChannelByName( $username );
		} else if ( strpos( $path, '/@' ) === 0 ) {
			$segments = explode( '/@', $path );
			$username = $segments[ count( $segments ) - 1 ];
			$channel  = $this->getChannelByName( $username );
		} else {
			throw new \Exception( 'Указанный URL-адрес не похож на URL-адрес Youtube канала' );
		}

		return $channel;
	}

	/**
	 * @param \stdClass $playlist
	 *
	 * @return string
	 */
	public function getPlaylistUrlFromPlaylist( $playlist ) {
		if ( $playlist->id ) {
			return "https://www.youtube.com/playlist?list={$playlist->id}";
		}

		return '';
	}

	/**
	 * @param string $url
	 *
	 * @return false|\StdClass
	 * @throws \Exception
	 */
	public function getPlaylistFromUrl( $url ) {
		$query = [];
		parse_str( parse_url( $url, PHP_URL_QUERY ) ?: '', $query );
		$id = $query['list'] ?? '';

		return $id ? $this->getPlaylistById( $id ) : false;
	}
}