<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * FILE CONTENTS:
 *  1. BASIC FUNCTIONS
 *  2. FILE SYSTEM SPECIFIC WRAPPERS
 *  3. TAXONOMY AND TERM SPECIFIC
 *  4. BACK-END SPECIFIC
 *  5. EXPORT IMPORT
 *  6. NON-AJAX RESULTS
 *  7. FRONT-END
*/

//----------------------------------------------------------------------------------------------------------------------
// 1. BASIC FUNCTIONS
//----------------------------------------------------------------------------------------------------------------------

if (!function_exists('wpd_is_wp_version')) {
    function wpd_is_wp_version($operator = '>', $version = '4.5') {
        global $wp_version;

        return version_compare($wp_version, $version, $operator);
    }
}

if (!function_exists('wpd_is_wp_older')) {
    function wpd_is_wp_older($version = '4.5') {
        return wpd_is_wp_version('<', $version);
    }
}

if (!function_exists('wpd_is_wp_newer')) {
    function wpd_is_wp_newer($version = '4.5') {
        return wpd_is_wp_version('>', $version);
    }
}

if (!function_exists('wpd_get_terms')) {
    function wpd_get_terms($args = array()) {
        if ( wpd_is_wp_older('4.5') ) {
            return get_terms($args['taxonomy'], $args);
        } else {
            return get_terms($args);
        }
    }
}

if (!function_exists('wpd_get_languages_array')) {
    /* Get WPML or Polylang languages list in array */
    function wpd_get_languages_array() {
        $langs = array();
        if ( function_exists('pll_languages_list') ) {
            $_langs = pll_languages_list(array(
                'fields' => 'slug'
            ));
            if (is_array($_langs)) {
                foreach($_langs as $key => $_lang)
                    $langs[$_lang] = $_lang;
            }
        } else if ( function_exists('icl_get_languages') ) {
            $_langs = icl_get_languages('skip_missing=0&orderby=KEY&order=DIR&link_empty_to=str');
            if (is_array($_langs)) {
                foreach(array_reverse($_langs) as $key => $_lang)
                    $langs[$_lang['language_code']] = $_lang['translated_name'];
            }
        }

        return $langs;
    }
}

if (!function_exists("asp_get_inner_substring")) {
    /**
     * Get the string from inbetween delimiters
     *
     * @param $string
     * @param $delim
     * @return string
     */
    function asp_get_inner_substring($string, $delim) {

        $s = explode($delim, $string, 3); // also, we only need 2 items at most
        return isset($s[1]) ? $s[1] : '';
    }
}

if (!function_exists("asp_get_outer_substring")) {
    /**
     * Get the string from outside the delimiters
     *
     * @param $string
     * @param $delim
     * @return string
     */
    function asp_get_outer_substring($string, $delim) {
        $s = explode($delim, $string, 3); // also, we only need 2 items at most
        return isset($s[0], $s[2]) ? $s[0] . $s[2] : $string;
    }
}

if (!function_exists("wpd_comma_separated_to_array")) {
    /**
     * @param $string - Input string to convert to array
     * @param string $separator - Separator to separate by (default: ,)
     *
     * @return array
     */
    function wpd_comma_separated_to_array($string, $separator = ',') {
        //Explode on comma
        $vals = explode($separator, $string);

        //Trim whitespace
        foreach ($vals as $key => $val) {
            $vals[$key] = trim($val);
        }
        //Return empty array if no items found
        //http://php.net/manual/en/function.explode.php#114273
        return array_diff($vals, array(""));
    }
}

if ( !function_exists('wd_strip_tags_ws') ) {
    /**
     * Strips tags, but replaces them with whitespace
     *
     * @param string $string
     * @param string $allowable_tags
     * @return string
     * @link https://stackoverflow.com/a/38200395
     */
    function wd_strip_tags_ws($string, $allowable_tags = '') {
        $string = str_replace('<', ' <', $string);
        $string = strip_tags($string, $allowable_tags);
        $string = str_replace('  ', ' ', $string);
        $string = trim($string);

        return $string;
    }
}

if (!function_exists("wd_closetags")) {
    /**
     * Close unclosed HTML tags
     *
     * @param $html
     * @return string
     */
    function wd_closetags ( $html ) {
        $unpaired = array('hr', 'br', 'img');

        // put all opened tags into an array
        preg_match_all ( "#<([a-z]+)( .*)?(?!/)>#iU", $html, $result );
        $openedtags = $result[1];
        // remove unpaired tags
        if (is_array($openedtags) && count($openedtags)>0) {
            foreach ($openedtags as $k=>$tag) {
                if (in_array($tag, $unpaired))
                    unset($openedtags[$k]);
            }
        } else {
	        // Replace a possible un-closed tag from the end, 30 characters backwards check
	        $html = preg_replace('/(.*)(\<[a-zA-Z].{0,30})$/', '$1', $html);
            return $html;
        }
        // put all closed tags into an array
        preg_match_all ( "#</([a-z]+)>#iU", $html, $result );
        $closedtags = $result[1];
        $len_opened = count ( $openedtags );
        // all tags are closed
        if( count ( $closedtags ) == $len_opened ) {
	        // Replace a possible un-closed tag from the end, 30 characters backwards check
	        $html = preg_replace('/(.*)(\<[a-zA-Z].{0,30})$/', '$1', $html);
            return $html;
        }
        $openedtags = array_reverse ( $openedtags );
        // close tags
        for( $i = 0; $i < $len_opened; $i++ ) {
            if ( !in_array ( $openedtags[$i], $closedtags ) ) {
                $html .= "</" . $openedtags[$i] . ">";
            } else {
                unset ( $closedtags[array_search ( $openedtags[$i], $closedtags)] );
            }
        }
	    // Replace a possible un-closed tag from the end, 30 characters backwards check
	    $html = preg_replace('/(.*)(\<[a-zA-Z].{0,30})$/', '$1', $html);
        return $html;
    }
}

if (!function_exists("wd_mysql_escape_mimic")) {
	/**
	 * Mimics the old mysql_escape function
	 *
	 * @internal
	 * @param $inp
	 * @return mixed
	 */
	function wd_mysql_escape_mimic($inp) {
		if(is_array($inp))
			return array_map(__METHOD__, $inp);

		if(!empty($inp) && is_string($inp)) {
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
		}

		return $inp;
	}
}

if (!function_exists("wd_substr_at_word")) {
    /**
     * Substring cut off at word endings
     *
     * @param $text
     * @param $length
     * @param $tolerance
     * @return string
     */
    function wd_substr_at_word($text, $length, $tolerance = 8) {

        if ( function_exists("mb_strlen") &&
             function_exists("mb_strrpos") &&
             function_exists("mb_substr")
        ) {
            $fn_strlen = "mb_strlen";
            $fn_strrpos = "mb_strrpos";
            $fn_substr = "mb_substr";
        } else {
            $fn_strlen = "strlen";
            $fn_strrpos = "strrpos";
            $fn_substr = "substr";
        }

        if ($fn_strlen($text) <= $length) return $text;

        $s = $fn_substr($text, 0, $length);
        $s = $fn_substr($s, 0, $fn_strrpos($s, ' '));

        // In case of a long mash-up, it will not let overflow the length
        if ( $fn_strlen($s) > ($length + $tolerance) )
            return $fn_substr($s, 0, ($length + $tolerance));

        return $s;
  }
}

if (!function_exists("wd_in_array_r")) {
    /**
     * Recursive in_array
     *
     * @param $needle
     * @param $haystack
     * @param bool $strict
     * @return bool
     */
    function wd_in_array_r($needle, $haystack, $strict = false) {
      foreach ($haystack as $item) {
          if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && wd_in_array_r($needle, $item, $strict))) {
              return true;
          }
      }
  
      return false;
  }
}

if (!function_exists("wd_array_to_string")) {
    /**
     * Converts a multi-depth array elements into one string, elements separated by space.
     *
     * @param $arr
     * @param int $level
     *
     * @return string
     */
    function wd_array_to_string($arr, $level = 0) {
        $str = "";
        if (is_array($arr)) {
            foreach ($arr as $sub_arr) {
                $str .= wd_array_to_string($sub_arr, $level + 1);
            }
        } else {
            $str = " " . $arr;
        }
        if ($level == 0) {
            $str = trim($str);
        }

        return $str;
    }
}

if (!function_exists("wd_explode")) {
    /**
     * Explode with a trim function
     *
     * @param string $delim delimiter
     * @param string $s input string
     *
     * @return string
     */
    function wd_explode($delim=',', $str) {
        if ( !is_array($str) )
            $str = explode($delim, $str);
        foreach ( $str as $k => &$v) {
            $v = trim($v);
            if ( $v == '' )
                unset($str[$k]);
        }
        return $str;
    }
}

