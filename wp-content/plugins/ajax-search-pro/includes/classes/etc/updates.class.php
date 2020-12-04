<?php
class asp_updates {

	private static $_instance;

	private $url = "http://update.wp-dreams.com/version/asp.txt";

	// 2 seconds of timeout, no need to hold up the back-end
	private $timeout = 2;

	private $interval = 1800;

	private $option_name = "asp_updates";

	private $data = false;

	private $version = "";

	private $version_string = "";

	private $requires_version = "3.5";

	private $tested_version = "4.4";

	private $downloaded_count = 0;

	private $last_updated = "2015-01-01";

	private $knowledge_base = "";

	private $support = "";

	private $change_log = array();

	private $update_notes = array();

	private $important_notes = "";

    // -------------------------------------------- Auto Updater Stuff here---------------------------------------------
    public $title = "Ajax Search Pro";

    protected $download_link_url = 'http://update.wp-dreams.com/u.php';

	function __construct() {
	    if (
	        defined('ASP_BLOCK_EXTERNAL') ||
	        ( defined('WP_HTTP_BLOCK_EXTERNAL') && WP_HTTP_BLOCK_EXTERNAL )
        )
	        return false;

	    // Redundant: Let's make sure, that the version check is not executed during Ajax requests, by any chance
	    if (  !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            $this->getData();
            $this->processData();
        }

        add_filter( 'upgrader_pre_download', array( $this, 'preUpgradeFilter' ), 10, 4 );
	}

	function getData($force_update = false) {
		$last_checked = get_option($this->option_name . "_lc", time() - $this->interval - 500);

		if ($this->data != "" && $force_update != true) return;

		if (
			((time() - $this->interval) > $last_checked) ||
			$force_update
		) {
			$response = wp_remote_get( $this->url . "?t=" . time(), array( 'timeout' => $this->timeout ) );
			if ( is_wp_error( $response ) ) {
				$this->data = get_option($this->option_name, false);
			} else {
				$this->data = $response['body'];
				update_option($this->option_name, $this->data);
			}
            /**
             * Any case, success or failure, the last checked timer should be updated. Otherwise if the remote server
             * is offline, it will block each back-end page load every time for 'timeout' seconds
             */
            update_option($this->option_name . "_lc", time());
		} else {
			$this->data = get_option($this->option_name, false);
		}
	}

	function processData() {
		if ($this->data === false) return false;

		// Version
		preg_match("/VERSION:(.*?)[\r\n]/s", $this->data, $m);
		$this->version = isset($m[1]) ? (trim($m[1]) + 0) : $this->version;

		// Version string
		preg_match("/VERSION_STRING:(.*?)[\r\n]/s", $this->data, $m);
		$this->version_string = isset($m[1]) ? trim($m[1]) : $this->version_string;

		// Requires version string
		preg_match("/REQUIRES:(.*?)[\r\n]/s", $this->data, $m);
		$this->requires_version = isset($m[1]) ? trim($m[1]) : $this->requires_version;

		// Tested version string
		preg_match("/TESTED:(.*?)[\r\n]/s", $this->data, $m);
		$this->tested_version = isset($m[1]) ? trim($m[1]) : $this->tested_version;

		// Downloaded count
		preg_match("/DOWNLOADED:(.*?)[\r\n]/s", $this->data, $m);
		$this->downloaded_count = isset($m[1]) ? trim($m[1]) : $this->downloaded_count;

		// Last updated date
		preg_match("/LAST_UPDATED:(.*?)[\r\n]/s", $this->data, $m);
		$this->last_updated = isset($m[1]) ? trim($m[1]) : $this->last_updated;

		// Support
		preg_match("/===SUPPORT===(.*?)(?:===|\Z)/s", $this->data, $m);
		$this->support = isset($m[1]) ? trim($m[1]) : $this->support;

		// Update notice message, changed in 4.9.1
		preg_match("/===UPDATE_NOTES_PER_VERSION===(.*?)(?:===|\Z)/s", $this->data, $m);
		$update_notes = isset($m[1]) ? trim($m[1]) : false;
		if ($update_notes !== false) {
			preg_match_all( "/==(.*?)==[\r\n](.*?)==/s", $update_notes, $mm );
			if (isset($mm[1]) && isset($mm[2]))
				foreach ($mm[1] as $k => $v) {
					// x[version] = version_changelog
					$this->update_notes[$v] = $mm[2][$k];
				}
		}

		// Important notice message
		preg_match("/===IMPORTANT_NOTES===(.*?)(?:===|\Z)/s", $this->data, $m);
		$this->important_notes = isset($m[1]) ? trim($m[1]) : $this->important_notes;

		// Knowledge Base
		preg_match("/===KNOWLEDGE_BASE===(.*?)(?:===|\Z)/s", $this->data, $m);
		$this->knowledge_base = isset($m[1]) ? trim($m[1]) : $this->knowledge_base;
		$this->knowledge_base = preg_replace("/\[(.+?)\]\((.+?)\)/sm", "<li><a href='$2' target='_blank'>$1</a></li>", $this->knowledge_base);

		// ChangeLog
		preg_match("/===CHANGELOG===(.*?)(?:===|\Z)/sm", $this->data, $m);
		$changelog = isset($m[1]) ? trim($m[1]) : false;

		if ($changelog !== false) {
			preg_match_all( "/==(.*?)==[\r\n](.*?)==/s", $changelog, $mm );
			if (isset($mm[1]) && isset($mm[2]))
				foreach ($mm[1] as $k => $v) {
					// x[version] = version_changelog
					$this->change_log[$v] = $mm[2][$k];
				}
		}

        return true;
	}

