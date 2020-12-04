<?php

/**
 * By: Fakhri Alsadi
 * Date: 2/2/2015
 * Time: 12:45 PM
 * 
 * Dependencies:
 * In: 
 * Out: security
 */
if(!class_exists('cf_browser_1')){
    class cf_browser_1 {
        
        private $cf;
        private $security;
        
        /* Set the object's parent cf to access all objects ------------- */        
        public function set_cf($cf)
        {
            $this->cf=$cf;
            $this->security= call_user_func(array($cf, 'get_security'));
            //$this->security = $cf::get_security();
        }
    
        public function get_referrer()
        {
            if(array_key_exists('HTTP_REFERER',$_SERVER))
            {
                return $this->security->sanitize($_SERVER['HTTP_REFERER'], 'URL');
            }
            else
            {
                return '';
            }
        }

        /* get_visitor_IP ------------------------------------------------------  */
        public function get_visitor_IP()
        {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            return $ipaddress ;
        }

        /* get_visitor_OS ------------------------------------------------------  */
        public function get_visitor_OS()
        {
            $userAgent= $_SERVER['HTTP_USER_AGENT'];
            $oses = array (
                'iPhone' => '(iPhone)',
                'Windows 3.11' => 'Win16',
                'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
                'Windows 98' => '(Windows 98)|(Win98)',
                'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
                'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
                'Windows 2003' => '(Windows NT 5.2)',
                'Windows Vista' => '(Windows NT 6.0)|(Windows Vista)',
                'Windows 7' => '(Windows NT 6.1)|(Windows 7)',
                'Windows 8' => '(Windows NT 6.2)|(Windows 8)',
                'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
                'Windows ME' => 'Windows ME',
                'Open BSD'=>'OpenBSD',
                'Sun OS'=>'SunOS',
                //'Linux'=>'(Linux)|(X11)', to detect if android or not
                'Safari' => '(Safari)',
                'Macintosh'=>'(Mac_PowerPC)|(Macintosh)',
                'QNX'=>'QNX',
                'BeOS'=>'BeOS',
                'OS/2'=>'OS\/2',
                'SearchBot'=>'(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp\/cat)|(msnbot)|(ia_archiver)'
            );
            foreach($oses as $os=>$pattern){
                if(preg_match('/'.$pattern. '/i', $userAgent)) {
                    return $os;
                }
            }
            // more tests
            $ua = strtolower($userAgent);
            if(stripos($ua,'android') !== false) {
                return 'Android';
            }
            if(stripos($ua,'iphone') !== false) {
                return 'iOS';
            }
            if(stripos($ua,'ipad') !== false) {
                return 'iOS';
            }
            if(stripos($ua,'ipod') !== false) {
                return 'iOS';
            }
            if(stripos($ua,'windows') !== false) {
                return 'Windows';
            }
            if(stripos($ua,'linux') !== false) {
                return 'Linux';
            }
            if(stripos($ua,'googlebot') !== false) {
                return 'Googlebot';
            }
            if(stripos($ua,'bot') !== false) {
                return 'SearchBot';
            }
            return 'Unknown';
        }

        /* get_visitor_browser ------------------------------------------------------  */
        public function get_visitor_browser()
        {
            $u_agent= $_SERVER['HTTP_USER_AGENT'];
            $bname = 'Unknown';
            if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
            {
                $bname = 'Internet Explorer';
            }
            elseif(preg_match('/Firefox/i',$u_agent))
            {
                $bname = 'Firefox';
            }
            elseif(preg_match('/Chrome/i',$u_agent))
            {
                $bname = 'Chrome';
            }
            elseif(preg_match('/Safari/i',$u_agent))
            {
                $bname = 'Safari';
            }
            elseif(preg_match('/Opera/i',$u_agent))
            {
                $bname = 'Opera';
            }
            elseif(preg_match('/Netscape/i',$u_agent))
            {
                $bname = 'Netscape';
            }
            elseif(preg_match('/googlebot/i',$u_agent))
            {
                $bname = 'GoogleBot';
            }
            elseif(preg_match('/bot/i',$u_agent))
            {
                $bname = 'SearchBot';
            }
            return $bname;
        }

        /* get_visitor_country ------------------------------------------------------  */
        public function get_visitor_country()
        {
            $client  = @$_SERVER['HTTP_CLIENT_IP'];
            $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
            $remote  = $_SERVER['REMOTE_ADDR'];
            $result  = "Unknown";
            if(filter_var($client, FILTER_VALIDATE_IP))
            {
                $ip = $client;
            }
            elseif(filter_var($forward, FILTER_VALIDATE_IP))
            {
                $ip = $forward;
            }
            else
            {
                $ip = $remote;
            }
            $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));
            if($ip_data && $ip_data->geoplugin_countryName != null)
            {
                $result = $ip_data->geoplugin_countryName;
            }
            return $result;
        }


        
    }
}