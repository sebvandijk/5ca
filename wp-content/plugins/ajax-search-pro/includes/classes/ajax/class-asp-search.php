<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Search_Handler")) {
    /**
     * Class WD_ASP_Search_Handler
     *
     * This is the ajax search handler class
     *
     * @class         WD_ASP_Search_Handler
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Search_Handler extends WD_ASP_Handler_Abstract {
        private $cache;

        /**
         * Oversees and handles the search request
         *
         * @param bool $dontGroup
         * @return array|mixed|void
         */
        public function handle($dontGroup = false) {

            $perf_options = wd_asp()->o['asp_performance'];

            if (w_isset_def($perf_options['enabled'], 1)) {
                $performance = new wpd_Performance('asp_performance_stats');
                $performance->start_measuring();
            }
            $s = $_POST['aspp'];

            if (is_array($_POST['options']))
                $options = $_POST['options'];
            else
                parse_str($_POST['options'], $options);

            $id = (int)$_POST['asid'];
            $instance = wd_asp()->instances->get($id);
            $sd = &$instance['data'];

            if (
                wd_asp()->o['asp_caching']['caching'] == 1 &&
                !($sd['showmoreresults'] == 1 && $sd['more_results_action'] == 'ajax')
            ) {
                $this->printCache($options, $s, $id);
            }

            // If preview, we need the details
            if ( isset($_POST['asp_preview_options']) && (current_user_can("activate_plugins") || ASP_DEMO) ) {
                require_once(ASP_PATH . "backend" . DIRECTORY_SEPARATOR . "settings" . DIRECTORY_SEPARATOR . "types.inc.php");
                parse_str( $_POST['asp_preview_options'], $preview_options );
                $_POST['asp_preview_options'] = wpdreams_parse_params($preview_options);
                $_POST['asp_preview_options'] = wd_asp()->instances->decode_params($_POST['asp_preview_options']);
                $sd = $_POST['asp_preview_options'];
            }

            $asp_query = new ASP_Query(array(
                "s"    => $s,
                "_id"  => $id,
                "_ajax_search"  => true,
                "_call_num"     => isset($_POST['asp_call_num']) ? $_POST['asp_call_num'] : 0
            ), $id, $options);
            $results = $asp_query->posts;

            if (count($results) <= 0 && $sd['keywordsuggestions']) {
                $results = $asp_query->kwSuggestions();
            } else if (count($results) > 0) {
                $results = apply_filters('asp_only_non_keyword_results', $results, $id, $s);
            }

            $results = apply_filters('asp_ajax_results', $results, $id, $s, $sd);

            /*if (get_option("asp_stat") == 1)
                asp_statistics::addKeyword($id, $s);*/

            do_action('asp_after_search', $s, $results, $id);

            if (w_isset_def($perf_options['enabled'], 1)) {
                $performance->stop_measuring();
            }

            $html_results = ASP_Helpers::generateHTMLResults($results, $sd, $id, $s);

            // Override from hooks
            if (isset($_POST['asp_get_as_array'])) {
                return $results;
            }

            $html_results = apply_filters('asp_before_ajax_output', $html_results, $id);

            $final_output = "";
            /* Clear output buffer, possible warnings */
            $final_output .= "!!ASPSTART_HTML!!" . $html_results . "!!ASPEND_HTML!!";
            $final_output .= "!!ASPSTART_DATA!!";
            $final_output .= json_encode(array(
                'results_count' => isset($results["keywords"]) ? 0 : count($results),
                'full_results_count' => $asp_query->found_posts
            ));
            $final_output .= "!!ASPEND_DATA!!";

            $this->setCache($final_output);
			ASP_Helpers::prepareAjaxHeaders();
            print_r($final_output);
            die();
        }

        private function printCache($options, $s, $id) {
            $this->cache = new wpd_TextCache(wd_asp()->upload_path, "xasp", wd_asp()->o['asp_caching']['cachinginterval'] * 60);

            $file_name = md5(json_encode($options) . $s . $id);

            if ( wd_asp()->o['asp_caching']['caching_method'] == 'file' ) {
                $cache_content = $this->cache->getCache($file_name);
            } else {
                $cache_content = $this->cache->getDBCache($file_name);
            }
            if ( $cache_content !== false ) {
                $cache_content = apply_filters('asp_cached_content', $cache_content);
                do_action('asp_after_search', $s, $cache_content);
                print "cached(" . date("F d Y H:i:s.", $this->cache->getLastFileMtime()) . ")";
                print_r($cache_content);
                die;
            }
        }

        private function setCache($content) {
            if ( isset($this->cache) ) {
                if ( wd_asp()->o['asp_caching']['caching_method'] == 'file' ) {
                    return $this->cache->setCache('!!ASPSTART!!' . $content . "!!ASPEND!!");
                } else {
                    return $this->cache->setDBCache('!!ASPSTART!!' . $content . "!!ASPEND!!");
                }

            }
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