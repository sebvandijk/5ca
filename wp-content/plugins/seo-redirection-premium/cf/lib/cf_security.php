<?php
/**
 * By: Fakhri Alsadi
 * Date: 2/2/2015
 * Time: 12:45 PM
 * 
 * Dependencies:
 * In: request,tabs
 * Out: -
 */
if(!class_exists('cf_security_2')){
    class cf_security_2 {
        
        private $cf;
        
        /* Set the object's parent cf to access all objects ------------- */        
        public function set_cf($cf)
        {
            $this->cf=$cf;
        }
    
        /* sanitize ----------------------------------------------------------  */
        public function sanitize($unsafe_val,$type='text')
        {
            switch ($type) {
                case 'text': return sanitize_text_field($unsafe_val);
                    break;
                case 'int': return intval($unsafe_val);
                    break;
                case 'email': return sanitize_email($unsafe_val);
                    break;
                case 'filename': return sanitize_file_name($unsafe_val);
                    break;
                case 'title': return sanitize_title($unsafe_val);
                    break;
                case 'URL': return esc_url($unsafe_val);
                    break;
                case 'textbox': return htmlentities(sanitize_text_field($unsafe_val),ENT_QUOTES);
                    break;
                default:
                    return sanitize_text_field($unsafe_val);
            }
        }

        /* anti_XSS ------------------------------------------------------------  */
        public function anti_XSS($input)
        {
            return htmlspecialchars($input, ENT_QUOTES);
        }
        
        /* encrypt ------------------------------------------------------------  */
        function encrypt($text, $salt = "clogica_salt")
        {
		
          // return trim(base64_encode(openssl_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
           return trim(base64_encode($text));
        }
        
        /* decrypt ------------------------------------------------------------  */
        function decrypt($text, $salt = "clogica_salt")
        {
           //return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
           return trim(base64_decode($text));
        }
        
        /* hide url -----------------------------------------------------------  */
        function hide_url($URL)
        { 
            //
        }
        
        /* show url -----------------------------------------------------------  */
        function show_url($URL)
        {
            //
        }
        
        /* encode url -----------------------------------------------------------  */
        function encode_url($URL)
        {
            $str = urlencode($URL);
            $str = str_replace('.', '%2E', $str);
            $str = str_replace('-', '%2D', $str);
            return $str;
        }
        
        
    }
}
