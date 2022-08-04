<?php

class Kaltura_NetworkAdminController extends Kaltura_BaseController {
	protected function allowedActions() {
		return array(
			'login',
			'info'
		);
	}

	public function execute() {
		if ( ! current_user_can( 'manage_network_options' ) ) {
			wp_die( 'Access denied' );
		}

		wp_enqueue_script( 'kaltura-admin' );
		wp_enqueue_style( 'kaltura-admin' );
		$kalturaPartnerId = get_site_option( 'kaltura_partner_id' );
		$partnerLogin     = KalturaHelpers::getRequestParam( 'partner_login', false );
		if ( ! $kalturaPartnerId || $partnerLogin ) {
			$this->loginAction();
		} else {
			$this->infoAction();
		}
	}

	public function loginAction() {
		$params['error']   = null;
		$params['success'] = false;
		if ( count( $_POST ) ) {
			$email     = KalturaHelpers::getRequestPostParam( 'email' );
			$email     = sanitize_email($email);
			update_site_option( 'kaltura_cms_user', sanitize_text_field((string)$email) );

			$password  = KalturaHelpers::getRequestPostParam( 'password' );
			$partnerId = KalturaHelpers::getRequestPostParam( 'partner_id' );

			$serverurl = KalturaHelpers::getRequestPostParam( 'server_url' );
			$serverurl = esc_url_raw((string)$serverurl);
			update_site_option( 'kaltura_server_url', $serverurl );

			$config            = KalturaHelpers::getKalturaConfiguration();
			$config->partnerId = $partnerId;
			$kmodel            = KalturaModel::getInstance();
			try {
				$partner = $kmodel->getSecrets( $partnerId, $email, $password );
			} catch ( Exception $ex ) {
				$params['error'] = $ex->getMessage();
			}			
			if ( ! $params['error'] ) {
				$partnerId   = $partner->id;
				$secret      = $partner->secret;
				$adminSecret = $partner->adminSecret;

				// save partner details
				update_site_option( 'kaltura_partner_id', $partnerId );
				update_site_option( 'kaltura_secret', $secret );
				update_site_option( 'kaltura_admin_secret', $adminSecret );

				$params['success'] = true;
			}

		}

		$this->renderView( 'network-admin/login.php', $params );
	}

	public function _pingTest() {
		$kmodel = KalturaModel::getInstance();
		try {
			$kmodel->pingTest();
		} catch ( Kaltura_Client_Exception $ex ) {
			$err = $ex->getMessage() . ' - ' . $ex->getCode();
			return $err;
		}
		return null;
	}

