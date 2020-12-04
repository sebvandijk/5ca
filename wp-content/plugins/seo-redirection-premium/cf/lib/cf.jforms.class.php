<?php

if(!class_exists('jforms')) {
    class jforms
    {
        private $forms = array();
        private $scripts = array();

        public function add_form($selector,$function, $options="")
        {
            $index = count($this->forms);

             if(!$this->selector_exists($selector))
             {
                 $this->forms[$index]['selector']=$selector;
                 $this->forms[$index]['function']=$function;
                 $this->forms[$index]['options']=$options;
             }
        }

        public function add_script($script)
        {
            $index = count($this->scripts);
            $this->scripts[$index]=$script;
        }

        public function selector_exists($selector)
        {
           for($i=0;$i<count($this->forms);$i++)
           {
               if($this->forms[$i]['selector']==$selector)
               {
                   return true;
               }
           }
            return false;
        }

        public function add_select_picker()
        {
            $this->add_form(".selectpicker","selectpicker");
        }

        public function add_switch($name)
        {
            $this->add_form("[name='" . $name . "']","bootstrapSwitch");
        }

        public function add_color_picker()
        {
            $this->add_form(".color_picker","minicolors");
        }

        public function hide_alerts($stime=3000,$selector='.alert')
        {
            //$this->add_script("$('$selector').delay($stime).slideUp('slow');");
        }

        public function set_small_select_pickers()
        {
            $this->add_script("$('.selectpicker').addClass('btn-sm');");
        }

        public function run()
        {
            $functions="";
            $scripts="";

            for($i=0;$i<count($this->forms);$i++)
            {
                $functions= $functions . "\n" .  '$("' . $this->forms[$i]['selector']  . '").' . $this->forms[$i]['function'];
                if($this->forms[$i]['options']=='')
                {
                    $functions = $functions . "();";
                }
                else
                {
                    $functions = $functions . "({
                    " . $this->forms[$i]['options'] . "
                    });";
                }
            }

            for($i=0;$i<count($this->scripts);$i++)
            {
                $scripts = $scripts . "\n" . $this->scripts[$i];
            }

echo "<script>
jQuery(document).ready(function($){";
echo $functions;
echo $scripts;
echo "\n
});
</script>";

        }


    }
}