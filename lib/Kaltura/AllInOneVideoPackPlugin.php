<?php

class Kaltura_AllInOneVideoPackPlugin {
	public function init() {

		if(is_multisite() && ! ( function_exists( 'wpcom_is_vip' ) && wpcom_is_vip() ) && apply_filters( 'kaltura_use_network_settings', true )) {
			add_action( 'network_admin_menu', array($this, 'networkAdminMenuAction' ) );
		}

		if (defined('MULTISITE') && defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) {
			add_action('network_admin_menu', array($this, 'networkAdminMenuAction'));
		}

        // show notice on admin pages except for kaltura_options
		if ( ! KalturaHelpers::getOption( 'kaltura_partner_id' ) &&	! isset( $_POST['submit'] ) &&	(!isset( $_GET['page'] ) || 'kaltura_options' !== $_GET['page']) ) {
			add_action( 'admin_notices', array($this, 'adminWarning' ) );
			return;
		}

		// filters
		add_filter( 'media_buttons_context', array($this, 'mediaButtonsContextFilter' ) );
		add_filter( 'media_upload_tabs', array($this, 'mediaUploadTabsFilter' ) );
		add_filter( 'mce_external_plugins', array($this, 'mceExternalPluginsFilter' ) );
		add_filter( 'tiny_mce_version', array($this, 'tinyMceVersionFilter' ) );

		// actions
		add_action( 'admin_menu', array($this, 'adminMenuAction' ) );
		//add_action( 'wp_enqueue_scripts', array($this, 'enqueueScripts' ) );
		add_action( 'admin_enqueue_scripts', array($this, 'adminEnqueueScripts' ) );
		add_action( 'edit_form_top', array( $this, 'editorEnqueueScripts' ) );

		// media upload actions
		add_action( 'media_upload_kaltura_upload', array($this, 'mediaUploadAction' ) );
		add_action( 'media_upload_kaltura_browse', array($this, 'mediaBrowseAction' ) );

		add_action( 'wp_ajax_kaltura_ajax', array($this, 'executeLibraryController' ) );

		add_shortcode( 'kaltura-widget', array($this, 'shortcodeHandler' ) );
		
		//Hook: initialize Block assets
		add_action( 'init', array($this,'initializeKalturaVideoBlock'));

	}

	public function initializeKalturaVideoBlock() {
		// Register block styles for both frontend + backend.
		/*wp_register_style(
			'kaltura-video-gb-style', // Handle.
			KalturaHelpers::jsUrl( 'dist/blocks.style.build.css'), // Block style CSS.
			is_admin() ? array( 'wp-editor' ) : null, // Dependency to include the CSS after it.
			null //Version
		);*/

		// Register block editor script for backend.
		wp_register_script(
			'kaltura-video-gb-block', // Handle.
			KalturaHelpers::jsUrl( 'dist/blocks.build.js' ), // We register the block here. 
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
			null, // Version
			true // Enqueue the script in the footer.
		);	
		
		// Register block editor styles for backend.
		wp_register_style(
			'kaltura-video-gb-block-editor', // Handle.
			KalturaHelpers::jsUrl( 'dist/blocks.editor.build.css' ), // Block editor CSS.
			array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
			null //Version
		);

		// WP Localized globals. Use dynamic PHP stuff in JavaScript via `cgbGlobal` object.
		wp_localize_script(
			'kaltura-video-gb-block-js',
			'kvGbGlobal', // Array containing dynamic data for a JS Global.
			[
				'pluginDirPath' => plugin_dir_path( __DIR__ ),
				'pluginDirUrl'  => plugin_dir_url( __DIR__ ),
				// Add more data here that you want to access from `cgbGlobal` object.
			]
		);

		/**
		 * Register Gutenberg block on server-side.
		 *
		 * Register the block on server-side to ensure that the block
		 * scripts and styles for both frontend and backend are
		 * enqueued when the editor loads.
		 *
		 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
		 * @since 1.16.0
		 */
		register_block_type(
			'kaltura/video', array(
				// Enqueue style.css on both frontend & backend.
				'style'         => 'kaltura-video-gb-style',

				// Enqueue blocks.build.js in the editor only.
				'editor_script' => 'kaltura-video-gb-block',

				// Enqueue editor.css in the editor only.
				'editor_style'  => 'kaltura-video-gb-block-editor',
			)
		);
	}

	public function adminWarning() {
        $kalturaOptionsPageUrl = admin_url('options-general.php?page=kaltura_options');
		echo '<div class="updated fade">
		    <p>
		        <strong>' . esc_html__( 'To complete the Kaltura Video installation, ') . '<a href="' . esc_url($kalturaOptionsPageUrl) . '">' . esc_html__('you must get a Partner ID.') . '</a></strong>
		    </p>
		</div>';
	}

	public function mceExternalPluginsFilter( $content ) {
		$pluginUrl          = KalturaHelpers::getPluginUrl();
		$content['kaltura'] = esc_url_raw($pluginUrl . '/assets/js/tinymce/kaltura_tinymce.js?v' . KALTURA_PLUGIN_VERSION );

		return $content;
	}

