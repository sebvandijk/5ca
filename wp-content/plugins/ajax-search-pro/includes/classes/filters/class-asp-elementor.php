<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Elementor_Filter")) {
    /**
     * Class WD_ASP_Elementor_Filter
     *
     * Elementor related filters
     *
     * @class         WD_ASP_Elementor_Filter
     * @version       1.1
     * @package       AjaxSearchPro/Classes/Filters
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Elementor_Filter extends WD_ASP_Filter_Abstract {

        public function posts( $args = array(), $widget = array() ) {
            if ( isset($_GET['asp_ls']) ) {
                if ( isset($_GET['p_asid']) ) {
                    $id = intval( $_GET['p_asid'] );
                } else if ( isset($_POST['p_asid']) ) {
                    $id = intval( $_POST['p_asid'] );
                } else if ( get_option("asp_st_override", -1) > 0 ) {
                    $id = get_option("asp_st_override", -1);
                } else {
                    return $args;
                }
                $data = $widget->get_data();
                if (
                    wd_asp()->instances->exists( $id ) &&
                    isset($data['settings'], $data['settings']['_css_classes']) &&
                    strpos($data['settings']['_css_classes'], 'asp_es_'.$id) !== false
                ) {
                    if ( isset($_GET['asp_force_reset_pagination']) ) {
                        // For the correct pagination highlight
                        $args['paged']= 1;
                    }
                    $args['post_type'] = 'asp_override';
                    $args['is_elementor'] = true;
                }
            }
            return $args;
        }

        public function posts_archive( $args = array() ) {
            if ( isset($_GET['asp_ls']) ) {
                if ( isset($_GET['p_asid']) ) {
                    $id = intval( $_GET['p_asid'] );
                } else if ( isset($_POST['p_asid']) ) {
                    $id = intval( $_POST['p_asid'] );
                } else if ( get_option("asp_st_override", -1) > 0 ) {
                    $id = get_option("asp_st_override", -1);
                } else {
                    return $args;
                }

                if (
                    wd_asp()->instances->exists( $id )
                ) {
                    if ( isset($_GET['asp_force_reset_pagination']) ) {
                        // For the correct pagination highlight
                        $args['paged'] = 1;
                    }
                    $args['post_type'] = 'asp_override';
                }
            }
            return $args;
        }

        public function products($args, $atts, $type) {
            if ( isset($_GET['asp_ls']) ) {
                if ( isset($_GET['p_asid']) ) {
                    $id = intval( $_GET['p_asid'] );
                } else if ( isset($_POST['p_asid']) ) {
                    $id = intval( $_POST['p_asid'] );
                } else if ( get_option("asp_st_override", -1) > 0 ) {
                    $id = get_option("asp_st_override", -1);
                } else {
                    return $args;
                }

                if (
                    wd_asp()->instances->exists( $id )
                ) {
                    $ids = array();
                    $phrase = isset($_GET['asp_ls']) ? $_GET['asp_ls'] : $_GET['s'];
                    $search_args = array(
                        "s" => $phrase,
                        "_ajax_search" => false,
                        "post_type" => array('product'),
                        "search_type" => array('cpt'),
                        'posts_per_page' =>9999
                    );
                    $search_args = WD_ASP_SearchOverride_Filter::getAdditionalArgs($search_args);

                    if ( isset($_GET['asp_force_reset_pagination']) ) {
                        // For the correct pagination highlight
                        $search_args['page'] = 1;
                    }
                    $options = ASP_Helpers::getSearchOptions();
                    if ( $options === false || count($options) == 0 )
                        $asp_query = new ASP_Query($search_args, $id);
                    else
                        $asp_query = new ASP_Query($search_args, $id, $options);

                    foreach ( $asp_query->posts as $r ) {
                        $ids[] = $r->ID;
                    }
                    if ( count($ids) > 0 ) {
                        $args['post__in'] = $ids;
                    } else {
                        $args['post__in'] = array(-1);
                        echo ASP_Helpers::generateHTMLResults(array(), false, $id, $phrase, 'elementor');
                    }
                    $args['orderby'] = 'post__in';
                }
            }

            return $args;
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