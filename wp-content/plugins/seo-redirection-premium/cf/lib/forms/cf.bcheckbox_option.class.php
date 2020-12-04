<?php
/*
Author: Fakhri Alsadi
Date: 24-4-2015

A simple class to create checkbox options
*/

if(!class_exists('bcheckbox_option')){
    class bcheckbox_option{

        private $group_name="";
        private $list_nodes="";
        private $list_selected_nodes = array();
        private $style = "default";

        /* set style ------------------------------------------------------------------- */
        public function set_style($style)
        {
            $this->style = $style;
        }
        /* Get style ------------------------------------------------------------------- */
        public function get_style()
        {
            return $this->style;
        }

        /* default style ------------------------------------------------------------------- */
        public function set_default_style()
        {
           $this->set_style("default");
        }

        /* default style ------------------------------------------------------------------- */
        public function set_primary_style()
        {
            $this->set_style("primary");
        }


        /* check all css class ------------------------------------------------------------------- */
        public function get_check_all_css_class()
        {
            return 'bcheckbox_check_all';
        }

        /* uncheck all css class ------------------------------------------------------------------- */
        public function get_uncheck_all_css_class()
        {
            return 'bcheckbox_unckeck_all';
        }

        /* get check uncheck attribute ----------------------------------------------------------*/
        public function get_check_uncheck_attribute($group_name)
        {
            return "data-group=\"$group_name\"";
        }

        /* define a group -------------------------------------------------------------------*/
        public function set_group($group_name)
        {
            $this->group_name=$group_name;
        }

        /* define a list -------------------------------------------------------------------*/
        public function set_list($list_name)
        {
            $this->set_group($list_name);
            $this->list_nodes= array();
        }

        /* reset group -------------------------------------------------------------------*/
        public function reset_group()
        {
            $this->group_name="";
        }

        /* reset list -------------------------------------------------------------------*/
        public function reset_list()
        {
            $this->reset_group();
            $this->list_nodes="";
        }

        /* is group -------------------------------------------------------------------*/
        public function is_group()
        {
            return($this->group_name!="");
        }

        /* is list -------------------------------------------------------------------*/
        public function is_list()
        {
            return (is_array($this->list_nodes) && $this->is_group());
        }

        /* get group name -------------------------------------------------------------------*/
        public function get_group_name()
        {
            return $this->group_name ;
        }

        /* get list name -------------------------------------------------------------------*/
        public function get_list_name()
        {
            return $this->get_group_name();
        }

        /* add to list ----------------------------------------------------------------*/
        public function add_to_list($name, $title)
        {
            $index= count($this->list_nodes);
            $this->list_nodes[$index]['name']= $name;
            $this->list_nodes[$index]['title']= $title;
        }

        /* Selected items -----------------------------------------------------------------------------*/
        public function selected_items($items)
        {
            if(!is_array($items))
            {
                $items = explode(",",$items);
            }
            $this->list_selected_nodes = $items;
        }

        /* Create list -------------------------------------------------------------*/
        public function create_list_option()
        {
           if($this->is_list())
           {
               for($i=0;$i<count($this->list_nodes);$i++)
               {
                   $checked=0;
                   if(array_search($this->list_nodes[$i]['name'],$this->list_selected_nodes)!==false)
                   {
                       $checked=1;
                   }
                   echo "<div style=\"margin-top: 3px\">";
                   $this->create_grouped_option($this->list_nodes[$i]['name'],$checked);
                   echo " {$this->list_nodes[$i]['title']}</div>";
               }
           }else
           {
               echo __("You have no lists to create!",'wsr');
           }
        }

        /* create grouped option ---------------------------------------------------------*/
        public function create_grouped_option($value, $checked=0)
        {
            if($this->is_group())
            {
                $this->create_option($this->get_group_name(),$value,$checked);
            }else
            {
                echo __("To use this function you must previously used set_group()",'wsr');
            }
        }

        /* create grouped option ------------------------------------------------------------------ */
        public function create_check_all_option($value='bcheckbox_check_unckeck_all', $checked=0)
        {
            if($this->is_group())
            {
                $this->create_option($this->get_group_name(),$value,$checked,'bcheckbox_check_unckeck_all');
            }else
            {
                echo __("To use this function you must previously used set_group()",'wsr');
            }
        }

        /* Create check all button ---------------------------------------------------------------*/
        public function create_check_all_button($title="Check All")
        {
            if($this->is_group())
            {
                echo "<a {$this->get_check_uncheck_attribute($this->get_group_name())} class=\"btn btn-{$this->get_style()} btn-xs {$this->get_check_all_css_class()}\">
                        <span class=\"glyphicon glyphicon-check\"></span> $title</a>";
            }else
            {
                echo __("To use this function you must previously used set_group()",'wsr');
            }
        }


        /* Create check all button ---------------------------------------------------------------*/
        public function create_uncheck_all_button($title="None")
        {
            if($this->is_group())
            {
                echo "<a {$this->get_check_uncheck_attribute($this->get_group_name())} class=\"btn btn-{$this->get_style()} btn-xs {$this->get_uncheck_all_css_class()}\">
                        <span class=\"glyphicon glyphicon-unchecked\"></span> $title</a>";
            }else
            {
                echo __("To use this function you must previously used set_group()",'wsr');
            }
        }


        /* Create single check box ------------------------------------------------------------------*/
        public function create_single_option($name,$checked=0)
        {
            if($checked!=0)
            {
                $checked=1;
            }
            $this->create_option($name,1,$checked);
        }

        /* Create Check Option -------------------------------------------------------------------*/
        private function create_option($name, $value, $checked=0, $type='')
        {
            $check_html = "";
            $check_class ="";
            $brackets="";
            $data_group="";
            $id=$name;
            if($this->is_group())
            {
                $data_group="data-group=\"{$name}\"";
                $brackets="[]";
                $id= $name . '_' . str_replace(' ','',$value);
            }
            if($type!='')
            {
                $brackets= "_" . $type;
                $name= $name . "_" . $type;
            }
            if($checked==1)
            {
                $check_html = "checked=\"checked\"";
                $check_class = "active";
            }
                echo "<div data-toggle=\"buttons\" {$data_group} data-id=\"{$id}\" class=\"btn-group bcheckbox $type\">
                         <label class=\"btn btn-{$this->get_style()} btn-xs {$check_class} {$name}\" id=\"{$id}_label\" >
                            <input type=\"checkbox\" autocomplete=\"off\" value=\"{$value}\" id=\"{$id}\" name=\"{$name}{$brackets}\" $check_html>
                            <span class=\"glyphicon glyphicon-ok\"></span>
                         </label>
                      </div>";
        }



    }
}