	public function tinyMceVersionFilter( $content ) {
		return $content . '_k' . KALTURA_PLUGIN_VERSION;
	}

	public function adminMenuAction() {
		add_options_page( 'Kaltura Video', 'Kaltura Video', 'manage_options', 'kaltura_options', array($this, 'executeAdminController' ) );
		add_media_page( 'Kaltura Video', 'Kaltura Video', 'edit_posts', 'kaltura_library', array($this, 'executeLibraryController' ) );
	}

	public function enqueueScripts() {
		//wp_enqueue_style( 'kaltura', KalturaHelpers::cssUrl( 'assets/css/kaltura.css' ), array(), KALTURA_PLUGIN_VERSION );
		//wp_enqueue_script( 'kaltura', KalturaHelpers::jsUrl( 'assets/js/kaltura.js' ), array('jquery'), KALTURA_PLUGIN_VERSION, false );
	}

	public function adminEnqueueScripts() {
		wp_register_script( 'kaltura', KalturaHelpers::jsUrl( 'assets/js/kaltura.js' ), array('jquery'), KALTURA_PLUGIN_VERSION, false );
		wp_register_script( 'kaltura-admin', KalturaHelpers::jsUrl( 'assets/js/kaltura-admin.js' ), array(), KALTURA_PLUGIN_VERSION, false );
		wp_register_script( 'kaltura-lightbox', KalturaHelpers::jsUrl( 'assets/js/kaltura-lightbox.js' ), array(), KALTURA_PLUGIN_VERSION, false );
		wp_register_script( 'kaltura-player-selector', KalturaHelpers::jsUrl( 'assets/js/kaltura-player-selector.js' ), array(), KALTURA_PLUGIN_VERSION, true );
		wp_register_script( 'kaltura-entry-status-checker', KalturaHelpers::jsUrl( 'assets/js/kaltura-entry-status-checker.js' ), array(), KALTURA_PLUGIN_VERSION, true );
		wp_register_script( 'kaltura-editable-name', KalturaHelpers::jsUrl( 'assets/js/kaltura-editable-name.js' ), array(), KALTURA_PLUGIN_VERSION, true );
		wp_register_script( 'kaltura-jquery-validate', KalturaHelpers::jsUrl( 'assets/js/jquery.validate.min.js' ), array(), KALTURA_PLUGIN_VERSION, true );
		wp_register_script( 'kaltura-playlist-control', KalturaHelpers::jsUrl( 'assets/js/kaltura-playlist-control.js' ), array(), KALTURA_PLUGIN_VERSION, true );
		wp_register_style( 'kaltura-playlist-stub', KalturaHelpers::cssUrl( 'assets/css/playlist-stub.css' ), array(), KALTURA_PLUGIN_VERSION );
		//nanoscroller
		wp_register_script( 'kaltura-jquery-perfect-scrollbar', KalturaHelpers::jsUrl( 'assets/js/perfect-scrollbar.jquery.min.js' ), array(), KALTURA_PLUGIN_VERSION, true );
		wp_register_style( 'kaltura-jquery-perfect-scrollbar', KalturaHelpers::cssUrl( 'assets/css/perfect-scrollbar.min.css' ), array(), KALTURA_PLUGIN_VERSION );
		
		// bootstrap
		wp_register_style( 'kaltura-bootstrap', KalturaHelpers::cssUrl( 'bootstrap/css/bootstrap.min.css' ), array(), KALTURA_PLUGIN_VERSION );
		wp_register_script( 'kaltura-bootstrap', KalturaHelpers::jsUrl( 'bootstrap/js/bootstrap.js' ), array(), KALTURA_PLUGIN_VERSION );

		// chunked-file-upload-jquery
		wp_register_script( 'kaltura-jquery.ui.widget', KalturaHelpers::jsUrl( 'chunked-file-upload-jquery/js/jquery.ui.widget.js' ), array(), KALTURA_PLUGIN_VERSION, true );
		wp_register_script( 'kaltura-jquery.iframe-transport', KalturaHelpers::jsUrl( 'chunked-file-upload-jquery/js/jquery.iframe-transport.js' ), array(), KALTURA_PLUGIN_VERSION, true );
		wp_register_script( 'kaltura-webtoolkit.md5', KalturaHelpers::jsUrl( 'chunked-file-upload-jquery/js/webtoolkit.md5.js' ), array(), KALTURA_PLUGIN_VERSION, true );
		wp_register_script( 'kaltura-jquery.fileupload-process', KalturaHelpers::jsUrl( 'chunked-file-upload-jquery/js/jquery.fileupload-process.js' ), array('kaltura-jquery.fileupload'), KALTURA_PLUGIN_VERSION, true );
		wp_register_script( 'kaltura-jquery.fileupload-validate', KalturaHelpers::jsUrl( 'chunked-file-upload-jquery/js/jquery.fileupload-validate.js' ), array('kaltura-jquery.fileupload'), KALTURA_PLUGIN_VERSION, true );
		wp_register_script( 'kaltura-jquery.fileupload-kaltura-base', KalturaHelpers::jsUrl( 'chunked-file-upload-jquery/js/jquery.fileupload-kaltura-base.js' ), array( 'kaltura-jquery.fileupload-kaltura'), KALTURA_PLUGIN_VERSION, true );
		wp_register_script( 'kaltura-jquery.fileupload', KalturaHelpers::jsUrl( 'chunked-file-upload-jquery/js/jquery.fileupload.js' ), array(
				'kaltura-jquery.ui.widget',
				'kaltura-jquery.iframe-transport',
				'kaltura-webtoolkit.md5',
		), KALTURA_PLUGIN_VERSION, true );
		wp_register_script( 'kaltura-jquery.fileupload-kaltura', KalturaHelpers::jsUrl( 'chunked-file-upload-jquery/js/jquery.fileupload-kaltura.js' ), array(
				'kaltura-jquery.fileupload',
				'kaltura-jquery.fileupload-process',
				'kaltura-jquery.fileupload-validate',
		), KALTURA_PLUGIN_VERSION, true );
		wp_register_style( 'kaltura-jquery.fileupload-ui', KalturaHelpers::cssUrl( 'chunked-file-upload-jquery/css/jquery.fileupload-ui.css' ), array( ), KALTURA_PLUGIN_VERSION );
		wp_register_style( 'kaltura-jquery.fileupload-ui-kaltura', KalturaHelpers::cssUrl( 'chunked-file-upload-jquery/css/jquery.fileupload-ui-kaltura.css' ), array( 'kaltura-jquery.fileupload-ui', 'kaltura-bootstrap' ), KALTURA_PLUGIN_VERSION );
		wp_register_style( 'kaltura-admin', KalturaHelpers::cssUrl( 'assets/css/admin.css' ), array(), KALTURA_PLUGIN_VERSION );

		wp_enqueue_script( 'kaltura-lightbox' );
		wp_enqueue_style( 'kaltura-admin' );
		wp_enqueue_script( 'kaltura-admin' );
		$this->enqueueScripts();
	}

