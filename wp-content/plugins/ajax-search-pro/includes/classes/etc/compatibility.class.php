<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpdreamsCompatibility')) {
    class wpdreamsCompatibility {

        public static function Instance() {
            static $inst = null;
            if ($inst === null) {
                $inst = new wpdreamsCompatibility();
            }
            return $inst;
        }

        function __construct() {
            $this->errorNum = 0;
            $this->errors = array();
            $this->consequences = array();
            $this->solutions = array();
        }

        function has_errors() {
            return (count($this->errors) > 0 ? true : false);
        }

        function get_last_error() {
            if ($this->has_errors()) {
                return array(end($this->errors), end($this->consequences), end($this->solutions));
            }
            return false;
        }

        function get_errors() {
            if ($this->has_errors()) {
                return array('errors' => $this->errors, 'cons' => $this->consequences, 'solutions' => $this->solutions);
            }
            return false;
        }

        function check_dir_w($path, $cons = '') {
            $writeable = is_writeable($path);
            if ($writeable === false) {
                $this->errors[] = "The <b>" . $path . "</b> directory is not writeable!";
                $this->consequences[] = $cons;
                $this->solutions[] = "
            Use an ftp clien to chmod (change permissions) the <b>" . $path . "</b> directory to 666, 755, or 777<br />
            Read <a href='http://www.siteground.com/tutorials/ftp/ftp_chmod.htm'>this siteground</a> article if you need help.
          ";
            }
            return $writeable;
        }

        function can_open_url($cons = '') {
            if (function_exists('curl_init')) {
                return true;
            } else if (ini_get('allow_url_fopen') == true) {
                return true;
            }
            $this->errors[] = "Curl and url fopen is disabled on this server!";
            $this->consequences[] = $cons;
            $this->solutions[] = "You might need to contact the server administrator to resolve this issue for you.";
            return false;
        }

        function can_write_files() {
            $access_type = get_filesystem_method();
            if ($access_type === 'direct')
                return true;

            $this->errors[] = "WordPress does not have access to it's own file system!";
            $this->consequences[] = "Images, CSS Files, Cache Files will be included with an alternative inline method, which is slower.";
            $this->solutions[] = "
        <ol>
        <li>
        You will have to fill in FTP credentials into the wp-config.php file.<br><br>
        <strong>define( 'FTP_USER', 'username' );<br>
        define( 'FTP_PASS', 'password' );<br>
        define( 'FTP_HOST', 'ftp.example.org' );</strong> <br><br>
        In some cases more constants might be required. <br>
        Please read: <a href='https://codex.wordpress.org/Editing_wp-config.php#WordPress_Upgrade_Constants'>WordPress Upgrage Constants</a> 
        </li>
        <li>
        ADVANCED USERS ONLY!<br><br>
        Change ownership of the WordPress directory and all of its contents via SSH for the <strong>www-data</strong> process.<br><br>
        
        <a href='http://stackoverflow.com/questions/18352682/correct-file-permissions-for-wordpress'>Changing Ownership if WordPress is in the ROOT directory</a>
        </li>
        </ol>";
            return false;
        }
    }
}