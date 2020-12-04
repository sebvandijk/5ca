<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('ASP_Search_BUDDYPRESS')) {
    /**
     * BuddyPress Group and Activity search
     *
     * @class       ASP_Search_BUDDYPRESS
     * @version     1.0
     * @package     AjaxSearchPro/Classes
     * @category    Class
     * @author      Ernest Marcinko
     */
    class ASP_Search_BUDDYPRESS extends ASP_Search {

        /**
         * @var array of query parts
         */
        private $parts = array();

        /**
         * The search function
         *
         * @return array|string
         */
        protected function do_search() {
            global $wpdb;
            global $q_config;

            $args = &$this->args;
            if (isset($args["_sd"]))
                $sd = &$args["_sd"];
            else
                $sd = array();

            $kw_logic = $args['keyword_logic'];
            $q_config['language'] = $args['_qtranslate_lang'];

            $s = $this->s; // full keyword
            $_s = $this->_s; // array of keywords

            $wcl = '%'; // Wildcard Left
            $wcr = '%'; // Wildcard right
            if ( $args["_exact_matches"] == 1 ) {
                if ( $args['_exact_match_location'] == 'start' )
                    $wcl = '';
                else if ( $args['_exact_match_location'] == 'end' )
                    $wcr = '';
            }


            if ( $args['_limit'] > 0 ) {
                $limit = $args['_limit'];
            } else {
                if ( $args['_ajax_search'] )
                    $limit = $args['buddypress_limit'];
                else
                    $limit = $args['buddypress_limit_override'];
            }

            if ($limit <= 0)
                return array();

            $repliesresults = array();
            $userresults = array();
            $groupresults = array();
            $activityresults = array();

            $words = $args['_exact_matches'] == 1 ? array($s) : $_s;

            if ( strpos($args['post_primary_order'], 'customfp') !== false )
                $orderby_primary = 'relevance DESC';
            else
                $orderby_primary = str_replace('post_', '', $args['post_primary_order']);

            if ( strpos($args['post_secondary_order'], 'customfs') !== false )
                $orderby_secondary = 'date DESC';
            else
                $orderby_secondary = str_replace('post_', '', $args['post_secondary_order']);


            if (function_exists('bp_core_get_user_domain')) {
                /*----------------------- User query ---------------------------*/

                // User query had been replaced with user results..

                /*---------------------------------------------------------------*/

                /*----------------------- Groups query --------------------------*/
                if ($args['bp_groups_search'] && bp_is_active('groups')) {
                    $parts = array();
                    $relevance_parts = array();
                    /*------------------------- Statuses ----------------------------*/
                    $statuses = array();
                    if ($args['bp_groups_search_public'])
                        $statuses[] = 'public';
                    if ($args['bp_groups_search_private'])
                        $statuses[] = 'private';
                    if ($args['bp_groups_search_hidden'])
                        $statuses[] = 'hidden';
                    if (count($statuses) < 1)
                        return '';
                    $swords = implode("','", $statuses);
                    $group_statuses = "( " . $wpdb->prefix . "bp_groups.status IN ('$swords') )";
                    /*---------------------------------------------------------------*/

                    if ($s != "") {
                        /*------------------------- Title query -------------------------*/
                        if ($kw_logic == 'or' || $kw_logic == 'and') {
                            $op = strtoupper($kw_logic);
                            if (count($_s) > 0)
                                $_like = implode("%' " . $op . " " . $wpdb->prefix . "bp_groups.name LIKE '%", $words);
                            else
                                $_like = $s;
                            $parts[] = "( " . $wpdb->prefix . "bp_groups.name LIKE '$wcl" . $_like . "$wcr' )";
                        } else {
                            $_like = array();
                            $op = $kw_logic == 'andex' ? 'AND' : 'OR';
                            foreach ($words as $word) {
                                $_like[] = "
                           ( " . $wpdb->prefix . "bp_groups.name LIKE '% " . $word . " %'
                        OR  " . $wpdb->prefix . "bp_groups.name LIKE '" . $word . " %'
                        OR  " . $wpdb->prefix . "bp_groups.name LIKE '% " . $word . "'
                        OR  " . $wpdb->prefix . "bp_groups.name = '" . $word . "')";
                            }
                            $parts[] = "(" . implode(' ' . $op . ' ', $_like) . ")";
                        }

                        if (count($_s) > 0) {
                            $relevance_parts[] = "(case when
                      ( " . $wpdb->prefix . "bp_groups.name LIKE '%$_s[0]%')
                       then " . w_isset_def($sd['titleweight'], 10) . " else 0 end)";
                        }
                        $relevance_parts[] = "(case when
                    ( " . $wpdb->prefix . "bp_groups.name LIKE '%$s%')
                     then " . w_isset_def($sd['etitleweight'], 10) . " else 0 end)";

                        /*---------------------------------------------------------------*/

                        /*---------------------- Description query ----------------------*/
                        if ($kw_logic == 'or' || $kw_logic == 'and') {
                            $op = strtoupper($kw_logic);
                            if (count($_s) > 0)
                                $_like = implode("%' " . $op . " lower(" . $wpdb->prefix . "bp_groups.description) LIKE '%", $words);
                            else
                                $_like = $s;
                            $parts[] = "( lower(" . $wpdb->prefix . "bp_groups.description) LIKE '$wcl" . $_like . "$wcr' )";
                        } else {
                            $_like = array();
                            $op = $kw_logic == 'andex' ? 'AND' : 'OR';
                            foreach ($words as $word) {
                                $_like[] = "
                           (lower(" . $wpdb->prefix . "bp_groups.description) LIKE '% " . $word . " %'
                        OR  lower(" . $wpdb->prefix . "bp_groups.description) LIKE '" . $word . " %'
                        OR  lower(" . $wpdb->prefix . "bp_groups.description) LIKE '% " . $word . "'
                        OR  lower(" . $wpdb->prefix . "bp_groups.description) = '" . $word . "')";
                            }
                            $parts[] = "(" . implode(' ' . $op . ' ', $_like) . ")";
                        }

                        if (count($_s) > 0) {
                            $relevance_parts[] = "(case when
                      ( " . $wpdb->prefix . "bp_groups.description LIKE '%$_s[0]%')
                       then " . w_isset_def($sd['contentweight'], 10) . " else 0 end)";
                        }
                        $relevance_parts[] = "(case when
                    ( " . $wpdb->prefix . "bp_groups.description LIKE '%$s%')
                     then " . w_isset_def($sd['econtentweight'], 10) . " else 0 end)";
                        /*---------------------------------------------------------------*/
                    }

                    /*------------------------- Build like --------------------------*/
                    $like_query = implode(' OR ', $parts);
                    if ($like_query == "")
                        $like_query = "(1)";
                    else {
                        $like_query = "($like_query)";
                    }
                    /*---------------------------------------------------------------*/

                    /*---------------------- Build relevance ------------------------*/
                    $relevance = implode(' + ', $relevance_parts);
                    if ($args['_post_use_relevance'] != 1 || $relevance == "")
                        $relevance = "(1)";
                    else {
                        $relevance = "($relevance)";
                    }
                    /*---------------------------------------------------------------*/

                    $querystr = "
             SELECT
               " . $wpdb->prefix . "bp_groups.id as id,
               $this->c_blogid as blogid,
               " . $wpdb->prefix . "bp_groups.name as title,
               " . $wpdb->prefix . "bp_groups.description as content,
               " . $wpdb->prefix . "bp_groups.date_created as date,
               $wpdb->users.user_nicename as author,
               'bp_group' as content_type,
               'bp_groups' as g_content_type,
               $relevance as relevance
             FROM
               " . $wpdb->prefix . "bp_groups
             LEFT JOIN $wpdb->users ON $wpdb->users.ID = " . $wpdb->prefix . "bp_groups.creator_id
             WHERE
                  $group_statuses
              AND $like_query
              ORDER BY $orderby_primary, $orderby_secondary
              LIMIT " . $limit;


                    $groupresults = $wpdb->get_results($querystr, OBJECT);
                    if (isset($sd['image_options'])) {
                        foreach ($groupresults as $k => $v) {
                            $group = new BP_Groups_Group($v->id);
                            $groupresults[$k]->link = bp_get_group_permalink($group);
                            if ($sd['image_options']['show_images'] == 1) {
                                $avatar_options = array('item_id' => $v->id, 'object' => 'group', 'type' => 'full', 'html' => false);
                                $im = bp_core_fetch_avatar($avatar_options);

                                if ($im != '') {
                                    $groupresults[$k]->image = $im;
                                }
                            }
                            /* Remove the results in polaroid mode */
                            if ($args['_ajax_search'] && empty($v->image) && isset($sd['resultstype']) &&
                                $sd['resultstype'] == 'polaroid' && $sd['pifnoimage'] == 'removeres') {
                                unset($groupresults[$k]);
                                continue;
                            }
                            if ($groupresults[$k]->content != '')
                                $groupresults[$k]->content = wd_substr_at_word(strip_tags($groupresults[$k]->content), $sd['descriptionlength']) . "...";

                            // --------------------------------- DATE -----------------------------------
                            if ($sd["showdate"] == 1) {
                                $post_time = strtotime($groupresults[$k]->date);

                                if ($sd['custom_date'] == 1) {
                                    $date_format = w_isset_def($sd['custom_date_format'], "Y-m-d H:i:s");
                                } else {
                                    $date_format = get_option('date_format', "Y-m-d") . " " . get_option('time_format', "H:i:s");
                                }

                                $groupresults[$k]->date = @date_i18n($date_format, $post_time);
                            }
                            // --------------------------------------------------------------------------
                        }
                    }
                }
                /*---------------------------------------------------------------*/

                /*----------------------- Activity query ------------------------*/

                if ($args['bp_activities_search'] && bp_is_active('activity')) {
                    $parts = array();
                    $relevance_parts = array();
                    /*---------------------- Description query ----------------------*/
                    if ($s != "") {
                        if ($kw_logic == 'or' || $kw_logic == 'and') {
                            $op = strtoupper($kw_logic);
                            if (count($_s) > 0)
                                $_like = implode("%' " . $op . " lower(" . $wpdb->prefix . "bp_activity.content) LIKE '%", $words);
                            else
                                $_like = $s;
                            $parts[] = "( " . $wpdb->prefix . "bp_activity.content LIKE '$wcl" . $_like . "$wcr' )";
                        } else {
                            $_like = array();
                            $op = $kw_logic == 'andex' ? 'AND' : 'OR';
                            foreach ($words as $word) {
                                $_like[] = "
                           ( " . $wpdb->prefix . "bp_activity.content LIKE '% " . $word . " %'
                        OR  " . $wpdb->prefix . "bp_activity.content LIKE '" . $word . " %'
                        OR  " . $wpdb->prefix . "bp_activity.content LIKE '% " . $word . "'
                        OR  " . $wpdb->prefix . "bp_activity.content = '" . $word . "')";
                            }
                            $parts[] = "(" . implode(' ' . $op . ' ', $_like) . ")";
                        }

                        if (count($_s) > 0) {
                            $relevance_parts[] = "(case when
                      ( " . $wpdb->prefix . "bp_activity.content LIKE '%$_s[0]%')
                       then " . w_isset_def($sd['econtentweight'], 10) . " else 0 end)";
                        }
                        $relevance_parts[] = "(case when
                        ( " . $wpdb->prefix . "bp_activity.content LIKE '%$s%')
                         then " . w_isset_def($sd['contentweight'], 10) . " else 0 end)";
                    }
                    /*---------------------------------------------------------------*/

                    /*------------------------- Build like --------------------------*/
                    $like_query = implode(' OR ', $parts);
                    if ($like_query == "")
                        $like_query = "(1)";
                    else {
                        $like_query = "($like_query)";
                    }
                    /*---------------------------------------------------------------*/

                    /*---------------------- Build relevance ------------------------*/
                    $relevance = implode(' + ', $relevance_parts);
                    if ($args['_post_use_relevance'] != 1 || $relevance == "")
                        $relevance = "(1)";
                    else {
                        $relevance = "($relevance)";
                    }
                    /*---------------------------------------------------------------*/
                    $querystr = "
                 SELECT
                   " . $wpdb->prefix . "bp_activity.id as id,
                   $this->c_blogid as blogid,
                   $wpdb->users.display_name as title,
                   " . $wpdb->prefix . "bp_activity.content as content,
                   " . $wpdb->prefix . "bp_activity.date_recorded as date,
                   $wpdb->users.display_name as author,
                   " . $wpdb->prefix . "bp_activity.user_id as author_id,
                   'bp_activity' as content_type,
                   'bp_activities' as g_content_type,
                   $relevance as relevance
                 FROM
                   " . $wpdb->prefix . "bp_activity
                 LEFT JOIN $wpdb->users ON $wpdb->users.ID = " . $wpdb->prefix . "bp_activity.user_id
                 WHERE
                   " . $wpdb->prefix . "bp_activity.component IN ('activity', 'groups')
                   AND " . $wpdb->prefix . "bp_activity.is_spam = 0
                   AND " . $wpdb->prefix . "bp_activity.hide_sitewide = 0
                   AND $like_query
                   ORDER BY $orderby_primary, $orderby_secondary
                   LIMIT " . $limit;

                    $activityresults = $wpdb->get_results($querystr, OBJECT);

                    foreach ($activityresults as $k => $v) {
                        $activityresults[$k]->link = bp_activity_get_permalink($v->id);
                        $activityresults[$k]->image = bp_core_fetch_avatar(array('item_id' => $v->author_id, 'html' => false));

                        $activityresults[$k]->content = do_shortcode($activityresults[$k]->content);
                        $activityresults[$k]->content = preg_replace("~(?:\[/?)[^\]]+/?\]~su", '', strip_tags($activityresults[$k]->content));
                        $activityresults[$k]->content = stripslashes($activityresults[$k]->content);
                        $new_content = wd_substr_at_word( $activityresults[$k]->content, $sd['descriptionlength'] );
                        if ( strlen($new_content) < strlen($activityresults[$k]->content) )
                            $activityresults[$k]->content = $new_content;

                        // --------------------------------- DATE -----------------------------------
                        if (isset($sd["showdate"]) && $sd["showdate"] == 1) {
                            $post_time = strtotime($activityresults[$k]->date);
                            /* Remove the results in polaroid mode */
                            if ($args['_ajax_search'] && empty($v->image) && isset($sd['resultstype']) &&
                                $sd['resultstype'] == 'polaroid' && $sd['pifnoimage'] == 'removeres') {
                                unset($activityresults[$k]);
                                continue;
                            }
                            if ($sd['custom_date'] == 1) {
                                $date_format = w_isset_def($sd['custom_date_format'], "Y-m-d H:i:s");
                            } else {
                                $date_format = get_option('date_format', "Y-m-d") . " " . get_option('time_format', "H:i:s");
                            }

                            $activityresults[$k]->date = @date_i18n($date_format, $post_time);
                        }
                        // --------------------------------------------------------------------------
                    }
                }


                do_action('bbpress_init');
            }

            $this->results_count = count($groupresults) + count($activityresults);

            // For non-ajax search, results count needs to be limited to the maximum limit,
            // ..as nothing is parsed beyond that
            if ($args['_ajax_search'] == false && $this->results_count > $limit) {
                $this->results_count = $limit;
            }

            $this->results = array(
                'repliesresults' => $repliesresults,
                'groupresults' => $groupresults,
                'activityresults' => $activityresults
            );

            $this->return_count = count($repliesresults) + count($groupresults) + count($activityresults);

            return $this->results;
        }

        /**
         * @param $parts
         * @param bool $is_multi
         * @return mixed
         */
        protected function build_query($parts, $is_multi = false) {
            $args = &$this->args;

            $l_parts = array();
            $r_parts = array();

            if ($is_multi == true) {
                foreach ($parts as $part) {
                    $l_parts = array_merge($l_parts, $part[0]);
                    $r_parts = array_merge($r_parts, $part[1]);
                }
            } else {
                $l_parts = $parts[0];
                $r_parts = $parts[1];
            }

            /*------------------------- Build like --------------------------*/
            $like_query = implode(' OR ', $l_parts);
            if ($like_query == "")
                $like_query = "(1)";
            else {
                $like_query = "($like_query)";
            }
            /*---------------------------------------------------------------*/

            /*---------------------- Build relevance ------------------------*/
            $relevance = implode(' + ', $r_parts);
            if ($args['_post_use_relevance'] != 1 || $relevance == "")
                $relevance = "(1)";
            else {
                $relevance = "($relevance)";
            }
            /*---------------------------------------------------------------*/


            return str_replace(
                array("{relevance_query}", "{like_query}", "{remaining_limit}"),
                array($relevance, $like_query, $this->remaining_limit),
                $this->query
            );

        }

    }
}