	function editorEnqueueScripts() {
		wp_enqueue_script( 'kaltura' );
	}

	function executeLibraryController() {
		if ( ! isset( $_GET['kaction'] ) ) {
			$_GET['kaction'] = 'library';
		}
		$controller = new Kaltura_LibraryController();
		$controller->execute();
	}

	function executeAdminController() {
		$controller = new Kaltura_AdminController();
		$controller->execute();
	}

	public function mediaButtonsContextFilter( $content ) {
		$kaltura_title             = esc_attr__( 'Add Kaltura Media' );
		$kaltura_button_src        = KalturaHelpers::getPluginUrl() . '/assets/images/kaltura_button.png';

		$content .= '<a id="kaltura-media-button" href="#" title="' . esc_attr__($kaltura_title) . '">
						<img src="' . esc_url($kaltura_button_src) . '" alt="' . esc_attr__($kaltura_title) . '" />
					</a>';

		return $content;
	}

	public function mediaUploadTabsFilter( $content ) {
		// hide other tabs when user clicks on our tab
		if (in_array(KalturaHelpers::getRequestParam('tab'), array('kaltura_upload', 'kaltura_browse')))
			$content = array();

		$content['kaltura_upload'] = esc_html__( 'Add Media' );
		$content['kaltura_browse'] = esc_html__( 'Browse Existing Media' );

		return $content;
	}

	public function mediaUploadTabsFilterOnlyKaltura() {
		$content = array();

		return $this->mediaUploadTabsFilter( $content );
	}

	public function mediaUploadAction() {
		if ( ! isset( $_GET['kaction'] ) ) {
			$_GET['kaction'] = 'upload';
		}

		$controller = new Kaltura_LibraryController();

		wp_iframe( array( $controller, 'execute' ) );
	}

	public function mediaBrowseAction() {
		if ( ! isset( $_GET['kaction'] ) ) {
			$_GET['kaction'] = 'browse';
		}

		$controller = new Kaltura_LibraryController();

		wp_iframe( array( $controller, 'execute' ) );
	}

	public function shortcodeHandler( $attrs ) {
		if ( ! isset( $attrs['entryid'] ) ) {
			return '';
		}

		$attrs = KalturaSanitizer::shortCodeAttributes($attrs);
		$viewRenderer = new Kaltura_ViewRenderer();
		ob_start();
		$viewRenderer->renderView( 'embed-code.php', array('attrs' => $attrs) );
		$embedCode = ob_get_clean();
		wp_enqueue_script( 'isInviewPort', KalturaHelpers::jsUrl( 'assets/js/isInViewport.jquery.js' ), array('jquery-core'), KALTURA_PLUGIN_VERSION );

		return $embedCode;
	}

	public function networkAdminMenuAction() {
		add_submenu_page( 'settings.php', 'Kaltura Video', 'Kaltura Video', 'manage_network_options', 'all-in-one-video-pack-mu-settings', array($this, 'networkSettings' ) );
		}

	public function networkSettings() {
		$controller = new Kaltura_NetworkAdminController();
		$controller->execute();
    }
}