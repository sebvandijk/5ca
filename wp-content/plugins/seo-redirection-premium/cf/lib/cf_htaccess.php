<?php
/**
 * Author: Fakhri Alsadi
 * Date: 2/8/2015
 * Time: 9:06 PM
 *
    $htaccess = new clogica_htaccess();
    $htaccess->set_util($util);
    $htaccess->init();
    $htaccess->add_rule('ErrorDocument',
    "
        ErrorDocument 404 /index.php?error=404
        ErrorDocument 404 /index.php?error=302
    ");
    $htaccess->delete_rule('ErrorDocument1');
    $htaccess->update_htaccess();
 *
 * Dependencies:
 * In: 
 * Out: app
 */

if(!class_exists('cf_htaccess_1')){
    class cf_htaccess_1 {

        private $rules;
        private $htaccess='';
        private $cf;
        private $app;

                
        /* Constructor -------------------------------------- */
        public function init()
        {
            $this->read_htaccess();
            $this->rules = array();
        }
        
        /* ------------------------------------------------------------------- */   
        public function set_cf($cf)
        {
            $this->cf=$cf;
            $this->app= call_user_func(array($cf, 'get_app'));
            //$this->app=$cf::get_app();
        }

        /* Add Rule ----------------------------------------- */
        public function add_rule($rule_name,$rule_content)
        {
            if(is_array($this->rules))
            {
                if($this->is_added_rule($rule_name)===false)
                {
                    $index=count($this->rules);
                    $this->rules[$index]['rule_name']=$rule_name;
                    $this->rules[$index]['rule_content']=$rule_content;
                    unset($index);
                }
            }else
            {
                $this->rules[0]['rule_name']=$rule_name;
                $this->rules[0]['rule_content']=$rule_content;
            }
        }       

        /* Check Rule ----------------------------------------- */
        public function is_added_rule($rule_name)
        {
             for($i=0;$i<count($this->rules);$i++)
             {
                 if($this->rules[$i]['rule_name']==$rule_name)
                 {
                     return $i;
                 }
             }
            return false;
        }

        /* Check Rule ----------------------------------------- */
        public function is_saved_rule($rule_name)
        {
            return (strpos($this->htaccess , $this->begin_marker($rule_name)) !== false);
        }

        /* Delete Saved Rule --------------------------------------- */
        public function delete_saved_rule($rule_name)
        {
            if($this->is_saved_rule($rule_name))
            {
                $cut_from = strpos($this->htaccess , $this->begin_marker($rule_name)) - 1;
                $cut_to = strpos($this->htaccess , $this->end_marker($rule_name)) + 1;
                $str_from = substr($this->htaccess,0,$cut_from);
                $str_to = substr($this->htaccess,($cut_to + strlen($this->end_marker($rule_name))),strlen($this->htaccess));
                $this->htaccess = $str_from . $str_to;
            }            
            $this->delete_rule($rule_name);
        }

        /* Delete Rule --------------------------------------- */
        public function delete_rule($rule_name)
        {
            if($this->is_added_rule($rule_name)!==false)
            {
                $index = $this->is_added_rule($rule_name);
                $this->rules[$index]['rule_name'] = '';
                $this->rules[$index]['rule_content'] = '';
                unset($index);
            }
        }

        /* Format Rule Content --------------------------------------- */
        public function format_rule_content($rule_name,$rule_content)
        {
            $frule = "\n" . $this->begin_marker($rule_name) . "\n"  . $rule_content  . "\n" . $this->end_marker($rule_name) . "\n";
            return $frule;
        }

        /* Begin marker -------------------------------------- */
        public function begin_marker($rule_name)
        {
            return "# BEGIN [". $rule_name ."]";
        }

        /* End marker ----------------------------------------- */
        public function end_marker($rule_name)
        {
            return "# END [". $rule_name ."]";
        }

        /* Get Rule ------------------------------------------ */
        public function get_rule($rule_name)
        {
            $cut_from = strpos($this->htaccess , $this->begin_marker($rule_name));
            $cut_to = strpos($this->htaccess , $this->end_marker($rule_name));
            return substr($this->htaccess,$cut_from ,$cut_to - $cut_from + strlen($this->end_marker($rule_name)));
        }

        /* Get Rule Content ---------------------------------- */
        public function get_rule_content($rule_name)
        {
            $cut_from = strpos($this->htaccess , $this->begin_marker($rule_name));
            $cut_to = strpos($this->htaccess , $this->end_marker($rule_name));
            return substr($this->htaccess, $cut_from + strlen($this->begin_marker($rule_name)),$cut_to - ($cut_from + strlen($this->begin_marker($rule_name)) ) );
        }

        /* Htaccess path ------------------------------------- */
        public function htaccess_contains($needle)
        {
            return (strpos($this->htaccess , $needle) !== false);
        }

        /* Htaccess path ------------------------------------- */
        public function htaccess_path()
        {           
            return $this->app->get_home_path() . "/.htaccess";          
        }

        /* Read Htaccess ------------------------------------- */
        public function read_htaccess()
        {
            if(file_exists($this->htaccess_path()))
            {
               $this->htaccess= @file_get_contents($this->htaccess_path(), false, NULL);
            }else
            {
                @file_put_contents($this->htaccess_path(), '');
            }
        }

        /* Update Htaccess ------------------------------------- */
        public function update_htaccess($ontop=1)
        {
            $all_rules = '';
            if(is_array($this->rules) && count($this->rules)>0)
            {
                for($i=0;$i<count($this->rules);$i++)
                {
                    if($this->rules[$i]['rule_name']!='')
                    {
                        $all_rules = $all_rules . $this->format_rule_content($this->rules[$i]['rule_name'], $this->rules[$i]['rule_content']);
                        if($this->is_saved_rule($this->rules[$i]['rule_name']))
                        {
                           $this->delete_saved_rule($this->rules[$i]['rule_name']);
                        }
                    }
                }
                if($ontop==0)
                {
                  $this->htaccess = $this->htaccess . $all_rules;  
                }else
                {
                  $this->htaccess = $all_rules . $this->htaccess ;  
                }
               
                unset($all_rules);              
            }
            $this->save_htaccess();
        }
         
        /* Save htaccess ------------------------------------ */
        public function save_htaccess() {
            $res=@file_put_contents($this->htaccess_path(), $this->htaccess , LOCK_EX);
        }
        /* Truncate ----------------------------------------- */
        public function truncate()
        {
            unset($this->rules);
            $this->rules = array();
        }

    }}