if (!function_exists("wd_current_page_url")) {
    /**
     * Returns the current page url
     *
     * @return string
     */
    function wd_current_page_url() {
        $pageURL = 'http';

        $port = !empty($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : 80;

        $server_name = !empty($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "";
        $server_name = empty($server_name) && !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $server_name;

        if( isset($_SERVER["HTTPS"]) ) {
            if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
        }
        $pageURL .= "://";
        if ($port != "80") {
            $pageURL .= $server_name.":".$port.$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $server_name.$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }
} 
if (!function_exists("wpdreams_hex2rgb")) {
    /**
     * HEX to RGB string conversion
     *
     * Works both 3-6 lengths, with or without hash tags
     *
     * @param $color
     * @return bool|string
     * @uses wpdreams_rgb2hex()
     */
    function wpdreams_hex2rgb($color) {
      if (strlen($color)>7)
          $color = wpdreams_rgb2hex($color);
      if (strlen($color)>7)
          return $color;
      if (strlen($color)<3) return "0, 0, 0";
      if ($color[0] == '#')
          $color = substr($color, 1);
      if (strlen($color) == 6)
          list($r, $g, $b) = array($color[0].$color[1],
                                   $color[2].$color[3],
                                   $color[4].$color[5]);
      elseif (strlen($color) == 3)
          list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
      else
          return false;
      $r = hexdec($r); $g = hexdec($g); $b = hexdec($b); 
      return $r.", ".$g.", ".$b;
  }  
}

if (!function_exists("wpdreams_rgb2hex")) {
    /**
     * RGB to HEX string converter
     *
     * @param $color
     * @return string
     */
    function wpdreams_rgb2hex($color)
    {
        if (strlen($color)>7) {
          preg_match("/.*?\((\d+), (\d+), (\d+).*?/", $color, $c);
          if (is_array($c) && count($c)>3) {
             $color = "#".sprintf("%02X", $c[1]);
             $color .= sprintf("%02X", $c[2]);
             $color .= sprintf("%02X", $c[3]);
          }
        }
        return $color;
    }
}

if (!function_exists("asp_get_image_from_content")) {
    /**
     * Gets an image from the HTML content
     *
     * @param $content
     * @param int $number
     * @param array|string $exclude
     * @return bool|string
     */
    function asp_get_image_from_content($content, $number = 0, $exclude = array()) {
        if ($content == "" || !class_exists('domDocument'))
            return false;

        // The arguments expects 1 as the first image, while it is the 0th
        $number = intval($number) - 1;
        $number = $number < 0 ? 0 : $number;

        if ( !is_array($exclude) ) {
            $exclude = strval($exclude);
            $exclude = explode(',', $exclude);
        }
        foreach ( $exclude as $k => &$v ) {
            $v = trim($v);
            if ( $v == '' ) {
                unset($exclude[$k]);
            }
        }

		$attributes = array('src', 'data-src-fg');
		$im = false;

        $dom = new domDocument();
        if ( function_exists('mb_convert_encoding') )
            @$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        else
            @$dom->loadHTML($content);
        $dom->preserveWhiteSpace = false;
        @$images = $dom->getElementsByTagName('img');
        if ($images->length > 0) {
			$get = $images->length > $number ? $number : 0;
			for ($i=$get;$i<$images->length;$i++) {
                foreach ( $attributes as $att ) {
                    $im = $images->item($i)->getAttribute($att);
                    if ( !empty($im) )
                        break;
                }
				foreach ( $exclude as $ex ) {
					if ( strpos($im, $ex) !== false ) {
					    $im = '';
						continue 2;
					}
				}
				break;
			}
            return $im;
        } else {
            return false;
        }
    }
}

if ( !function_exists('wpdreams_get_image_from_content') ) {
    /* Backwards compatibility */
    function wpdreams_get_image_from_content($content, $number) {
        return asp_get_image_from_content($content, $number);
    }
}

/* Extra Functions */
if (!function_exists("wd_isEmpty")) {
    /**
     * @param $v
     * @return bool
     */
    function wd_isEmpty($v) {
  	if (trim($v) != "")
  		return false;
  	else
  		return true;
  }
}

if (!function_exists("wpdreams_on_backend_post_editor")) {
    /**
     * Checks if current page is the post editor
     *
     * @return bool
     */
    function wpdreams_on_backend_post_editor() {
        $current_url = wd_current_page_url();
        return (strpos($current_url, 'post-new.php')!==false ||
            strpos($current_url, 'post.php')!==false);
    }
}

if (!function_exists("wpdreams_get_blog_list")) {
    /**
     * Gets all the blogs from the multisite network
     *
     * @param int $start
     * @param int $num
     * @param bool $ids_only
     * @return array
     */
    function wpdreams_get_blog_list( $start = 0, $num = 10, $ids_only = false ) {
  
  	global $wpdb;
    if (!isset($wpdb->blogs)) return array();
  	$blogs = $wpdb->get_results( $wpdb->prepare("SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = %d ORDER BY registered DESC", $wpdb->siteid), ARRAY_A );

	if ($ids_only) {
		foreach ( (array) $blogs as $details ) {
			$blog_list[ $details['blog_id'] ] = $details['blog_id'];
			//$blog_list[ $details['blog_id'] ]['postcount'] = $wpdb->get_var( "SELECT COUNT(ID) FROM " . $wpdb->get_blog_prefix( $details['blog_id'] ). "posts WHERE post_status='publish' AND post_type='post'" );
		}
	} else {
		foreach ( (array) $blogs as $details ) {
			$blog_list[ $details['blog_id'] ] = $details;
			//$blog_list[ $details['blog_id'] ]['postcount'] = $wpdb->get_var( "SELECT COUNT(ID) FROM " . $wpdb->get_blog_prefix( $details['blog_id'] ). "posts WHERE post_status='publish' AND post_type='post'" );
		}
	}

  	unset( $blogs );
  	$blogs = $blog_list;
  
  	if ( false == is_array( $blogs ) )
  		return array();
  
  	if ( $num == 'all' )
  		return array_slice( $blogs, $start, count( $blogs ) );
  	else
  		return array_slice( $blogs, $start, $num );
  }
}

if (!function_exists("wpd_mem_convert")) {
    /**
     * Converts number to memory value with units
     *
     * @param $size
     * @return string
     */
    function wpd_mem_convert($size) {
        if ( $size <= 0 ) return "0B";
        $unit=array('B','KB','MB','GB','TB','PB');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
}

if ( !function_exists('asp_get_search_query') ) {
    function asp_get_search_query() {
        return isset($_GET['asp_ls']) ? $_GET['asp_ls'] : get_search_query();
    }
}


//----------------------------------------------------------------------------------------------------------------------
// 2. FILE SYSTEM SPECIFIC WRAPPERS
//----------------------------------------------------------------------------------------------------------------------
if ( !function_exists('wpd_fs_init') ) {
    function wpd_fs_init($include = false, $check_method = '') {
        global $wp_filesystem;
        if ( $include && empty($wp_filesystem) ) {
            require_once (ABSPATH . '/wp-admin/includes/file.php');
        }
        if ( function_exists('WP_Filesystem') && WP_Filesystem() === true && is_object($wp_filesystem) ) {
            if ( $check_method != '' ) {
                return method_exists($wp_filesystem, $check_method);
            }
            return true;
        }
        // Did not init
        return false;
    }
}
if ( !function_exists('wpd_fs_wrapper') ) {
    function wpd_fs_wrapper($function) {
        global $wp_filesystem;
        $args = func_get_args();
        array_shift($args);
        return wpd_fs_init(false, $function) ?
            call_user_func_array(array($wp_filesystem, $function), $args) :
            call_user_func_array($function, $args);
    }
}
if (!function_exists('wpd_mtime')) {
    /**
     * Checks the last modification time on file
     * NOTE: Use this function in own plugin pages/actions/ajax only! Loading the WP_Filesystem(); can cause compatibility issues
     *
     * @param $file
     * @return int|bool
     */
    function wpd_mtime($file) {
        global $wp_filesystem;
        // Did it fail?
        if ( wpd_fs_init(false, 'mtime') ) {
            return $wp_filesystem->mtime($file);
        }
        return filemtime( $file );
    }
}

if (!function_exists('wpd_is_file')) {
    /**
     * Checks if the file exists and if it is a regular file
     * NOTE: Use this function in own plugin pages/actions/ajax only! Loading the WP_Filesystem(); can cause compatibility issues
     *
     * @param $file
     * @return bool
     */
    function wpd_is_file($file) {
        return wpd_fs_wrapper('is_file', $file);
    }
}

if (!function_exists('wpd_is_dir')) {
    /**
     * Checks if the directory exists and if it is indeed a directory
     * NOTE: Use this function in own plugin pages/actions/ajax only! Loading the WP_Filesystem(); can cause compatibility issues
     *
     * @param $file
     * @return bool
     */
    function wpd_is_dir($file) {
        return wpd_fs_wrapper('is_dir', $file);
    }
}

if ( !function_exists("wpd_get_file") ) {
    /**
     * Gets file contents with the use of WordPress file API with a fallback to file_get_contents()
     * NOTE: Use this function in own plugin pages/actions/ajax only! Loading the WP_Filesystem(); can cause compatibility issues
     *
     * @param $filename
     * @param $exist_check
     * @return bool
     */
    function wpd_get_file($filename, $exist_check = false) {
        global $wp_filesystem;
        // Replace double
        $filename = str_replace(array('\\\\', '//'), array('\\', '/'), $filename);

        if ( $exist_check && !file_exists($filename) )
            return '';

        if ( wpd_fs_init(false, 'get_contents') ) {
            // All went well, return
            return $wp_filesystem->get_contents( $filename );
        }

        return @file_get_contents($filename);
    }
}

if (!function_exists("wpd_put_file")) {
    /**
     * Writes to a file with the use of WordPress file API with a fallback to file_put_contents()
     * NOTE: Use this function in own plugin pages/actions/ajax only! Loading the WP_Filesystem(); can cause compatibility issues
     *
     * @param $filename
     * @param $contents
     * @return bool
     */
    function wpd_put_file($filename, $contents) {
        global $wp_filesystem;
        // Replace double
        $filename = str_replace(array('\\\\', '//'), array('\\', '/'), $filename);

        // Did it fail?
        if ( !wpd_fs_init(false, 'put_contents') ) {
            /* any problems and we exit */
            return @file_put_contents($filename, $contents) === false ? false : true;
        }

        // It worked, use it!
        if ( defined('FS_CHMOD_FILE') ) {
            if ( !$wp_filesystem->put_contents($filename, $contents, FS_CHMOD_FILE) ) {
                return @file_put_contents($filename, $contents) === false ? false : true;
            }
        } else {
            if ( !$wp_filesystem->put_contents($filename, $contents) ) {
                return @file_put_contents($filename, $contents) === false ? false : true;
            }
        }

        // All went well, return
        return true;
    }
}

if (!function_exists("wpd_rmdir")) {
    /**
     * Deletes a directory
     * NOTE: Use this function in own plugin pages/actions only! Loading the WP_Filesystem(); can cause compatibility issues
     *
     * @param $dir
     * @param $recursive
     * @param $force
     * @return bool
     */
    function wpd_rmdir($dir, $recursive = false, $force = false) {
        global $wp_filesystem;

        if ( $force ) {
            wpd_rec_rmdir($dir);
            return true;
        }

        // Did it fail?
        if ( !wpd_fs_init(false, 'rmdir') ) {
            // $recursive is not supported in the default php rmdir function
            return rmdir( $dir );
        }

        $wp_filesystem->rmdir($dir, $recursive);
    }
}

if ( !function_exists('wpd_rec_rmdir') ) {
    function wpd_rec_rmdir($dirPath) {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                wpd_rec_rmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
}

if (!function_exists("asp_put_file")) {
    /**
     * Writes to a the pre-defined upload path
     * NOTE: Use this function in own plugin pages/actions only! Loading the WP_Filesystem(); can cause compatibility issues
     *
     * @param $filename
     * @param $contents
     * @return bool
     * @uses wd_asp()->upload_path
     * @uses wpd_put_file()
     */
    function asp_put_file($filename, $contents) {
        return wpd_put_file( wd_asp()->upload_path . $filename , $contents);
    }
}

if (!function_exists("wpd_del_file")) {
    /**
     * Deletes the file in the path
     * NOTE: Use this function in own plugin pages/actions only! Loading the WP_Filesystem(); can cause compatibility issues
     *
     * @param $filename
     * @return bool
     */
    function wpd_del_file($filename) {
        global $wp_filesystem;

        // Did it fail?
        if ( !wpd_fs_init(false, 'delete') ) {
            /* any problems and we exit */
            return @unlink( $filename );
        }

        return $wp_filesystem->delete($filename);
    }
}

if (!function_exists("asp_del_file")) {
    /**
     * Deletes the file in pre-defined upload path
     *
     * @param $filename
     * @return bool
     * @uses wd_asp()->upload_path
     * @uses wpd_del_file()
     */
    function asp_del_file($filename) {
        return wpd_del_file( wd_asp()->upload_path . $filename );
    }
}


//----------------------------------------------------------------------------------------------------------------------
// 3. TAXONOMY AND TERM SPECIFIC
//----------------------------------------------------------------------------------------------------------------------

if (!function_exists("wd_sort_terms_hierarchicaly")) {
    /**
     * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
     * placed under a 'children' member of their parent term. Handles missing parent categories as well.
     *
     * @param Array   $cats     taxonomy term objects to sort, use get_terms(..)
     * @param Array   $into     result array to put them in
     * @param integer $parentId the current parent ID to put them in
     * @param integer $depth the current recursion depth
     */
    function wd_sort_terms_hierarchicaly(Array &$cats, Array &$into, $parentId = 0, $depth = 0) {
        foreach ($cats as $i => $cat) {
            if ($cat->parent == $parentId) {
                $into[$cat->term_id] = $cat;
                unset($cats[$i]);
            }
        }

        foreach ($into as $k => $topCat) {
            $into[$k]->children = array();
            wd_sort_terms_hierarchicaly($cats, $into[$k]->children, $topCat->term_id, $depth + 1);
        }

        // Use a copy to go through, as the original is modified
        $copy_cats = $cats;

        // Try the remaining - the first parent might be excluded
        if (is_array($copy_cats) && $depth == 0) {
            foreach ($copy_cats as $k => $topCat) {
                // This item might not exist in the original array, check it first
                if ( isset($cats[$k]) ) {
                    $cats[$k]->children = array();
                    wd_sort_terms_hierarchicaly($cats, $cats[$k]->children, $topCat->term_id, $depth + 1);
                }
            }
        }

        // Still any remaining for some satanic reason? Put the rest to the end...
        if (is_array($cats) && $depth == 0)
            foreach ( $cats as $i => $cat ) {
                $into[$cat->term_id] = $cat;
                unset( $cats[$i] );
            }

    }
}


if (!function_exists("wd_flatten_hierarchical_terms")) {
	/**
	 * Flattens a hierarchical array of terms into a flat array, marking levels.
	 * Keeps ordering, sets a $cat->level attribute
	 *
	 * @param Array   $cats     Taxonomy term objects to sort, use get_terms(..)
	 * @param Array   $into     Target array
	 * @param int     $level    The current recursion depth
	 */
	function wd_flatten_hierarchical_terms(Array &$cats, Array &$into, $level = 0) {
		foreach ($cats as $i => $cat) {
			$cat->level = $level;
			$into[] = $cat;
			if ( isset($cat->children) && count($cat->children) > 0 ) {
				wd_flatten_hierarchical_terms( $cat->children, $into, $level + 1 );
			}
		}

		// We don't need the children structure
		foreach ($into as $cat) {
			unset($cat->children);
		}
	}
}


//----------------------------------------------------------------------------------------------------------------------
// 4. BACK-END SPECIFIC
//----------------------------------------------------------------------------------------------------------------------

if (!function_exists("w_isset_def")) {
    function w_isset_def(&$v, $d)
    {
        if (isset($v)) return $v;
        return $d;
    }
}

if (!function_exists("w_get_custom_fields")) {
    function w_get_custom_fields( $limit = 1000 )
    {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM " . $wpdb->postmeta . " GROUP BY meta_key LIMIT %d", $limit),
            ARRAY_A
        );
    }
}

if (!function_exists("wd_opt_or_def")) {
    /**
     * Checks if the option is set in the options array, returns default if not.
     *
     * @param $options
     * @param $defaults
     * @param $key
     * @return int
     */
    function wd_opt_or_def($options, $defaults, $key) {
        if ( isset($options[$key]) )
            return $options[$key];
        if ( isset($defaults[$key]) )
            return $defaults[$key];
        return 0;
    }
}

if (!function_exists("wpdreams_parse_params")) {
    /**
     * This method is intended to use on params BEFORE written into the DB
     *
     * @param $params
     * @return mixed
     */
    function wpdreams_parse_params($params) {
        foreach ($params as $k=>$v) {
            $_tmp = explode('classname-', $k);
            if ($_tmp!=null && count($_tmp)>1) {
                ob_start();
                $c = new $v('0', '0', $params[$_tmp[1]]);
                $out = ob_get_clean();
                $params['selected-'.$_tmp[1]] = $c->getSelected();
            }
            $_tmp = null;
            $_tmp = explode('wpdfont-', $k);
            if ($_tmp!=null && count($_tmp)>1) {
                ob_start();
                $c = new $v('0', '0', $params[$_tmp[1]]);
                $out = ob_get_clean();
                $params['import-'.$_tmp[1]] = $c->getImport();

            }
        }
        return $params;
    }
}

if (!function_exists("wpdreams_admin_hex2rgb")) {
    function wpdreams_admin_hex2rgb($color)
    {
        if (strlen($color)>7) return $color;
        if (strlen($color)<3) return "rgba(0, 0, 0, 1)";
        if ($color[0] == '#')
            $color = substr($color, 1);
        if (strlen($color) == 6)
            list($r, $g, $b) = array($color[0].$color[1],
                $color[2].$color[3],
                $color[4].$color[5]);
        elseif (strlen($color) == 3)
            list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
        else
            return false;
        $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
        return "rgba(".$r.", ".$g.", ".$b.", 1)";
    }
}

if (!function_exists("wpdreams_admin_rgb2hex")) {
    function wpdreams_admin_rgb2hex($color)
    {
        if ($color[0] == '#') return $color;
        if (strlen($color) == 6) return '#'.$color;
        preg_match("/.*?\((.*?),[\s]*(.*?),[\s]*(.*?)[,\)]/", $color, $matches);

        return "#" . dechex($matches[1]) . dechex($matches[2]) . dechex($matches[3]);

    }
}

if (!function_exists("wpdreams_four_to_string")) {
    function wpdreams_four_to_string($data) {
        // 1.Top 2.Bottom 3.Right 4.Left
        preg_match("/\|\|(.*?)\|\|(.*?)\|\|(.*?)\|\|(.*?)\|\|/", $data, $matches);
        // 1.Top 3.Right 2.Bottom 4.Left
        return $matches[1]." ".$matches[3]." ".$matches[2]." ".$matches[4];
    }
}

if (!function_exists("wpdreams_four_to_array")) {
    function wpdreams_four_to_array($data) {
        // 1.Top 2.Bottom 3.Right 4.Left
        preg_match("/\|\|(.*?)\|\|(.*?)\|\|(.*?)\|\|(.*?)\|\|/", $data, $matches);
        // 1.Top 3.Right 2.Bottom 4.Left
        return array(
            "top" => $matches[1],
            "right" => $matches[3],
            "bottom" => $matches[2],
            "left" => $matches[4]
        );
    }
}

if (!function_exists("wpdreams_box_shadow_css")) {
    function wpdreams_box_shadow_css($css) {
        $css = str_replace("\n", "", $css);
        preg_match("/box-shadow:(.*?)px (.*?)px (.*?)px (.*?)px (.*?);/", $css, $matches);
        $ci = $matches[5];
        $hlength = $matches[1];
        $vlength = $matches[2];
        $blurradius = $matches[3];
        $spread = $matches[4];
        $moz_blur = ($blurradius>2)?$blurradius - 2:0;
        if ($hlength==0 && $vlength==0 && $blurradius==0 && $spread==0) {
            echo "box-shadow: none;";
        } else {
            echo "box-shadow:".$hlength."px ".$vlength."px ".$moz_blur."px ".$spread."px ".$ci.";";
            echo "-webkit-box-shadow:".$hlength."px ".$vlength."px ".$blurradius."px ".$spread."px ".$ci.";";
            echo "-ms-box-shadow:".$hlength."px ".$vlength."px ".$blurradius."px ".$spread."px ".$ci.";";
        }
    }
}

if (!function_exists("wpdreams_gradient_css")) {
    function wpdreams_gradient_css($data, $print=true) {

        $data = str_replace("\n", "", $data);
        if ( $data == "" )
            return "";

        preg_match("/(.*?)-(.*?)-(.*?)-(.*)/", $data, $matches);

        if (!isset($matches[1]) || !isset($matches[2]) || !isset($matches[3])) {
            // Probably only 1 color..
            if ($print) echo "background: ".$data.";";
            return "background: ".$data.";";
        }

        $type = $matches[1];
        $deg = $matches[2];
        $color1 = wpdreams_admin_hex2rgb($matches[3]);
        $color2 = wpdreams_admin_hex2rgb($matches[4]);
        $color1_hex = wpdreams_admin_rgb2hex($color1);
        $color2_hex = wpdreams_admin_rgb2hex($color2);

        // Check for full transparency
        preg_match("/rgba\(.*?,.*?,.*?,[\s]*(.*?)\)/", $color1, $opacity1);
        preg_match("/rgba\(.*?,.*?,.*?,[\s]*(.*?)\)/", $color2, $opacity2);
        if (isset($opacity1[1]) && $opacity1[1] == "0" && isset($opacity2[1]) && $opacity2[1] == "0") {
            if ($print) echo "background: transparent;";
            return "background: transparent;";
        }

        ob_start();

        if ($type!='0' || $type!=0) {
            ?>
            background-image: -webkit-linear-gradient(<?php echo $deg; ?>deg, <?php echo $color1; ?>, <?php echo $color2; ?>);
            background-image: -moz-linear-gradient(<?php echo $deg; ?>deg, <?php echo $color1; ?>, <?php echo $color2; ?>);
            background-image: -o-linear-gradient(<?php echo $deg; ?>deg, <?php echo $color1; ?>, <?php echo $color2; ?>);
            background-image: -ms-linear-gradient(<?php echo $deg; ?>deg, <?php echo $color1; ?> 0%, <?php echo $color2; ?> 100%);
            background-image: linear-gradient(<?php echo $deg; ?>deg, <?php echo $color1; ?>, <?php echo $color2; ?>);
        <?php
        } else {
            //radial
            ?>
            background-image: -moz-radial-gradient(center, ellipse cover,  <?php echo $color1; ?>, <?php echo $color2; ?>);
            background-image: -webkit-gradient(radial, center center, 0px, center center, 100%, <?php echo $color1; ?>, <?php echo $color2; ?>);
            background-image: -webkit-radial-gradient(center, ellipse cover,  <?php echo $color1; ?>, <?php echo $color2; ?>);
            background-image: -o-radial-gradient(center, ellipse cover,  <?php echo $color1; ?>, <?php echo $color2; ?>);
            background-image: -ms-radial-gradient(center, ellipse cover,  <?php echo $color1; ?>, <?php echo $color2; ?>);
            background-image: radial-gradient(ellipse at center,  <?php echo $color1; ?>, <?php echo $color2; ?>);
        <?php
        }
        $out = ob_get_clean();
        if ($print) echo $out;
        return $out;
    }
}

if (!function_exists("wpdreams_border_width")) {
    function wpdreams_border_width($css) {
        $css = str_replace("\n", "", $css);
        preg_match("/border:(.*?)px (.*?) (.*?);/", $css, $matches);

        return $matches[1];
    }
}

if (!function_exists("wpdreams_width_from_px")) {
    function wpdreams_width_from_px($css) {
        $css = str_replace("\n", "", $css);
        preg_match("/(.*?)px/", $css, $matches);

        return $matches[1];
    }
}


//----------------------------------------------------------------------------------------------------------------------
// 5. EXPORT IMPORT
//----------------------------------------------------------------------------------------------------------------------

/**
 * Generates exported search instances in serialized base 64 encded format
 *
 * @return array
 */
function asp_get_all_exported_instances() {
    global $wpdb;

    $return = array();

    $search_instances = $wpdb->get_results("SELECT * FROM " . wd_asp()->tables->main, ARRAY_A);
    foreach ($search_instances as $instance)
        $return[$instance['id']] = base64_encode(serialize($instance));
    return $return;
}

/**
 * Get a single exported search instance by ID
 *
 * @param int $id
 * @return bool
 */
function asp_get_exported_instance($id=0) {
    $instances = asp_get_all_exported_instances();
    return isset($instances[$id])?$instances[$id]:false;
}

/**
 * Imports the search instance
 *
 * @param $data
 * @return false on failure, affected rows on success
 */
function asp_import_instances($data) {
    global $wpdb;

    $s_data = json_decode(stripcslashes($data));

    $asp_def = wd_asp()->options;

    $count = 0;

    if (is_array($s_data)) {
        foreach ($s_data as $dec_instance) {
            $_instance = unserialize(base64_decode($dec_instance));
            if (is_array($_instance)) {

                // Merge with the defaults, in case of updated imports..
                $data = json_decode($_instance['data'], true);
                if ( $data === null ) continue;

                $data = array_merge($asp_def['asp_defaults'], $data);

                $wpdb->insert(
                    wd_asp()->tables->main,
                    array(
                        'name' => $_instance['name'].' Imported',
                        'data' => json_encode($data)
                    ),
                    array('%s', '%s')
                );

                $count++;
            } else {
                return false;
            }
        }
    } else {
        return false;
    }

    return $count;
}

function asp_import_settings($id, $data) {
    global $wpdb;

    //$data = stripcslashes($data);
    $data = unserialize(base64_decode($data));

    $asp_def = wd_asp()->options;
    // Merge with the defaults, in case of updated imports..
    $data = json_decode($data['data'], true);
    if ( $data === null ) return;

    $data = array_merge($asp_def['asp_defaults'], $data);

    if (is_array($data)) {
        return $wpdb->update(
            wd_asp()->tables->main,
            array(
                'data' => json_encode($data)
            ),
            array( 'id' => $id ),
            array(
                '%s'
            ),
            array( '%d' )
        );
    } else {
        return false;
    }
}


//----------------------------------------------------------------------------------------------------------------------
// 6. NON-AJAX RESULTS
//----------------------------------------------------------------------------------------------------------------------

if ( !class_exists("ASP_Post") )  {
    /**
     * Class ASP_Post
     *
     * A default class to instantiate to generate post like results.
     */
    class ASP_Post {

        public $ID = 0;                     // Don't use negative value, because WPML will break into pieces
        public $post_title = "";
        public $post_author = "";
        public $post_name = "";
        public $post_type = "post";         // Everything unknown is going to be a post
        public $post_date = '0000-00-00 00:00:00';             // Format: 0000-00-00 00:00:00
        public $post_date_gmt = '0000-00-00 00:00:00';         // Format: 0000-00-00 00:00:00
        public $post_content = '';          // The full content of the post
        public $post_content_filtered = '';
        public $post_excerpt = "";          // User-defined post excerpt
        public $post_status = "publish";    // See get_post_status for values
        public $comment_status = "closed";  // Returns: { open, closed }
        public $ping_status = "closed";     // Returns: { open, closed }
        public $post_password = "";         // Returns empty string if no password
        public $post_parent = 0;            // Parent Post ID (default 0)
        public $post_mime_type = '';
        public $to_ping = '';
        public $pinged = '';
        public $post_modified = "";         // Format: 0000-00-00 00:00:00
        public $post_modified_gmt = "";     // Format: 0000-00-00 00:00:00
        public $comment_count = 0;          // Number of comments on post (numeric string)
        public $menu_order = 0;             // Order value as set through page-attribute when enabled (numeric string. Defaults to 0)
        public $guid = "";
        public $asp_guid;
        public $asp_id;
        public $asp_data;                   // All the original results data
        public $blogid;

        public function __construct() {}
    }
}

if ( !function_exists("asp_results_to_wp_obj") ) {
    /**
     * Converts ajax results from Ajax Search Pro to post like objects to be displayable
     * on the regular search results page.
     *
     * @param $results
     * @param int $from
     * @param string $count
     * @return array
     */
    function asp_results_to_wp_obj($results, $from = 0, $count = "all") {
        if (empty($results))
            return array();

        if ($count == "all")
            $results_slice = array_slice($results, $from);
        else
            $results_slice = array_slice($results, $from, $count);

        if (empty($results_slice))
            return array();

        $wp_res_arr = array();

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');

        $current_date = date($date_format . " " . $time_format, time());

        foreach ($results_slice as $r) {

            $switched_blog = false;
            if (is_multisite()) {
                if ( get_current_blog_id() != $r->blogid ) {
                    switch_to_blog($r->blogid);
                    $switched_blog = true;
                }
            }

            if ( !isset($r->content_type) ) continue;

            switch ($r->content_type) {
                case "attachment":
                case "pagepost":
                    $res = get_post($r->id);
                    $res->asp_guid = get_permalink($r->id);
					$r->link = $res->asp_guid;
					$r->url = $res->asp_guid;
                    $res->asp_id = $r->id;  // Save the ID in case needed for some reason
                    $res->blogid = $r->blogid;
                    /**
                     * On multisite the page and other post type links are filtered in such a way
                     * that the post type object is reset with get_post(), deleting the ->asp_guid
                     * attribute. Therefore the post type post must be enforced.
                     */
                    if ( is_multisite() && $res->post_type != 'post' ) {
                        // Is this a WooCommerce search?
                        if (
                        !(
                            in_array($res->post_type, array('product', 'product_variation')) &&
                            isset($_GET['post_type']) &&
                            $_GET['post_type'] == 'product'
                        )
                        ) {
                            $res->post_type = 'post'; // Enforce
                            if ( $switched_blog )
                                $res->ID = -10;
                        }
                    }
                    break;
                case "blog":
                    $res = new ASP_Post();
                    $res->post_title = $r->title;
                    $res->asp_guid = $r->link;
                    $res->post_content = $r->content;
                    $res->post_excerpt = $r->content;
                    $res->post_date = $current_date;
                    $res->asp_id = $r->id;
                    $res->ID = -10;
                    break;
                case "bp_group":
                    $res = new ASP_Post();
                    $res->post_title = $r->title;
                    $res->asp_guid = $r->link;
                    $res->post_content = $r->content;
                    $res->post_excerpt = $r->content;
                    $res->post_date = $r->date;
                    $res->asp_id = $r->id;
                    $res->ID = -10;
                    break;
                case "bp_activity":
                    $res = new ASP_Post();
                    $res->post_title = $r->title;
                    $res->asp_guid = $r->link;
                    $res->post_content = $r->content;
                    $res->post_excerpt = $r->content;
                    $res->post_date = $r->date;
                    $res->asp_id = $r->id;
                    $res->ID = -10;
                    break;
                case "comment":
                    $res = get_post($r->post_id);
                    if (isset($res->post_title)) {
                        $res->post_title = $r->title;
                        $res->asp_guid = $r->link;
                        $res->asp_id = $r->id;
                        $res->post_content = $r->content;
                        $res->post_excerpt = $r->content;
                    }
                    break;
                case "term":
                    $res = new ASP_Post();
                    $res->post_title = $r->title;
                    $res->asp_guid = $r->link;
                    $res->guid = $r->link;
                    $res->post_date = $current_date;
                    $res->asp_id = $r->id;
                    $res->ID = -10;
                    break;
                case "user":
                    $res = new ASP_Post();
                    $res->post_title = $r->title;
                    $res->asp_guid = $r->link;
                    $res->guid = $r->link;
                    $res->post_date = $current_date;
                    $res->asp_id = $r->id;
                    $res->ID = -10;
                    break;
                case "peepso_group":
                    if ( class_exists('PeepSoGroup') ) {
                        $pg = new PeepSoGroup($r->id);
                        $res = get_post($r->id);
                        $res->asp_guid = $pg->get_url();
                        $res->asp_id = $r->id;  // Save the ID in case needed for some reason
                    }
                    break;
                case "peepso_activity":
                    $res = get_post($r->id);
                    $res->asp_guid = get_permalink($r->id);
                    $res->asp_id = $r->id;  // Save the ID in case needed for some reason
                    break;
            }

            if (!empty($res)) {
                $res->asp_data = $r;
                $res = apply_filters("asp_regular_search_result", $res, $r);
                $wp_res_arr[] = $res;
            }

            if (is_multisite())
                restore_current_blog();
        }

        return $wp_res_arr;
    }
}

if ( !function_exists("get_asp_result_field") ) {
    function get_asp_result_field($field = 'all') {
        global $post;

        if ( !is_string($field) )
            return false;

        if ($field === 'all') {
            if (isset($post, $post->asp_data)) {
                return $post->asp_data;
            } else {
                return false;
            }
        } else {
            if (isset($post, $post->asp_data) && property_exists($post->asp_data, $field)) {
                return $post->asp_data->{$field};
            } else {
                return false;
            }
        }
    }
}
if ( !function_exists("the_asp_result_field") ) {
    function the_asp_result_field( $field = 'title', $echo = true ) {
        if ( $echo ) {
            if ( !is_string($field) )
                return;
            $print = $field == 'all' ? '' : get_asp_result_field($field);
            if ( $print !== false )
                echo $print;
        } else {
            return get_asp_result_field($field);
        }
    }
}



//----------------------------------------------------------------------------------------------------------------------
// 7. FRONT-END
//----------------------------------------------------------------------------------------------------------------------
if ( !function_exists("asp_acf_get_field_choices") ) {
    function asp_acf_get_field_choices($field_name, $multi = false) {
        $results = array();
        if ( trim($field_name) == '' )
            return $results;

        if ( isset($GLOBALS['acf_register_field_group']) ) {
            foreach ($GLOBALS['acf_register_field_group'] as $acf) {
                foreach ($acf['fields'] as $field) {
                    if (substr($field['key'], 0, 6) == 'field_') {
                        if ($field['name'] == $field_name && isset($field['choices'])) {
                            if (!$multi) return $field['choices'];
                            else $results [] = $field;
                        }
                    }

                }
            }
        }

        /*
         * Method 2: This should be tried first, as Method 3 seems to miss some of the fields (reported via support)
         * Reference: https://wp-dreams.com/forums/topic/get_value-filter-not-updating
         */
        if ( empty($results) ) {
            $fkey = asp_acf_get_field_key($field_name);
            if ( !empty($fkey) ) {
                $field = get_field_object($fkey);
                if ( !empty($field['choices']) ) {
                    return $field['choices'];
                }
            }
        }

        // Method 3: Let us try going through the ACF registered post types
        if ( empty($results) ) {
            $acf_posts = get_posts( array('post_type' => 'acf', 'posts_per_page' => -1, 'post_status' => 'all') );
            if ( !is_wp_error($acf_posts) ) {
                foreach ($acf_posts as $acf) {
                    $meta = get_post_meta($acf->ID);
                    foreach ($meta as $key => $field) {
                        if (substr($key, 0, 6) == 'field_') {
                            $field = unserialize($field[0]);
                            if ($field['name'] == $field_name && isset($field['choices'])) {
                                if (!$multi) return $field['choices'];
                                else $results [] = $field;
                            }
                        }
                    }
                }
            }
        }

        return $results;
    }
}
if ( !function_exists("asp_acf_get_field_key") ) {
	function asp_acf_get_field_key( $field_name ) {
		global $wpdb;
		$acf_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_parent,post_name FROM $wpdb->posts WHERE post_excerpt=%s AND post_type=%s" , $field_name , 'acf-field' ) );
		// get all fields with that name.
		switch ( count( $acf_fields ) ) {
			case 0: // no such field
				return false;
			case 1: // just one result.
				return $acf_fields[0]->post_name;
            default:
                $last = end($acf_fields);
                return isset($last->post_name) ? $last->post_name : false;

		}
		return false;
		// ASP note: The code does not ever get below, as the Post ID is never known

        // Rest of the code for possible future use
        $post_id = 0; // // ASP note: This was passed as the function argument
		// result is ambiguous
		// get IDs of all field groups for this post
		$field_groups_ids = array();
		$field_groups = acf_get_field_groups( array(
			'post_id' => $post_id,
		) );
		foreach ( $field_groups as $field_group )
			$field_groups_ids[] = $field_group['ID'];

		// Check if field is part of one of the field groups
		// Return the first one.
		foreach ( $acf_fields as $acf_field ) {
			if ( in_array($acf_field->post_parent,$field_groups_ids) )
				return $acf_field->post_name;
		}
		return false;
	}
}

if ( !function_exists('asp_parse_filters') ) {
    function asp_parse_filters( $id, $o, $clear = false, $to_display = true ) {
        if ( !isset(wd_asp()->front_filters) || is_object(wd_asp()->front_filters) )
            wd_asp()->front_filters = WD_ASP_FrontFilters::getInstance();
        wd_asp()->front_filters->setSearchId($id);

        // Only call this once per intance
        static $called = array();

        if ( $clear ) {
            wd_asp()->front_filters->clear($id);
            if ( !in_array($id, $called) )
                $called[] = $id;
        } else {
            if ( in_array($id, $called) ) return;
            $called[] = $id;
        }

        do_action('asp_pre_parse_filters', $id, $o);

        asp_parse_tax_term_filters($o);
        asp_parse_content_type_filters($o);
        asp_parse_custom_field_filters($o, $to_display);
        asp_parse_post_type_filters($o);
        asp_parse_date_filters($o);
        asp_parse_generic_filters($o);
        asp_parse_post_tag_filters($o);
        asp_parse_button_filters($o);

        do_action('asp_post_parse_filters', $id, $o);
    }
}


if ( !function_exists("asp_parse_custom_field_filters") ) {
    function asp_parse_custom_field_filters($o, $to_display = true) {
        global $wpdb;

        if ( w_isset_def($o['custom_field_items'], "") == "" ) {
            return array();
        }

        //$filters = array();
        $f = explode('|', $o['custom_field_items']);

        foreach ($f as $k => $v) {
            $m = null;
            //$f[$k] = json_decode(base64_decode($v));
            $bfield = json_decode(base64_decode($v));

            $user_meta = isset($bfield->asp_f_source) && $bfield->asp_f_source == 'usermeta';
            $table_name = $user_meta ? $wpdb->usermeta : $wpdb->postmeta;

            $logic = '';
            if ( !empty($bfield->asp_f_checkboxes_logic) )
                $logic = $bfield->asp_f_checkboxes_logic;
            if ( !empty($bfield->asp_f_dropdown_logic) )
                $logic = $bfield->asp_f_dropdown_logic;
            $date_store_format = '';
            if ( !empty($bfield->asp_f_datepicker_operator) ) {
                $date_store_format = w_isset_def($bfield->asp_f_datepicker_store_format, "acf");
            }
            if ( $bfield->asp_f_type == 'range' ) {
                $bfield->asp_f_operator = 'BETWEEN';
            }

            $data = array(
                "field" => $bfield->asp_f_field,
                "source" => w_isset_def($bfield->asp_f_source, 'postmeta'),
                "required" => w_isset_def($bfield->asp_f_required, 'asp_unchecked') == 'asp_checked',
                "invalid_input_text" => w_isset_def($bfield->asp_f_invalid_input_text, 'This field is required!'),
                "operator" => !empty($bfield->asp_f_datepicker_operator) ? $bfield->asp_f_datepicker_operator : $bfield->asp_f_operator,
                "logic" => $logic,
                "date_store_format" => $date_store_format,
                "is_api" => false
            );

            if ( isset($bfield->asp_f_dropdown_search_text) ) {
                $data["placeholder"] = asp_icl_t('[CF Filter Placeholder] ' . $bfield->asp_f_dropdown_search_text, $bfield->asp_f_dropdown_search_text);
            }

            $label = w_isset_def($bfield->asp_f_show_title, 'asp_checked') == 'asp_checked' ? $bfield->asp_f_title : '';
            if ( $label != '' ) {
                $label = asp_icl_t('[CF Filter Label] ' . $label, $label);
            }

            $filter = wd_asp()->front_filters->create(
                'custom_field',
                $label,
                $bfield->asp_f_type,
                $data
            );

            $unique_field_name = $filter->getUniqueFieldName();

            if ( !$to_display ) {
                wd_asp()->front_filters->add($filter);
                continue;
            }

            //$f[$k]->asp_f_title = asp_icl_t('[CF Filter Label] ' . $bfield->asp_f_title, $bfield->asp_f_title);

            if ( isset($bfield->asp_f_text_value) ) {
                $add = array();
                $default = $bfield->asp_f_text_value;
                $label = str_replace('**', '', $bfield->asp_f_title);
                $label = asp_icl_t('[CF Filter] ' . $label, $label);

                if ( isset($o['_fo'], $o['_fo']['aspf'][$unique_field_name]) ) {
                    $add['value'] = $o['_fo']['aspf'][$unique_field_name];
                } else {
                    $add['value'] = $default;
                }

                $add = array_merge($add, array(
                    "label" => $label,
                    "default" => $default
                ));
                $filter->add($add);
            }

            if ( isset($bfield->asp_f_hidden_value) ) {
                $add = array();
                $default = $bfield->asp_f_hidden_value;

                if ( isset($o['_fo'], $o['_fo']['aspf'][$unique_field_name]) ) {
                    $add['value'] = $o['_fo']['aspf'][$unique_field_name];
                } else {
                    $add['value'] = $default;
                }

                $add = array_merge($add, array(
                    "label" => '',
                    "default" => $default
                ));
                $filter->add($add);
            }

            if ( isset($bfield->asp_f_radio_value) ) {
                $lines = preg_split("/\\r\\n|\\r|\\n/", $bfield->asp_f_radio_value);
                //$f[$k]->asp_f_radio_value = array();
                foreach ($lines as $kk => $val) {
                    $add_arr = array();
                    if ( trim($val) == '{get_values}') {
                        $_used_acf = false;
                        if ( !$user_meta && function_exists('get_field_object') ) {
                            $acf_field_o = asp_acf_get_field_choices($bfield->asp_f_field);
                            if ( count($acf_field_o) > 0 ) {
                                foreach ( $acf_field_o as $acf_f_o_k => $acf_f_o_v) {
                                    //$f[$k]->asp_f_radio_value[] = array($acf_f_o_k, asp_icl_t('[CF Filter] ' . $acf_f_o_v, $acf_f_o_v));
                                    $add_arr[] = array(
                                        'label' => $acf_f_o_v,
                                        'value' => $acf_f_o_k
                                    );
                                }
                                $_used_acf = true;
                            }
                        }
                        if ( !$_used_acf ) {
                            $cf_query = $wpdb->prepare(
                                "SELECT DISTINCT(meta_value) FROM $table_name WHERE meta_key LIKE '%s' ORDER BY meta_value ASC LIMIT %d",
                                $bfield->asp_f_field, 5000);
                            $cf_results = $wpdb->get_results($cf_query, OBJECT);
                            if (!is_wp_error($cf_results) && count($cf_results) > 0) {
                                foreach ($cf_results as $cf_result) {
                                    //$f[$k]->asp_f_radio_value[] = array($cf_result->meta_value, asp_icl_t('[CF Filter] ' .$cf_result->meta_value, $cf_result->meta_value));
                                    $add_arr[] = array(
                                        'label' => $cf_result->meta_value,
                                        'value' => $cf_result->meta_value
                                    );
                                }
                            }
                        }
                    } else {
                        preg_match('/^(.*?)\|\|(.*)/', $val, $m);
                        //$f[$k]->asp_f_radio_value[] = array($m[1], asp_icl_t('[CF Filter] ' .$m[2], $m[2]));
                        $add_arr[] = array(
                            'label' => $m[2],
                            'value' => $m[1]
                        );
                    }

                    if ( count($add_arr) > 0 ) {
                        foreach ( $add_arr as $add ) {
                            $default = strpos($add['label'], '**') > 0;
                            $label = str_replace('**', '', $add['label']);
                            $label = asp_icl_t('[CF Filter] ' . $label, $label);

                            if (isset($o['_fo']) && isset($o['_fo']['aspf'][$unique_field_name])) {
                                $checked = $o['_fo']['aspf'][$unique_field_name] == $add['value'];
                            } else {
                                $checked = $default;
                            }

                            $add['value'] = apply_filters("asp_cf_radio_values", $add['value'], $bfield);
                            $add = array_merge($add, array(
                                "label" => $label,
                                "default" => $default,
                                "selected" => $checked
                            ));
                            if ( $add['label'] != '' )
                                $filter->add($add);
                        }
                    }
                }
            }
            if ( isset($bfield->asp_f_dropdown_value) ) {
                if ( w_isset_def($bfield->asp_f_dropdown_search, 'asp_unchecked') == 'asp_checked' ) {
                    if ( w_isset_def($bfield->asp_f_dropdown_multi, 'asp_unchecked') == 'asp_checked' ) {
                        $filter->display_mode = 'multisearch';
                        $filter->data['multiple'] = true;
                    } else {
                        $filter->display_mode = 'dropdownsearch';
                    }
                } else {
                    $filter->display_mode = 'dropdown';
                    if ( w_isset_def($bfield->asp_f_dropdown_multi, 'asp_unchecked') == 'asp_checked' ) {
                        $filter->data['multiple'] = true;
                    }
                }

                $lines = preg_split("/\\r\\n|\\r|\\n/", $bfield->asp_f_dropdown_value);
                //$f[$k]->asp_f_dropdown_value = array();
                foreach ($lines as $kk => $val) {
                    $add_arr = array();
                    if ( trim($val) == '{get_values}') {
                        $_used_acf = false;
                        if ( !$user_meta && function_exists('get_field_object') ) {
                            $acf_field_o = asp_acf_get_field_choices($bfield->asp_f_field);
                            if (count($acf_field_o) > 0) {
                                foreach ($acf_field_o as $acf_f_o_k => $acf_f_o_v) {
                                    //$f[$k]->asp_f_dropdown_value[] = array($acf_f_o_k, asp_icl_t('[CF Filter] ' . $acf_f_o_v, $acf_f_o_v));
                                    $add_arr[] = array(
                                        'label' => $acf_f_o_v,
                                        'value' => $acf_f_o_k
                                    );
                                }
                                $_used_acf = true;
                            }
                        }
                        if (!$_used_acf) {
                            $cf_query = $wpdb->prepare(
                                "SELECT DISTINCT(meta_value) FROM $table_name WHERE meta_key LIKE '%s' ORDER BY meta_value ASC LIMIT %d",
                                $bfield->asp_f_field, 5000);
                            $cf_results = $wpdb->get_results($cf_query, OBJECT);
                            if (!is_wp_error($cf_results) && count($cf_results) > 0) {
                                foreach ($cf_results as $cf_result) {
                                    //$f[$k]->asp_f_dropdown_value[] = array($cf_result->meta_value, asp_icl_t('[CF Filter] ' .$cf_result->meta_value, $cf_result->meta_value));
                                    $add_arr[] = array(
                                        'label' => $cf_result->meta_value,
                                        'value' => $cf_result->meta_value
                                    );
                                }
                            }
                        }
                    } else if ( ASP_mb::strpos($val, '||') !== false ) {    // Individual value
                        preg_match('/^(.*?)\|\|(.*)/', $val, $m);
                        if (!isset($m[1], $m[2])) {
                            $add_arr[] = array(
                                'label' => '',
                                'value' => ''
                            );
                        } else {
                            $add_arr[] = array(
                                'label' => $m[2],
                                'value' => $m[1]
                            );
                        }
                    } else {    // Option group
                        $add_arr[] = array(
                            'label' => $val,
                            'value' => $val,
                            'option_group' => true
                        );
                    }

                    foreach ($add_arr as $add) {
                        $default = strpos($add['label'], '**') > 0;
                        $label = str_replace('**', '', $add['label']);
                        if ( isset($add['option_group']) ) {
                            $label = asp_icl_t('[CF Filter Option Group] ' . $label, $label);
                        } else {
                            $label = asp_icl_t('[CF Filter] ' . $label, $label);
                        }

                        // Special case because of the multi-select
                        if ( isset($o['_fo']) && isset($o['_fo']['aspf'][$unique_field_name]) ) {
                            $o['_fo']['aspf'][$unique_field_name] = is_array($o['_fo']['aspf'][$unique_field_name]) ?
                                $o['_fo']['aspf'][$unique_field_name] : array($o['_fo']['aspf'][$unique_field_name]);
                            $checked = in_array($add['value'], $o['_fo']['aspf'][$unique_field_name]);
                        } else {
                            $checked = $default;
                        }

                        // Backwards filter compatibility
                        $vals = apply_filters("asp_cf_dropdown_values", array($add['value'], $label), $bfield);

                        $add = array_merge($add, array(
                            "label" => $vals[1],
                            "value" => $vals[0],
                            "default" => $default,
                            "selected" => $checked
                        ));
                        if ( $add['label'] != '' )
                            $filter->add($add);
                    }
                }
            }
            if ( isset($bfield->asp_f_checkboxes_value) ) {
                $lines = preg_split("/\\r\\n|\\r|\\n/", $bfield->asp_f_checkboxes_value);
                //$f[$k]->asp_f_checkboxes_value = array();
                foreach ($lines as $kk => $val) {
                    $add_arr = array();
                    if ( strpos(trim($val), '{get_values') === 0 ) {
                        $_used_acf = false;
                        if ( !$user_meta && function_exists('get_field_object') ) {
                            $acf_field_o = asp_acf_get_field_choices($bfield->asp_f_field);
                            if (count($acf_field_o) > 0) {
                                foreach ($acf_field_o as $acf_f_o_k => $acf_f_o_v) {
                                    $acf_f_o_v = asp_icl_t('[CF Filter] ' . $acf_f_o_v, $acf_f_o_v);
                                    /*if (strpos(trim($val), 'checked') !== false) {
                                        $f[$k]->asp_f_checkboxes_value[] = array($acf_f_o_k, $acf_f_o_v . '**');
                                    } else {
                                        $f[$k]->asp_f_checkboxes_value[] = array($acf_f_o_k, $acf_f_o_v);
                                    }*/
                                    $add_arr[] = array(
                                        'label' => $acf_f_o_v,
                                        'value' => $acf_f_o_k
                                    );
                                }
                                $_used_acf = true;
                            }
                        }
                        if ( !$_used_acf ) {
                            $cf_query = $wpdb->prepare(
                                "SELECT DISTINCT(meta_value) FROM $table_name WHERE meta_key LIKE '%s' ORDER BY meta_value ASC LIMIT %d",
                                $bfield->asp_f_field, 5000);
                            $cf_results = $wpdb->get_results($cf_query, OBJECT);
                            if (!is_wp_error($cf_results) && count($cf_results) > 0) {
                                foreach ($cf_results as $cf_result) {
                                    /*$acf_f_o_v = asp_icl_t('[CF Filter] ' . $cf_result->meta_value, $cf_result->meta_value);
                                    if (strpos(trim($val), 'checked') !== false)
                                        $f[$k]->asp_f_checkboxes_value[] = array($cf_result->meta_value, $cf_result->meta_value . '**');
                                    else
                                        $f[$k]->asp_f_checkboxes_value[] = array($cf_result->meta_value, $cf_result->meta_value);*/
                                    $add_arr[] = array(
                                        'label' => $cf_result->meta_value,
                                        'value' => $cf_result->meta_value
                                    );
                                }
                            }
                        }
                    } else {
                        preg_match('/^(.*?)\|\|(.*)/', $val, $m);
                        //$f[$k]->asp_f_checkboxes_value[] = array($m[1], asp_icl_t('[CF Filter] ' . $m[2], $m[2]));
                        $add_arr[] = array(
                            'label' => $m[2],
                            'value' => $m[1]
                        );
                    }

                    foreach( $add_arr as $add ) {
                        $default = strpos($add['label'], '**') > 0;
                        $label = str_replace('**', '', $add['label']);
                        $label = asp_icl_t('[CF Filter] ' . $label, $label);

                        if ( isset($o['_fo'], $o['_fo']['aspf']) ) {
                            // Select all variation, keep it on the default value, as it is not possible to check
                            if ( $add['value'] == '' && $kk == 0 ) {
                                $checked = $default;
                            } else {
                                $checked = isset($o['_fo']['aspf'][$unique_field_name][$kk]);
                            }
                        } else {
                            $checked = $default;
                        }

                        $select_all = $kk == 0 & $add['value'] == '';

                        $vals = apply_filters("asp_cf_checkbox_values", array($add['value'], $label), $bfield);

                        $add = array_merge($add, array(
                            "label" => $vals[1],
                            "value" => $vals[0],
                            "default" => $default,
                            "selected" => $checked,
                            "select_all" => $select_all
                        ));
                        if ( $add['label'] != '' )
                            $filter->add($add);
                    }
                }
            }
            if ( isset($bfield->asp_f_slider_from, $bfield->asp_f_slider_to) ) {
                if ( $bfield->asp_f_slider_from == '' ) {
                    $min = $wpdb->get_var(
                        $wpdb->prepare(
                        "SELECT MIN(CAST(meta_value as SIGNED)) FROM $table_name WHERE meta_key LIKE '%s'",
                        $bfield->asp_f_field)
                    );
                    if ( !is_wp_error($min) && $min != null )
                        $bfield->asp_f_slider_from = $min;
                }
                if ( $bfield->asp_f_slider_to == '' ) {
                    $max = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT MAX(CAST(meta_value as SIGNED)) FROM $table_name WHERE meta_key LIKE '%s'",
                            $bfield->asp_f_field)
                    );
                    if ( !is_wp_error($max) && $max != null )
                        $bfield->asp_f_slider_to = $max;
                }

                $default = $bfield->asp_f_slider_default == '' ? $bfield->asp_f_slider_from : $bfield->asp_f_slider_default;
                if ( isset($o['_fo']) && isset($o['_fo']['aspf'][$unique_field_name]) )
                    $value = ASP_Helpers::force_numeric($o['_fo']['aspf'][$unique_field_name]);
                else
                    $value = $default;
                //$bfield->asp_f_slider_default = $bfield->asp_f_slider_default == '' ? $bfield->asp_f_slider_from : $bfield->asp_f_slider_default;
                $filter->add(array(
                    'label' => '',
                    'default' => $default,
                    'value' => $value
                ));
                $filter->data = array_merge($filter->data, array(
                    'slider_prefix' => $bfield->asp_f_slider_prefix,
                    'slider_suffix' => $bfield->asp_f_slider_suffix,
                    'slider_step' => $bfield->asp_f_slider_step,
                    'slider_from' => $bfield->asp_f_slider_from,
                    'slider_to'   => $bfield->asp_f_slider_to,
                    'slider_decimals' => w_isset_def($bfield->asp_f_slider_decimals, 0),
                    'slider_t_separator' => isset($bfield->asp_f_slider_t_separator) ? $bfield->asp_f_slider_t_separator : ' '
                ));
            }
            if ( isset($bfield->asp_f_range_from, $bfield->asp_f_range_to) ) {
                if ( $bfield->asp_f_range_from == '' ) {
                    $min = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT MIN(CAST(meta_value as SIGNED)) FROM $table_name WHERE meta_key LIKE '%s'",
                            $bfield->asp_f_field)
                    );
                    if ( !is_wp_error($min) && $min != null )
                        $bfield->asp_f_range_from = $min;
                }
                if ( $bfield->asp_f_range_to == '' ) {
                    $max = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT MAX(CAST(meta_value as SIGNED)) FROM $table_name WHERE meta_key LIKE '%s'",
                            $bfield->asp_f_field)
                    );
                    if ( !is_wp_error($max) && $max != null )
                        $bfield->asp_f_range_to = $max;
                }

                $bfield->asp_f_range_default1 = $bfield->asp_f_range_default1 == '' ? $bfield->asp_f_range_from : $bfield->asp_f_range_default1;
                $bfield->asp_f_range_default2 = $bfield->asp_f_range_default2 == '' ? $bfield->asp_f_range_to : $bfield->asp_f_range_default2;

                $default = array($bfield->asp_f_range_default1, $bfield->asp_f_range_default2);

                if ( isset($o['_fo']) && isset($o['_fo']['aspf'][$unique_field_name]['lower']) ) {
                    $value = array(
                        ASP_Helpers::force_numeric($o['_fo']['aspf'][$unique_field_name]['lower']),
                        ASP_Helpers::force_numeric($o['_fo']['aspf'][$unique_field_name]['upper'])
                    );
                } else {
                    $value = $default;
                }

                $filter->data = array_merge($filter->data, array(
                    'range_prefix' => $bfield->asp_f_range_prefix,
                    'range_suffix' => $bfield->asp_f_range_suffix,
                    'range_step' => $bfield->asp_f_range_step,
                    'range_from' => $bfield->asp_f_range_from,
                    'range_to'   => $bfield->asp_f_range_to,
                    'range_decimals' => w_isset_def($bfield->asp_f_range_decimals, 0),
                    'range_t_separator' => isset($bfield->asp_f_range_t_separator) ? $bfield->asp_f_range_t_separator : ' '
                ));

                $filter->add(array(
                    'label' => '',
                    'slider_from' => $bfield->asp_f_range_from,
                    'slider_to'   => $bfield->asp_f_range_to,
                    'value' => $value,
                    'default' => $default
                ));
            }

            if ( $bfield->asp_f_type == 'datepicker' ) {
                switch ($bfield->asp_f_datepicker_defval) {
                    case "none":
                        $default = "";
                        break;
                    case "current":
                        $default = "+0";
                        break;
                    case "relative":
                        $default = $bfield->asp_f_datepicker_from_months . "m " . $bfield->asp_f_datepicker_from_days . "d";
                        break;
                    default:
                        $default = $bfield->asp_f_datepicker_value;
                        break;
                }

                if ( isset($o['_fo']) && isset($o['_fo']['aspf'][$unique_field_name."_real"]) ) {
                    $value = sanitize_text_field($o['_fo']['aspf'][$unique_field_name."_real"]);
                } else {
                    $value = $default;
                }

                if ( !isset($bfield->asp_f_datepicker_placeholder) ) {
                    //$bfield->asp_f_datepicker_placeholder = '';
                    $filter->data['placeholder'] = '';
                } else {
                    //$bfield->asp_f_datepicker_placeholder = asp_icl_t('[CF Date Filter Placeholder] ' . $bfield->asp_f_datepicker_placeholder, $bfield->asp_f_datepicker_placeholder);
                    $filter->data['placeholder'] = asp_icl_t('[CF Date Filter Placeholder] ' . $bfield->asp_f_datepicker_placeholder, $bfield->asp_f_datepicker_placeholder);
                }
                $filter->data['date_format'] = $bfield->asp_f_datepicker_format;

                $filter->add(array(
                    'label' => '',
                    'value' => $value,
                    'default' => $default
                ));
            }

            //$filters[] = $filter;
            wd_asp()->front_filters->add($filter);
        }
        //return $f;
    }
}

