<?php
/**
 * Author: Fakhri Alsadi
 * Date: 2/24/2015
 * Time: 10:39 AM
 */
if(!class_exists('switch_option')){
class switch_option {

    function __construct($name="" , $value, &$jforms=null)
    {
        if($name!="")
        {
            $this->create_switch_option($name,$value,$jforms);
        }
    }

    public function create_switch_option($name, $value, &$jforms=null)
    {
        if($value == "on")
        {
            $value="checked";
        }else
        {
            $value="";
        }

        echo "<input type=\"checkbox\" name=\"$name\" id=\"$name\" $value data-size=\"small\" data-on-color=\"success\" data-off-color=\"danger\">";

        if(!is_null($jforms))
        {
            $jforms->add_switch($name);
        }
    }

}}