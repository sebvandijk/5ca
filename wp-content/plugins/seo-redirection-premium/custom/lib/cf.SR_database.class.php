<?php

if(!class_exists('SR_database')){
    class SR_database {
        
        public static function WP_SEO_Redirection()
        {
            global $wpdb;
            return $wpdb->base_prefix . 'WP_SEO_Redirection';
        }

        public static function WP_SEO_Redirection_LOG()
        {
            global $wpdb;
            return $wpdb->base_prefix . 'WP_SEO_Redirection_LOG';
        }

        public static function WP_SEO_Groups()
        {
            global $wpdb;
            return $wpdb->base_prefix . 'WP_SEO_Groups';
        }

        public static function WP_SEO_Cache()
        {
            global $wpdb;
            return $wpdb->base_prefix . 'WP_SEO_Cache';
        }

        public static function WP_SEO_404_links()
        {
            global $wpdb;
            return $wpdb->base_prefix . 'WP_SEO_404_links';
        }
        
        public static function util()
        {
            return new clogica_util_1();
        }
    }
}