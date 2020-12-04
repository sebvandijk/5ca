<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_IndexTable_Handler")) {
    /**
     * Class WD_MS_IndexTable_Handler
     *
     * Index Table requests handler
     *
     * @class         WD_ASP_IndexTable_Handler
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_IndexTable_Handler extends WD_ASP_Handler_Abstract {

        /**
         * This function handles the index table ajax requests
         */
        public function handle() {
            if ( $_POST['action'] == 'asp_indextable_optimize' ) {
                $it = new asp_indexTable();
                print "OPTIMIZE: " . $it->optimize();
                die();
            }

			ASP_Helpers::prepareAjaxHeaders();
            if (isset($_POST['data'])) {
                if ( is_array($_POST['data']) )
                    $options = $_POST['data'];
                else
                    parse_str($_POST['data'], $options);
                $options = wd_asp()->instances->decode_params($options);
            } else {
                print "No post data detected, function terminated.";
                die();
            }

            update_option("asp_recreate_index", 0);

            $limit = $options['it_limit'];
            // Adjust the limit based on the previous and longest request duration
            if ( isset($_POST['last_request_duration'], $_POST['longest_request_duration']) ) {
                $dur = ( intval( $_POST['last_request_duration'] ) + intval( $_POST['longest_request_duration'] ) ) / 2;
                if ( $dur > 25 ) {
                    $limit = intval($limit / 5);
                } else if ( $dur > 20 ) {
                    $limit = intval($limit / 4);
                } else if ( $dur > 15 ) {
                    $limit = intval($limit / 3);
                } else if ( $dur > 10 ) {
                    $limit = intval($limit / 2);
                }
                $limit = $limit < 1 ? 1 : $limit;
            }

            $it_obj = new asp_indexTable(array(
                'index_title' => $options['it_index_title'],
                'index_content' => $options['it_index_content'],
                'index_excerpt' => $options['it_index_excerpt'],
                'index_tags' => $options['it_index_tags'],
                'index_categories' => $options['it_index_categories'],
                'post_types' => $options['it_post_types'],
                'attachment_mime_types' => $options['it_attachment_mime_types'],

                'index_pdf_content' => $options['it_index_pdf_content'],
                'index_pdf_method' => $options['it_index_pdf_method'],
                'index_text_content' => $options['it_index_text_content'],
                'index_richtext_content' => $options['it_index_richtext_content'],
                'index_msword_content' => $options['it_index_msword_content'],
                'index_msexcel_content' => $options['it_index_msexcel_content'],
                'index_msppt_content' => $options['it_index_msppt_content'],

                'post_statuses' => $options['it_post_statuses'],
                'index_taxonomies' =>$options['it_index_taxonomies'],
                'index_permalinks' =>$options['it_index_permalinks'],
                'index_customfields' => $options['it_index_customfields'],
                'index_author_name'  => $options['it_index_author_name'],
                'index_author_bio'   => $options['it_index_author_bio'],
                'blog_id' => !empty($_POST['blog_id']) ? $_POST['blog_id'] : get_current_blog_id(),
                'extend' => (w_isset_def($_POST['asp_index_action'], 'new') == 'extend' ? 1 : 0),
                'limit'  => $limit,
                'use_stopwords' => $options['it_use_stopwords'],
                'stopwords' => $options['it_stopwords'],
                'min_word_length' => $options['it_min_word_length'],
                'extract_shortcodes' => $options['it_extract_shortcodes'],
                'exclude_shortcodes' => $options['it_exclude_shortcodes'],
                'synonyms_as_keywords' => $options['it_synonyms_as_keywords']
            ));
            if ( $_POST['action'] == 'asp_indextable_get_stats' ) {
                $stats = array(
                    "postsIndexed" => $it_obj->getPostsIndexed(),
                    "postsToIndex" => $it_obj->getPostIdsToIndexCount(),
                    "totalKeywords" => $it_obj->getTotalKeywords()
                );
                print "!!!ASP_INDEX_STAT_START!!!";
                print_r(json_encode($stats));
                print "!!!ASP_INDEX_STAT_STOP!!!";
                die();
            }
            if ( isset($_POST['asp_index_action']) ) {
                switch ($_POST['asp_index_action']) {
                    case 'new':
                        $ret = $it_obj->newIndex();
                        print "New index !!!ASP_INDEX_START!!!";
                        print_r(json_encode($ret));
                        print "!!!ASP_INDEX_STOP!!!";
                        die();
                        break;
                    case 'extend':
                        $ret = $it_obj->extendIndex();
                        print "Extend index !!!ASP_INDEX_START!!!";
                        print_r(json_encode($ret));
                        print "!!!ASP_INDEX_STOP!!!";
                        die();
                        break;
                    case 'switching_blog':
                        $ret = $it_obj->extendIndex(true);
                        print "Extend index (blog_switch) !!!ASP_INDEX_START!!!";
                        print_r(json_encode($ret));
                        print "!!!ASP_INDEX_STOP!!!";
                        die();
                        break;
                    case 'delete':
                        $ret = $it_obj->emptyIndex();
                        print "Delete index !!!ASP_INDEX_START!!!";
                        print_r(json_encode($ret));
                        print "!!!ASP_INDEX_STOP!!!";
                        die();
                        break;
                }
            }
            // no action set, or other failure
            print "No action !!!ASP_INDEX_START!!!0!!!ASP_INDEX_STOP!!!";
            die();
        }

        // ------------------------------------------------------------
        //   ---------------- SINGLETON SPECIFIC --------------------
        // ------------------------------------------------------------
        public static function getInstance() {
            if ( ! ( self::$_instance instanceof self ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }
}