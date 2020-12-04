<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_DBMan")) {
    /**
     * Class WD_ASP_DBMan
     *
     * Manager of main database related operations
     *
     * @class         WD_ASP_Manager
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Core
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_DBMan {

        /**
         * All the table slug => name combinations used
         *
         * @since 1.0
         * @var array
         */
        private $tables = array(
            "main" => "ajaxsearchpro",
            "stats" => "ajaxsearchpro_statistics",
            "priorities" => "ajaxsearchpro_priorities",
            "synonyms" => "asp_synonyms",
            "index" => "asp_index",
            'instant' => 'asp_instant'
        );

        private static $_instance;

        private function __construct() {
            global $wpdb;

            if (isset($wpdb->base_prefix)) {
                wd_asp()->_prefix = $wpdb->base_prefix;
            } else {
                wd_asp()->_prefix = $wpdb->prefix;
            }

            foreach ($this->tables as $slug => $table)
                $this->tables[$slug] = wd_asp()->_prefix . $table;

            // Push the correct table names to the globals back
            $this->tables = (object) $this->tables;
            wd_asp()->tables = $this->tables;
        }

        function create() {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $return = array();

            $table_name = $this->table("main");
            $query = "
            CREATE TABLE IF NOT EXISTS `$table_name` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` text NOT NULL,
              `data` mediumtext NOT NULL,
              PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
          ";
            dbDelta($query);
            $wpdb->query($query);
            $return[] = $query;

            // 4.6.1+ change the data row to medium text
            $query = "ALTER TABLE `$table_name` MODIFY `data` mediumtext";
            dbDelta($query);
            $wpdb->query($query);
            $return[] = $query;

            $table_name = $this->table("stats");
            $query = "
            CREATE TABLE IF NOT EXISTS `$table_name` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `search_id` int(11) NOT NULL,
              `keyword` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
              `num` int(11) NOT NULL,
              `last_date` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
          ";
            dbDelta($query);
            $wpdb->query($query);
            $return[] = $query;

            // ------------- SYNONYMS DB ---------------------------
            require_once(ASP_CLASSES_PATH . 'etc/synonyms.class.php');
            $table_name = $this->table("synonyms");
            $syn = ASP_Synonyms::getInstance();
            $return = array_merge($return, $syn->createTable($table_name));
            // -----------------------------------------------------

            $table_name = $this->table("priorities");
            $query = "
            CREATE TABLE IF NOT EXISTS `$table_name` (
              `post_id` int(11) NOT NULL,
              `blog_id` int(11) NOT NULL,
              `priority` int(11) NOT NULL,
              PRIMARY KEY (`post_id`, `blog_id`)
            ) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
          ";
            dbDelta($query);
            $wpdb->query($query);
            $return[] = $query;

            $query = "SHOW INDEX FROM `$table_name` WHERE KEY_NAME = 'post_blog_id'";
            $index_exists = $wpdb->query($query);
            if ($index_exists == 0) {
                $query = "ALTER TABLE `$table_name` ADD INDEX `post_blog_id` (`post_id`, `blog_id`);";
                $wpdb->query($query);
                $return[] = $query;
            }

            // ------------- Index TABLE DB ------------------------
            $indexObj = new asp_indexTable();
            $return = array_merge($return, $indexObj->createIndexTable());
            // -----------------------------------------------------

            $ret = '';
            foreach ( $return as $k => &$r ){
                $r = trim($r);
                if ( $r != '' ) {
                    $r = substr($r, -1) != ';' ? $r . ';' : $r;
                    $ret .= $r . PHP_EOL;
                }
            }

            return $ret;
        }

        public function delete($table_slug = '') {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            if ( empty($table_slug) ) {
                foreach ($this->tables as $table_name) {
                    $q = "DROP TABLE IF EXISTS `$table_name`;";
                    dbDelta($q);
                    $wpdb->query($q);
                }
                delete_option('_asp_tables');
            } else {
                $q = "DROP TABLE IF EXISTS `$table_slug`;";
                dbDelta($q);
                $wpdb->query($q);
            }
        }

        public function exists($table_slug = '', $force_check = false) {
            global $wpdb;
            // Store the data in the options cache as SHOW TABLES .. query is expensive
            $table_opt = get_option('_asp_tables', array());

            if ( !$force_check ) {
                if ( isset($table_opt[$table_slug]) && $table_opt[$table_slug] == 1 )
                    return true;
            }

            $table_name = $this->table($table_slug);
            $db_table_name = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
            // SHOW command probably prohibited
            if ( is_wp_error($db_table_name) )
                return true;
            $db_table_name = is_string($db_table_name) ? $db_table_name : '';

            if (
                $table_name === false ||
                strtolower($db_table_name) != strtolower($table_name)   // Some databases return lowercase, ignore that
            ) {
                $table_opt[$table_slug] = 0;
                update_option('_asp_tables', $table_opt);
                return false;
            } else {
                $table_opt[$table_slug] = 1;
                update_option('_asp_tables', $table_opt);
                return true;
            }
        }


        /**
         * Return the table name by table slug
         *
         * @param $table_slug
         * @return string|boolean
         */
        function table($table_slug) {
            if ( isset($this->tables->{$table_slug}) )
                return $this->tables->{$table_slug};
            else
                return false;
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