if ( !function_exists('asp_parse_tax_term_filters') ) {
    /**
     * Parses the taxonomy term filters from the search options
     *
     * @param array $o Search options
     * @return aspTaxFilter[] Filters array
     */
    function asp_parse_tax_term_filters($o) {
        /**
         * Return value
         * array[{
         *      ->label
         *      ->display_mode
         *      ->values => array[{
         *          ->label
         *          ->selected
         *          ->id
         *          ->level
         *          ->default
         *          ->parent
         *          ->taxonomy
         *      }]
         * }, ...]
         */
        //$filters = array();
        $have_selected = false;
        $term_ordering = w_isset_def($o['selected-frontend_term_order'], array('name', 'ASC'));

        // Re-group by taxonomy
        $tax_terms = array();
        foreach ($o['show_terms']['terms'] as $t) {
            if ( !isset($tax_terms[$t['taxonomy']]) )
                $tax_terms[$t['taxonomy']] = array();
            $tax_terms[$t['taxonomy']][$t['id']] = $t;
        }

        if ( isset($o['_fo']) &&
            !isset($o['_fo']['termset']) ) {
            $o['_fo']['termset'] = array();
        }

        foreach ($tax_terms as $taxonomy => $terms) {

            $term_ids = array();
            foreach ($terms as $k => $t) {
                $term_ids[] = $t['id'];
            }

            if ( !empty($terms) ) {

                if (isset($o['_fo']) &&
                    !isset($o['_fo']['termset'][$taxonomy])) {
                    $o['_fo']['termset'][$taxonomy] = array();
                }

                // Need all terms
                if (count($terms) == 1 && isset($terms[-1], $terms[-1]["id"]) && $terms[-1]["id"] == -1) {
                    $_needed_all = true;
                    $_needed_terms_full = get_terms($taxonomy, array(
                        'taxonomy' => $taxonomy,
                        'orderby' => $term_ordering[0],
                        'order' => $term_ordering[1],
                        'hide_empty' => 0,
                        'exclude' => isset($terms[-1]["ex_ids"]) ? $terms[-1]["ex_ids"] : array()
                    ));
                } else {
                    $_needed_all = false;
                    $_needed_terms_full = get_terms($taxonomy, array(
                        'taxonomy' => $taxonomy,
                        'orderby' => 'include',
                        'order' => 'ASC',
                        'include' => $term_ids,
                        'hide_empty' => 0
                    ));
                }

                if (is_wp_error($_needed_terms_full))
                    continue;

                $_needed_terms_full = apply_filters('asp_fontend_get_taxonomy_terms',
                    $_needed_terms_full,
                    $taxonomy,
                    array(
                        'orderby' => $term_ordering[0],
                        'order' => $term_ordering[1],
                        'include' => $terms,
                        'include_ids' => $term_ids
                    ),
                    $_needed_all
                );

                $_needed_terms_sorted = array();
                $needed_terms_flat = array();

                // This is passed as the $data argument
                $display_mode = array(
                    "type" => "checkboxes",
                    "required" => false,
                    "invalid_input_text" => 'This field is required!',
                    "default" => "checked",
                    "select_all" => 0,
                    "select_all_text" => "",
                    "box_header_text" => "",
                    "box_placeholder_text" => ""
                );

                if ( count($o['show_terms']['display_mode']) > 0) {
                    if ( $o['show_terms']['separate_filter_boxes'] != 1 ) {
                        $display_mode = array_merge($display_mode, $o['show_terms']['display_mode']['all']);
                    } else if (isset($o['show_terms']['display_mode'][$taxonomy])) {
                        $display_mode = array_merge($display_mode, $o['show_terms']['display_mode'][$taxonomy]);
                    }
                }
                $display_mode["taxonomy"] = $o['show_terms']['separate_filter_boxes'] == 0 ? 'terms' : $taxonomy;

                if (w_isset_def($o['frontend_term_hierarchy'], 1) == 1) {
                    wd_sort_terms_hierarchicaly( $_needed_terms_full, $_needed_terms_sorted );
                    wd_flatten_hierarchical_terms( $_needed_terms_sorted, $needed_terms_flat );
                } else {
                    $needed_terms_flat = $_needed_terms_full;
                }

                $display_mode['is_api'] = false;
                $display_mode['placeholder'] = $display_mode['box_placeholder_text'];

                if ( $o['show_terms']['separate_filter_boxes'] == 1 || !isset($current_filter) ) {
                    $current_filter = wd_asp()->front_filters->create('taxonomy', $display_mode['box_header_text'], $display_mode['type'], $display_mode);
                }

                if ( $display_mode['type'] == "checkboxes" ) {

                    if ( $display_mode['select_all'] == 1 && $current_filter->isEmpty() ) {
                        $filter = array(
                            'label' => $display_mode['select_all_text'],
                            'selected' => $display_mode['select_all'] == 1,
                            'default' => $display_mode['select_all'] == 1,
                            'taxonomy' => $display_mode['select_all'] == 0 ? 'terms' : $taxonomy,
                            'level' => 0
                        );
                        $current_filter->add($filter);
                    }

                    $chb_default_state = true;
                    if ( $display_mode["default"] == "unchecked" )
                        $chb_default_state = false;

                    foreach ($needed_terms_flat as $k => $term) {
                        $filter = array(
                            'label' => $term->name,
                            'id' => $term->term_id,
                            'taxonomy' => $taxonomy,
                            'level' => $term->level
                        );

                        /**
                         * Explanation: $tax_terms contains the originals sorted by taxonomy.
                         * If the current term is in the array, then it was excplicitly selected, not by "Use all from.."
                         * In this case it is only unchecked when in the un_checked array. Otherwise the default state applies.
                         */
                        if ( isset($tax_terms[$term->taxonomy][$term->term_id]) ) {
                            $filter['default'] = !in_array($term->term_id, $o['show_terms']['un_checked']);
                        } else {
                            $filter['default'] = $chb_default_state;
                        }

                        if ( isset($o['_fo']) ) {
                            $filter['selected'] = in_array($term->term_id, $o['_fo']['termset'][$taxonomy]);
                        } else {
                            $filter['selected'] = $filter['default'];
                        }

                        $current_filter->add($filter);
                    }

                } else if (
                    $display_mode['type'] == "dropdown" ||
                    $display_mode['type'] == "dropdownsearch" ||
                    $display_mode['type'] == "multisearch"
                ) {
                    if ( $display_mode['select_all'] == 1 && $current_filter->isEmpty() && $display_mode['type'] != "multisearch") {
                        $filter = array(
                            'label' => $display_mode['select_all_text'],
                            'selected' => $display_mode['default'] == "all",
                            'default' => $display_mode['default'] == "all",
                            'taxonomy' => $display_mode['select_all'] == 0 ? 'terms' : $taxonomy,
                            'level' => 0
                        );
                        $current_filter->add($filter);
                    }

                    $len = count($needed_terms_flat);
                    $i = 0;
                    $selected = false;
                    if ($o['show_terms']['separate_filter_boxes'] == 1)
                        $have_selected = false;
                    foreach ($needed_terms_flat as $k => $term) {
                        $filter = array(
                            'label' => $term->name,
                            'id' => $term->term_id,
                            'taxonomy' => $taxonomy,
                            'level' => $term->level
                        );

                        if (  $display_mode['type'] == 'multisearch' ) {
                            if ( isset($tax_terms[$term->taxonomy][$term->term_id]) ) {
                                $filter['default'] = !in_array($term->term_id, $o['show_terms']['un_checked']);
                            } else {
                                $filter['default'] = false;
                            }
                        } else {
                            if (
                                !$have_selected && (
                                    ( $i == 0 && $display_mode['default'] == "first" ) ||
                                    ( ($i == $len -1) && $display_mode['default'] == "last" ) ||
                                    $term->term_id == $display_mode['default']
                                )
                            ) {
                                $filter['default'] = true;
                                $have_selected = true;
                            } else {
                                $filter['default'] = false;
                            }
                        }


                        if ( isset($o['_fo']) ) {
                            if ( !$have_selected ) {
                                $filter['selected'] = in_array($term->term_id, $o['_fo']['termset'][$taxonomy]);
                                if ( $selected && $display_mode['type'] != 'multisearch' )
                                    $have_selected = true;
                            } else {
                                 $filter['selected'] = false;
                            }
                        } else {
                             $filter['selected'] = $filter['default'];
                        }

                        $current_filter->add($filter);
                        $i++;
                    }

                } else if ($display_mode['type'] == "radio") {

                    if ( $display_mode['select_all'] == 1 && $current_filter->isEmpty() ) {
                        $filter = array(
                            'label' => $display_mode['select_all_text'],
                            'selected' => $display_mode['default'] == "all",
                            'default' => $display_mode['default'] == "all",
                            'taxonomy' => $display_mode['select_all'] == 0 ? 'terms' : $taxonomy,
                            'level' => 0
                        );
                        $current_filter->add($filter);
                    }

                    $len = count($needed_terms_flat);
                    $i = 0;
                    $selected = false;
                    if ($o['show_terms']['separate_filter_boxes'] == 1)
                        $have_selected = false;
                    foreach ($needed_terms_flat as $k => $term) {
                        $filter = array(
                            'label' => $term->name,
                            'id' => $term->term_id,
                            'taxonomy' => $taxonomy,
                            'level' => $term->level
                        );

                        if (
                            !$have_selected && (
                                ($i == 0 && $display_mode['default'] == "first") ||
                                (($i == $len -1) && $display_mode['default'] == "last") ||
                                $term->term_id == $display_mode['default']
                            )
                        ) {
                            $filter['default'] = true;
                            $have_selected = true;
                        } else {
                            $filter['default'] = false;
                        }

                        if ( isset($o['_fo']) ) {
                            if ( !$have_selected ) {
                                $filter['selected'] = in_array($term->term_id, $o['_fo']['termset'][$taxonomy]);
                                if ( $selected )
                                    $have_selected = true;
                            } else {
                                $filter['selected'] = false;
                            }
                        } else {
                             $filter['selected'] = $filter['default'];
                        }

                        $current_filter->add($filter);
                    }
                }

                if ( $o['show_terms']['separate_filter_boxes'] == 1 )
                    //$filters[] = $current_filter;
                    wd_asp()->front_filters->add($current_filter);
            }
            //--- TAX LOOP
        }
        if ( $o['show_terms']['separate_filter_boxes'] == 0 && isset($current_filter) )
            //$filters[] = $current_filter;
            wd_asp()->front_filters->add($current_filter);

        //return apply_filters('asp_taxonomy_filters', $filters, $o);
    }
}

