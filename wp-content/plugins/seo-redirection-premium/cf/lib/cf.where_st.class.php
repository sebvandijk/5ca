<?php
/*
Author: Fakhri Alsadi
Date: 16-7-2010
Contact: www.clogica.com   info@clogica.com    mobile: +972599322252

A simple class to create where statement for SQL queries easily using PHP

---------------------------------------------------------
example:
---------------------------------------------------------

$wherest = new where_st();
$wherest->add_param("and", "binary tawajoh like '%$msn%' ");
$wherest->add_text( "some text ");
$wherest->get_statment();

*/



if(!class_exists('where_st')){
    class where_st{

        private $where='';

        /* ------------------------------------------------- */
        public function add_param($op , $value)
        {
            if($this->where == '')
                $this->where=" where  " . $value . " ";
            else
                $this->where= $this->where . " " . $op . " " . $value;
        }

        /* ------------------------------------------------- */
        public function add_text($value)
        {
            $this->where=$this->where . $value ;
        }

        /* ------------------------------------------------- */
        public function get_statment()
        {
            return $this->where;
        }

    }}

?>