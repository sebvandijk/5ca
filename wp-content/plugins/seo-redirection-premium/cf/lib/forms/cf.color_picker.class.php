<?php
/**
 * Author: Fakhri Alsadi
 * Date: 2/24/2015
 * Time: 10:39 AM
 */

if(!class_exists('color_picker')){
class color_picker {

    function __construct($name="", $value="" , &$jforms=null)
    {
        if($name!="")
        {
            $this->create_color_picker($name,$value,$jforms);
        }
    }

    public function create_color_picker($name, $value="", &$jforms=null)
    {
        echo "<input type=\"text\"  class=\"form-control color_picker\" value=\"$value\" name=\"$name\" id=\"$name\" data-control=\"saturation\">";

        if(!is_null($jforms))
        {
            $jforms->add_color_picker();
        }
    }

}}