if ( !function_exists('asp_parse_post_tag_filters') ) {
    function asp_parse_post_tag_filters($o) {
        if (
            empty($o["selected-show_frontend_tags"]) ||
            $o["selected-show_frontend_tags"]['active'] == 0
        ) return;

        /* Let us make it a bit more accessible */
        $_sfto = $o["selected-show_frontend_tags"];

        if ($_sfto['source'] == "all") {
            // Limit all tags to 400. I mean that should be more than enough..
            $_sftags = get_terms("post_tag", array("number"=>400));
        } else {
            $_sftags = asp_get_terms_ordered_by_ids("post_tag", $_sfto['tag_ids']);
        }

        if ( empty($_sftags) ) return;

        if ($_sfto['source'] == "all") {
            $_sel_tags = $_sfto['default_tag'] != "" ? array($_sfto['default_tag']) : array();
        } else {
            $_sel_tags = $_sfto["checked_tag_ids"];
        }

        if ( isset($o['_fo']) && !isset($o['_fo']['post_tag_set']) )
            $o['_fo']['post_tag_set'] = array();

        $display_mode = array(
            "type" => $_sfto['display_mode'],
            "taxonomy" => "post_tag",
            "custom_name" => 'post_tag_set[]',
            "default" => $_sfto['display_mode'] == 'checkboxes' ? ($o['all_tags_check_opt_state'] == 'checked' ? 1 : 0) : 1,
            "select_all" => $_sfto['display_mode'] == 'checkboxes' ? $o['display_all_tags_check_opt'] : $o['display_all_tags_option'],
            "select_all_text" => $_sfto['display_mode'] == 'checkboxes' ? $o['all_tags_check_opt_text'] : $o['all_tags_opt_text'],
            "box_header_text" => w_isset_def($o['frontend_tags_header'], "Filter by Tags"),
            "placeholder" => $o["frontend_tags_placeholder"],
            "required" => $o['frontend_tags_required'] == 1,
            "invalid_input_text" => $o['frontend_tags_invalid_input_text'],
            "is_api" => false
        );

        $filter = wd_asp()->front_filters->create('post_tags', $display_mode['box_header_text'], $display_mode['type'], $display_mode);

        if ( $display_mode["select_all"] == 1 ) {
            $filter->add(array(
                'label' => $display_mode['select_all_text'],
                'selected' => $display_mode['default'],
                'default' => $display_mode['default'],
                'taxonomy' => 'post_tag',
                'custom_name' => 'post_tag_set[]'
            ));
        }

        foreach($_sftags as $_sftag) {
            if ( $display_mode['type'] == 'checkboxes' && $_sfto['source'] == "all" ) {
                $default = $_sfto['all_status'] == "checked";
            } else {
                $default = in_array($_sftag->term_id, $_sel_tags);
            }
            if ( isset($o['_fo']) ) {
                $selected = in_array($_sftag->term_id, $o['_fo']['post_tag_set']);
            } else {
                $selected = $default;
            }

            $filter->add(array(
                'label' => $_sftag->name,
                'id' => $_sftag->term_id,
                'selected' => $selected,
                'default' => $default,
                'taxonomy' => 'post_tag'
            ));
        }

        wd_asp()->front_filters->add($filter);
    }
}

