<?php

require_once "cf/build.2.php";

class SRP_PLUGIN {
    
    /**
     * @var cf_build_2
     */
    public static $cf;

    
    /*-----------------------------------------------------------*/
    public static function init($option_group_name='clogica_option_group',$plugin_file='')
    {
        self::$cf = new cf_build_2();        
        self::$cf->get_app()->init($plugin_file);
        self::$cf->get_options()->init($option_group_name);
    }
    
    
    //----------------------------------------------------   
    public static function get_app()
    {
        return self::$cf->get_app();
    }
    
    //------------------------------------------------------
    public static function get_options()
    {
        return self::$cf->get_options();   
    }
    
    //----------------------------------------------------
    public static function get_request()
    {
        return self::$cf->get_request();     
    }
    
    //----------------------------------------------------
    public static function get_response()
    {
        return self::$cf->get_response(); 
    }
    
    //----------------------------------------------------
    public static function get_security()
    {
       return self::$cf->get_security(); 
    } 
    
    //---------------------------------------------------- 
    public static function get_browser()
    {
       return self::$cf->get_browser();  
    }
    
    //----------------------------------------------------
    public static function get_htaccess()
    {
        return self::$cf->get_htaccess();
    }
    
    //----------------------------------------------------
    public static function get_forms()
    {
        return self::$cf->get_forms();
    }
    
    //----------------------------------------------------
    public static function get_misc()
    {
        return self::$cf->get_misc();
    }
    
    //----------------------------------------------------
    public static function get_tabs()
    {
        return self::$cf->get_tabs();
    }
    
    
}
