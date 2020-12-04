<?php
/**
 * By: Fakhri Alsadi
 * Date: 2/2/2015
 * Time: 12:45 PM
 * 
 * Dependencies:
 * In: tabs,options
 * Out: -
 */
if(!class_exists('cf_request_2')){
    class cf_request_2 {
        
        private $cf;
        private $security;
        
        /* Set the object's parent cf to access all objects ------------- */        
        public function set_cf($cf)
        {
            $this->cf=$cf;
            $this->security= call_user_func(array($cf, 'get_security'));
            //$this->security = $cf::get_security();
            
        }
    
        /* get ---------------------------------------------------------------  */
        public function get($key, $type='text', $array_delimiter=',',$escape=false)
        {
            if(array_key_exists($key,$_GET))
            {
                $value = $_GET[$key];
                if(is_array($value))
                {
                    $value = implode($array_delimiter,$value);
                }
                if(!$escape)
                {
                    return $this->security->sanitize($value,$type);
                }else
                {
                    return $value;
                }
            }
            else
            {
                return '';
            }
        }

        /* post ---------------------------------------------------------------  */
        public function post($key, $type='text', $array_delimiter=',', $escape=false)
        {
            if(array_key_exists($key,$_POST))
            {
                $value = $_POST[$key];
                if(is_array($value))
                {
                    $value = implode($array_delimiter,$value);
                }
                if(!$escape)
                {
                    return $this->security->sanitize($value,$type);
                }else
                {
                    return $value;
                }
            }else
            {
                return '';
            }
        }

        /* get_current_parameters ----------------------------------------------  */
        public function get_current_parameters($remove_parameter="")
        {
            if($_SERVER['QUERY_STRING']!='')
            {
                $qry = '?' . urldecode($_SERVER['QUERY_STRING']);
                if(is_array($remove_parameter))
                {
                    for($i=0;$i<count($remove_parameter);$i++)
                    {
                        if(array_key_exists($remove_parameter[$i],$_GET)){
                            $string_remove = '&' . $remove_parameter[$i] . "=" . urldecode($_GET[$remove_parameter[$i]]);
                            $qry=str_ireplace($string_remove,"",$qry);
                            $string_remove = '?' . $remove_parameter[$i] . "=" . urldecode($_GET[$remove_parameter[$i]]);
                            $qry=str_ireplace($string_remove,"",$qry);
                        }
                    }
                }else{
                    if($remove_parameter!='')
                    {
                        if(array_key_exists($remove_parameter,$_GET)){
                            $string_remove = '&' . $remove_parameter . "=" . urldecode($_GET[$remove_parameter]);
                            $qry=str_ireplace($string_remove,"",$qry);
                            $string_remove = '?' . $remove_parameter . "=" . urldecode($_GET[$remove_parameter]);
                            $qry=str_ireplace($string_remove,"",$qry);
                        }
                    }
                }
                return $qry;
            }else
            {
                return "";
            }
        }


        /* add_htmlentities_get_filter ----------------------------------------------  */
        function add_htmlentities_get_filter($key)
        {
            if($this->get($key)!="")
                $_GET[$key]=htmlentities($this->get($key,'title'));
        }

        /* add_htmlentities_post_filter ----------------------------------------------  */
        function add_htmlentities_post_filter($key)
        {
            if($this->post($key)!="")
                $_POST[$key]=htmlentities($this->post($key,'title'));
        }

        /* get_current_URL ----------------------------------------------  */
        public function get_current_URL()
        {
            $pageURL = 'http';
            if ( array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"] == "on")
            {
                $pageURL .= "s";
            }
            $pageURL .= "://";

            if (array_key_exists("SERVER_PORT",$_SERVER) && $_SERVER["SERVER_PORT"] != "80") {
                $pageURL .= $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
            }
            return $pageURL;

        }

        /* valid_url ----------------------------------------------  */
        public function is_valid_url($url)
        {
            if(stripos($url,'://')!== false || substr($url,0, 1)=='/')
            {
                return true;
            }else{
                return false;
            }
        }


        //-----------------------------------------------------

        public function remove_url_http_www($url)
        {
            if(is_ssl())
            {
                $url = str_ireplace("https://www.",'',$url);
                $url = str_ireplace("https://",'',$url);
                $url = str_ireplace(":443",'',$url);
                return $url;
            }else
            {
                $url = str_ireplace("http://www.",'',$url);
                $url = str_ireplace("http://",'',$url);
                return $url;
            }
            return "";
        }
        //-----------------------------------------------------
        public function make_relative_url($url)
        {
            if($url=="")
            {
                return "";
            }
            $site_url = $this->remove_url_http_www(site_url());
            
            if(stripos($url,$site_url) !==false)
            {
                $url_no_www = $this->remove_url_http_www($url);
                if(strtolower(substr($url_no_www,0,strlen($site_url))) == strtolower($site_url))
                {
                    $url = str_ireplace($site_url,'',$url_no_www);
                }
            }            
            if($url=="")
            {
                $url="/";
            }
            return $url;
        }

        //----------------------------------------------------
        public function make_absolute_url($url)
        {
            if(substr($url,0,1)=='/')
            {
                $url = site_url() . $url;
            }
            return $url;
        }

        //----------------------------------------------------

        public function get_current_relative_url()
        {
            return $this->make_relative_url($this->get_current_URL());
        }
        //----------------------------------------------------

        
    }
}