if ( !function_exists("asp_parse_button_filters") ) {
    function asp_parse_button_filters($o) {
        if ( $o['fe_reset_button'] == 1 || $o['fe_search_button'] == 1 ) {
            $filter = wd_asp()->front_filters->create('button');
            $filter->is_api = false;
            if ( $o['fe_reset_button'] == 1 && $o['fe_rb_position'] == 'before' ) {
                $filter->add(array(
                    'label' => asp_icl_t('Reset button', $o['fe_rb_text']),
                    'type' => 'reset',
                    'container_class' => 'asp_r_btn_div',
                    'button_class' => 'asp_reset_btn asp_r_btn'
                ));
            }
            if ( $o['fe_search_button'] == 1 ) {
                $filter->add(array(
                    'label' => asp_icl_t('Search button', $o['fe_sb_text']),
                    'type' => 'search',
                    'container_class' => 'asp_s_btn_div',
                    'button_class' => 'asp_search_btn asp_s_btn'
                ));
            }
            if ( $o['fe_reset_button'] == 1 && $o['fe_rb_position'] == 'after' ) {
                $filter->add(array(
                    'label' => asp_icl_t('Reset button', $o['fe_rb_text']),
                    'type' => 'reset',
                    'container_class' => 'asp_r_btn_div',
                    'button_class' => 'asp_reset_btn asp_r_btn'
                ));
            }
            wd_asp()->front_filters->add($filter);
        }
    }
}

