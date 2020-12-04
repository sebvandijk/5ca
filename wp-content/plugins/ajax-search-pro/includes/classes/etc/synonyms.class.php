<?php
/* Prevent direct access */
defined( 'ABSPATH' ) or die( "You can't access this file directly." );

if ( ! class_exists( 'ASP_Synonyms' ) ) {
    /**
     * Class operating the synonyms (singleton)
     *
     * @class        ASP_Synonyms
     * @version        1.0
     * @package        AjaxSearchPro/Classes
     * @category      Class
     * @author        Ernest Marcinko
     */

    class ASP_Synonyms {

        /**
         * Core singleton class
         * @var asp_synonyms self
         */
        private static $_instance;

        /**
         * Array of synonyms, set during the init process
         * @var array
         */
        private $synonyms = array();
        /**
         * False until initialized
         * @var bool
         */
        private $initialized = false;

        /**
         * False, until the synonyms as keywords conversion is called
         * @var bool
         */
        private $converted = false;

        /**
         * Gets keyword synonyms, based on keyword+language. Returns all synonyms when $keyword is empty.
         *
         * @param string $keyword
         * @param string $language
         * @param bool $refresh
         * @return array|bool Array of synonyms, all synonyms array or False on failure.
         */
        public function get($keyword = '', $language = '', $refresh = false) {
            if ( $keyword == '' ) {
                if ( $refresh || $this->initialized === false ) {
                    $this->init();
                }
                return $this->synonyms; // return all
            } else {
                $keyword = $this->processKeyword($keyword);
                if ( $keyword != '' ) {
                    if ( $refresh || $this->initialized === false ) {
                        $this->init();
                    }
                    $lang = $language == '' ? 'default' : $language;
                    if ( isset($this->synonyms[$lang], $this->synonyms[$lang][$keyword]) )
                        return $this->synonyms[$lang][$keyword];
                    else
                        return false;
                }
            }

            return false;
        }

        /**
         * Checks if a keyword+language pair has synonyms.
         * If $keyword is empty, checks if there are any synonyms are available
         *
         * @param string $keyword
         * @param string $language
         * @return bool
         */
        public function exists($keyword = '', $language = '') {
            $keyword = $this->processKeyword($keyword);
            if ( $this->initialized === false ) {
                $this->init();
            }
            $lang = $language == '' ? 'default' : $language;
            if ( $keyword == '' ) {
                return count($this->synonyms) > 0;
            } else {
                return isset($this->synonyms[$lang], $this->synonyms[$lang][$keyword]);
            }
        }

        /**
         * Adds a keyword and synonyms to the database
         *
         * @param string $keyword
         * @param string|array $synonyms
         * @param string $language
         * @return int number of rows inserted
         */
        public function add($keyword, $synonyms, $language = '') {
            $synonyms_arr = $this->processSynonyms($synonyms);
            $keyword = $this->processKeyword($keyword);

            if ( count($synonyms_arr) < 1 || $keyword == '' )
                return 0;

            global $wpdb;
            $table = wd_asp()->db->table('synonyms');
            // Use INSERT IGNORE to prevent any errors (only warnings)
            $query = $wpdb->prepare(
                "INSERT IGNORE INTO `$table` (keyword, synonyms, lang) VALUES( '%s', '%s', '%s')",
                stripslashes_deep($keyword),
                implode(',', stripslashes_deep($synonyms_arr)),
                $language
            );
            return $wpdb->query($query);
        }

        /**
         * Updates are row, based on $keyword+$lang unique key. If the row does not exist, it is created.
         *
         * @param string $keyword
         * @param string|array $synonyms
         * @param string $language
         * @param bool $overwrite_existing
         * @return int number of affected rows
         */
        public function update($keyword, $synonyms, $language = '', $overwrite_existing = true) {
            $synonyms_arr = $this->processSynonyms($synonyms);
            $keyword = $this->processKeyword($keyword);

            if ( count($synonyms_arr) < 1 || $keyword == '' )
                return 0;

            if ( !$this->exists($keyword, $language) ) {
                return $this->add($keyword, $synonyms_arr, $language);
            } else {
                if ( $overwrite_existing ) {
                    global $wpdb;
                    $table = wd_asp()->db->table('synonyms');
                    $query = $wpdb->prepare(
                        "UPDATE `$table` SET synonyms='%s' WHERE keyword='%s' AND lang='%s'",
                        implode(',', $synonyms_arr),
                        $keyword,
                        $language
                    );
                    return $wpdb->query($query);
                } else {
                    return 0;
                }
            }
        }

        /**
         *  Converts synonyms into keyword => synonym pairs
         */
        public function synonymsAsKeywords() {
            if ( $this->initialized === false ) {
                $this->init();
            }
            if ( count($this->synonyms) > 0 && $this->converted == false ) {
                foreach ( $this->synonyms as $lang => $synonyms ) {
                    $additional_syns = array();
                    foreach ( $synonyms as $kw => $syns ) {
                        foreach ( $syns as $syn ) {
                            if ( !isset($additional_syns[$syn]) ) {
                                $additional_syns[$syn] = array_merge(
                                   array($kw),
                                   array_diff($syns, array($syn))
                                );
                            }
                        }
                    }
                    if ( count($additional_syns) > 0 ) {
                        $this->synonyms[$lang] = array_merge(
                            $this->synonyms[$lang],
                            $additional_syns
                        );
                    }
                }
                $this->converted = true;
            }
        }

        /**
         * Deletes a row based on keyword+language keys
         *
         * @param string $keyword
         * @param string $language
         * @return int number of affected rows
         */
        public function delete($keyword, $language = '') {
            $keyword = $this->processKeyword($keyword);

            if ( $keyword != '' ) {
                global $wpdb;
                $table = wd_asp()->db->table('synonyms');
                $query = $wpdb->prepare(
                    "DELETE FROM `$table` WHERE keyword='%s' AND lang='%s'",
                    $keyword,
                    $language
                );
                return $wpdb->query($query);
            } else {
                return 0;
            }
        }

        /**
         * Deletes a row by ID
         *
         * @param $id
         * @return int number of affected rows
         */
        public function deleteByID($id) {
            $id = $id + 0;
            if ( $id != 0 ) {
                global $wpdb;
                $table = wd_asp()->db->table('synonyms');
                $query = $wpdb->prepare(
                    "DELETE FROM `$table` WHERE id=%d",
                    $id
                );
                return $wpdb->query($query);
            } else {
                return 0;
            }
        }

        /**
         * Deletes all rows from the synonyms database table
         */
        public function wipe() {
            global $wpdb;
            $table = wd_asp()->db->table('synonyms');
            if ( $table != '' )
                $wpdb->query( "TRUNCATE TABLE `$table`" );
        }


        /**
         * Looks for a synonym based on keyword and language. If keyword is empty, lists all results from the language.
         *
         * @param string $keyword
         * @param string $language When set to 'any', looks in all languages. Empty '' value is the default language.
         * @param int $limit
         * @param bool $exact
         * @return array Results
         */
        public function find($keyword = '', $language = '', $limit = 30, $exact = false) {
            $keyword = $this->processKeyword($keyword);
            global $wpdb;

            $table = wd_asp()->db->table('synonyms');
            if ( $keyword == '' ) {
                if ( $language != 'any' )
                    $lang_query = "WHERE lang LIKE '".esc_sql($language)."' ";
                else
                    $lang_query = '';
                $res = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT keyword, synonyms, lang, id
                     FROM `$table`
                     $lang_query
                     ORDER BY id DESC LIMIT %d",
                        ($limit + 0)
                    ),
                    ARRAY_A
                );
            } else {
                if ( $language != 'any' )
                    $lang_query = "AND lang LIKE '".esc_sql($language)."' ";
                else
                    $lang_query = '';
                $kw = $exact == true ? $keyword : '%' . $wpdb->esc_like($keyword) . '%';
                $res = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT keyword, synonyms, lang, id,
                     (
                      (case when
                        (keyword LIKE '%s')
                         then 1 else 0 end
                      ) +
                      (case when
                        (keyword LIKE '%s')
                         then 1 else 0 end
                      )
                     ) as relevance
                     FROM `$table`
                     WHERE
                      keyword LIKE '%s' 
                      $lang_query
                     ORDER BY relevance DESC, id DESC LIMIT %d",
                        $keyword,
                        $wpdb->esc_like($keyword) . '%',
                        $kw,
                        ($limit + 0)
                    ),
                    ARRAY_A
                );
            }


            return $res;
        }

        /**
         * Generates an export file to the upload directory.
         *
         * @return int|string -1 on error, 0 when no rows to export, URL of file on success
         */
        public function export() {
            if ( $this->exists() === false )
                return 0;

            global $wpdb;
            $table = wd_asp()->db->table('synonyms');
            $res = $wpdb->get_results(
                "SELECT keyword, synonyms, lang
                 FROM `$table`
                 ORDER BY id ASC LIMIT 100000",
                ARRAY_A
            );

            if ( count($res) == 0 )
                return 0; // Nothing to export

            $contents = json_encode($res);
            $filename = 'asp_synonyms_export.txt';

            if ( wpd_put_file( wd_asp()->upload_path . $filename , $contents) !== false )
                return wd_asp()->upload_url . $filename;
            else
                return -1;
        }

        /**
         * Imports synonyms from an export file.
         *
         * @param $path
         * @return int Number of affected rows. -2 on file IO errors, -1 on file content errors
         */
        public function import($path) {
            global $wpdb;

            $att = attachment_url_to_postid($path);
            if ( $att != 0 ) {
                $att = get_attached_file($att);
                $contents = wpd_get_file($att);
            } else {
                $contents = wpd_get_file($path);
            }

            if ( !empty($contents) ) {
                $contents = json_decode($contents, true);
                if ( is_array($contents) ) {
                    $inserted = 0;
                    $values = array();
                    $table = wd_asp()->db->table('synonyms');
                    foreach ( $contents as $syn ) {
                        $value    = $wpdb->prepare(
                            "(%s, %s, %s)",
                            $syn['keyword'],
                            $syn['synonyms'],
                            $syn['lang']
                        );
                        $values[] = $value;

                        // Split INSERT at every 200 records
                        if ( count( $values ) > 199 ) {
                            $values = implode( ', ', $values );
                            $query  = "INSERT IGNORE INTO `$table`
                                (`keyword`, `synonyms`, `lang`)
                                VALUES $values";
                            $inserted += $wpdb->query( $query );
                            $values = array();
                        }
                    }
                    // Remaining synonyms
                    if ( count( $values ) > 0 ) {
                        $values = implode( ', ', $values );
                        $query  = "INSERT IGNORE INTO `$table`
                                (`keyword`, `synonyms`, `lang`)
                                VALUES $values";
                        $inserted += $wpdb->query( $query );
                    }

                    return $inserted;
                } else {
                    return -1; // Invalid content?
                }
            } else {
                return -2; // Something went wrong
            }
        }

        /**
         * Creates the synonyms table and the constraints.
         *
         * @param string $table_name
         * @return array queries
         */
        public function createTable($table_name = '') {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $return = array();

            if ($table_name == '')
                $table_name = wd_asp()->db->table('synonyms');

            $charset_collate = "";
            if ( ! empty( $wpdb->charset ) ) {
                $charset_collate_bin_column = "CHARACTER SET $wpdb->charset";
                $charset_collate            = "DEFAULT $charset_collate_bin_column";
            }
            if ( strpos( $wpdb->collate, "_" ) > 0 ) {
                $charset_collate .= " COLLATE $wpdb->collate";
            }

            $query = "
            CREATE TABLE IF NOT EXISTS `$table_name` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `keyword` varchar(50) NOT NULL,
              `synonyms` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
              `lang` varchar(20) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE (`keyword`, `lang`)
            ) $charset_collate AUTO_INCREMENT=1 ;";
            dbDelta($query);
            $wpdb->query($query);
            $return[] = $query;

            $query            = "SHOW INDEX FROM $table_name";
            $indices          = $wpdb->get_results( $query );
            $existing_indices = array();

            foreach ( $indices as $index ) {
                if ( isset( $index->Key_name ) ) {
                    $existing_indices[] = $index->Key_name;
                }
            }

            // Worst case scenario optimal indexes
            if ( ! in_array( 'keyword_lang', $existing_indices ) ) {
                $sql = "CREATE INDEX keyword_lang ON $table_name (keyword(50), lang(20))";
                $wpdb->query( $sql );
                $return[] = $sql;
            }

            return $return;
        }

        /**
         * Initializes the synonyms variable
         *
         * @param bool $synonyms_as_keywords When true, it will treat each synonym as a keyword as well
         */
        private function init() {
            global $wpdb;
            $table = wd_asp()->db->table('synonyms');
            $res = $wpdb->get_results(
                "SELECT keyword, synonyms, lang FROM `$table` ORDER BY id DESC LIMIT 15000",
                ARRAY_A
            );
            if ( !is_wp_error($res) && count($res) > 0 ) {
                $this->synonyms = array();
                foreach( $res as $row ) {
                    $lang = $row['lang'] == '' ? 'default' : $row['lang'];
                    if ( !isset($this->synonyms[$lang]) )
                        $this->synonyms[$lang] = array();
                    $this->synonyms[$lang][$row['keyword']] = wpd_comma_separated_to_array($row['synonyms']);
                }
            }   // else $this->synonyms stays false

            $this->initialized = true;
        }

        /**
         * Clears the synonyms array before the DB processing
         *
         * @param $synonyms
         * @return array
         */
        private function processSynonyms($synonyms) {
            $synonyms_arr = is_array($synonyms) ?
                wpd_comma_separated_to_array(implode(',',$synonyms)) : wpd_comma_separated_to_array($synonyms);

            foreach ( $synonyms_arr as $k=>&$w ) {
                $w = ASP_mb::strtolower($w);
            }

            return $synonyms_arr;
        }

        /**
         * Clears the keyword before the DB processing
         *
         * @param $keyword
         * @return mixed
         */
        private function processKeyword($keyword) {
            return ASP_mb::strtolower(trim($keyword));
        }

        /**
         * Get the instane of asp_indexTable
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