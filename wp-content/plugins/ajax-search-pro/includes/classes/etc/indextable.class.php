<?php /** @noinspection CssInvalidAtRule */
/* Prevent direct access */
defined( 'ABSPATH' ) or die( "You can't access this file directly." );

if ( ! class_exists( 'asp_indexTable' ) ) {
	/**
	 * Class operating the index table
	 *
	 * @class        asp_indexTable
	 * @version        1.0
	 * @package        AjaxSearchPro/Classes
	 * @category      Class
	 * @author        Ernest Marcinko
	 */
	class asp_indexTable {

		/**
		 * @var array of constructor arguments
		 */
		private $args;

		/**
		 * @var string the index table name without prefix here
		 */
		private $asp_index_table = 'asp_index';

		/**
		 * @var int keywords found and added to database this session
		 */
		private $keywords_found = 0;

        /**
         * @var array posts indexed through
         */
        private $posts_indexed_now = 0;

        /**
         * @var array of post IDs to ignore from selection
         */
        private $posts_to_ignore = array();

        /**
         * @var string unique random string for special replacements
         */
        private $randstr = "wpivdny3htnydqd6mlyg";

        private $abort = false;

        private $the_post = false;

        private $lang = '';

        /**
         * Static instance storage. This is not a singleton, but used in static method to access object only functions
         *
         * @var self
         */
        private static $_instance;

		// ------------------------------------------- PUBLIC METHODS --------------------------------------------------

		function __construct( $args = array() ) {

			$defaults = array(
				// Arguments here
				'index_title'         => 1,
				'index_content'       => 1,
                'index_pdf_content'   => 0,
                'index_pdf_method'    => 'auto',
                'index_text_content'    => 0,
                'index_richtext_content'  => 0,
                'index_msword_content'    => 0,
                'index_msexcel_content'   => 0,
                'index_msppt_content'     => 0,
                'index_excerpt'       => 1,
				'index_tags'          => 0,
				'index_categories'    => 0,
				'index_taxonomies'    => "",
                'attachment_mime_types' => "",
				'index_permalinks'	  => 0,
				'index_customfields'  => "",
				'index_author_name'   => "",
				'index_author_bio'    => "",
				'blog_id'             => get_current_blog_id(),
				'extend'              => 1,
				'limit'               => 25,
				'use_stopwords'       => 1,
				'stopwords'           => '',
				'min_word_length'     => 3,
				'post_types'          => array('post', 'page'),
				'post_statuses'       => 'publish',
				'extract_shortcodes'  => 1,
				'exclude_shortcodes'  => '',
				'extract_iframes'	  => 0,
                'synonyms_as_keywords'=> 0
			);

			$this->args = wp_parse_args( $args, $defaults );
			$this->args = apply_filters( 'asp_it_args', $this->args, $defaults);

			// Swap here to have the asp_posts_indexed option for each blog different
			if ( is_multisite() && !empty($this->args['blog_id']) && $this->args['blog_id'] != get_current_blog_id() ) {
				switch_to_blog( $this->args['blog_id'] );
			}

			$this->asp_index_table = wd_asp()->tables->index;
            $this->posts_indexed_now = 0;
            $this->initIngoreList();

            require_once(ASP_CLASSES_PATH . 'etc/synonyms.class.php');
		}

		/**
		 * Generates the index table if it does not exist
		 */
		function createIndexTable() {
			global $wpdb;
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$return = array();

			$charset_collate = "";

			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate_bin_column = "CHARACTER SET $wpdb->charset";
				$charset_collate            = "DEFAULT $charset_collate_bin_column";
			}
			if ( strpos( $wpdb->collate, "_" ) > 0 ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			$table_name = $this->asp_index_table;
			$query      = "
				CREATE TABLE IF NOT EXISTS " . $table_name . " (
					doc bigint(20) NOT NULL DEFAULT '0',
					term varchar(50) NOT NULL DEFAULT '0',
					term_reverse varchar(50) NOT NULL DEFAULT '0',
					blogid mediumint(9) NOT NULL DEFAULT '0',
					content mediumint(9) NOT NULL DEFAULT '0',
					title mediumint(9) NOT NULL DEFAULT '0',
					comment mediumint(9) NOT NULL DEFAULT '0',
					tag mediumint(9) NOT NULL DEFAULT '0',
					link mediumint(9) NOT NULL DEFAULT '0',
					author mediumint(9) NOT NULL DEFAULT '0',
					category mediumint(9) NOT NULL DEFAULT '0',
					excerpt mediumint(9) NOT NULL DEFAULT '0',
					taxonomy mediumint(9) NOT NULL DEFAULT '0',
					customfield mediumint(9) NOT NULL DEFAULT '0',
					post_type varchar(50) NOT NULL DEFAULT 'post',
					item bigint(20) NOT NULL DEFAULT '0',
					lang varchar(20) NOT NULL DEFAULT '0',
			    UNIQUE KEY doctermitem (doc, term, blogid)) $charset_collate";

			dbDelta( $query );
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
			if ( ! in_array( 'term_ptype_bid_lang', $existing_indices ) ) {
				$sql = "CREATE INDEX term_ptype_bid_lang ON $table_name (term(20), post_type(20), blogid, lang(10))";
				$wpdb->query( $sql );
				$return[] = $sql;
			}
			if ( ! in_array( 'rterm_ptype_bid_lang', $existing_indices ) ) {
				$sql = "CREATE INDEX rterm_ptype_bid_lang ON $table_name (term_reverse(20), post_type(20), blogid, lang(10))";
				$wpdb->query( $sql );
				$return[] = $sql;
			}

			return $return;
		}

        /**
         * Runs a table optimize query on the index table
         *
         * @return bool|false|int
         */
        public function optimize() {
			global $wpdb;
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			// In innoDB this is mapped to "ALTER TABLE .. FORCE", aka. defragmenting
            // OPTIMIZE only needs SELECT and INSERT privileges
			return $wpdb->query( "OPTIMIZE TABLE ".$this->asp_index_table );
        }

		/**
		 * Re-generates the index table
		 *
		 * @return array (posts to index, posts indexed)
		 */
		function newIndex() {
			$this->emptyIndex( false );
            $this->emptyIgnoreList();

			return $this->extendIndex();
		}


		/**
		 * Extends the index database
		 *
		 * @param bool $switching_blog - will clear the indexed posts array
		 *
		 * @return array (posts to index, posts indexed)
		 */
		function extendIndex( $switching_blog = false ) {

			// this respects the limit, no need to count again
			$posts = $this->getPostIdsToIndex();

			foreach ( $posts as $tpost ) {
			    if ( $this->abort )
			        break;

                $this->updateIgnoreList($tpost);

				if ( $this->indexDocument( $tpost->ID, false ) ) {
                    $this->posts_indexed_now++;
                    // The post stays on ignore list otherwise (even on error 500)
                    $this->updateIgnoreList($tpost, true);
                }

			}

			// THIS MUST BE HERE!!
			// ..the statment below restores the blog before getting the correct count!
			$return = array(
				'postsToIndex'    => $this->getPostIdsToIndexCount( true ),
				//'postsIndexed'    => $this->getPostsIndexed(),
                'postsIndexedNow' => $this->getPostsIndexedNow(),
				'keywordsFound'   => $this->keywords_found,
                //'totalKeywords'   => $this->getTotalKeywords(),
                'totalIgnored'    => $this->getIgnoredList( true )
			);

			if ( is_multisite() ) {
				restore_current_blog();
			}

			return $return;
		}

        function initIngoreList() {
            $this->posts_to_ignore = get_option("_asp_index_ignore", array());
        }

        function getIgnoredList( $count_only = false ) {
		    if ( $count_only ) {
                return count($this->posts_to_ignore, COUNT_RECURSIVE) - count($this->posts_to_ignore);
            } else {
                return $this->posts_to_ignore;
            }
        }

        function emptyIgnoreList() {
            delete_option("_asp_index_ignore");
            $this->posts_to_ignore = array();
        }

        function updateIgnoreList( $post = null, $remove = false ) {
		    if ( !empty($post) ) {
		        if ( $remove ) {
		            if (
		                isset($this->posts_to_ignore[$this->args['blog_id']]) &&
                        in_array($post->ID, $this->posts_to_ignore[$this->args['blog_id']])
                    ) {
		                if (($key = array_search($post->ID, $this->posts_to_ignore[$this->args['blog_id']])) !== false) {
                            unset($this->posts_to_ignore[$this->args['blog_id']][$key]);
                        }
		                if ( empty($this->posts_to_ignore[$this->args['blog_id']]) )
		                    unset($this->posts_to_ignore[$this->args['blog_id']]);
                    }
                } else {
                    if ( !isset($this->posts_to_ignore[$this->args['blog_id']]) )
                            $this->posts_to_ignore[$this->args['blog_id']] = array();
                    $this->posts_to_ignore[$this->args['blog_id']][] = $post->ID;
                }
            }
		    update_option("_asp_index_ignore", $this->posts_to_ignore);
        }

		/**
		 * Indexes a document based on its ID
		 *
		 * @param int $post_id the post id
		 * @param bool $remove_first
		 *
		 * @return bool
		 */
		function indexDocument( $post_id, $remove_first = true, $post_editor_context = false ) {
			$args = $this->args;

			// array of all needed tokens
			$tokens = array();

            // forbidden post types
            $forbidden_pt = array('tablepress_table', 'vc_grid_item', 'revision', 'nav_menu_item', 'custom_css', 'acf');

			// On creating or extending the index, no need to remove
			if ( $remove_first ) {
				$this->removeDocument( $post_id );
			}

            /**
             * This prevents the fancy quotes and special characters to HTML output
             * NOTE: it has to be executed here before every get_post() call!!
             */
            remove_filter('the_title', 'wptexturize');
            remove_filter('the_title', 'convert_chars');

			$the_post = get_post( $post_id );
            $this->the_post = $the_post;

			if ( $the_post == null ) {
				return false;
			}

			// This needs to be here, after the get_post()
			if ( $post_editor_context === true ) {
				if ( count($args['post_types']) ) {
					if ( !in_array($the_post->post_type, $args['post_types']) )
						return false;
				} else {
					return false;
				}
			}

			// Is this a forbidden post type?
			if ( in_array($the_post->post_type, $forbidden_pt) )
			    return false;

			// Check if attachment, if so, check the mime types allowed
            if ( $the_post->post_type == 'attachment' ) {
                $mimes_arr = wpd_comma_separated_to_array($args['attachment_mime_types']);
                if ( !in_array($the_post->post_mime_type, $mimes_arr) ) {
                    return false;
                }
            }

            // --- GET THE LANGUAGE INFORMATION, IF ANY ---
            $lang = '';
            // Is WPML used?
            if ( class_exists('SitePress') )
                $lang = $this->wpml_langcode_post_id( $the_post );
            // Is Polylang used?
            if ( function_exists('pll_get_post_language') && $lang == "" ) {
                if ( $the_post->post_type == 'product_variation' && class_exists('WooCommerce') ) {
                    $lang = pll_get_post_language($the_post->post_parent, 'slug');
                } else {
                    $lang = pll_get_post_language($the_post->ID, 'slug');
                }
            }
            $this->lang = $lang;

            /**
             * For product variations set the title, content and excerpt to the original product
             */
            if ( $the_post->post_type == "product_variation" ) {
                $parent_post = get_post($the_post->post_parent);
                if ( !empty($parent_post) ) {
                    $the_post->post_title .= " " . $parent_post->post_title;
                    $the_post->post_content = $parent_post->post_content;
                    $the_post->post_excerpt = $parent_post->post_excerpt;
                }
            }

			if ( $args['index_content'] == 1 ) {
				$this->tokenizeContent( $the_post, $tokens );
			}

			if ( $args['index_title'] == 1 ) {
				$this->tokenizeTitle( $the_post, $tokens );
			}

			if ( $the_post->post_type == 'attachment' ) {
                $this->tokenizeMedia( $the_post, $tokens );
            }

			if ( $args['index_excerpt'] == 1 ) {
				$this->tokenizeExcerpt( $the_post, $tokens );
			}

			if ( $args['index_categories'] == 1 || $args['index_tags'] == 1 || $args['index_taxonomies'] != "" ) {
				$this->tokenizeTerms( $the_post, $tokens );
			}

			if ( $args['index_author_name'] == 1 || $args['index_author_bio'] == 1 ) {
				$this->tokenizeAuthor( $the_post, $tokens );
			}

			if ( $args['index_permalinks'] == 1 ) {
				$this->tokenizePermalinks( $the_post, $tokens );
			}

			$this->tokenizeCustomFields( $the_post, $tokens );

			if ( count( $tokens ) > 0 ) {
				return $this->insertTokensToDB( $the_post, $tokens );
			}

			/*
			 DO NOT call finishOperation() here, it would switch back the blog too early.
			 Calling this function from an action hooks does not require switching the blog,
			 as the correct one is in use there.
			*/

			return false;
		}

		/**
		 * Removes a document from the index (in case of deleting posts, etc..)
		 *
		 * @param int|array $post_id the post id
		 */
		function removeDocument( $post_id ) {
			global $wpdb;
			$asp_index_table = $this->asp_index_table;

            if ( is_array($post_id) ) {
                foreach ( $post_id as $k=>&$v )
                    $v = $v + 0;
                $post_ids = implode(', ', $post_id);
                $wpdb->query( "DELETE FROM $asp_index_table WHERE doc IN ($post_ids)"  );
            } else {
                $wpdb->query( $wpdb->prepare(
                    "DELETE FROM $asp_index_table WHERE doc = %d", $post_id
                ) );
            }

			/*
			 DO NOT call finishOperation() here, it would switch back the blog too early.
			 Calling this function from an action hooks does not require switching the blog,
			 as the correct one is in use there.
			*/
		}


        /**
         * Empties the index table
         *
         * @param bool $restore_current_blog if set to false, it wont restore multiste blog - for internal usage mainly
         * @return array
         */
		function emptyIndex( $restore_current_blog = true ) {
			global $wpdb;
			$asp_index_table = $this->asp_index_table;
			$wpdb->query( "TRUNCATE TABLE $asp_index_table" );

			if ( is_multisite() ) {
				$current = get_current_blog_id();
				$blogs   = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
				if ( $blogs ) {
					foreach ( $blogs as $blog ) {
						switch_to_blog( $blog['blog_id'] );
					}
					// Switch back to the current, like nothing happened
					switch_to_blog( $current );
				}
			}

			if ( $restore_current_blog && is_multisite() ) {
				restore_current_blog();
			}

            $return = array(
                'postsToIndex'    => $this->getPostIdsToIndexCount(),
                'totalKeywords'   => $this->getTotalKeywords()
            );

            return $return;
		}

        /**
         * Suggests pool sizes for the index table search process
         *
         * @param bool $refresh
         *
         * @return string
         */
		public static function suggestPoolSizes( $refresh = false ) {
            $double = $triple = $quadruple = $default = array(
                'one'   => 8000,
                'two'   => 10000,
                'three' => 12000,
                'rest'  => 15000
            );
            foreach ( $default as $k => $v ) {
                $double[$k] = $v * 2;
                $triple[$k] = $v * 2;
                $quadruple[$k] = $v * 2;
            }
            if ( $refresh ) {
                if ( ! ( self::$_instance instanceof self ) ) {
                    self::$_instance = new self();
                }
                $size = self::$_instance->getTotalKeywords();
                if ( $size > 500000 ) {
                    $pool = $default;
                } else if ($size > 100000) {
                    $pool = $double;
                } else if ( $size > 50000 ) {
                    $pool = $triple;
                } else if ( $size > 10000 ) {
                    $pool = $quadruple;
                } else {
                    $pool = $quadruple;
                }
                update_option('_asp_it_pool_sizes', $pool);
                return $pool;
            } else {
                return get_option('_asp_it_pool_sizes', $default);
            }
        }

		/**
		 * An empty function to override individual shortcodes. This must be a public method.
		 *
		 * @return string
		 */
		function return_empty_string() {
			return "";
		}


		// ------------------------------------------- PRIVATE METHODS -------------------------------------------------

		/**
		 * Generates the content tokens and puts them into the tokens array
		 *
		 * @param object $the_post the post object
		 * @param array $tokens tokens array
		 *
		 * @return int keywords count
		 */
		private function tokenizeContent( $the_post, &$tokens ) {
			$args = $this->args;

			// Some custom editors like Themeco X Pro requires the global $post object for it to work.
			global $post;
			$post = $the_post;

			$content = apply_filters( 'asp_post_content_before_tokenize_clear', $the_post->post_content, $the_post );

			if ( $args['extract_shortcodes'] ) {
				$content = $this->executeShortcodes( $content );
			}

			if ( $args['extract_iframes'] == 1 )
				$content .= ' ' . $this->extractIframeContent($content);

			// Strip the remaining shortcodes
			$content = strip_shortcodes( $content );

			$content = preg_replace( '/<[a-zA-Z\/][^>]*>/', ' ', $content );
			$content = strip_tags( $content );

			$filtered_content = apply_filters( 'asp_post_content_before_tokenize', $content, $the_post );

			if ( $filtered_content == "" ) {
				return 0;
			}

			$content_keywords = $this->tokenize( $filtered_content );

			foreach ( $content_keywords as $keyword ) {
				$this->insertToken( $tokens, $keyword[0], $keyword[1], 'content' );
			}

			return count( $content_keywords );
		}

		/**
		 * Generates the excerpt tokens and puts them into the tokens array
		 *
		 * @param object $the_post the post object
		 * @param array $tokens tokens array
		 *
		 * @return int keywords count
		 */
		private function tokenizeExcerpt( $the_post, &$tokens ) {
            $args = $this->args;

			if ( $the_post->post_excerpt == "" ) {
				return 0;
			}

			$filtered_excerpt = apply_filters( 'asp_post_excerpt_before_tokenize', $the_post->post_excerpt, $the_post );

            if ( $args['extract_shortcodes'] ) {
                $filtered_excerpt = $this->executeShortcodes( $filtered_excerpt );
            }

			$excerpt_keywords = $this->tokenize( $filtered_excerpt );

			foreach ( $excerpt_keywords as $keyword ) {
				$this->insertToken( $tokens, $keyword[0], $keyword[1], 'excerpt' );
			}

			return count( $excerpt_keywords );
		}

		/**
		 * Generates the title tokens and puts them into the tokens array
		 *
		 * @param object $the_post the post object
		 * @param array $tokens tokens array
		 *
		 * @return int keywords count
		 */
		private function tokenizeTitle( $the_post, &$tokens ) {
			$filtered_title = apply_filters( 'asp_post_title_before_tokenize', $the_post->post_title, $the_post );

			$title          = apply_filters( 'the_title', $filtered_title, $the_post->ID );
			$title_keywords = $this->tokenize( $title );

            // No-reverse exact title
            $single_title = $this->tokenizeSimple($title);
            if ( $single_title != '' ) {
                if (function_exists('mb_strpos') && function_exists('mb_substr')) {
                    $single_title = mb_substr($single_title, 0, 45);
                    $pos = mb_strpos($single_title, ' ');
                } else {
                    $single_title = substr($single_title, 0, 45);
                    $pos = strpos($single_title, ' ');
                }
                /*
                 * The index table unique key is (doc, term, item) - so if this word already exists, then it will be ignored
                 * by the database. To make sure it is added, append a unique string at the end, that is not searched.
                 */
                if ($pos === false)
                    $single_title .= '___';

                $this->insertToken($tokens, $single_title, 1, 'title', true);

                $single_title_al = str_replace(array('"', "'", "`", '’', '‘', '”', '“'), '', $single_title);
                if ($single_title_al !== $single_title) {
                    $this->insertToken($tokens, $single_title_al, 1, 'title', true);
                }
            }

			foreach ( $title_keywords as $keyword ) {
				$this->insertToken( $tokens, $keyword[0], $keyword[1], 'title' );
			}

			return count( $title_keywords );
		}

        /**
         * Generates the media file contents depending on the media type
         *
         * @param object $the_post the post object
         * @param array $tokens tokens array
         *
         * @return int keywords count
         */
        private function tokenizeMedia( $the_post, &$tokens ) {
            $args = $this->args;

            $filename = get_attached_file( $the_post->ID );
            if ( is_wp_error($filename) || empty($filename) || !file_exists($filename) )
                return 0;

            include_once(ASP_CLASSES_PATH . 'etc/class-asp-media-parser.php');

            $p = new ASP_Media_Parser(array(
                'pdf_parser' => $args['index_pdf_method']
            ));
            $mime = $the_post->post_mime_type;

            $this->abort = true; // Preemptively set the abort flag, so no other document content is indexed

            if ( $p->isThis('text', $mime) && $args['index_text_content'] ) {
                $contents = $p->parseTXT($filename, $mime);
                // CSV files often store the values in quotes. We don't need those in this case.
                if ( $mime == 'text/csv' )
                    $contents = str_replace(array('"', "'"), ' ', $contents);
            } else if ( $p->isThis('richtext', $mime) && $args['index_richtext_content'] ) {
                $contents = $p->parseRTF($filename, $mime);
            } else if ( $p->isThis('pdf', $mime) && $args['index_pdf_content'] ) {
                $contents = $p->parsePDF($filename, $mime);
            } else if ( $p->isThis('mso_word', $mime) && $args['index_msword_content'] ) {
                $contents = $p->parseMSOWord($filename, $mime);
            } else if ( $p->isThis('mso_excel', $mime) && $args['index_msexcel_content'] ) {
                $contents = $p->parseMSOExcel($filename, $mime);
            } else if ( $p->isThis('mso_powerpoint', $mime) && $args['index_msppt_content'] ) {
                $contents = $p->parseMSOPpt($filename, $mime);
            } else {
                $contents = '';
                $this->abort = false; // Reset the abort flag, as no document was processed
            }

            $contents = apply_filters( 'asp_file_contents_before_tokenize', $contents, $the_post );
            $keywords = $this->tokenize( $contents );

            foreach ( $keywords as $keyword ) {
                $this->insertToken( $tokens, $keyword[0], $keyword[1], 'content' );
            }

            return count( $keywords );
        }

		/**
		 * Generates the permalink tokens and puts them into the tokens array
		 *
		 * @param object $the_post the post object
		 * @param array $tokens tokens array
		 *
		 * @return int keywords count
		 */
		private function tokenizePermalinks( $the_post, &$tokens ) {
			$filtered_permalink = apply_filters( 'asp_post_permalink_before_tokenize', $the_post->post_name, $the_post );
			// Store the permalink as is, with an occurence of 1
			$this->insertToken( $tokens, $filtered_permalink, 1, 'link' );

			return 1;
		}

		/**
		 * Generates the author display name and biography tokens and puts them into the tokens array
		 *
		 * @param object $the_post the post object
		 * @param array $tokens tokens array
		 *
		 * @return int keywords count
		 */
		private function tokenizeAuthor( $the_post, &$tokens ) {
			global $wpdb;
			$args = $this->args;
			$bio  = "";

			$display_name = $wpdb->get_var(
				$wpdb->prepare( "SELECT display_name FROM $wpdb->users WHERE ID=%d", $the_post->post_author )
			);
			if ( $args['index_author_bio'] ) {
				$bio = get_user_meta( $the_post->post_author, 'description', true );
			}

			$author_keywords = $this->tokenize( $display_name . " " . $bio );
			foreach ( $author_keywords as $keyword ) {
				$this->insertToken( $tokens, $keyword[0], $keyword[1], 'author' );
			}

			return count( $author_keywords );
		}

		/**
		 * Generates taxonomy term tokens and puts them into the tokens array
		 *
		 * @param object $the_post the post object
		 * @param array $tokens tokens array
		 *
		 * @return int keywords count
		 */
		private function tokenizeTerms( $the_post, &$tokens ) {
			$args       = $this->args;
			$taxonomies = array();
			$all_terms  = array();

			if ( $args['index_tags'] ) {
				$taxonomies[] = 'post_tag';
			}
			if ( $args['index_categories'] ) {
				$taxonomies[] = 'category';
			}
			$custom_taxonomies = explode( '|', $args['index_taxonomies'] );

			$taxonomies = array_merge( $taxonomies, $custom_taxonomies );

			foreach ( $taxonomies as $taxonomy ) {
				$terms = wp_get_post_terms( $the_post->ID, trim( $taxonomy ), array( "fields" => "names" ) );
                $terms = apply_filters('asp_index_terms', $terms, $taxonomy, $the_post);
				if ( is_array( $terms ) ) {
					$all_terms = array_merge( $all_terms, $terms );
				}
			}

			if ( count( $all_terms ) > 0 ) {
				$terms_string  = implode( ' ', $all_terms );
				$term_keywords = $this->tokenize( $terms_string );

				// everything goes under the tags, thus the tokinezer is called only once
				foreach ( $term_keywords as $keyword ) {
					$this->insertToken( $tokens, $keyword[0], $keyword[1], 'tag' );
				}

				return count( $term_keywords );
			}

			return 0;
		}

		/**
		 * Generates selected custom field tokens and puts them into the tokens array
		 *
		 * @param object $the_post the post object
		 * @param array $tokens tokens array
		 *
		 * @return int keywords count
		 */
		private function tokenizeCustomFields( $the_post, &$tokens ) {
			$args = $this->args;

            if ( function_exists("mb_strlen") )
                $fn_strlen = "mb_strlen";
            else
                $fn_strlen = "strlen";

			// all of the CF content to this variable
			$cf_content = "";

			if ( $args['index_customfields'] != "" )
				$custom_fields = explode( '|', $args['index_customfields'] );
			else
				$custom_fields = array();

			if ( !in_array('_asp_additional_tags', $custom_fields) )
				$custom_fields[] = '_asp_additional_tags';

			foreach ( $custom_fields as $field ) {
			    $values = array();
			    if ( strpos($field, '__pods__') !== false ) {
			        $field = str_replace('__pods__', '', $field);
			        if ( function_exists('pods') ) {
                        $p = pods($the_post->post_type, $the_post->ID);
                        if ( is_object($p) ) {
                            $values = $p->field($field, false);
                        }
                    }
                }

			    if ( empty($values) )
			        $values = get_post_meta( $the_post->ID, $field, false );
			    $values = is_array($values) ? $values : array($values);
                $values = apply_filters( 'asp_post_custom_field_before_tokenize', $values, $the_post, $field );

				foreach ( $values as $value ) {
					if ( is_array( $value ) ) {
						$value = $this->arrayToString( $value );
					}
					$cf_content .= " " . $value;

                    // Without spaces for short values (for example product SKUs)
                    if ( $fn_strlen($value) <= 50 )
                        $cf_content .= " " . str_replace(' ', '', $value);
				}
			}

			$cf_content = apply_filters('asp_index_cf_contents_before_tokenize', $cf_content, $the_post);

			if ( $cf_content != "" ) {
				$cf_keywords = $this->tokenize( $cf_content );
				foreach ( $cf_keywords as $keyword ) {
					$this->insertToken( $tokens, $keyword[0], $keyword[1], 'customfield' );
				}

				return count( $cf_keywords );
			}

			return 0;
		}

		/**
		 * Extracts content from an IFRAME source
		 *
		 * @param $str
		 * @return string
		 * @uses ASP_Helpers::stripTagsWithContent
         */
		private function extractIframeContent($str ) {
			preg_match_all('/\<iframe.+?src=[\'"]([^"\']+)["\']/', $str, $match);
			if ( isset($match[1]) ) {
				$ret = '';
				foreach($match[1] as $link) {
					$s = wp_remote_get($link);
					if ( !is_wp_error($s) ) {
						$xs = explode('<body', $s['body']);
						$final = $s['body'];
						if ( isset($xs[1]) ) {
							$final = '<html><body ' . $xs[1];
						}
						$ret .= ' ' . ASP_Helpers::stripTagsWithContent($final, array('head','script', 'style', 'img', 'input'));
					}
				}
				return $ret;
			}
			return '';
		}


		/**
		 * Puts the keyword token into the tokens array.
		 *
		 * @param array $tokens array to the tokens
		 * @param string $keyword keyword
		 * @param int $count keyword occurrence count
		 * @param string $field the field
         * @param bool $no_reverse if the reverse keyword should be stored
		 */
		private function insertToken( &$tokens, $keyword, $count = 1, $field = 'content', $no_reverse = false ) {
		    // Take care of accidental empty keyowrds
		    if ( trim($keyword) == '' )
		        return;
			// Cant use numeric keys, it would break things..
			// We need to trim it at inserting
            $key = $keyword;
            if ( is_numeric( $keyword ) ) {
                $key = " " . $keyword;
			}

			// Preserve the non-reverse key uniqueness
			if ( $no_reverse )
                $key .= '__NOREV__';

			if ( isset( $tokens[ $key ] ) ) {
				// No need to check if $field key exists, it must exist due to the else statement
				$tokens[ $key ][ $field ] += $count;
			} else {
				$tokens[ $key ] = array(
					"content"     => 0,
					"title"       => 0,
					"comment"     => 0,
					"tag"         => 0,
					"link"        => 0,
					"author"      => 0,
					"category"    => 0,
					"excerpt"     => 0,
					"customfield" => 0,
					"taxonomy"    => 0,
					'_keyword'    => $keyword,
                    '_no_reverse' => $no_reverse
				);
				$tokens[ $key ][ $field ] += $count;
			}
		}


		/**
		 * Generates the query based on the post and the token array and inserts into DB
		 *
		 * @param object $the_post the post
		 * @param array $tokens tokens array
		 *
		 * @return bool
		 */
		private function insertTokensToDB( $the_post, $tokens ) {
			global $wpdb;
			$asp_index_table = $this->asp_index_table;
			$args            = $this->args;
			$values          = array();

			if ( count( $tokens ) <= 0 ) {
				return false;
			}

            $lang = $this->lang;

			foreach ( $tokens as $_key => $d ) {
				// If it's numeric, delete the leading space
				$term = trim( $d['_keyword'] );

                if ( isset($d['_no_reverse']) && $d['_no_reverse'] === true ) {
                    $value    = $wpdb->prepare(
                        "(%d, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %d, %s)",
                        $the_post->ID, $term, '', $args['blog_id'], $d['content'], $d['title'], $d['comment'], $d['tag'],
                        $d['link'], $d['author'], $d['category'], $d['excerpt'], $d['taxonomy'], $d['customfield'],
                        $the_post->post_type, 0, $lang
                    );
                } else {
                    $value    = $wpdb->prepare(
                        "(%d, %s, REVERSE(%s), %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %d, %s)",
                        $the_post->ID, $term, $term, $args['blog_id'], $d['content'], $d['title'], $d['comment'], $d['tag'],
                        $d['link'], $d['author'], $d['category'], $d['excerpt'], $d['taxonomy'], $d['customfield'],
                        $the_post->post_type, 0, $lang
                    );
                }

				$values[] = $value;

                // Split INSERT at every 200 records
                if ( count( $values ) > 199 ) {
                    $values = implode( ', ', $values );
                    $query  = "INSERT IGNORE INTO $asp_index_table
                    (`doc`, `term`, `term_reverse`, `blogid`, `content`, `title`, `comment`, `tag`, `link`, `author`,
                     `category`, `excerpt`, `taxonomy`, `customfield`, `post_type`, `item`, `lang`)
                    VALUES $values";
                    $wpdb->query( $query );
                    $values = array();
                }
			}

			// Add the remaining as well
			if ( count( $values ) > 0 ) {
				$values = implode( ', ', $values );
				$query  = "INSERT IGNORE INTO $asp_index_table
				(`doc`, `term`, `term_reverse`, `blogid`, `content`, `title`, `comment`, `tag`, `link`, `author`,
				 `category`, `excerpt`, `taxonomy`, `customfield`, `post_type`, `item`, `lang`)
				VALUES $values";
				$wpdb->query( $query );

                $this->keywords_found += count( $tokens );
			}

			return true;
		}

        /**
         * Performs a simple trimming, character removal on a string
         *
         * @param string $str content to tokenize
         *
         * @return string
         */
		private function tokenizeSimple( $str ) {
            if ( function_exists( 'mb_internal_encoding' ) ) {
                mb_internal_encoding( "UTF-8" );
            }

            $str = $this->html2txt( $str );
            $str = strip_tags( $str );
            $str = stripslashes( $str );
            $str = preg_replace( '/[[:space:]]+/', ' ', $str );

            $str = str_replace( array( "\n", "\r", "  " ), " ", $str );
            // Most objects except unicode characters
            $str = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $str );
            // Line feeds, carriage returns, tabs
            $str = preg_replace( '/[\x00-\x1F\x80-\x9F]/u', '', $str );

            if ( function_exists( 'mb_strtolower' ) ) {
                $str = mb_strtolower( $str );
            } else {
                $str = strtolower( $str );
            }

            $stop_words = $this->getStopWords();
            foreach ( $stop_words as $stop_word ) {
                // If there is a stopword within, this case is over
                if ( strpos($str, $stop_word) !== false ) {
                    return '';
                }
            }

            return $str;
        }

		/**
		 * Performs a keyword extraction on the given content string.
		 *
		 * @param string $str content to tokenize
		 *
		 * @return array of keywords $keyword = array( 'keyword', {count} )
		 */
		private function tokenize( $str ) {

			if ( is_array( $str ) ) {
				$str = $this->arrayToString( $str );
			}
            if ( function_exists("mb_strlen") )
                $fn_strlen = "mb_strlen";
            else
                $fn_strlen = "strlen";

			$args      = $this->args;

			if ( function_exists( 'mb_internal_encoding' ) ) {
				mb_internal_encoding( "UTF-8" );
			}

			$str = apply_filters( 'asp_indexing_string_pre_process', $str );

			$str = $this->html2txt( $str );

			$str = strip_tags( $str );
			$str = stripslashes( $str );

            // Replace non-word boundary dots with a unique string + 'd'
            $str = preg_replace("/([0-9])[\.]([0-9])/", "$1".$this->randstr."d$2", $str);

			// Remove potentially dangerous characters
			$str = str_replace( array(
				"Â·",
				"â€¦",
				"â‚¬",
				"&shy;"
			), "", $str );
			$str = str_replace( array(
				". ", // dot followed by space as boundary, otherwise it might be a part of the word
				", ", // comma followed by space only, otherwise it might be a word part
				"\\",
				//"{",
				"^",
				//"}",
				"?",
				"!",
				";",
				"Ă‹â€ˇ",
				"Ă‚Â°",
				"~",
				"Ă‹â€ş",
				"Ă‹ĹĄ",
				"Ă‚Â¸",
				"Ă‚Â§",
				//"%",
				//"=",
				"Ă‚Â¨",
				"â€™",
				"â€",
				"â€ť",
				"â€ś",
				"â€ž",
				"Â´",
				"â€”",
				"â€“",
				"Ă—",
				'&#8217;',
				"&nbsp;",
				chr( 194 ) . chr( 160 )
			), " ", $str );
			$str = str_replace( 'Ăź', 'ss', $str );

			//$str = preg_replace( '/[[:punct:]]+/u', ' ', $str );
			$str = preg_replace( '/[[:space:]]+/', ' ', $str );

			$str = str_replace( array( "\n", "\r", "  " ), " ", $str );

			// Most objects except unicode characters
			$str = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $str );

			// Line feeds, carriage returns, tabs
			$str = preg_replace( '/[\x00-\x1F\x80-\x9F]/u', '', $str );

			if ( function_exists( 'mb_strtolower' ) ) {
				$str = mb_strtolower( $str );
			} else {
				$str = strtolower( $str );
			}

			//$str = preg_replace('/[^\p{L}0-9 ]/', ' ', $str);
			$str = str_replace( "\xEF\xBB\xBF", '', $str );

			$str = trim( preg_replace( '/\s+/', ' ', $str ) );

            // Set back the non-word boundary dots
            $str = str_replace( $this->randstr."d", '.', $str );

			$str = apply_filters( 'asp_indexing_string_post_process', $str );

			$words = explode( ' ', $str );

            // Get additional words if available
			$additional_words = array();
            $pattern = array('"', "'", "`", '’', '‘', '”', '“', "+", '.', ',', '-', '_', "=", "%", '(', ')', '{', '}', '*', '[', ']', '|');
			foreach ($words as $wk => $ww) {

                // ex.: 123-45-678 to 123, 45, 678
				$ww1 = str_replace($pattern, ' ', $ww);
				$wa = explode(" ", $ww1);
				if (count($wa) > 1) {
				    foreach ( $wa as $wak => $wav ) {
                        $wav = trim(preg_replace( '/[[:space:]]+/', ' ', $wav ));
                        if ( $wav != '' ) {
                            $wa[$wak] = $wav;
                        } else {
                            unset($wa[$wak]);
                        }
                    }
                    $additional_words = array_merge($additional_words, $wa);
                }
                // ex.: 123-45-678 to 12345678
                $ww2 = str_replace($pattern, '', $ww);
                if ( $ww2 != '' && $ww2 != $ww ) {
                    $additional_words[] = $ww2;
                }
			}

			// Append them after the words array
			$words = array_merge($words, $additional_words);

            // Synonyms
            $syn_inst = ASP_Synonyms::getInstance();
            if ( $syn_inst->exists() ) {
                if ( $this->args['synonyms_as_keywords'] == 1 )
                    $syn_inst->synonymsAsKeywords();
                $additional_words = array();
                foreach ($words as $wk => $ww) {
                    if ( trim($ww) == '' )
                        continue;
                    // For the set language
                    $synonyms = $syn_inst->get($ww, $this->lang);
                    if ($synonyms !== false) {
                        $additional_words = array_merge($additional_words, $synonyms);
                    }
                    // For default language as well
                    if ( $this->lang != '' ) {
                        $synonyms = $syn_inst->get($ww, '');
                        if ($synonyms !== false) {
                            $additional_words = array_merge($additional_words, $synonyms);
                        }
                    }
                }
                if ( count($additional_words) > 0 )
                    $words = array_merge($words, $additional_words);
            }

            $stopWords = $this->getStopWords();
			$keywords = array();

			while ( ( $c_word = array_shift( $words ) ) !== null ) {
                $c_word = trim($c_word);

				if ( $c_word == '' || $fn_strlen( $c_word ) < $args['min_word_length'] ) {
					continue;
				}
				if ( !empty($stopWords) && in_array( $c_word, $stopWords ) ) {
					continue;
				}
				// Numerics wont work otherwise, need to trim that later
				if ( is_numeric( $c_word ) ) {
					$c_word = " " . $c_word;
				}

				if ( array_key_exists( $c_word, $keywords ) ) {
					$keywords[ $c_word ][1] ++;
				} else {
					$keywords[ $c_word ] = array( $c_word, 1 );
				}
			}

			$keywords = apply_filters( 'asp_indexing_keywords', $keywords );

			return $keywords;
		}

        /**
         * Returns the stop words, including the negative keywords for the current post object
         *
         * @return array
         */
        private function getStopWords() {
		    $stopWords = array();
			// Only compare to common words if $restrict is set to false
			if ( $this->args['use_stopwords'] == 1 && $this->args['stopwords'] != "" ) {
				$this->args['stopwords'] = str_replace(" ", "", $this->args['stopwords']);
				$stopWords = explode( ',', $this->args['stopwords'] );
			}
            // Post level stop-words, negative keywords
			if ( $this->the_post !== false ) {
			    $negative_keywords = get_post_meta($this->the_post->ID, '_asp_negative_keywords', true);
			    if ( !empty($negative_keywords) ) {
			        $negative_keywords = trim( preg_replace('/\s+/', ' ',$negative_keywords) );
			        $negative_keywords = explode(' ', $negative_keywords);
			        $stopWords = array_merge($stopWords, $negative_keywords);
                }
            }
			$stopWords = array_unique( $stopWords );
			foreach ( $stopWords as $sk => &$sv ) {
			    $sv = trim($sv);
			    if ( $sv == '' ) {
			        unset($stopWords[$sk]);
                }
            }

			return $stopWords;
        }

		/**
		 * Converts a multi-depth array elements into one string, elements separated by space.
		 *
		 * @param $arr
		 * @param int $level
		 *
		 * @return string
		 */
		private function arrayToString( $arr, $level = 0 ) {
			$str = "";
			if ( is_array( $arr ) ) {
				foreach ( $arr as $sub_arr ) {
					$str .= $this->arrayToString( $sub_arr, $level + 1 );
				}
			} else {
			    // Check for objects, as those yield a fatal error when converted to strings
                if ( !is_object($arr) ) {
                  $str = " " . $arr;
                }
			}
			if ( $level == 0 ) {
				$str = trim( $str );
			}

			return $str;
		}

        /**
         * Executes the shortcodes within the given string
         *
         * @param string $content
         * @return string
         */
        private function executeShortcodes($content) {
            $args = $this->args;

            $content = apply_filters( 'asp_it_before_shortcode_removal', $content );

            // WP Table Reloaded support
            if ( defined( 'WP_TABLE_RELOADED_ABSPATH' ) ) {
                include_once( WP_TABLE_RELOADED_ABSPATH . 'controllers/controller-frontend.php' );
                $wpt_reloaded = new WP_Table_Reloaded_Controller_Frontend();
            }
            // TablePress support
            if ( defined( 'TABLEPRESS_ABSPATH' ) && class_exists('TablePress') ) {
                TablePress::$model_options = TablePress::load_model( 'options' );
                TablePress::$model_table = TablePress::load_model( 'table' );
                TablePress::$controller = TablePress::load_controller( 'frontend' );
                TablePress::$controller->init_shortcodes();
            }

            // Remove user defined shortcodes
            $shortcodes = explode( ',', $args['exclude_shortcodes'] );
            $try_getting_sc_content = apply_filters('asp_it_try_getting_sc_content', true);
            foreach ( $shortcodes as $shortcode ) {
				$shortcode = trim($shortcode);
				if ( $shortcode == '' )
					continue;
                // First let us try to get any contents from the shortcode itself
                if ( $try_getting_sc_content ) {
                    $content = preg_replace(
                        '/(?:\[' . $shortcode . '[ ]+.*?\]|\[' . $shortcode . '[ ]*\])(.*?)\[\/' . $shortcode . '[ ]*]/su',
                        ' $1 ',
                        $content
                    );
                }
                // Then remove the shortcode completely
                remove_shortcode( trim( $shortcode ) );
                add_shortcode( trim( $shortcode ), array( $this, 'return_empty_string' ) );
            }

			// Try extracting the content of these shortcodes, but do not execute
            $more_shortcodes = array(
                'cws-widget', 'cws-row', 'cws-column', 'col', 'row', 'item'
            );
            foreach ( $more_shortcodes as $shortcode ) {
                // First let us try to get any contents from the shortcode itself
                $content = preg_replace(
                    '/(?:\[' . $shortcode . '[ ]+.*?\]|\[' . $shortcode . '[ ]*\])(.*?)\[\/' . $shortcode . '[ ]*]/su',
                    ' $1 ',
                    $content
                );
                remove_shortcode( $shortcode );
                add_shortcode( $shortcode, array( $this, 'return_empty_string' ) );
            }

			// These shortcodes are completely ignored, and removed with content
            $ignore_shortcodes = array(
                'vc_asp_search',
                'wd_asp',
                'wpdreams_ajaxsearchpro',
                'wpdreams_ajaxsearchpro_results',
                'wpdreams_asp_settings',
                'contact-form',
                'starrater',
                'responsive-flipbook',
                'avatar_upload',
                'product_categories',
                'recent_products',
                'templatera',
				'bsf-info-box', 'logo-slider',
                'ourteam', 'embedyt', 'gallery', 'bsf-info-box', 'tweet', 'blog', 'portfolio',
                'peepso_activity', 'peepso_profile', 'peepso_group'
            );
            foreach ( $ignore_shortcodes as $shortcode ) {
                remove_shortcode( $shortcode );
                add_shortcode( $shortcode, array( $this, 'return_empty_string' ) );
            }

            $content = do_shortcode( $content );

            // WP 4.2 emoji strip
            if ( function_exists( 'wp_encode_emoji' ) ) {
                $content = wp_encode_emoji( $content );
            }

            if ( defined( 'TABLEPRESS_ABSPATH' ) ) {
                unset( $tp_controller );
            }

            if ( defined( 'WP_TABLE_RELOADED_ABSPATH' ) ) {
                unset( $wpt_reloaded );
            }

            return apply_filters( 'asp_it_after_shortcode_removal', $content );
        }

		/**
		 * A better powerful strip tags - removes scripts, styles completely
		 *
		 * @param $document
		 *
		 * @return string stripped document
		 */
		private function html2txt( $document ) {
			$search = array(
				'@<script[^>]*?>.*?</script>@si', // Strip out javascript
				'@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
				'@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
				'@<![\s\S]*?--[ \t\n\r]*>@' // Strip multi-line comments including CDATA
			);
			$text   = preg_replace( $search, '', $document );

			return $text;
		}

        /**
         * A working hack to get the post language by post object WPML
         *
         * @param Post $post object
         *
         * @return string language string
         */
        private function wpml_langcode_post_id($post){
            global $wpdb;

            $post_type = "post_" . $post->post_type;

            $query = $wpdb->prepare("
				SELECT language_code
				FROM " . $wpdb->prefix . "icl_translations
				WHERE
				element_type = '%s' AND
				element_id = %d"
                , $post_type, $post->ID);
            $query_exec = $wpdb->get_row($query);

            if ( null !== $query_exec )
                return $query_exec->language_code;

            return "";
        }

		/**
		 * Gets the post IDs to index
		 *
		 * @return array of post IDs
		 */
		private function getPostIdsToIndex() {
			global $wpdb;
			$asp_index_table = $this->asp_index_table;
			$args            = $this->args;
			$parent_join = '';

            $_statuses = explode(",", $args['post_statuses']);
            foreach ($_statuses as $sk => &$sv)
                $sv = trim($sv);
            $valid_status    = "'" . implode("', '", $_statuses ) . "'";

			if ( count($args['post_types']) > 0 ) {
                $post_types = $args['post_types'];
                if ( class_exists('WooCommerce') && in_array('product_variation', $post_types) ) { // Special case for Woo variations
                    $post_types = array_diff($post_types, array('product_variation'));
                    $rest = '';
                    if (count($post_types) > 0)
                        $rest = " OR post.post_type IN('".implode("', '", $post_types)."') ";
                    // In case of product variation the parent post status must also match, otherwise it is not relevant
                    $parent_join = "LEFT JOIN $wpdb->posts parent ON (post.post_parent = parent.ID)";
                    $restriction = " AND ( (post.post_type = 'product_variation' AND parent.post_status IN($valid_status) ) $rest )";
                } else {
                    $restriction = " AND post.post_type IN ('" .implode("', '", $post_types). "')";
                }
			} else {
                return array();
            }

            $ignore_posts = "";
            if ( !empty($this->posts_to_ignore[$this->args['blog_id']]) )
                $ignore_posts = " AND post.ID NOT IN( ".implode(',', $this->posts_to_ignore[$this->args['blog_id']])." )";

            $mimes_restrict = '';
            if ( $args['attachment_mime_types'] != '' ) {
                $mimes_arr = wpd_comma_separated_to_array($args['attachment_mime_types']);
                if ( count($mimes_arr) > 0 )
                    $mimes_restrict = "OR ( post.post_status = 'inherit' AND post.post_mime_type IN ('" . implode("','", $mimes_arr) . "') )";
            }

			$limit        = $args['limit'] > 1000 ? 1000 : ( $args['limit'] + 0 );

			if ( $args['extend'] == 1 ) {
				// We are extending, so keep the existing
				$q = "SELECT post.ID
						FROM $wpdb->posts post
						$parent_join
						LEFT JOIN $asp_index_table r ON (post.ID = r.doc AND r.blogid = " . $args['blog_id'] . ")
						WHERE
								r.doc is null
						AND
                            (
                                post.post_status IN ($valid_status)
                                $mimes_restrict
                            )
						$restriction
						$ignore_posts
						ORDER BY post.ID ASC
						LIMIT $limit";
			} else {
				$q = "SELECT post.ID
						FROM $wpdb->posts post
						$parent_join
						WHERE
                            (
                                post.post_status IN ($valid_status)
                                $mimes_restrict
                            )
						$restriction
						$ignore_posts
						ORDER BY post.ID ASC
						LIMIT $limit";

			}
			$res = $wpdb->get_results( $q );

			return $res;
		}

		/**
		 * Gets the number documents to index
		 *
		 * @return int number of documents to index yet
		 */
		public function getPostIdsToIndexCount( $check_only = false ) {
		    if ( defined('ASP_INDEX_BYPASS_COUNT') ) {
		        return 9999;
            }

			global $wpdb;
			$args = $this->args;
			$parent_join = '';

			$asp_index_table = $this->asp_index_table;

			$_statuses = explode(",", $args['post_statuses']);
			foreach ($_statuses as $sk => &$sv)
				$sv = trim($sv);
			$valid_status    = "'" . implode("', '", $_statuses ) . "'";

            if ( count($args['post_types']) > 0  ) {
                $post_types = $args['post_types'];
                if ( class_exists('WooCommerce') && in_array('product_variation', $post_types) ) { // Special case for Woo variations
                    $post_types = array_diff($post_types, array('product_variation'));
                    $rest = '';
                    if (count($post_types) > 0) // ..are there any left?
                        $rest = " OR post.post_type IN('".implode("', '", $post_types)."') ";
                    // In case of product variation the parent post status must also match, otherwise it is not relevant
                    $parent_join = "LEFT JOIN $wpdb->posts parent ON (post.post_parent = parent.ID)";
                    $restriction = " AND ( (post.post_type = 'product_variation' AND parent.post_status IN($valid_status) ) $rest )";
                } else {
                    $restriction = " AND post.post_type IN ('" .implode("', '", $post_types). "')";
                }
            } else {
                return 0;
            }

            $ignore_posts = "";
            if ( !empty($this->posts_to_ignore[$this->args['blog_id']]) )
                $ignore_posts = " AND post.ID NOT IN( ".implode(',', $this->posts_to_ignore[$this->args['blog_id']])." )";

            $mimes_restrict = '';
            if ( $args['attachment_mime_types'] != '' ) {
                $mimes_arr = wpd_comma_separated_to_array($args['attachment_mime_types']);
                if ( count($mimes_arr) > 0 ) {
                    $mimes_restrict = "OR ( post.post_status = 'inherit' AND post.post_mime_type IN ('" . implode("','", $mimes_arr) . "') )";
                }
            }

            if ( $check_only ) {
                $q = "SELECT 1
                        FROM $wpdb->posts post
                        $parent_join
                        LEFT JOIN $asp_index_table r ON (post.ID = r.doc AND r.blogid = " . $args['blog_id'] . ")
                        WHERE
                            r.doc is null
                        AND
                            (
                                post.post_status IN ($valid_status)
                                $mimes_restrict
                            )
                        $restriction
                        $ignore_posts
                        LIMIT 1";
            } else {
                $q = "SELECT COUNT(DISTINCT post.ID)
                        FROM $wpdb->posts post
                        $parent_join
                        LEFT JOIN $asp_index_table r ON (post.ID = r.doc AND r.blogid = " . $args['blog_id'] . ")
                        WHERE
                                r.doc is null
                        AND
                            (
                                post.post_status IN ($valid_status)
                                $mimes_restrict
                            )
                        $restriction
                        $ignore_posts";
            }

			return intval( $wpdb->get_var( $q ) );
		}

        /**
         * Gets the number of so far indexed documents
         *
         * @return int number of indexed documents
         */
        public function getPostsIndexed() {
		    if ( defined('ASP_INDEX_BYPASS_COUNT') ) {
		        return 9999;
            }
            global $wpdb;
		    // Tested faster as a regular single query count
            $sql = "SELECT COUNT(count) FROM (SELECT 1 as count FROM ".$this->asp_index_table." GROUP BY doc) as A";
            return $wpdb->get_var($sql);
        }

        /**
         * Gets the number of items in the index table, multisite supported
         *
         * @return int number of rows
         */
        public function getTotalKeywords() {
		    if ( defined('ASP_INDEX_BYPASS_COUNT') ) {
		        return 9999;
            }
            global $wpdb;

            if ( is_multisite() )
                $sql = "SELECT COUNT(doc) FROM " . $this->asp_index_table;
            else
                $sql = "SELECT COUNT(doc) FROM " . $this->asp_index_table . " WHERE blogid = " . get_current_blog_id();

            return $wpdb->get_var($sql);
        }

        public function isEmpty() {
            global $wpdb;
            return $wpdb->query("SELECT 1 FROM ".$this->asp_index_table." LIMIT 1") == 0;
        }

        /**
         * Gets the number of indexed documents on this run instance
         *
         * @return int number of indexed documents
         */
        private function getPostsIndexedNow() {
            return $this->posts_indexed_now;
        }


    }
}