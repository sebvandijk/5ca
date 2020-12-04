<?php

/**
 * By: Fakhri Alsadi
 * Date: 2/2/2015
 * Time: 12:45 PM
 * 
 * Dependencies:
 * In: -
 * Out: -
 */
if(!class_exists('cf_misc_1')){
    class cf_misc_1 {
        
        private $cf;
        
        /* Set the object's parent cf to access all objects ------------- */        
        public function set_cf($cf)
        {
            $this->cf=$cf;
        }
    
        public function js_redirect($link)
        {
            echo "<script type=\"text/javascript\">window.location.href = '$link';</script>";
        } 
        
        /* regex_prepare ---------------------------------------------  */
        public function regex_prepare($string)
        {
            $from= array('.', '+', '*', '?','[','^',']','$','(',')','{','}','=','!','<','>','|',':','-',')','/', '\\');
            $to= array('\\.', '\\+', '\\*', '\\?','\\[','\\^','\\]','\\$','\\(','\\)','\\{','\\}','\\=','\\!','\\<','\\>','\\|','\\:','\\-','\\)','\\/','\\\\');
            return str_replace($from,$to,$string);
        }
    
    
    }
}