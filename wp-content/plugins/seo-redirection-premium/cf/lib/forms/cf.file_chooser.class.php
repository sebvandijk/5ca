<?php
/*
Author: Fakhri Alsadi
Date: 24-2-2015
*/

if(!class_exists('file_chooser')){
    class file_chooser
    {

        private $name="file";
        private $type="image";

        /*-----------------------------------------*/
        function __construct($name="", $value="" , &$jforms=null)
        {
            if($name!="")
            {
                $this->create_file_chooser($name,$value,$jforms);
            }
        }

        /*-----------------------------------------*/
        public function set_chooser_type($type)
        {
            $this->type = $type;
        }

        /*-----------------------------------------*/
        public function get_chooser_type()
        {
            return $this->type;
        }

        /*-----------------------------------------*/
        public function set_choose_name($name)
        {
            $this->name = $name;
        }

        /*-----------------------------------------*/
        public function get_chooser_name()
        {
            return $this->name;
        }

        /*-----------------------------------------*/
        public function create_file_chooser($name, $value="", &$jforms=null)
        {

            $style="display: block;";
            if($value =="")
            {
                $style="display: none;";
            }

            if($this->type == "image")
            {
                echo "
                <input type=\"text\" class=\"form-control file_chooser_field\" style=\"display: none;\" value=\"{$value}\"  data-type=\"{$this->type}\" name=\"{$name}\" id=\"{$name}\">
                <img src=\"{$value}\" style=\"$style\" id=\"{$name}_preview\" class=\"img-thumbnail img-responsive\"/>
                <a id=\"choose_button\" data-input-field=\"{$name}\" data-type=\"{$this->get_chooser_type()}\" class=\"btn btn-default btn-sm file_chooser\">
                    <i class=\"glyphicon glyphicon-folder-close\"></i> Choose</a>
                <a id=\"empty_button\" class=\"btn btn-danger btn-sm file_chooser_empty\" data-input-field=\"{$name}\" data-type=\"{$this->type}\">
                    <i class=\"glyphicon glyphicon-trash\"></i> Delete</a> ";
            }
            else
            {
                echo "
                <input type=\"text\" class=\"form-control file_chooser_field\" value=\"{$value}\"  data-type=\"{$this->type}\" name=\"{$name}\" id=\"{$name}\">
                <img src=\"\" style=\"$style\" id=\"{$name}_preview\" class=\"img-thumbnail img-responsive\"/>
                <a id=\"choose_button\" data-input-field=\"{$name}\" data-type=\"{$this->get_chooser_type()}\" class=\"btn btn-default btn-sm file_chooser\">
                    <i class=\"glyphicon glyphicon-folder-close\"></i> Choose</a>
                <a id=\"empty_button\" class=\"btn btn-danger btn-sm file_chooser_empty\" data-input-field=\"{$name}\" data-type=\"{$this->type}\">
                    <i class=\"glyphicon glyphicon-trash\"></i> Delete</a> ";
            }


            if(!is_null($jforms))
            {
               /*
                $script = "
                    update_chooser('{$name}', '{$value}', '{$this->get_chooser_type()}');
                ";
                $jforms->add_script($script);
               */
            }

        }

    }
}