	public function infoAction() {
		$params                = array();
		$params['error']       = null;
		$params['showMessage'] = false;
		$kmodel                = KalturaModel::getInstance();
		$players               = $kmodel->listPlayersUiConfs();
		$players               = $players->objects;
		$playlistEmbedAllowed  = KalturaHelpers::getOption( 'kaltura_allow_embed_playlist', false );
		$playlistPlayers = $kmodel->listPlaylistPlayersUiConfs();
		$playlistPlayers = $playlistPlayers->objects;
		$playerDimensionsList = array(
			'Standard (4:3)' => '4:3',
			'Wide (16:9)' => '16:9',
		);
		if ( count( $_POST ) ) {
			if ( !wp_verify_nonce( isset( $_POST['_kalturanonce'] ) ? $_POST['_kalturanonce'] : null, 'info' )) {
				print 'Sorry, your nonce did not verify.';
				exit;
			}

			$defaultPlayerType          = KalturaHelpers::getRequestPostParam( 'default_player_type' );
			$defaultKCWType             = KalturaHelpers::getRequestPostParam( 'default_kcw_type' );
			$defaultKCWType             = ! empty( $defaultKCWType ) ? $defaultKCWType : KalturaHelpers::getOption( 'kcw_ui_conf_id_admin' );
			$userIdentifier             = KalturaHelpers::getRequestPostParam( 'kaltura_user_identifier' );
			$showMediaFrom              = KalturaHelpers::getRequestPostParam( 'show_media_from' );
			$rootCategory               = KalturaHelpers::getRequestPostParam( 'root_category' );
			$rootCategory               = ! empty( $rootCategory ) ? $rootCategory : 0;
			$allowedPlayers             = KalturaHelpers::getRequestPostParam( 'allowed_players' );
			$allowedPlayers             = ! empty( $allowedPlayers ) && is_array($allowedPlayers) ? $allowedPlayers : array();
			$enableKcw                  = KalturaHelpers::getRequestPostParam( 'enable_kcw' );
			$enableEmbedPlaylist        = KalturaHelpers::getRequestPostParam( 'allow_embed_playlist' );
			$defaultPlayerDimensions    = KalturaHelpers::getRequestPostParam( 'default_player_dimensions', '16:9');

			$kmcserverurl    			= KalturaHelpers::getRequestPostParam( 'server_url');
			$kmcserverurl    			= esc_url_raw((string)$kmcserverurl);
			$kmccdnurl    				= KalturaHelpers::getRequestPostParam( 'cdn_url');

			update_site_option( 'kaltura_default_player_type', sanitize_text_field((string)$defaultPlayerType));
			update_site_option( 'kaltura_show_media_from', sanitize_text_field((string)$showMediaFrom));
			update_site_option( 'kaltura_default_kcw_type', sanitize_text_field((string)$defaultKCWType) );
			update_site_option( 'kaltura_user_identifier', sanitize_text_field((string)$userIdentifier) );
			update_site_option( 'kaltura_root_category', sanitize_text_field((string)$rootCategory) );
			update_site_option( 'kaltura_enable_kcw', (bool)$enableKcw);
            update_site_option( 'kaltura_allow_embed_playlist', (bool)$enableEmbedPlaylist);
            update_site_option( 'kaltura_default_player_dimensions', sanitize_text_field((string)$defaultPlayerDimensions));

			$old_serverurl = get_site_option('kaltura_server_url');
            update_site_option( 'kaltura_server_url', $kmcserverurl);
            update_site_option( 'kaltura_cdn_url', esc_url_raw((string)$kmccdnurl));

			if (!empty($old_serverurl) && $old_serverurl != $kmcserverurl) {
				//note: if URL is changed than delete partner details to force relogin to Kaltura Server
				delete_site_option( 'kaltura_partner_id' );
				delete_site_option( 'kaltura_secret' );
				delete_site_option( 'kaltura_admin_secret' );
				$params['error'] = $this->_pingTest();
			} else {
				// only set allowed players when it was provided and when not all players were selected
				if ( count( $allowedPlayers ) > 0 && count( $allowedPlayers ) < count( $players ) ) {
					update_site_option( 'kaltura_allowed_players', array_values( $allowedPlayers ) );
				} else {
					// otherwise, we reset to empty array to allow all players
					update_site_option( 'kaltura_allowed_players', array() );
				}

				if ($enableEmbedPlaylist = (bool)$enableEmbedPlaylist) {
					$placeholderArray = array();
					if ($enableEmbedPlaylist && !$playlistEmbedAllowed) {
						$allowedPlaylistPlayers   = array_keys(KalturaHelpers::getAllowedPlaylistPlayers());
						$placeholderArray = array_map('strval', $allowedPlaylistPlayers);
					}

					$allowedPostPlaylistPlayers     = KalturaHelpers::getRequestPostParam( 'allowed_playlist_players' );
					$allowedPlaylistPlayers     = ! empty( $allowedPostPlaylistPlayers ) && is_array($allowedPostPlaylistPlayers) ? $allowedPostPlaylistPlayers : $placeholderArray;
					// only set allowed players when it was provided and when not all players were selected
					if ( count( $allowedPlaylistPlayers ) > 0 && count( $allowedPlaylistPlayers ) <= count( $playlistPlayers ) ) {
						update_site_option( 'kaltura_allowed_playlist_players', array_values( $allowedPlaylistPlayers ) );
					} else {
						// otherwise, we reset to empty array to allow all players
						update_site_option( 'kaltura_allowed_playlist_players', array() );
					}
				}

				$params['showMessage'] = true;
			}

		} else {
			$params['error'] = $this->_pingTest();
		}

		$showEmail       = boolval(KalturaHelpers::getOption('kaltura_show_kmc_email'));
		$allowedPlayers = KalturaHelpers::getAllowedPlayers();
		$categories     = $kmodel->generateRootTree();
		$playlistEmbedAllowed  = KalturaHelpers::getOption( 'kaltura_allow_embed_playlist', false );
		$params['playlistEmbedAllowed']    = $playlistEmbedAllowed;
		$params['playerDimensionsList']    = $playerDimensionsList;
		if ($playlistEmbedAllowed) {
			$allowedPlaylistPlayers   = KalturaHelpers::getAllowedPlaylistPlayers();
			$params['playlistPlayers']         = $playlistPlayers;
			$params['allowedPlaylistPlayers']  = $allowedPlaylistPlayers;
		}

		$params['players']         = $players;
		$params['categories']      = $categories;
		$params['allowedPlayers']  = $allowedPlayers;
		$params['showEmail']       = $showEmail;
		$params['isNetworkActive'] = ! KalturaHelpers::isPluginNetworkActivated();
		$this->renderView( 'admin/info.php', $params );
	}
}