if ( !function_exists("asp_parse_generic_filters") ) {
    function asp_parse_generic_filters($o) {
        $settingsFullyHidden =
            $o['show_frontend_search_settings']!= 1 &&
            $o['frontend_search_settings_visible'] != 1 ? true : false;

        if( $settingsFullyHidden ) {
            $_checked_def = array(
                "exact" => $o['exactonly'] == 1,
                "title" =>  $o['searchintitle'] == 1,
                "content" => $o['searchincontent'] == 1,
                "excerpt" => $o['searchinexcerpt'] == 1
            );
        } else {
            $_checked_def = array(
                "exact" => in_array('exact', $o['frontend_fields']['checked']),
                "title" => in_array('title', $o['frontend_fields']['checked']),
                "content" => in_array('content', $o['frontend_fields']['checked']),
                "excerpt" => in_array('excerpt', $o['frontend_fields']['checked'])
            );
        }

        // Search redirection, memorize options
        if ( isset($o['_fo']) ) {
            $o['_fo']['asp_gen'] = isset($o['_fo']['asp_gen']) ? $o['_fo']['asp_gen'] : array();
            $_checked = array(
                "exact" => in_array('exact', $o['_fo']['asp_gen']),
                "title" => in_array('title', $o['_fo']['asp_gen']),
                "content" => in_array('content', $o['_fo']['asp_gen']),
                "excerpt" => in_array('excerpt', $o['_fo']['asp_gen'])
            );
        } else {
            $_checked = $_checked_def;
        }


        $filter = wd_asp()->front_filters->create(
            'generic',
            asp_icl_t("Generic filter label", $o['generic_filter_label']),
            $o['frontend_fields']['display_mode'],
            $o['frontend_fields']
        );
        $filter->is_api = false;

        $filter->data['visible'] = count($o['frontend_fields']['selected']) > 0;

        foreach ($o['frontend_fields']['selected'] as $item) {
            $default = $_checked_def[$item];
            $selected = $_checked[$item];
            $filter->add(array(
                "value" => $item,
                "default" => $default,
                "label" => asp_icl_t("Generic field[" . $item . "]", $o['frontend_fields']['labels'][$item]),
                "selected" => $selected,
                "field" => $item
            ));
        }

        wd_asp()->front_filters->add($filter);
    }
}

