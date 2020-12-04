<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Manager")) {
    /**
     * Class WD_ASP_Manager
     *
     * This is the main controller class of the plugin, should be instantiated from the plugin main file.
     *
     * @class         WD_ASP_Manager
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Core
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Manager {

        /**
         * Context of the current WP environment
         *
         * Is used to include the correct and only necessary files for each context to save performance
         *
         * Possible values:
         *  ajax - an ajax call triggered by the search
         *  frontend - simple front-end call, or an ajax request not triggered by ASP
         *  backend - on any of the plugin back-end pages
         *  global_backend - on any other back-end page
         *  special - special cases
         *
         * @since 1.0
         * @var string
         */
        private $context = "frontend";

        /**
         * Core singleton class
         * @var WD_ASP_Manager self
         */
        private static $_instance;

        /**
         * Initialize and run the plugin-in
         */
        private function __construct() {
            do_action("wd_asp_before_load");

            $this->preLoad();
            $this->loadInstances();
            /**
             * Available after this point:
             *      (WD_ASP_Init) wd_asp()->instances, (global) $wd_asp->instances
             */

            register_activation_hook(ASP_FILE, array($this, 'activationHook'));
            /**
             * Available after this point:
             *      (array) wd_asp()->options, (global) $wd_asp->options
             *      (WD_ASP_Init) wd_asp()->init, (global) $wd_asp->init
             *      (WD_ASP_DBMan) wd_asp()->db, (global) $wd_asp->db
             */
            add_action( 'init', array( $this, 'init' ), 0 );
        }

        public function init() {
            // Check if the plugin needs to be stopped on certain conditions
            // ..this needs to be here, otherwise filter not accesible from functions.php
            if ( $this->stopLoading() )
                return false;

            $this->getContext();
            /**
             * Available after this point:
             *      $this->context
             */
            $this->initUploadGlobals();
            $this->loadIncludes();
            $this->loadShortcodes();
            $this->loadAssets();
            $this->loadMenu();

            $this->loadHooks();

            wd_asp()->init->safety_check();

            // Late init, just before footer print scripts
            add_action("wp_footer", array($this, "lateInit"), 99);

            add_action('admin_notices', array($this, "loadNotices"));

            do_action("wd_asp_loaded");
        }


        private function stopLoading() {
            $ret = false;

            if ( isset($_GET, $_GET['action']) ) {
                if ( $_GET['action'] == 'ere_property_search_ajax' ) {
                    $ret = true;
                }
            }

            // Allow filtering this condition
            return apply_filters('asp_stop_loading', $ret);
        }


        /**
         *  Preloading: for functions and other stuff needed
         */
        private function preLoad() {
            require_once(ASP_PATH . "/backend/settings/default_options.php");
            require_once(ASP_FUNCTIONS_PATH . "functions.php");
            require_once(ASP_CLASSES_PATH . "etc/class.asp-mb.php");

            require_once(ASP_CLASSES_PATH . "etc/indextable.class.php");
            // @ TODO 4.10.5
            //require_once(ASP_CLASSES_PATH . "suggest/class-asp-instant.php");

            // We need to initialize the init here to get the init->table() function
            wd_asp()->init = WD_ASP_Init::getInstance();

            // This needs to be registered here, the 'init' is too late to register
            add_filter( 'cron_schedules', array($this, 'cronExtraIntervals') );
        }

        /**
         * Adds additional intervals to cron jobs
         *
         * @param $schedules
         * @return mixed
         */
        function cronExtraIntervals( $schedules ) {
            $schedules['asp_cr_two_minutes'] = array(
                'interval'  => 120,
                'display'   => __( 'Every 2 Minutes', 'ajax-search-pro' )
            );
            $schedules['asp_cr_three_minutes'] = array(
                'interval'  => 180,
                'display'   => __( 'Every 3 Minutes', 'ajax-search-pro' )
            );
            $schedules['asp_cr_five_minutes'] = array(
                'interval'  => 300,
                'display'   => __( 'Every 5 Minutes', 'ajax-search-pro' )
            );
            $schedules['asp_cr_fifteen_minutes'] = array(
                'interval'  => 900,
                'display'   => __( 'Every 15 Minutes', 'ajax-search-pro' )
            );
            $schedules['asp_cr_thirty_minutes'] = array(
                'interval'  => 1800,
                'display'   => __( 'Every 30 Minutes', 'ajax-search-pro' )
            );
            return $schedules;
        }


        /**
         * Gets the upload path with back-slash
         */
        public function initUploadGlobals() {
            if ( is_multisite() ) {
                /**
                 * On multisite, the wp_upload_dir() returns the current blog URLs,
                 * even if the switch_to_blog(1) is initiated (WordPress core bug?)
                 *
                 * Bypass solution: Save the upload dir option as a site option, when the main blog is visited,
                 *                  then get this value on each site, with a fallback.
				 * WARNING: DO NOT USE get_site_option() and update_site_option() - as those slow down the multisite loading by a LOT
				 * 			use get_blog_option() and update_blog_option() instead
                 */
                $upload_dir = wp_upload_dir();
                if ( get_current_blog_id() == get_network()->site_id ) {
                    update_blog_option(get_network()->site_id, '_asp_upload_dir', $upload_dir);
                } else {
					$upload_dir = get_blog_option(get_network()->site_id, '_asp_upload_dir', $upload_dir);
                }
            } else {
                $upload_dir = wp_upload_dir();
            }

            $upload_dir = apply_filters('asp_glob_upload_dir', $upload_dir);

            wd_asp()->upload_path = $upload_dir['basedir'] . "/" . wd_asp()->upload_dir . "/";
            wd_asp()->upload_url = $upload_dir['baseurl'] . "/" . wd_asp()->upload_dir . "/";
            // Let us make sure, that the URL is using the correct protocol
            if ( defined('ASP_URL') ) {
                // Site is https, but URL is http
                if (strpos(ASP_URL, 'https://') === 0 && strpos(wd_asp()->upload_url, 'https://') === false) {
                    wd_asp()->upload_url = str_replace('http://', 'https://', wd_asp()->upload_url);
                    // Site is http, but URL is https
                } else if (strpos(ASP_URL, 'http://') === 0 && strpos(wd_asp()->upload_url, 'http://') === false) {
                    wd_asp()->upload_url = str_replace('https://', 'http://', wd_asp()->upload_url);
                }
            }

            if ( defined( 'BFITHUMB_UPLOAD_DIR' ) )
                wd_asp()->bfi_path = $upload_dir['basedir'] . "/" . BFITHUMB_UPLOAD_DIR . "/";
            else
                wd_asp()->bfi_path = $upload_dir['basedir'] . "/" . wd_asp()->bfi_dir . "/";

            // Allow globals modification for developers
            wd_asp()->upload_path = apply_filters('asp_glob_upload_path', wd_asp()->upload_path);
            wd_asp()->upload_url = apply_filters('asp_glob_upload_url', wd_asp()->upload_url);
            wd_asp()->bfi_path = apply_filters('asp_glob_bfi_path', wd_asp()->bfi_path);
        }

        /**
         * Gets the call context for further use
         */
        public function getContext() {

            $backend_pages = WD_ASP_Menu::getMenuPages();

            if ( !empty($_POST['action']) ) {
                if ( in_array($_POST['action'], WD_ASP_Ajax::getAll()) )
                    $this->context = "ajax";
                if ( isset($_POST['wd_required']) )
                    $this->context = "special";
                // If it is not part of the plugin ajax actions, the context stays "frontend"
            } else if (!empty($_GET['page']) && in_array($_GET['page'], $backend_pages)) {
                $this->context = "backend";
            } else if ( is_admin() ) {
                $this->context = "global_backend";
            } else {
                $this->context = "frontend";
            }

            return $this->context;
        }

        /**
         * Loads the instance data into the global scope
         */
        private function loadInstances() {

           wd_asp()->instances = WD_ASP_Instances::getInstance();

        }

        /**
         * Loads the required files based on the context
         */
        private function loadIncludes() {

            require_once(ASP_CLASSES_PATH . "ajax/ajax.inc.php");
            require_once(ASP_CLASSES_PATH . "filters/filters.inc.php");
            require_once(ASP_CLASSES_PATH . "frontend/frontend.inc.php");
            require_once(ASP_CLASSES_PATH . "etc/aspdebug.class.php");
            require_once(ASP_CLASSES_PATH . "etc/statistics.class.php");
            require_once(ASP_CLASSES_PATH . "etc/class-asp_helpers.php");

            require_once(ASP_CLASSES_PATH . "etc/performance.class.php");
            require_once(ASP_CLASSES_PATH . "etc/class-priorities.php");
            require_once(ASP_CLASSES_PATH . "etc/class-asp-priority-groups.php");
            require_once(ASP_CLASSES_PATH . "etc/class-license.php");
            require_once(ASP_CLASSES_PATH . "etc/class-mobiledetect.php");
            require_once(ASP_CLASSES_PATH . "etc/class-asp-themes.php");

            require_once(ASP_CLASSES_PATH . "cache/cache.inc.php");
            require_once(ASP_CLASSES_PATH . "suggest/suggest.inc.php");
            require_once(ASP_CLASSES_PATH . "search/search.inc.php");
            require_once(ASP_CLASSES_PATH . "shortcodes/shortcodes.inc.php");

            // This must be here!! If it's in a conditional statement, it will fail..
            require_once(ASP_PATH . "/backend/vc/vc.extend.php");

            switch ($this->context) {
                case "special":
                    require_once(ASP_PATH . "/backend/settings/types.inc.php");
                    break;
                case "ajax":
                    break;
                case "frontend":
                    break;
                case "backend":
                    require_once(ASP_PATH . "/backend/settings/types.inc.php");
                    require_once(ASP_CLASSES_PATH . "etc/compatibility.class.php");
                    break;
                case "global_backend":
                    break;
                default:
                    break;
            }

            // Special case
            if (wpdreams_on_backend_post_editor()) {
                require_once(ASP_PATH . "/backend/tinymce/buttons.php");
                require_once(ASP_PATH . "/backend/metaboxes/default.php");
                require_once(ASP_PATH . "/backend/gutenberg/gutenberg.php");
            }

            // Lifting some weight off from ajax requests
            if ( $this->context != "ajax") {
                require_once(ASP_CLASSES_PATH . "actions/actions.inc.php");
                require_once(ASP_CLASSES_PATH . "etc/updates.class.php");
                require_once(ASP_CLASSES_PATH . "etc/updates_manager.class.php");
                require_once(ASP_CLASSES_PATH . "etc/class-mobiledetect.php");
                wd_asp()->updates = asp_updates::getInstance();
                /* Includes on Post/Page/Custom post type edit pages */
                require_once(ASP_CLASSES_PATH . "widgets/widgets.inc.php");
            }

        }

        /**
         * Use the Shorcodes loader to assign the shortcodes to handler classes
         */
        private function loadShortcodes() {

            WD_ASP_Shortcodes::registerAll();

        }

        /**
         * Runs the Assets loader
         */
        private function loadAssets() {
            // JS
            //WD_MS_Assets::loadJS("ms_search_js");

            // CSS
            //WD_MS_Assets::loadCSS("ms_search_css_basic");

            if ($this->context == "backend")
                add_action('admin_enqueue_scripts', array(wd_asp()->init, 'scripts'));

            if ($this->context == "frontend" || $this->context == "backend") {
                add_action('wp_enqueue_scripts', array(wd_asp()->init, 'styles'));
                add_action('wp_enqueue_scripts', array(wd_asp()->init, 'scripts'));
                add_action('wp_footer', array(wd_asp()->init, 'footer'));
            }
        }

        /**
         * This is hooked to the admin_notices action
         */
        public function loadNotices() {
            //  -------------------- Handle requests here ----------------------
            // Update related notes
            if ( isset($_GET['asp_notice_clear_ru']) )
                update_option("asp_recently_updated", 0);
            if ( isset($_GET['asp_notice_clear_ri']) )
                update_option("asp_recreate_index", 0);

            //  -------------------- Handle notices here ----------------------
            // Update related notes
            if ( get_option("asp_recently_updated", 0) == 1 && wd_asp()->updates->getUpdateNotes(ASP_CURR_VER) != "" ) {
                 echo '<div class="notice notice-error asp-notice-nohide">'.wd_asp()->updates->getUpdateNotes(ASP_CURR_VER).'</div>';
            }
            // Important notes
            if ( wd_asp()->updates->getVersion() > ASP_CURR_VER && wd_asp()->updates->getImportantNotes() != "" ) {
                echo '<div class="notice notice-error asp-notice-nohide">'.wd_asp()->updates->getImportantNotes().'</div>';
            }
            // Index table re-creation note
            if ( $this->context == "backend" && get_option("asp_recreate_index", 0) == 1 ) {
                ?>
                <div class="notice notice-error asp-notice-nohide asp-notice-ri">
                    <p>
                    <?php echo __('<b>Ajax Search Pro notice: </b> The Index Table options have been modified, please re-create the index table!', 'ajax-search-pro'); ?>
                    <a class="button button-primary" href="<?php  echo get_admin_url() . "admin.php?page=asp_index_table"; ?>"><?php echo __('Let\'s do it!', 'ajax-search-pro'); ?></a>
                    <a class="button button-secondary" href="<?php echo add_query_arg(array("asp_notice_clear_ri" => "1")); ?>"><?php echo __('Hide this message', 'ajax-search-pro'); ?></a>
                    </p>
                </div>
                <?php
            }
        }

        /**
         * Generates the menu
         */
        private function loadMenu() {

            add_action('admin_menu', array('WD_ASP_Menu', 'register'));

        }

        /**
         *
         */
        private function loadHooks() {

            // Register handlers only if the context is ajax indeed
            if ($this->context == "ajax")
                WD_ASP_Ajax::registerAll();

            if ( $this->context != "ajax") {
                if ($this->context == "backend")
                    WD_ASP_Actions::register("admin_init", "Compatibility");

                WD_ASP_Actions::registerAll();
            }

            WD_ASP_Filters::registerAll();
        }

        /**
         * Run at the plugin activation
         */
        public function activationHook() {

            // Run the activation tasks
            wd_asp()->init->activate();

        }

        /**
         * This is triggered in the footer. Used for conditional loading assets and stuff.
         */
        public function lateInit() {

        }


        // ------------------------------------------------------------
        //   ---------------- SINGLETON SPECIFIC --------------------
        // ------------------------------------------------------------

        /**
         * Get the instane of WD_ASP_Manager
         *
         * @return self
         */
        public static function getInstance() {
            if (!(self::$_instance instanceof self)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }
}