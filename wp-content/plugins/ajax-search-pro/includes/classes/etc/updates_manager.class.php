<?php
if (!class_exists('asp_updates_manager')) {
	class asp_updates_manager {

		/**
		 * Plugin Slug (plugin_directory/plugin_file.php)
		 * @var string
		 */
		public $plugin_slug;

		/**
		 * Plugin name (plugin_file)
		 * @var string
		 */
		public $slug;

		/**
		 * Updates Object
		 * @var Object
		 */
		private $updates_o;

		/**
		 * Initialize a new instance of the WordPress Auto-Update class
		 *
		 * @param string $plugin_slug
		 * @param object $updates_o
		 */
		function __construct( $plugin_name, $plugin_slug, $udpates_o ) {
			// Set the class public variables
			$this->plugin_slug = $plugin_slug;
			$this->udpates_o   = $udpates_o;
			$t                 = explode( '/', $plugin_slug );
			$this->slug        = str_replace( '.php', '', $t[1] );

			// define the alternative API for updating checking
			add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'check_update' ) );

			// Define the alternative response for information checking
			add_filter( 'plugins_api', array( &$this, 'check_info' ), 10, 3 );

			add_action( 'in_plugin_update_message-' . $plugin_name, array( &$this, 'addUpgradeMessageLink' ) );
		}

		/**
		 * Add our self-hosted autoupdate plugin to the filter transient
		 *
		 * @param $transient
		 *
		 * @return object $ transient
		 */
		public function check_update( $transient ) {

			if ( empty( $transient->checked ) ) {
				return $transient;
			}

			// If a newer version is available, add the update
			if ( $this->udpates_o->needsUpdate() ) {
				$obj                                       = new stdClass();
				$obj->slug                                 = $this->slug;
				$obj->new_version                          = $this->udpates_o->getVersionString();
				$obj->url                                  = "";
				$obj->package                              = "asp";
				$obj->plugin                               = $this->plugin_slug;
				$obj->tested                               = $this->udpates_o->getTestedVersion();
				$transient->response[ $this->plugin_slug ] = $obj;
			}

			return $transient;
		}

		/**
		 * Add our self-hosted description to the filter
		 *
		 * @param boolean $false
		 * @param array $action
		 * @param object $arg
		 *
		 * @return bool|object
		 */
		public function check_info( $false, $action, $arg ) {

            if (!is_object($arg) || !isset($arg->slug) || !isset($this->slug))
                return $false;

			if ( $arg->slug === $this->slug && $this->udpates_o->needsUpdate() ) {
				$information = new stdClass();

				$information->name          = "Ajax Search Pro";
				$information->slug          = $this->slug;
				$information->plugin        = $this->slug;
				$information->plugin_name   = $this->plugin_slug;
				$information->new_version   = $this->udpates_o->getVersionString();
				$information->requires      = $this->udpates_o->getRequiresVersion();
				$information->tested        = $this->udpates_o->getTestedVersion();
				$information->downloaded    = $this->udpates_o->getDownloadedCount();
				$information->last_updated  = $this->udpates_o->getLastUpdated();
				$information->sections      = array(
					'changelog' => "<h2>Version ".$this->udpates_o->getVersionString()."</h2><pre stlye='white-space: pre-line;overflow:hidden;'>" . $this->udpates_o->getLastChangelog() . "</pre>"
				);
				$information->download_link = 'http://codecanyon.net/downloads/';

				return $information;
			}

			return $false;
		}

		/**
		 * Shows message on Wp plugins page with a link for updating from envato.
		 */
		public function addUpgradeMessageLink() {
            echo '<style type="text/css" media="all">tr#ajax-search-pro + tr.plugin-update-tr a.thickbox + em { display: none; }</style>';
            if ( WD_ASP_License::isActivated() === false )
			    echo ' <a target="_blank" href="http://codecanyon.net/downloads/">' . __( 'Download new version from CodeCanyon.', 'ajax-search-pro' ) . '</a>';
            else
                echo ' or <a href="' . wp_nonce_url( admin_url( 'update.php?action=upgrade-plugin&plugin=' . ASP_PLUGIN_NAME ), 'upgrade-plugin_' . ASP_PLUGIN_NAME ) . '">' . __( 'Update Ajax Search Pro now.', 'ajax-search-pro' ) . '</a>';
		}

	}
}