if ( !function_exists('asp_parse_content_type_filters') ) {
    function asp_parse_content_type_filters( $o ) {
        if ( count($o['content_type_filter']['selected']) > 0 ) {
            // Search redirection, memorize options
            if ( isset($o['_fo']) ) {
                $_carr = isset( $o['_fo']['asp_ctf'] ) && is_array( $o['_fo']['asp_ctf'] ) ? $o['_fo']['asp_ctf'] : array();
                $_carr_def = $o['content_type_filter']['checked'];
            } else {
                $_carr = $o['content_type_filter']['checked'];
                $_carr_def = $o['content_type_filter']['checked'];
            }

            $_checked = array(
                "any" => in_array('any', $_carr),
                "cpt" => in_array('cpt', $_carr),
                "comments" => in_array('comments', $_carr),
                "taxonomies" => in_array('taxonomies', $_carr),
                "users" => in_array('users', $_carr),
                "blogs" => in_array('blogs', $_carr),
                "buddypress" => in_array('buddypress', $_carr),
                "attachments" => in_array('attachments', $_carr)
            );
            $_checked_def = array(
                "any" => in_array('any', $_carr_def),
                "cpt" => in_array('cpt', $_carr_def),
                "comments" => in_array('comments', $_carr_def),
                "taxonomies" => in_array('taxonomies', $_carr_def),
                "users" => in_array('users', $_carr_def),
                "blogs" => in_array('blogs', $_carr_def),
                "buddypress" => in_array('buddypress', $_carr_def),
                "attachments" => in_array('attachments', $_carr_def)
            );

            if (($akey = array_search('any', $o['content_type_filter']['selected'])) !== false) {
                unset($o['content_type_filter']['selected'][$akey]);
                $o['content_type_filter']['selected'] = array_merge(array('any'), $o['content_type_filter']['selected']);
            }

            $filter = wd_asp()->front_filters->create(
                'content_type',
                asp_icl_t("Content type filter label", $o['content_type_filter_label']),
                $o['content_type_filter']['display_mode'],
                $o['content_type_filter']
            );
            $filter->is_api = false;

            foreach ( $o['content_type_filter']['selected'] as $item ) {
                $value = $item == 'any' ? -1 : $item;
                $default = $_checked_def[$item];
                $selected = $_checked[$item];
                $filter->add(array(
                    "value" => $value,
                    "default" => $default,
                    "label" => asp_icl_t("Content Type field[".$item."]", $o['content_type_filter']['labels'][$item] ),
                    "selected" => $selected,
                    "field" => $item
                ));
            }

            wd_asp()->front_filters->add($filter);
        }
    }
}

if ( !function_exists('asp_parse_post_type_filters') ) {
    function asp_parse_post_type_filters( $o ) {
        if ( !isset($o['customtypes']) || !is_array($o['customtypes']) )
            $o['customtypes'] = array();
        $settingsFullyHidden =
            $o['show_frontend_search_settings']!= 1 &&
            $o['frontend_search_settings_visible'] != 1 ? true : false;
        // When settings are fully hidden, do not show the options
        if (
            $settingsFullyHidden ||
            !isset($o['selected-showcustomtypes']) ||
            !is_array($o['selected-showcustomtypes'])
        )
            $o['selected-showcustomtypes'] = array();

        if ( count($o['selected-showcustomtypes']) > 0 ) {
            $filter = wd_asp()->front_filters->create(
                'post_type',
                asp_icl_t("Custom post types label", w_isset_def($o['custom_types_label'], 'Filter by Custom Post Type')),
                $o['cpt_display_mode'],
                array(
                    'required' => $o['cpt_required'] == 1,
                    'invalid_input_text' =>$o['cpt_invalid_input_text']
                )
            );
            $filter->is_api = false;

            if ( $o['cpt_display_mode'] == 'checkboxes' && $o['cpt_cbx_show_select_all'] == 1 ) {
                $filter->add(array(
                    "value" => -1,
                    "default" => true,
                    "label" => asp_icl_t('Select all checkbox for post types', $o['cpt_cbx_show_select_all_text']),
                    "selected" => true,
                    "field" => 'select_all'
                ));
            }

            foreach ( $o['selected-showcustomtypes'] as $k => $item ) {

                if ( $o['cpt_display_mode'] == 'checkboxes' ) {
                    $default = in_array($item[0], $o['customtypes']);
                } else {
                    $default = $item[0] == $o['cpt_filter_default'];
                }
                if (isset($o['_fo'])) {
                    $selected = in_array($item[0], $o['_fo']['customset']);
                } else {
                    $selected = $default;
                }

                $filter->add(array(
                    "value" => $item[0],
                    "default" => $default,
                    "label" => asp_icl_t($item[0], $item[1]),
                    "selected" => $selected,
                    "field" => 'cpt'
                ));
            }

            wd_asp()->front_filters->add($filter);
        }
    }
}

