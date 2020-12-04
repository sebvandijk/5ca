<?php
if (!defined('ABSPATH'))
    die('-1');

if (!class_exists("WD_ASP_SearchBox_Shortcode")) {
    /**
     * Class WD_ASP_SearchBox_Shortcode
     *
     * New Search bar shortcode wrapper
     *
     * @class         WD_ASP_Search_Shortcode
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Shortcodes
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_SearchBox_Shortcode extends WD_ASP_Shortcode_Abstract
    {

        /**
         * Does the search shortcode stuff
         *
         * @param array|null $atts
         * @return string|void
         */
        public function handle($atts) {
            extract(shortcode_atts(array(
                'id' => '1',
                'elements' => 'search',
                'extra_class' => '',
                'ratio' => '33%, 33%, 33%',
                'display_on_mobile' => 1
            ), $atts));

			// Disable back-end display on taxonomy list pages
			if ( is_admin() && isset($_GET['taxonomy']) ) return;

            if (empty($elements)) {
                $elements = array('search');
            } else {
                $elements = str_replace(" ", "", $elements);
            }
            $elements = explode(',', $elements);

            $shortcodes = array(
                "search" => "wpdreams_ajaxsearchpro",
                "settings" => "wpdreams_asp_settings",
                "results" => "wpdreams_ajaxsearchpro_results"
            );

            if ( count($elements) == 1 ) {
                $attributes = "";
                foreach ($atts as $k => $v)
                    $attributes .= " " . $k . "=" . $v;
                if (isset($shortcodes[$elements[0]])) {
                    return do_shortcode("[" . $shortcodes[$elements[0]] . $attributes . "]");
                } else {
                    return "";
                }
            }

            $ratios = array();
            if ($ratio != "") {
                $ratio = str_replace(" ", "", $ratio);
                $ratios = explode(',', $ratio);
            }

            $out = "";

            /**
             * A very special case, where elements 1 and 3 can be placed into one column
             */
            if (
                count($elements) == 3 &&
                $ratios[0] === $ratios[2] &&
                ( 100 < (int)$ratios[2] + (int)$ratios[0] + (int)$ratios[1] )
            ) {
                $attributes = "";
                foreach ($atts as $k => $v)
                    $attributes .= " " . $k . "=" . $v;

                $out = "
                    <div style='flex-basis: ".$ratios[0].";' class='asp_shortcode_column'>
                        [" . $shortcodes[$elements[0]] . $attributes . "]
                        <div style='height: 10px;'></div>
                        [" . $shortcodes[$elements[2]] . $attributes . "]
                    </div>
                    <div style='flex-basis: ".$ratios[1].";' class='asp_shortcode_column'>
                        [" . $shortcodes[$elements[1]] . $attributes . "]
                    </div>
                ";
            } else {
                $i = 0;

                foreach ($elements as $element) {
                    if (isset($shortcodes[$element])) {
                        $attributes = "";
                        foreach ($atts as $k => $v)
                            $attributes .= " " . $k . "=" . $v;
                        $rt_style = "";
                        if (isset($ratios[$i])) {
                            $rt_style = "style='flex-basis: ".$ratios[$i].";'";
                        }
                        $out .= "<div $rt_style class='asp_shortcode_column'>[" . $shortcodes[$element] . $attributes . "]</div>";
                        $i++;
                    }
                }
            }

            if ($out == "")
                return "";
            else
                return "<div class='asp_shortcodes_container'>" . do_shortcode($out) . "</div>";
        }

        // ------------------------------------------------------------
        //   ---------------- SINGLETON SPECIFIC --------------------
        // ------------------------------------------------------------
        public static function getInstance() {
            if (!(self::$_instance instanceof self)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }
}