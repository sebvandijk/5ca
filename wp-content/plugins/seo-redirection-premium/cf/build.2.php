<?php
/**
 * By: Fakhri Alsadi
 * Date: 2/2/2015
 * Time: 12:46 PM
 */



require_once "lib/cf.pagination.class.php";

require_once "lib/cf.where_st.class.php";
require_once "lib/cf.jforms.class.php";
require_once "lib/forms/cf.bcheckbox_option.class.php";
require_once "lib/forms/cf.dropdownlist.class.php";
require_once "lib/forms/cf.color_picker.class.php";
require_once "lib/forms/cf.switch_option.class.php";
require_once "lib/forms/cf.file_chooser.class.php";

// new objects ------------------------------------
require_once "lib/cf_app.php";
require_once "lib/cf_options.php";
require_once "lib/cf_browser.php";
require_once "lib/cf_htaccess.php";
require_once "lib/cf_request.php";
require_once "lib/cf_response.php";
require_once "lib/cf_security.php";
require_once "lib/cf_forms.php";
require_once "lib/cf_misc.php";
require_once "lib/cf_tabs.php";


if(!class_exists('cf_build_2')){
class cf_build_2{  
    
    /**
     * @var cf_app_2
     */
    private static $app;
    
    /**
     * @var cf_options_1
     */
    private static $options;
    
    /**
     * @var cf_request_2
     */
    private static $request;
    
    /**
     * @var cf_response_1
     */
    private static $response;
    
    /**
     * @var cf_security_2
     */
    private static $security;
    
    /**
     * @var cf_browser_1
     */
    private static $browser;
    
    /**
     * @var cf_htaccess_1
     */
    private static $htaccess;
    
    /**
     * @var cf_forms_1
     */
    private static $forms;
    
    /**
     * @var cf_misc_1
     */
    private static $misc;
    
     /**
     * @var cf_tabs_1
     */
    private static $tabs;
    
    //------------------------------------------------------
    public static function get_app()
    {
        if(!is_object(self::$app)){
           self::$app= new cf_app_2();
           self::$app->set_cf(__CLASS__);
        }         
        return self::$app;
    }
    
    //------------------------------------------------------
    public static function get_options()
    {
        if(!is_object(self::$options)){
           self::$options= new cf_options_1();
           self::$options->set_cf(__CLASS__);
           
        }         
        return self::$options;
    }
    //------------------------------------------------------
    public static function get_request()
    {
        if(!is_object(self::$request)){
           self::$request= new cf_request_2(); 
           self::$request->set_cf(__CLASS__);
        }         
        return self::$request;
    }
    //------------------------------------------------------
    public static function get_response()
    {
        if(!is_object(self::$response)){
           self::$response= new cf_response_1(); 
           self::$response->set_cf(__CLASS__);
        }         
        return self::$response;
    }
    //------------------------------------------------------
    public static function get_security()
    {
        if(!is_object(self::$security)){
           self::$security= new cf_security_2(); 
           self::$security->set_cf(__CLASS__);
        }         
        return self::$security;
    }
    //------------------------------------------------------
    public static function get_browser()
    {
        if(!is_object(self::$browser)){
           self::$browser= new cf_browser_1(); 
           self::$browser->set_cf(__CLASS__);
        }         
        return self::$browser;
    }
    //------------------------------------------------------
    public static function get_htaccess()
    {
        if(!is_object(self::$htaccess)){
           self::$htaccess = new cf_htaccess_1(); 
           self::$htaccess->set_cf(__CLASS__);           
        }         
        return self::$htaccess;
    }
    //------------------------------------------------------
    public static function get_forms()
    {
        if(!is_object(self::$forms)){
           self::$forms= new cf_forms_1();
           self::$forms->set_cf(__CLASS__);
        }         
        return self::$forms;
    }
    //------------------------------------------------------
    public static function get_misc()
    {
        if(!is_object(self::$misc)){
           self::$misc= new cf_misc_1(); 
           self::$misc->set_cf(__CLASS__);
        }         
        return self::$misc;
    }
    //------------------------------------------------------
    public static function get_tabs()
    {
        if(!is_object(self::$tabs)){
           self::$tabs= new cf_tabs_1(); 
           self::$tabs->set_cf(__CLASS__);
        }         
        return self::$tabs;
    }
    
    
}
}