    /**
     * Get unique, short-lived download link
     *
     * @param string $license_key
     *
     * @return array|boolean JSON response or false if request failed
     */
    public function getDownloadUrl( $license_key ) {
        $url = rawurlencode( $_SERVER['HTTP_HOST'] );
        $key = rawurlencode( $license_key );

        $url = $this->download_link_url . '?file=asp&url=' . $url . '&key=' . $key . '&version=' . ASP_CURR_VER;

        $response = wp_remote_get( $url );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return json_decode( $response['body'], true );
    }

    /**
     * Get link to newest update
     *
     * @param $reply
     * @param $package
     * @param $updater
     *
     * @return mixed|string|WP_Error
     */
    public function preUpgradeFilter( $reply, $package, $updater ) {
        $condition1 = isset( $updater->skin->plugin ) && $updater->skin->plugin === ASP_PLUGIN_NAME;
        $condition2 = isset( $updater->skin->plugin_info ) && $updater->skin->plugin_info['Name'] === $this->title;
        if ( ! $condition1 && ! $condition2 ) {
            return $reply;
        }

        $res = $updater->fs_connect( array( WP_CONTENT_DIR ) );
        if ( ! $res ) {
            return new WP_Error( 'no_credentials', __( "Error! Can't connect to filesystem", 'ajax-search-pro' ) );
        }

        $license_key = WD_ASP_License::isActivated();

        if ( $license_key === false ) {
            return new WP_Error( 'no_credentials', __( 'To receive automatic updates license activation is required. Please visit <a href="' . admin_url( 'admin.php?page=asp_updates_help' ) . '' . '" target="_blank">Settings</a> to activate Ajax Search Pro.', 'ajax-search-pro' ) );
        }

        $updater->strings['downloading_package_url'] = __( 'Getting download link...', 'ajax-search-pro' );
        $updater->skin->feedback( 'downloading_package_url' );

        $response = $this->getDownloadUrl( $license_key );
        if ( empty($response) ) {
            return new WP_Error( 'no_credentials', __( 'Download link could not be retrieved', 'ajax-search-pro' ) );
        }

        /** Status != 1, meaning that the plugin is actually not active for this site */
        if ( isset($response['status']) && $response['status'] != 1 ) {
            WD_ASP_License::deactivate( false );

            return new WP_Error( 'inactive', $response['msg'] );
        }

        $updater->strings['downloading_package'] = __( 'Downloading package...', 'ajax-search-pro' );
        $updater->skin->feedback( 'downloading_package' );

        $downloaded_archive = download_url( $response['data'] );
        if ( is_wp_error( $downloaded_archive ) ) {
            return $downloaded_archive;
        }

        $plugin_directory_name = ASP_DIR;

        // WP will use same name for plugin directory as archive name, so we have to rename it
        if ( basename( $downloaded_archive, '.zip' ) !== $plugin_directory_name ) {
            $new_archive_name = dirname( $downloaded_archive ) . '/' . $plugin_directory_name . '.zip';
            rename( $downloaded_archive, $new_archive_name );
            $downloaded_archive = $new_archive_name;
        }

        return $downloaded_archive;
    }

    public function refresh() {
        $this->getData(true );
        $this->processData();
    }

	public function getVersion() {
		return $this->version;
	}

	public function getVersionString() {
		return $this->version_string;
	}

	public function needsUpdate( $refresh = false ) {
        if ( $refresh )
            $this->refresh();

		if ($this->version != "")
			if ($this->version > ASP_CURR_VER)
				return true;

		return false;
	}

	public function getRequiresVersion() {
		return $this->requires_version;
	}

	public function getTestedVersion() {
		return $this->tested_version;
	}

	public function getDownloadedCount() {
		return $this->downloaded_count;
	}

	public function getLastUpdated() {
		return $this->last_updated;
	}

	public function getLastChangelog() {
		foreach ($this->change_log as $ver => $log) {
			return $log;
		}
		return "";
	}

	public function getKnowledgeBase() {
		if ($this->knowledge_base != "")
			return "<ul>" . $this->knowledge_base . "</ul>";
		return $this->knowledge_base;
	}

	public function getUpdateNotes( $vn = 0 ) {
		if ( isset($this->update_notes[$vn]) && $this->update_notes[$vn] !="" ) {
			$url = add_query_arg(array(
					"asp_notice_clear_ru" => "1"
			));
			return str_replace(
					"{hide_button}",
					'<a class="button button-secondary" href="'.$url.'">Hide this message</a>',
					$this->update_notes[$vn]
			);
		}
	}

	public function getImportantNotes() {
		$url = add_query_arg(array(
				"asp_notice_clear_im" => "1"
		));
		return str_replace(
				"{hide_button}",
				'<a class="button button-secondary" href="'.$url.'">Hide this message</a>',
				$this->important_notes
		);
	}

	public function getChangeLog() {
		return $this->change_log;
	}

	public function getSupport() {
		return $this->support;
	}

	/**
	 * Get the instane of VC_Manager
	 *
	 * @return self
	 */
	public static function getInstance() {
		if ( ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}