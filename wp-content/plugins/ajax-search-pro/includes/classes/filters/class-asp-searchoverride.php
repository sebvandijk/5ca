<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_SearchOverride_Filter")) {
    /**
     * Class WD_ASP_SearchOverride_Filter
     *
     * Handles search override filters
     *
     * @class         WD_ASP_SearchOverride_Filter
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Filters
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_SearchOverride_Filter extends WD_ASP_Filter_Abstract {

        public function handle() {}

        /**
         * Checks and cancels the original search query made by WordPress, if required
         *
         * @param string $query The SQL query
         * @param WP_Query() $wp_query The instance of WP_Query() for this query
         * @return bool
         */
        public function maybeCancelWPQuery($query, $wp_query) {
            if ( $this->checkSearchOverride(true, $wp_query) === true ) {
                $query = false;
            }
            return $query;
        }

        /**
         * Overrides the $posts object array with the results from Ajax Search Pro
         *
         * @param array[] $posts array of posts
         * @param WP_Query() $wp_query The instance of WP_Query() for this query
         * @return array[] array of posts
         */
        public function override($posts, $wp_query) {
            global $wd_asp;

            $checkOverride = $this->checkSearchOverride(false, $wp_query);
            if ( $checkOverride === false) {
                return $posts;
            } else {
                $_p_id = $checkOverride[0];
                $s_data = $checkOverride[1];
            }

            // The get_query_var() is malfunctioning in some cases!!! use $_GET['paged']
            //$paged = (get_query_var('paged') != 0) ? get_query_var('paged') : 1;
            if ( isset($_GET['asp_force_reset_pagination']) ) {
                // For the correct pagination highlight
                $_GET['paged'] = 1;
                $paged = 1;
                set_query_var('paged', 1);
                set_query_var('page', 1);
            } else {
                if ( isset($_GET['paged']) ) {
                    $paged = $_GET['paged'];
                } else if ( isset($wp_query->query_vars['paged']) ) {
                    $paged = $wp_query->query_vars['paged'];
                } else {
                    $paged = 1;
                }
            }

            $instance = wd_asp()->instances->get($_p_id);
            $sd = $instance['data'];
            // First check the asp_ls, as s might be set already
            $phrase = isset($_GET['asp_ls']) ? $_GET['asp_ls'] : $_GET['s'];

            $paged = $paged <= 0 ? 1 : $paged;

            // Elementor related
            if (
                isset($wp_query->query_vars, $wp_query->query_vars['posts_per_page']) &&
                $wp_query->query_vars['post_type'] === 'asp_override'
            ) {
                $posts_per_page = $wp_query->query_vars['posts_per_page'];
            } else {
                $posts_per_page = $sd['results_per_page'];
            }
            if ( $posts_per_page == 'auto' ) {
                $posts_per_page = get_option( 'posts_per_page' );
            }
            $posts_per_page = $posts_per_page == 0 ? 1 : $posts_per_page;

            $s_data = apply_filters('asp_search_override_data', $s_data, $posts, $wp_query, $_p_id, $phrase);

            // A possible exit point for the user, if he sets the _abort argument
            if ( isset($s_data['_abort']) )
                return $posts;

            $args = array(
                "s" => $phrase,
                "_ajax_search" => false,
                "posts_per_page" => $posts_per_page,
                "page"  => $paged
            );

            $args = self::getAdditionalArgs($args);

            if ( count($s_data) == 0 )
                $asp_query = new ASP_Query($args, $_p_id);
            else
                $asp_query = new ASP_Query($args, $_p_id, $s_data);

            $res = $asp_query->posts;

            // Elementor Posts widget no results text
            if (
                count($res) == 0 &&
                isset($wp_query->query_vars, $wp_query->query_vars['is_elementor'])
            ) {
                echo ASP_Helpers::generateHTMLResults(array(), false, $_p_id, $phrase, 'elementor');
            }

            // Elementor override fix
            if ( defined('ELEMENTOR_VERSION') && isset($wp_query->posts) )
                $wp_query->posts = $res;

            $wp_query->found_posts = $asp_query->found_posts;
            if (($wp_query->found_posts / $posts_per_page) > 1)
                $wp_query->max_num_pages = ceil($wp_query->found_posts / $posts_per_page);
            else
                $wp_query->max_num_pages = 0;

            return $res;
        }

        /**
         * Checks and gets additional arguments for the override query
         *
         * @param mixed[] $args query arguments for the ASP_Query()
         * @return mixed[] modified query arguments
         */
        public static function getAdditionalArgs( $args ) {
            global $wpdb;

            // WooCommerce price filter
            if ( isset($_GET['min_price'], $_GET['max_price']) ) {
                $args['post_meta_filter'][] = array(
                    'key'     => '_price',         // meta key
                    'value'   => array( floatval($_GET['min_price']), floatval($_GET['max_price']) ),
                    'operator' => 'BETWEEN'
                );
            }

            // WooCommerce or other custom Ordering
            if ( isset($_GET['orderby']) || isset($_GET['product_orderby']) ) {
                $o_by = isset($_GET['orderby']) ? $_GET['orderby'] : $_GET['product_orderby'];
                $o_by = str_replace(' ', '', (strtolower($o_by)));
                if ( isset($_GET['order']) || isset($_GET['product_order']) ) {
                    $o_way = isset($_GET['order']) ? $_GET['order'] : $_GET['product_order'];
                } else {
                    if ( $o_by == 'price' || $o_by == 'product_price' ) {
                        $o_way = 'ASC';
                    } else {
                        $o_way = 'DESC';
                    }
                }
                $o_way = strtoupper($o_way);
                if ( $o_way != 'DESC' && $o_way != 'ASC' ) {
                    $o_way = 'DESC';
                }
                switch ( $o_by ) {
                    case 'id':
                    case 'post_id':
                    case 'product_id':
                        $args['post_primary_order'] = "id $o_way";
                        break;
                    case 'popularity':
                    case 'post_popularity':
                    case 'product_popularity':
                        $args['post_primary_order'] = "customfp $o_way";
                        $args['post_primary_order_metatype'] = 'numeric';
                        $args['_post_primary_order_metakey'] = 'total_sales';
                        break;
                    case 'rating':
                    case 'post_rating':
                    case 'product_rating':
                        // Custom query args here
                        $args['cpt_query']['fields'] = "(
                            SELECT
                                IF(AVG( $wpdb->commentmeta.meta_value ) IS NULL, 0, AVG( $wpdb->commentmeta.meta_value ))
                            FROM
                                $wpdb->comments
                                LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)
                            WHERE
                                $wpdb->posts.ID = $wpdb->comments.comment_post_ID
                                AND ( $wpdb->commentmeta.meta_key = 'rating' OR $wpdb->commentmeta.meta_key IS null )
                        ) as average_rating, ";
                        $args['cpt_query']['orderby'] = "average_rating $o_way, ";

                        // Force different field order for index table
                        $args['post_primary_order'] = "average_rating $o_way";
                        break;
                    case 'date':
                    case 'post_date':
                    case 'product_date':
                        $args['post_primary_order'] = "post_date $o_way";
                        break;
                    case 'name':
                    case 'post_name':
                    case 'product_name':
                        $args['post_primary_order'] = "post_title $o_way";
                        break;
                    case 'price':
                    case 'product_price':
                    case 'price-desc':
                        $args['post_primary_order'] = "customfp $o_way";
                        $args['post_primary_order_metatype'] = 'numeric';
                        $args['_post_primary_order_metakey'] = '_price';
                        break;
                    case 'relevance':
                        $args['post_primary_order'] = "relevance $o_way";
                        break;
                }
            }

            return $args;
        }

        /**
         * Checks if the default WordPress search query is executed right now, and if it needs an override.
         * Also sets some cookie and request variables, if needed.
         *
         * @param bool $check_only when true, only checks if the override should be initiated, no variable changes
         * @param WP_Query() $wp_query The instance of WP_Query() for this query
         * @return array|bool
         */
        public function checkSearchOverride($check_only = true, $wp_query) {
            // Check the search query
            if ( !$this->isSearch($wp_query) ) {
                return false;
            }

            // If get method is used, then the cookies are not present or not used
            if ( isset($_GET['p_asp_data']) ) {
                if ( $check_only )
                    return true;
                $_p_id = isset($_GET['p_asid']) ? $_GET['p_asid'] : $_GET['np_asid'];
                if ( $_GET['p_asp_data'] == 1 ) {
                    $s_data = $_GET;
                } else {
                    // Legacy support
                    parse_str(base64_decode($_GET['p_asp_data']), $s_data);
                }
            } else if (
                isset($_GET['s'], $_COOKIE['asp_data'], $_COOKIE['asp_phrase']) &&
                $_COOKIE['asp_phrase'] == $_GET['s']
            ) {
                if ( $check_only )
                    return true;
                parse_str($_COOKIE['asp_data'], $s_data);
                $_POST['np_asp_data'] = $_COOKIE['asp_data'];
                $_POST['np_asid'] = $_COOKIE['asp_id'];
                $_p_id = $_COOKIE['asp_id'];
            } else {
                // Probably the search results page visited via URL, not triggered via search bar
                if ( isset($_GET['post_type']) && $_GET['post_type'] == 'product') {
                    $override_id = get_option("asp_woo_override", -1);
                } else {
                    $override_id = get_option("asp_st_override", -1);
                }
                if ( $override_id > -1 && wd_asp()->instances->exists( $override_id ) ) {
                    $inst = wd_asp()->instances->get( $override_id );
                    if ( $inst['data']['override_default_results'] == 1 ) {
                        return array($override_id, array());
                    }
                }

                // Something is not right
                return false;
            }

            return array($_p_id, $s_data);
        }

        public function isSearch($wp_query) {
            $is_search = true;
            $soft_check = defined('ELEMENTOR_VERSION') || wd_asp()->o['asp_compatibility']['query_soft_check'];

            // This can't be a search query if none of this is set
            if ( !isset($wp_query, $wp_query->query_vars, $_GET['s']) ) {
                $is_search = false;
            } else {
                // Possible candidates for search below
                if ( $soft_check ) {
                    // In soft check mode, it does not have to be the main query
                    if ( !$wp_query->is_search() ) {
                        $is_search = false;
                    }
                } else {
                    if ( !$wp_query->is_search() || !$wp_query->is_main_query() ) {
                        $is_search = false;
                    }
                }
                if ( !$is_search && isset($wp_query->query_vars['aps_title']) ) {
                    $is_search = true;
                }
            }

            // GEO directory search, do not override
            if ( $is_search && isset($_GET['geodir_search']) ) {
                $is_search = false;
            }

            // Elementor or other forced override
            if ( isset($wp_query->query_vars) && $wp_query->query_vars['post_type'] === 'asp_override' ) {
                $is_search = true;
            }

            // Is this the admin area?
            if ( $is_search && is_admin() )
                $is_search = false;

            // Possibility to add exceptions
            return apply_filters('asp_query_is_search', $is_search, $wp_query);
        }

        /**
         * Fixes the non-live result URLs for generic themes
         *
         * @param $url
         * @param $post
         * @return mixed
         */
        public function fixUrls($url, $post ) {
            if (isset($post->asp_guid))
                return $post->asp_guid;
            return $url;
        }

        /**
         * Fixes the URLs of the non-live search results, when using the Genesis Framework
         *
         * @param $output
         * @param $wrap
         * @param $title
         * @return mixed
         */
        public function fixUrlsGenesis( $output, $wrap, $title ) {
            global $post;

            if ( isset($post, $post->asp_guid) && is_object($post) && function_exists('genesis_markup') ) {
                $pattern = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";
                $title = preg_replace($pattern, $post->asp_guid, $title);

                $output = genesis_markup( array(
                    'open'    => "<{$wrap} %s>",
                    'close'   => "</{$wrap}>",
                    'content' => $title,
                    'context' => 'entry-title',
                    'params'  => array(
                        'wrap'  => $wrap,
                    ),
                    'echo'    => false,
                ) );
            }

            return $output;
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