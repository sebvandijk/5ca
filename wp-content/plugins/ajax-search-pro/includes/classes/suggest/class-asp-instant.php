<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('WD_ASP_Instant')) {
    /**
     * Class WD_ASP_Instant
     *
     * Instant autocomplete suggestions and instant search management
     *
     */
    class WD_ASP_Instant {

        private $args = array();

        /* Class constants */
        public static $defaults = array(
            'table_name' => 'wd_instant',
            'file_name_pattern' => '{{id}}_autocomplete',
            'file_ext' => 'js'
        );

        public function __construct($args) {
            global $wpdb;
            // No search instance, use default args
            $args = wp_parse_args($args, self::$defaults);
            if (isset($wpdb->base_prefix)) {
                $prefix = $wpdb->base_prefix;
            } else {
                $prefix = $wpdb->prefix;
            }
            $args['table_name'] = $prefix . $args['table_name'];
            $args = apply_filters("asp_instant_auto_args", $args);
            $this->args = $args;
        }

        /* Communication from-to table data */
        public function get_suggestions($args) {
            global $wpdb;

            $defaults = array(
                'id' => 0,
                'autocomplete' => 0,
                'compact' => 1,
                'limit' => 1500
            );
            $args = wp_parse_args( $args, $defaults );

            if ( $args['autocomplete'] == 1 ) {
                $query = 'SELECT DISTINCT(title) FROM %s WHERE search_id=%d LIMIT %d';
            } else {
                $query = 'SELECT * FROM %s WHERE search_id=%d LIMIT %d';
            }
            $query = $wpdb->prepare();

            $wpdb->get_results( $query, OBJECT );
        }

        /**
         * Adds a list of suggestions to the database
         *
         * @param [
         *   'id' => int[] search ID,
         *   'autocomplete' => int 0 or 1
         *   'items' => mixed[] items to add
         * ] $args function arguments
         * @uses remove_accents()
         * @return int number of items added
         */
        public function add_suggestions($args) {
            global $wpdb;

            $defaults = array(
                'id' => 0,
                'autocomplete' => 0,
                'items' => array()
            );
            $args = wp_parse_args( $args, $defaults );
            if ( count($args['items']) == 0 ) return 0;

            $values = array();
            if ( $args['autocomplete'] == 1 ) {
                foreach ($args['items'] as $item) {
                    $values[] = $wpdb->prepare("(0,0,%d,'%s','','','','',1,'')", $args['id'], $item['title']);
                }
            } else {
                foreach ($args['items'] as $item) {
                    $values[] = $wpdb->prepare(
                        "(%d, %d, %d, '%s', '%s', '%s', '%s', '%s', 0, '%s')",
                        $item['object_id'], $item['object_type'], $args['id'], $item['title'],
                        $item['url'], $item['content'], $item['tax_terms'], $item['lang'], $item['image']
                    );
                }
            }
            $table_name = $this->db_table();
            $query = "INSERT IGNORE INTO $table_name (object_id, object_type, search_id, title, url, content, tax_terms, lang, autocomplete, image) VALUES ";
            $query .= implode(',', $values);

            $wpdb->query($query);
            return count($values);
        }

        /**
         * Removes a list of suggestions from the database
         *
         * @param int|int[] $ids
         * @return int number of items deleted
         */
        public function delete_suggestions($ids) {
            global $wpdb;

            if ( !is_array($ids) )
                $ids = array($ids);
            foreach ($ids as $k=>$id)
                $ids[$k] = $id + 0;
            if ( empty($ids) )
                return 0;
            $query = 'DELETE FROM '.$this->db_table().' WHERE id IN('.implode(',', $ids).')';
            $wpdb->query($query);
            return count($ids);
        }

        /**
         * Removes a list of suggestions from the database
         *
         * @param int|int[] $ids
         * @return int number of items deleted
         */
        public function clear_suggestions($id) {
            global $wpdb;

            $id = $id + 0;
            $query = $wpdb->prepare('DELETE FROM %s WHERE search_id = %d', $this->db_table(), $id);
            return $wpdb->query($query);
        }

        /* File handling related operations */
        public function get_file($id, $path) {
            return false; // Not exist
        }
        public function generate_files($id, $args) {
            return false; // Failure
        }
        public function file_exists($id, $path) {
            $filep = $this->file_path($id, $path);
            return ( file_exists($filep) && @filesize($filep)>1024 );
        }
        public function delete_file($id, $args) {
        }

        private function file_path($id, $path) {
            return str_replace('{{id}}', $id, $path . $this->args['file_name_pattern'] . $this->args['file_ext']);
        }

        /* Database table structure related operations */
        public function db_table() {
            return $this->args['table_name'];
        }
        public function db_check() {
            // Check table existence
        }
        public function db_create() {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $table_name = $this->args['table_name'];
            $query = "
                CREATE TABLE IF NOT EXISTS `$table_name` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `object_id` bigint(20) NOT NULL,
                  `object_type` tinyint(4) NOT NULL,
                  `search_id` int(11) NOT NULL,
                  `title` text NOT NULL,
                  `url` varchar(1000) NOT NULL,
                  `image` varchar(1000) NOT NULL,
                  `content` text NOT NULL,
                  `tax_terms` text NOT NULL,
                  `autocomplete` tinyint(1) unsigned NOT NULL,
                  PRIMARY KEY (`id`)
                ) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
            ";
            dbDelta($query);
            $wpdb->query($query);
        }
        public function db_update() {
            // Updates on the table
        }
        public function db_clear() {
            global $wpdb;
            $table_name = $this->args['table_name'];
            $query = "DELETE FROM $table_name";
            $wpdb->query($query);
            $query = "ALTER TABLE $table_name AUTO_INCREMENT = 1";
            $wpdb->query($query);
        }
        public function db_delete() {
            global $wpdb;
            $table_name = $this->args['table_name'];
            $query = "DROP TABLE IF EXISTS $table_name";
            $wpdb->query($query);
        }

    }
}