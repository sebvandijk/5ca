<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('ASP_Search_INDEX')) {
	/**
	 * Index table search class
	 *
	 * @class       ASP_Search_INDEX
	 * @version     1.0
	 * @package     AjaxSearchPro/Classes
	 * @category    Class
	 * @author      Ernest Marcinko
	 */
	class ASP_Search_INDEX extends ASP_Search_CPT {

		/**
		 * @var array of query parts
		 */
		protected $parts = array();
		/**
		 * @var array of custom field query parts
		 */
		protected $cf_parts = array();

		/**
		 * @var array results from the index table
		 */
		protected $raw_results = array();

		/**
		 * Content search function
		 *
		 * @return array|string
		 */
		protected function do_search() {
			global $wpdb;
			global $q_config;

            aspDebug::start('-ASP-ISEARCH-');

			$current_blog_id = get_current_blog_id();
			$args = &$this->args;

			// Check if there is anything left to look for
			if ( $args['_limit'] > 0 ) {
				$limit = $args['_limit'];
			} else {
				if ( $args['_ajax_search'] )
					$limit = $args['posts_limit'];
				else
					$limit = $args['posts_limit_override'];
			}
			if ($limit  <= 0)
				return array();

			$aspdb = wd_asp()->tables;
			if ( isset($args["_sd"]) )
				$sd = &$args["_sd"];
			else
				$sd = array();

			// General variables
			$parts = array();
			$relevance_parts = array();
			$types = array();
			$post_types = "(1)";
			$term_query = "(1)";

			$kw_logic             = $args['keyword_logic'];
			$q_config['language'] = $args['_qtranslate_lang'];

			$s = $this->s; // full keyword
			$_s = $this->_s; // array of keywords

			$sr = $this->sr; // full reversed keyword
			$_sr = $this->_sr; // array of reversed keywords

            $group_priority_select = $this->build_pgp_query( 'asp_index.doc' );
            $group_priority_select = $group_priority_select . " AS group_priority,";

			/**
			 * Determine if the priorities table should be used or not.
			 */
			$priority_select = WD_ASP_Priorities::count() > 0 ? "
	        IFNULL((
            	SELECT
	            aspp.priority
	            FROM $aspdb->priorities as aspp
	            WHERE aspp.post_id = asp_index.doc AND aspp.blog_id = " . get_current_blog_id() . "
            ), 100)
	        " : 100;


			/*------------------------- Statuses ----------------------------*/
			// Removed - it is already considered at index generation
			/*---------------------------------------------------------------*/

			/*----------------------- Gather Types --------------------------*/
			/*
             * @TODO: Cross-check with selected custom post types on the index table options panel, as if they match,
			 * this query is not needed at all!
			*/
			$page_q = "";
			if ( $args['_exclude_page_parent_child'] != '' )
				$page_q = " AND ( IF(asp_index.post_type <> 'page', 1,
							EXISTS (
								SELECT ID FROM $wpdb->posts xxp WHERE
								 xxp.ID = asp_index.doc AND
								 xxp.post_parent NOT IN (".str_replace('|', ',', $args['_exclude_page_parent_child']).") AND
								 xxp.ID NOT IN (".str_replace('|', ',', $args['_exclude_page_parent_child']).")
							)
						)
					)";

			// If no post types selected, well then return
			if ( count( $args['post_type'] ) < 1 && $page_q == "" ) {
				return '';
			} else {
				$words      = implode( "','", $args['post_type'] );
				$post_types = "(asp_index.post_type IN ('$words') $page_q)";
			}
			/*---------------------------------------------------------------*/

            $post_fields_query = '';
            $exc_fields = array_diff(array('title', 'content', 'excerpt'), $args['post_fields']);
            if ( count($exc_fields) >0 ) {
                $post_fields_arr = array();
                foreach ($args['post_fields'] as $pfield) {
                    if ( !in_array($pfield, array('title', 'content', 'excerpt')) )
                        continue;
                    $post_fields_arr[] = "asp_index.$pfield > 0";
                }
                if ( count($post_fields_arr) > 0 ) {
                    $post_fields_query = 'AND ('.implode(' OR ', $post_fields_arr).')';
                }
            }

			// ------------------------ Categories/tags/taxonomies ----------------------
			$term_query = $this->build_term_query( "asp_index.doc", "asp_index.post_type"  );
			// ---------------------------------------------------------------------


			/*------------- Custom Fields with Custom selectors -------------*/
            if ( count($args['post_type']) == 1 && in_array('attachment', $args['post_type']) && $args['attachments_cf_filters'] == 0 ) {
                $cf_select = '(1)';
            } else {
                $cf_select = $this->build_cff_query( "asp_index.doc" );
            }
			/*---------------------------------------------------------------*/

			/*------------------------ Include id's -------------------------*/
			if ( !empty($args['post_in']) ) {
				$include_posts = " AND (asp_index.doc IN (" . (is_array($args['post_in']) ? implode(",", $args['post_in']) : $args['post_in']) . "))";
			} else {
				$include_posts = "";
			}
			/*---------------------------------------------------------------*/

			/*------------------------ Exclude id's -------------------------*/
			if ( !empty($args['post_not_in']) ) {
				$exclude_posts = " AND (asp_index.doc NOT IN (" . (is_array($args['post_not_in']) ? implode(",", $args['post_not_in']) : $args['post_not_in']) . "))";
			} else {
				$exclude_posts = "";
			}
			if ( !empty($args['post_not_in2']) )
				$exclude_posts .= "AND (asp_index.doc NOT IN (".implode(",", $args['post_not_in2'])."))";
			/*---------------------------------------------------------------*/

			/*------------------------ Term JOIN -------------------------*/
			// No need, this should be indexed...
			/*---------------------------------------------------------------*/

			/*------------------------- WPML filter -------------------------*/
			$wpml_query = "";
			if ( $args['_wpml_lang'] != "" ) {
				global $sitepress;
				$site_lang_selected = false;

				if ( is_object($sitepress) && method_exists($sitepress, 'get_default_language') ) {
					$site_lang_selected = $sitepress->get_default_language() == $args['_wpml_lang'] ? true : false;
				}

				$wpml_query = "asp_index.lang = '" . ASP_Helpers::escape($args['_wpml_lang']) . "'";

				/**
				 * Imported or some custom post types might have missing translations for the site default language.
				 * If the user currently searches on the default language, empty translation string is allowed.
				 */
				if ($site_lang_selected)
					$wpml_query .= " OR asp_index.lang = ''";
				$wpml_query = " AND (" . $wpml_query . ")";
			}
			/*---------------------------------------------------------------*/

			/*----------------------- POLYLANG filter -----------------------*/
			$polylang_query = "";
			if ( $args['_polylang_lang'] != "" && $wpml_query == "" ) {
				$polylang_query = " AND (asp_index.lang = '" . ASP_Helpers::escape($args['_polylang_lang']) . "')";
			}
			/*---------------------------------------------------------------*/

			/*----------------------- Date filtering ------------------------*/
			$date_query = "";
			$date_query_parts = $this->get_date_query_parts( "ddpp" );

			if ( count($date_query_parts) > 0 )
				$date_query = " AND EXISTS( SELECT 1 FROM $wpdb->posts as ddpp WHERE " . implode(" AND ", $date_query_parts) . " AND ddpp.ID = asp_index.doc) ";
			/*---------------------------------------------------------------*/

			/*---------------------- Blog switching? ------------------------*/
            $blog_query = '';
			if ( is_multisite() ) {
                if (isset($args['_switch_on_preprocess']))
                    $blog_query = "AND asp_index.blogid IN (" . implode(',', $args['_selected_blogs']) . ")";
                else
                    $blog_query = "AND asp_index.blogid = " . $current_blog_id;
            }
			/*---------------------------------------------------------------*/

			/*---------------------- Relevance Values -----------------------*/
			$rel_val_title = w_isset_def($sd["it_title_weight"], 10);
			$rel_val_content = w_isset_def($sd["it_content_weight"], 8);
			$rel_val_excerpt = w_isset_def($sd["it_excerpt_weight"], 5);
			$rel_val_permalinks = w_isset_def($sd["it_terms_weight"], 3);
			$rel_val_terms = w_isset_def($sd["it_terms_weight"], 3);
			$rel_val_cf = w_isset_def($sd["it_cf_weight"], 3);
			$rel_val_author = w_isset_def($sd["it_author_weight"], 2);
			/*---------------------------------------------------------------*/

			/*------------------- Post type based ordering ------------------*/
			$p_type_priority = "";
			if ( isset($sd['use_post_type_order']) && $sd['use_post_type_order'] == 1 ) {
				foreach ( $sd['post_type_order'] as $pk => $p_order ) {
					$p_type_priority .= "
                    WHEN '$p_order' THEN $pk ";
				}
				if ( $p_type_priority != "")
					$p_type_priority = "
                        CASE asp_index.post_type
                    " . " " .$p_type_priority . "
						  ELSE 999
						END ";
				else
					$p_type_priority = "1";
			} else {
				$p_type_priority = "1";
			}
			/*---------------------------------------------------------------*/

			/*---------------- Primary custom field ordering ----------------*/
			$custom_field_selectp = "1 ";
			if (
					strpos($args['post_primary_order'], 'customfp') !== false &&
					$args['_post_primary_order_metakey'] !== false
			) {
				$custom_field_selectp = "(SELECT IF(meta_value IS NULL, 0, meta_value)
                FROM $wpdb->postmeta
                WHERE
                    $wpdb->postmeta.meta_key='".esc_sql($args['_post_primary_order_metakey'])."' AND
                    $wpdb->postmeta.post_id=asp_index.doc
                LIMIT 1
                ) ";
			}
			/*---------------------------------------------------------------*/

			/*--------------- Secondary custom field ordering ---------------*/
			$custom_field_selects = "1 ";
			if (
					strpos($args['post_secondary_order'], 'customfs') !== false &&
					$args['_post_secondary_order_metakey'] !== false
			) {
				$custom_field_selects = "(SELECT IF(meta_value IS NULL, 0, meta_value)
                FROM $wpdb->postmeta
                WHERE
                    $wpdb->postmeta.meta_key='".esc_sql($args['_post_secondary_order_metakey'])."' AND
                    $wpdb->postmeta.post_id=asp_index.doc
                LIMIT 1
                ) ";
			}
			/*---------------------------------------------------------------*/

			/*--------------------- Post parent IDs -------------------------*/
			$post_parents_select = '';
			if ( count($args['post_parent']) > 0 ) {
				$post_parents_select = "AND EXISTS (
					SELECT 1 FROM $wpdb->posts
					WHERE
						$wpdb->posts.ID = asp_index.doc AND
						$wpdb->posts.post_parent IN (".implode(',', $args['post_parent']).")
				) ";
			}
			/*---------------------------------------------------------------*/

			/*--------------------- Post parent IDs -------------------------*/
			$post_parents_exclude_select = '';
			if ( count($args['post_parent_exclude']) > 0 ) {
				$post_parents_exclude_select = "AND NOT EXISTS (
					SELECT 1 FROM $wpdb->posts
					WHERE
						$wpdb->posts.ID = asp_index.doc AND
						$wpdb->posts.post_parent IN (".implode(',', $args['post_parent_exclude']).")
				) ";
			}
			/*---------------------------------------------------------------*/

			/*----------------------- Exclude USER id -----------------------*/
			$user_select = "0";
			$user_join = "";
			$user_query = "";
			if ( isset($args['post_user_filter']['include']) ) {
				if ( !in_array(-1, $args['post_user_filter']['include']) )
					$user_query = "AND pj.post_author IN (".implode(", ", $args['post_user_filter']['include']).")
                    ";
			}
			if ( isset($args['post_user_filter']['exclude']) ) {
				if ( !in_array(-1, $args['post_user_filter']['exclude']) )
					$user_query = "AND pj.post_author NOT IN (".implode(", ", $args['post_user_filter']['exclude']).") ";
				else
					return array();
			}
			if ( $user_query != "" ) {
				$user_select = "pj.post_author";
				$user_join = "LEFT JOIN $wpdb->posts pj ON pj.ID = asp_index.doc";
			}
			/*---------------------------------------------------------------*/

			/*-------------- Additional Query parts by Filters --------------*/
			/**
			 * Use these filters to add additional parts to the select, join or where
			 * parts of the search query.
			 */
			$add_select = apply_filters('asp_it_query_add_select', '');
			$add_join = apply_filters('asp_it_query_add_join', '');
			$add_where = apply_filters('asp_it_query_add_where', '');
			/*---------------------------------------------------------------*/

			/**
			 * This is the main query.
			 *
			 * The ttid field is a bit tricky as the term_taxonomy_id doesn't always equal term_id,
			 * so we need the LEFT JOINS :(
			 */
			$this->ordering['primary'] = $args['post_primary_order'];
			$this->ordering['secondary'] = $args['post_secondary_order'];

			$_primary_field = explode(" ", $this->ordering['primary']);
			$this->ordering['primary_field'] = $_primary_field[0];

			$this->query = "
    		SELECT
    			{args_fields}
    		    $add_select
                asp_index.doc as id,
                asp_index.blogid as blogid,
                'pagepost' as content_type,
                $priority_select AS priority,
                $p_type_priority AS p_type_priority,
                $user_select AS post_author,
                $custom_field_selectp as customfp,
                $custom_field_selects as customfs,
                '' as date,
                '' as title,
                asp_index.post_type AS post_type,
                $group_priority_select
                (
                     asp_index.title * $rel_val_title * {rmod} +
                     asp_index.content * $rel_val_content * {rmod}  +
                     asp_index.excerpt * $rel_val_excerpt * {rmod}  +
                     asp_index.comment * $rel_val_terms * {rmod}  +
                     asp_index.link * $rel_val_permalinks * {rmod} +
                     asp_index.tag * $rel_val_terms * {rmod}  +
                     asp_index.customfield * $rel_val_cf * {rmod}  +
                     asp_index.author * $rel_val_author * {rmod}
                ) AS relevance
			FROM
				$aspdb->index as asp_index
				$user_join
				$add_join
				{args_join}
            WHERE
                    ({like_query})
                AND $post_types
                $blog_query
				$wpml_query
				$polylang_query
                $term_query
                $user_query
                AND $cf_select
                $exclude_posts
                $include_posts
                $post_parents_select
                $post_parents_exclude_select
                $date_query
                $post_fields_query
                $add_where
                {args_where}
            LIMIT {limit}";

			// Place the argument query fields
			if ( isset($args['cpt_query']) && is_array($args['cpt_query']) ) {
				$_mod_q = $args['cpt_query'];
				foreach ($_mod_q as $qk => $qv) {
					$_mod_q[$qk] = str_replace($wpdb->posts.'.ID', 'asp_index.doc', $_mod_q[$qk]);
				}
				$this->query = str_replace(
						array('{args_fields}', '{args_join}', '{args_where}', '{args_orderby}'),
						array($_mod_q['fields'], $_mod_q['join'], $_mod_q['where'], $_mod_q['orderby']),
						$this->query
				);
			} else {
				$this->query = str_replace(
						array('{args_fields}', '{args_join}', '{args_where}', '{args_orderby}'),
						'',
						$this->query
				);
			}

			$queries = array();
			$results_arr = array();

			//$words = $options['set_exactonly'] == 1 ? array($s) : $_s;
			$words = $_s;

			if ($kw_logic == "orex") {
				$rmod = 1;
				$like_query = "asp_index.term = '" . implode( "' OR asp_index.term = '", $words ) . "'";
				$queries[]  = str_replace( array('{like_query}', '{rmod}', '{limit}'), array($like_query, $rmod, $this->get_pool_size()), $this->query );
			} else if ($kw_logic == "andex") {
				foreach ( $words as $wk => $word ) {
					$rmod = 10 - ( $wk * 8 ) < 1 ? 1 : 10 - ( $wk * 8 );

					$like_query = "asp_index.term = '$word'";
					$queries[]  = str_replace( array('{like_query}', '{rmod}', '{limit}'), array($like_query, $rmod, $this->get_pool_size($word)), $this->query );
				}
			} else {
				foreach ( $words as $wk => $word ) {
					$rmod = 10 - ( $wk * 8 ) < 1 ? 1 : 10 - ( $wk * 8 );

					$like_query = "asp_index.term LIKE '".$word."%'";
					$queries[]  = str_replace( array('{like_query}', '{rmod}', '{limit}'), array($like_query, $rmod, $this->get_pool_size($word)), $this->query );

					$like_query = "asp_index.term_reverse LIKE '". (isset($_sr[$wk]) ? $_sr[$wk] : $sr ) ."%'";
					$queries[]  = str_replace( array('{like_query}', '{rmod}', '{limit}'), array($like_query, $rmod, $this->get_pool_size($word)), $this->query );
				}
			}

			/*---------------------- Post CPT IDs ---------------------------*/
			if ( in_array("ids", $args['post_fields']) ) {
				$queries[]  = str_replace( array('{like_query}', '{rmod}', '{limit}'), array("asp_index.doc LIKE '$s'", 1, $this->get_pool_size()), $this->query );
			}
			/*---------------------------------------------------------------*/

			/*----------------------- Improved title query ------------------*/
            if ( in_array('title', $args['post_fields']) && ( ASP_mb::strlen($s) > 2 || count($_s) == 0 ) ) {
                // Re-calculate the limit to slice the results to the real size
                if ( $args['_limit'] > 0 ) {
                    $limit = $args['_limit'];
                } else {
                    if ( $args['_ajax_search'] )
                        $limit = $args['posts_limit'];
                    else
                        $limit = $args['posts_limit_override'];
                }
                if ( !$args['_ajax_search'] || ( $args['_ajax_search'] && $args['_show_more_results'] ) )
                    $limit = $limit * $this->remaining_limit_mod;

                $title_query = str_replace( array('{like_query}', '{rmod}', '{limit}'), array("(asp_index.term_reverse = '' AND asp_index.term LIKE '$s%')", 1000, $limit), $this->query );
                aspDebug::start('-ASP-ISEARCH-QUERY-TITLE');
                $results_arr[99999] = $wpdb->get_results($title_query);
                aspDebug::stop('-ASP-ISEARCH-QUERY-TITLE');

                // We reached the required limit, reset the other queries, as we don't need them
                if ( count($results_arr[99999]) >= $limit )
                    $queries = array();

            }
			/*---------------------------------------------------------------*/

			if (count($queries) > 0) {
				foreach ($queries as $k => $query) {
                    aspDebug::start('-ASP-ISEARCH-QUERY-'.$k);
					$results_arr[$k] = $wpdb->get_results($query);
                    aspDebug::stop('-ASP-ISEARCH-QUERY-'.$k);
				}
			}

			// Merge results depending on the logic
			$results_arr = $this->merge_raw_results($results_arr, $kw_logic);

            aspDebug::start('-ASP-ISEARCH-PRESORT-');
			// We need to save this array with keys, will need the values later.
			$this->raw_results = $results_arr;

			// First of all sort by post type priority
			usort($results_arr, array($this, 'compare_by_ptype_priority'));

			// Do primary ordering here, because the results will sliced, and we need the correct ones on the top
			// compare_by_pr is NOT giving the correct sub-set
			usort($results_arr, array($this, 'compare_by_primary'));
            aspDebug::stop('-ASP-ISEARCH-PRESORT-');

			// Re-calculate the limit to slice the results to the real size
			if ( $args['_limit'] > 0 ) {
				$limit = $args['_limit'];
			} else {
				if ( $args['_ajax_search'] )
					$limit = $args['posts_limit'];
				else
					$limit = $args['posts_limit_override'];
			}

			$this->results_count = count($results_arr) > $limit * $this->remaining_limit_mod ? $limit * $this->remaining_limit_mod : count($results_arr);
			// For non-ajax search, results count needs to be limited to the maximum limit,
			// ..as nothing is parsed beyond that
			if ($args['_ajax_search'] == false && $this->results_count > $limit) {
				$this->results_count = $limit;
			}

			// Apply new limit, but perserve the keys
			$results_arr = array_slice($results_arr, $args['_call_num'] * $limit, $limit, true);

			$this->results = $results_arr;

			// Do some pre-processing
			$this->pre_process_results();

			// Were there items removed in the pre-process? In that case we need to lower the number of reported results.
			if ( count($this->results) < count($results_arr) ) {
				$this->results_count = $this->results_count - count($results_arr) + count($this->results);
			}

			$this->return_count = count($this->results);

            aspDebug::stop('-ASP-ISEARCH-');
			return $this->results;
		}


		/**
		 * Merges the initial results array, creating an union or intersection.
		 *
		 * The function also adds up the relevance values of the results object.
		 *
		 * @param array $results_arr
		 * @param bool $kw_logic keyword logic (and, or, andex, orex)
		 * @return array results array
		 */
		protected function merge_raw_results($results_arr, $kw_logic = "or") {
			// Store the title results array temporarily
			// We want these items to be the part of results, no matter what
			$title_results = array();
			if ( isset($results_arr[99999]) ) {
				$title_results = $results_arr[99999];
				unset($results_arr[99999]);
			}

			/*
			 * When using the "and" logic, the $results_arr contains the results in [term, term_reverse]
			 * results format. These should not be intersected with each other, so this small code
			 * snippet here divides the results array by groups of 2, then it merges ever pair to one result.
			 * This way it turns into [term1, term1_reverse, term2 ...]  array to [term1 union term1_reversed, ...]
			 *
			 * This is only neccessary with the "and" logic. Others work fine.
			 */
			if ($kw_logic == 'and') {
				$new_ra = array();
				$i = 0;
				$tmp_v = array();
				foreach ($results_arr as $_k => $_v) {
					if ($i & 1){
						// odd, so merge the previous with the current
						$new_ra[] = array_merge($tmp_v, $_v);
					}
					$tmp_v = $_v;
					$i++;
				}
				$results_arr = $new_ra;
			}

            aspDebug::start('-ASP-ISEARCH-MERGERAW-1-');
			$final_results = array();

			foreach ($results_arr as $results) {
				foreach ($results as $k => $r) {
					if ( isset( $final_results[ $r->blogid . "x" . $r->id ] ) ) {
						$final_results[ $r->blogid . "x" . $r->id ]->relevance += $r->relevance;
					} else {
						$final_results[ $r->blogid . "x" . $r->id ] = $r;
					}
				}
			}

            /**
             * NOTE: It is not possible that the $title_results array includes an element that is not part of
             * the $final_results already,
             *
             * WARNING: It may also include items that does NOT fit the search criteria,
             * that is why the else statement would INCORRECT
             */
            foreach ($title_results as $k => $r) {
                if (isset($final_results[$r->blogid . "x" . $r->id])) {
                    $final_results[ $r->blogid . "x" . $r->id ]->relevance += $r->relevance;
                } else {
                    $final_results[ $r->blogid . "x" . $r->id ] = $r;
                }
            }

            aspDebug::stop('-ASP-ISEARCH-MERGERAW-1-');

			if ($kw_logic == 'or' || $kw_logic == 'orex')
				return $final_results;

            aspDebug::start('-ASP-ISEARCH-MERGERAW-2-');
			foreach ($results_arr as $results) {
				/**
				 * array_merge($results, $title_results) - why?
				 *  -> Because here is an AND or ANDEX logic, so array intersections will be returned.
				 *     All elements in the $title_results array not necessarily are a union of subset of each $results array.
				 *     To make sure that the elements of $title_results are indeed used, merge it with the actual $results
				 *     array. The $final_results at the end will contain all items from $title_results at all times.
				 */
				$final_results = array_uintersect($final_results, array_merge($results, $title_results), array($this, 'compare_results'));
			}
            aspDebug::stop('-ASP-ISEARCH-MERGERAW-2-');

			return $final_results;
		}

		public function get_pool_size($s = false) {
		    $args = $this->args;
            $len = ASP_mb::strlen($s);

            if ( $s === false ) {
                $pool_size = $args['it_pool_size_rest'];
            } else if ( $len <= 1 ) {
                $pool_size = $args['it_pool_size_one'];
            } else if ( $len == 2 ) {
                $pool_size = $args['it_pool_size_two'];
            } else if ( $len == 3 ) {
                $pool_size = $args['it_pool_size_three'];
            } else {
                $pool_size = $args['it_pool_size_rest'];
            }

            $pool_size = $pool_size < 100 ? 100 : $pool_size;

            return $pool_size;
        }

		public function compare_by_ptype_priority($a, $b) {
			return  $a->p_type_priority - $b->p_type_priority;
		}


		/**
		 * A custom comparison function for results intersection
		 *
		 * @param $a
		 * @param $b
		 *
		 * @return mixed
		 */
		protected function compare_results($a, $b) {
			if ($a->blogid === $b->blogid)
				return $b->id - $a->id;
			// For different blogids return difference
			return $b->blogid - $a->blogid;
		}

		/**
		 * usort() custom function, sort by ID
		 *
		 * @param $obj_a
		 * @param $obj_b
		 * @return mixed
		 */
		protected function compare_posts($obj_a, $obj_b) {
			return $obj_a->id - $obj_b->id;
		}

		/**
		 * usort() custom function, sort by priority > relevance
		 *
		 * @param $a
		 * @param $b
		 * @return int
		 */
		protected function compare_by_pr($a, $b) {
			if ($a->priority === $b->priority)
				return $b->relevance - $a->relevance;
			return $b->priority - $a->priority;
		}

		private function pre_process_results() {
			// No results, save some resources
			if (count($this->results) == 0)
				return array();

			$pageposts = array();
			//$options = $this->options;
			//$searchId = $this->searchId;
			//$searchData = $this->searchData;
			$post_ids = array();
			$the_posts = array();

			$args = $this->args;
			if ( isset($args["_sd"]) )
				$sd = &$args["_sd"];
			else
				$sd = array();

			// Get the post IDs
			foreach ($this->results as $k => $v) {
				$post_ids[$v->blogid][] = $v->id;
			}

			foreach ( $post_ids as $blogid => $the_ids ) {
				if (isset($args['_switch_on_preprocess']) && is_multisite())
					switch_to_blog($blogid);
				$pargs      = array(
						'post__in'       => $the_ids,
						'orderby'       => 'post__in',
						'posts_per_page' => 1000000,
						'post_status'    => 'any',
						'post_type'      => $args['_exclude_page_parent_child'] != '' ?
								array_merge($args['post_type'], array('page')) : $args['post_type']
				);

				/**
				 * Polylang workaround
				 *  - Force any language, as the correct items are already returned by the index
				 *    table engine.
				 *  url: https://polylang.pro/doc/developpers-how-to/#all
				 */
				if ( function_exists('pll_the_languages') ) {
					$pargs['lang'] = '';
				}

				$get_posts = get_posts( $pargs );
				foreach ($get_posts as $gk=>$gv)
					$get_posts[$gk]->blogid = $blogid;
				$the_posts = array_merge( $the_posts, $get_posts );
			}

			if (isset($args['_switch_on_preprocess']) && is_multisite())
				restore_current_blog();


			// Merge the posts with the raw results to a new array
			foreach ($the_posts as $k => $r) {
				$new_result = new stdClass();

				$new_result->id = w_isset_def($r->ID, null);
				$new_result->blogid = $r->blogid;
				$new_result->title = w_isset_def($r->post_title, null);
				$new_result->post_title = $new_result->title;
				$new_result->content = w_isset_def($r->post_content, null);
				$new_result->excerpt = w_isset_def($r->post_excerpt, null);
				$new_result->image = null;

				if (isset($sd) && $sd['showauthor'] == 1) {
					$post_user = get_user_by("id", $r->post_author);

					if ( $post_user !== false ) {
						if ( $sd['author_field'] == "display_name" )
							$new_result->author = $post_user->data->display_name;
						else
							$new_result->author = $post_user->data->user_login;
					} else {
						$new_result->author = null;
					}
				}


				$new_result->date = w_isset_def($r->post_date, null);
				$new_result->post_date = $new_result->date;

				// Get the relevance and priority values
				$new_result->relevance = (int)$this->raw_results[$new_result->blogid . "x" . $new_result->id]->relevance;
				$new_result->priority = (int)$this->raw_results[$new_result->blogid . "x" . $new_result->id]->priority;
                $new_result->group_priority = (int)$this->raw_results[$new_result->blogid . "x" . $new_result->id]->group_priority;
				$new_result->p_type_priority = (int)$this->raw_results[$new_result->blogid . "x" . $new_result->id]->p_type_priority;
				$new_result->post_type = $this->raw_results[$new_result->blogid . "x" . $new_result->id]->post_type;
				if ( isset($this->raw_results[$new_result->blogid . "x" . $new_result->id]->average_rating) )
					$new_result->average_rating = (float)$this->raw_results[$new_result->blogid . "x" . $new_result->id]->average_rating;
				if ( isset($this->raw_results[$new_result->blogid . "x" . $new_result->id]->customfp) )
					$new_result->customfp = $this->raw_results[$new_result->blogid . "x" . $new_result->id]->customfp;
				if ( isset($this->raw_results[$new_result->blogid . "x" . $new_result->id]->customfs) )
					$new_result->customfs = $this->raw_results[$new_result->blogid . "x" . $new_result->id]->customfs;
				$new_result->content_type = "pagepost";
				$new_result->g_content_type = "post_page_cpt";

				$pageposts[] = $new_result;
			}

			// Order them once again
			if (count($pageposts) > 0) {
				usort( $pageposts, array( $this, 'compare_by_primary' ) );
				/**
				 * Let us save some time. There is going to be a user selecting the same sorting
				 * for both primary and secondary. Only do secondary if it is different from the primary.
				 */
				if ( $this->ordering['primary'] != $this->ordering['secondary'] ) {
					$i = 0;
					foreach ($pageposts as $pk => $pp) {
						$pageposts[$pk]->primary_order = $i;
						$i++;
					}

					usort( $pageposts, array( $this, 'compare_by_secondary' ) );
				}
			}

			$this->results = $pageposts;
		}
	}
}