if ( !function_exists('asp_parse_date_filters') ) {
    function asp_parse_date_filters( $o ) {
        /* Options not set or deactivated, return - if one is set, other is definitely set too */
        if ( empty($o["selected-date_filter_from"]) ) return;

        $_dff = &$o["selected-date_filter_from"];
        $_dft = &$o["selected-date_filter_to"];

        if ( $_dff['state'] == "disabled" && $_dft['state'] == "disabled" ) return;
        $_dfrom_t = w_isset_def($o['date_filter_from_t'], "Content from");
        $_dto_t = w_isset_def($o['date_filter_to_t'], "Content to");

        if ( $_dff['state'] == "rel_date" ) {
            $_def_dff_v = "-" . $_dff["rel_date"][0] . "y -".$_dff["rel_date"][1]."m -".$_dff["rel_date"][2] . "d";
        } else {
            $_def_dff_v = $_dff['date'];
        }
        if ( $_dft['state'] == "rel_date" ) {
            $_def_dft_v = "-" . $_dft["rel_date"][0] . "y -".$_dft["rel_date"][1]."m -".$_dft["rel_date"][2] . "d";
        } else {
            $_def_dft_v = $_dft['date'];
        }
        if ( isset($o['_fo']) && isset($o['_fo']['post_date_from']) ) {
            $_dff_v = sanitize_text_field($o['_fo']['post_date_from']);
        } else {
            $_dff_v = $_def_dff_v;
        }
        if ( isset($o['_fo']) && isset($o['_fo']['post_date_to']) ) {
            $_dft_v = sanitize_text_field($o['_fo']['post_date_to']);
        } else {
            $_dft_v = $_def_dft_v;
        }

        $filter = wd_asp()->front_filters->create(
            'date',
            "Post type date filters",
            'date',
            array(
                "required" => $o['date_filter_required'] == 1,
                "invalid_input_text" => $o['date_filter_invalid_input_text']
            )
        );
        $filter->is_api = false;

        if ( $_dff['state'] != 'disabled' ) {
            $filter->add(array(
                "value" => $_dff_v,
                "default" => $_def_dff_v,
                "name"  => "post_date_from",
                "label" => asp_icl_t( "Post date filter: Content from", $_dfrom_t ),
                "format" => $o["date_filter_from_format"],
                "placeholder" => asp_icl_t( "Post date filter placeholder (from)", $o["date_filter_from_placeholder"] )
            ));
        }
        if ( $_dft['state'] != 'disabled' ) {
            $filter->add(array(
                "value" => $_dft_v,
                "default" => $_def_dft_v,
                "name"  => "post_date_to",
                "label" => asp_icl_t( "Post date filter: Content to", $_dto_t ),
                "format" => $o["date_filter_to_format"],
                "placeholder" => asp_icl_t( "Post date filter placeholder (to)", $o["date_filter_to_placeholder"] )
            ));
        }

        wd_asp()->front_filters->add($filter);
    }
}

if ( !function_exists('asp_icl_t') ) {
    /* Ajax Search pro wrapper for WPML and Polylang print */
    function asp_icl_t($name, $value, $esc_html = false) {
        if ( $value == "" )
            return $value;

        $ret = $value;

        if (function_exists('pll_register_string') && function_exists('pll__')) {
            /**
             * PLL specific
             *   Pll does not actually register the unique string by $name, only the values, but it causes issues
             *   with saving them on the back-end, as they override each other. Removing the variables, from the names
             *   resolves the problem.
             */
            $name = preg_replace('/[0-9]+/', '', $name);
            $name = preg_replace('/\s+/', ' ', $name);
            $name = str_replace(array('()', '[]'), '', $name);

            if ( is_admin() )
                pll_register_string($name, $value, 'ajax-search-pro');
            //if ( function_exists('icl_register_string') ) {
              /**
               * pll_register_string is buggy, it does not register the strings sometimes correctly
               * however it has ICL compatibility, so we may register via that
               */
              //@icl_register_string('ajax-search-pro', $name, $value);
            //}
            $ret = pll__($value);
        } else if (function_exists('icl_register_string') && function_exists('icl_t')) {
            @icl_register_string('ajax-search-pro', $name, $value);
            $ret = @icl_t('ajax-search-pro', $name, $value);
        } else if (function_exists('qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
            $ret = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage( $value );
        }
        if ( $esc_html )
            return esc_html( stripslashes( $ret ) );
        else
            return stripslashes( $ret );
    }
}

if (!function_exists("asp_gen_rnd_str")) {
    function asp_gen_rnd_str($length = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

if ( !function_exists("asp_is_asset_required") ) {
    function asp_is_asset_required($asset) {
        if ( wd_asp()->manager->getContext() == 'backend' ) {
            return true;
        } else {
            $assets = asp_get_unused_assets(true);
            return !wd_in_array_r($asset, $assets);
        }
    }
}

if ( !function_exists("asp_get_unused_assets") ) {
    function asp_get_unused_assets( $return_stored = false ) {
        $dependencies = array(
            'vertical', 'horizontal', 'isotopic', 'polaroid', 'noui', 'datepicker', 'autocomplete'
        );
        $external_dependencies = array(
            'chosen', 'isotope', 'simplebar'
        );
        $filters_may_require_simplebar = false;
        if ( $return_stored !== false ) {
            return get_option('asp_unused_assets', array(
                'internal' => $dependencies,
                'external' => $external_dependencies
            ));
        }

        $search = wd_asp()->instances->get();
        if (is_array($search) && count($search)>0) {
            foreach ($search as $s) {
                // $style and $id needed in the include
                $style = $s['data'];
                $id = $s['id'];

                // Calculate flags for the generated basic CSS
                // --- Results type
                $dependencies = array_diff($dependencies, array($s['data']['resultstype']));
                // --- NOUI
                asp_parse_filters($id, $style, true, true);
                foreach (wd_asp()->front_filters->get() as $filter) {
                    if ($filter->display_mode == 'slider' || $filter->display_mode == 'range') {
                        $dependencies = array_diff($dependencies, array('noui'));
                        break;
                    }
                }
                // --- Datepicker
                foreach (wd_asp()->front_filters->get() as $filter) {
                    if ($filter->display_mode == 'date' || $filter->display_mode == 'datepicker') {
                        $dependencies = array_diff($dependencies, array('datepicker'));
                        break;
                    }
                }
                // --- Scrollable filters
                foreach (wd_asp()->front_filters->get() as $filter) {
                    if ( isset($filter->data['visible']) && $filter->data['visible'] == 0 ) {
                        continue;
                    }
                    if ($filter->display_mode == 'checkboxes' || $filter->display_mode == 'radio') {
                        $filters_may_require_simplebar = true;
                        break;
                    }
                }
                // --- Autocomplete (not used yet)

                // --- Chosen
                foreach (wd_asp()->front_filters->get() as $filter) {
                    if ($filter->display_mode == 'dropdownsearch' || $filter->display_mode == 'multisearch') {
                        $external_dependencies = array_diff($external_dependencies, array('chosen'));
                        break;
                    }
                }

                // --- Isotope
                if ( $s['data']['resultstype'] == 'isotopic' || $s['data']['fss_column_layout'] == 'masonry' ) {
                    $external_dependencies = array_diff($external_dependencies, array('isotope'));
                }
            }
        }

        // No vertical or horizontal results results, and no filters that may trigger the scroll script
        if (
            $filters_may_require_simplebar ||
            !in_array('vertical', $dependencies) ||
            !in_array('horizontal', $dependencies)
        ) {
            $external_dependencies = array_diff($external_dependencies, array('simplebar'));
        }

        // Store for the init script
        update_option('asp_unused_assets', array(
            'internal' => $dependencies,
            'external' => $external_dependencies
        ));

        return array(
            'internal' => $dependencies,
            'external' => $external_dependencies
        );
    }
}

if ( !function_exists("asp_get_css_url") ) {
    function asp_get_css_url( $handle ) {
        $externals = array(
            'chosen' => ASP_URL . 'css/chosen/chosen.css',
            'wpdreams-asp-chosen' => ASP_URL . 'css/chosen/chosen.css'
        );
        if ( isset($externals[$handle]) ) {
            return $externals[$handle];
        } else {
            if ( '' != $file = asp_get_css_filename($handle) ) {
                return wd_asp()->upload_url . $file;
            } else {
                return '';
            }
        }
    }
}

if ( !function_exists("asp_get_css_filename") ) {
    function asp_get_css_filename( $handle ) {
        $media_flags = get_option('asp_css_flags', array(
            'basic' => ''
        ));
        $files = array(
            'basic' => 'style.basic'.$media_flags['basic'].'.css',
            'wpdreams-asp-basics' => 'style.basic'.$media_flags['basic'].'.css',
            'instances' => 'style.instances'.$media_flags['basic'].'.css',
            'wpdreams-ajaxsearchpro-instances' => 'style.instances'.$media_flags['basic'].'.css'
        );
        if ( isset($files[$handle]) ) {
            return $files[$handle];
        } else {
            return '';
        }
    }
}

if (!function_exists("asp_generate_the_css")) {
    /**
     * Generates all Ajax Search Pro CSS code
     */
    function asp_generate_the_css( $remake_media_query = true ) {
        $css_arr = array();
        $comp_settings = wd_asp()->o['asp_compatibility'];
        $async_load = w_isset_def($comp_settings['css_async_load'], false);
        $basic_flags_string = '';

        $search = wd_asp()->instances->get();
        if (is_array($search) && count($search)>0) {
            // Basic CSS
            ob_start();
            include(ASP_PATH . "/css/style.basic.css.php");
            $basic_css = ob_get_clean();
            $unused_assets = asp_get_unused_assets();
            foreach ( $unused_assets['internal'] as $flag ) {
                // Remove unneccessary CSS
                $basic_css = asp_get_outer_substring($basic_css, '/*[' . $flag . ']*/');
                $basic_flags_string .= '-' . substr($flag, 0, 2);
            }

            // Instances CSS
            foreach ($search as $s) {
                // $style and $id needed in the include
                $style = &$s['data'];
                $id = $s['id'];
                ob_start();
                include(ASP_PATH . "/css/style.css.php");
                $out = ob_get_contents();
                $css_arr[$id] = $out;
                ob_end_clean();
            }

            // Too big, disabled...
            $css = implode(" ", $css_arr);

            if ( $async_load == 1 ) {
                foreach ($css_arr as $sid => $c) {
                    if ( $comp_settings['css_minify'] == 1 )
                        $c = asp_css_minify($c);
                    asp_put_file("search".$sid.".css", $c);
                }
            }
            // Save the style instances file nevertheless, even if async enabled
            if ( $comp_settings['css_minify'] == 1 ) {
                $css = asp_css_minify($css);
                $basic_css = asp_css_minify($basic_css);
            }

            asp_put_file("style.instances.css", $basic_css . $css);
            asp_put_file("style.basic.css", $basic_css);
            if ( $basic_flags_string != '' ) {
                asp_put_file("style.basic" . $basic_flags_string . ".css", $basic_css);
                asp_put_file("style.instances" . $basic_flags_string . ".css", $basic_css . $css);
            }

            update_option('asp_css_flags', array(
                'basic' => $basic_flags_string
            ));

            if ( $remake_media_query )
                update_option( "asp_media_query", asp_gen_rnd_str() );

            return $basic_css . $css;
        }
    }
}

if (!function_exists("asp_css_minify")) {
    /**
     * Very simple CSS minification, with some additional logic, basic support for CSS3
     *
     * @param string $css CSS to minify
     * @return string Minified CSS
     */
    function asp_css_minify($css) {
        // Normalize whitespace
        $css = preg_replace( '/\s+/', ' ', $css );
        // Remove spaces before and after comment
        $css = preg_replace( '/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $css );
        // Remove comment blocks, everything between /* and */, unless
        // preserved with /*! ... */ or /** ... */
        $css = preg_replace( '~/\*(?![\!|\*])(.*?)\*/~', '', $css );
        // Remove space after , : ; { } */ >
        $css = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $css );
        // Remove space before , ; { } ( ) >
        $css = preg_replace( '/ (,|;|\{|}|\(|\)|>)/', '$1', $css );
        // Add back the space for media queries operator
        $css = preg_replace( '/and\(/', 'and (', $css );
        // Strips leading 0 on decimal values (converts 0.5px into .5px)
        $css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
        // Strips units if value is 0 (converts 0px to 0)
        $css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );
        // Converts all zeros value into short-hand
        $css = preg_replace( '/0 0 0 0;/', '0;', $css );
        $css = preg_replace( '/0 0 0 0\}/', '0}', $css );
        // Invisible inset box shadow
        $css = preg_replace( '/box-shadow:0 0 0(?: 0)? [a-fA-F0-9()#,rgb]+(?: inset)?([};])/i', 'box-shadow:none${1}', $css );
        // Transparent box shadow
        $css = preg_replace( '/box-shadow:[0-9px ]+ (transparent inset|transparent)([};])/i', 'box-shadow:none${2}', $css );
        // Invisible text shadow
        $css = preg_replace( '/text-shadow:0 0(?: 0)? [a-fA-F0-9()#,rgb]+([};])/i', 'text-shadow:none${1}', $css );
        // Transparent text shadow
        $css = preg_replace( '/text-shadow:[0-9px ]+ transparent([};])/i', 'text-shadow:none${1}', $css );
        // Shorten 6-character hex color codes to 3-character where possible
        $css = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $css );
        // Remove ; before }
        $css = preg_replace( '/;(?=\s*})/', '', $css );
        return trim( $css );
    }
}

if (!function_exists("asp_get_terms_ordered_by_ids")) {
    function asp_get_terms_ordered_by_ids($taxonomy, $ids) {
        if ( empty($ids) ) return array();

        $tag_keys_arr = array();
        $final_tags_arr = array();

        foreach ($ids as $position => $tag_id) {
            $tag_keys_arr[$tag_id] = $position;
        }

        $tags = get_terms($taxonomy, array("include" => $ids));

        foreach ($tags as $tag) {
            $final_tags_arr[$tag_keys_arr[$tag->term_id]] = $tag;
        }

        ksort($final_tags_arr);

        return $final_tags_arr;
    }
}

function asp_str_remove_protocol( $str ) {
    return str_replace( array(
        'https://',
        'http://',
    ), '//', $str );
}