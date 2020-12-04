<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_License")) {
    /**
     * Class WD_ASP_License
     *
     * License checking and activation class
     *
     * @class         WD_ASP_License
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Etc
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_License {

        static $url = "http://update.wp-dreams.com/a.php";

        static function activate( $license_key ) {
            $url = rawurlencode( $_SERVER['HTTP_HOST'] );
            $key = rawurlencode( $license_key );

            $url = WD_ASP_License::$url . '?url=' . $url . '&key=' . $key . "&op=activate&p=asp";

            try {
                $response = @wp_remote_get( $url );
            } catch (Exception $e) {
                return false;
            }

            if ( is_wp_error( $response ) ) {
                return false;
            }

            $data = json_decode( $response['body'], true );

            // something went wrong
            if ( empty($data) ) return false;

            if ( isset($data['status']) && $data['status'] == 1 )
                update_option("asp_update_data", array(
                    "key"  => $license_key,
                    "host" => $_SERVER['HTTP_HOST']
                ));

            return $data;
        }

        static function deactivate( $remote_check = true ) {
            $data = false;

            if ( $remote_check )
                if (false !== ($key = WD_ASP_License::isActivated())) {
                    $url = rawurlencode( $_SERVER['HTTP_HOST'] );
                    $key = rawurlencode( $key );

                    $url = WD_ASP_License::$url . '?url=' . $url . '&key=' . $key . "&op=deactivate";
                    $response = wp_remote_get( $url );

                    if ( is_wp_error( $response ) ) {
                        return false;
                    }
                    $data = json_decode( $response['body'], true );
                }

            delete_option("asp_update_data");
            return $data;
        }

        static function deactivateRemote( $key, $url ) {
            $url = rawurlencode( $url );
            $key = rawurlencode( $key );

            $url = WD_ASP_License::$url . '?url=' . $url . '&key=' . $key . "&op=deactivate";
            $response = wp_remote_get( $url );

            if ( is_wp_error( $response ) ) {
                return false;
            }
            $data = json_decode( $response['body'], true );

            return $data;
        }

        static function isActivated( $remote_check = false ) {
            $data = get_option("asp_update_data");

            if ( $data === false || !isset($data['host']) || !isset($data['key']) ) return false;

            if ( $remote_check ) {
                $url = rawurlencode( $_SERVER['HTTP_HOST'] );
                $key = rawurlencode( $data['key'] );

                $url = WD_ASP_License::$url . '?url=' . $url . '&key=' . $key . "&op=check";

                $response = wp_remote_get( $url );

                if ( is_wp_error( $response ) ) {
                    return false;
                }

                $rdata = json_decode( $response['body'], true );

                return $rdata['status'] == 1 ? $data['key'] : false;
            }

            return $data['key'];
        }

    }
}