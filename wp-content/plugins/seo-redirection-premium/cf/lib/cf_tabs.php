<?php

/*
 *
 * $phptabs = new clogica_phptabs();
 * $phptabs->set_util($util);
 *
 * $phptabs->add_file_tab('general_options','General Options','option_blank_page.php','file');
 * $phptabs->add_file_tab('help_center','Help Center','option_blank_page.php','file');
 * $phptabs->run();
 *
 * Dependencies:
 * In: -
 * Out: app,request,security
 * */ 

if(!class_exists('cf_tabs_1')){
class cf_tabs_1{

	private $tabs;
	private $parameter = 'tab';
	private $ignore_parameters='';
	private $type="_main";
	private $style_postfix = '_default';
        
        private $app;
        private $request;

        /* Set the object's parent cf to access all objects ------------- */        
        public function set_cf($cf)
        {
            $this->cf=$cf;
            $this->app= call_user_func(array($cf, 'get_app'));
            $this->request= call_user_func(array($cf, 'get_request'));
            //$this->app = $cf::get_app();
            //$this->request = $cf::get_request();            
        }

	/* -------------------------------------------- */
	public function __construct($parameter='tab',$type='_main')
	{
		$this->set_parameter($parameter);
		$this->set_type($type);
		$this->tabs = array();
	}

	/* ---------------------------------------------- */
	public function init()
	{
		$this->tabs = array();
	}

	/* -------------------------------------------- */
	public function add_file_tab($num, $title, $content, $type )
	{
		$index=$this->tabs_count();
		$this->tabs[$index] = array('num' => $num , 'title'=> $title , 'content'=> $content, 'type' => $type );
	}

	/* -------------------------------------------- */
	public function tabs_count()
	{
		if(is_array($this->tabs))
		return count($this->tabs);
		else
		return 0;
	}

	/* -------------------------------------------- */
	public function set_parameter($param)
	{
		$this->parameter = $param;
	}

	/* -------------------------------------------- */
	public function get_parameter()
	{
		return $this->parameter;
	}

	/* -------------------------------------------- */
	public function set_ignore_parameter($ar)
	{
		if(is_array($ar))
		$this->ignore_parameters =$ar;
		else
		$this->ignore_parameters =array($ar);
	}


	/* -------------------------------------------- */
	public function get_ignore_parameter($ar)
	{
		return $this->ignore_parameters;
	}

	/* -------------------------------------------- */
	public function set_main_type()
	{
		$this->type = "_main";
	}

	/* -------------------------------------------- */
	public function set_type($type)
	{
		$this->type = $type;
	}

	/* -------------------------------------------- */
	public function set_sub_type()
	{
		$this->type = "_sub";
	}


	/* -------------------------------------------- */
	public function get_type()
	{
		return $this->type;
	}
	/* -------------------------------------------- */
	public function set_style_postfix($prefix)
	{
		$this->style_postfix = $prefix;
	}

	/* -------------------------------------------- */
	public function get_style_postfix()
	{
		return $this->style_postfix;
	}


	/* -------------------------------------------- */
	public function run()
	{
            $options_path="";

            $tab_index= $this->request->get($this->parameter);
            if($tab_index=='')
            {
                $tab_index=$this->tabs[0]['num'];
            }

            if(is_array($this->ignore_parameters))
            {
                $ignore=array_merge(array($this->parameter),$this->ignore_parameters);
                $options_path= $this->request->get_current_parameters($ignore);
            }else
            {
                $options_path= $this->request->get_current_parameters($this->parameter);
            }
                $num_index=-1;

                echo '<div class="cf_tabs_container' . $this->get_type() . $this->get_style_postfix() . '"><ul class="cf_tabs' . $this->get_type() . $this->get_style_postfix() . '">';
                        for($i=0;$i<$this->tabs_count();$i++)
                        {
                                if($this->tabs[$i]['num']==$tab_index){
                                echo '<li class="active"><a href="' . $options_path . '&' . $this->parameter .'=' . $this->tabs[$i]['num'] . '">' .  $this->tabs[$i]['title'] . '</a></li>';
                                $num_index=$i;
                        }
                        else
                        {
                                echo '<li><a href="' . $options_path . '&' . $this->parameter .'=' . $this->tabs[$i]['num'] . '">' .  $this->tabs[$i]['title'] . '</a></li>';
                        }
                        }
                        echo '</ul>';
                        echo '<div id="tab1" class="cf_tabs_content' . $this->get_type() . $this->get_style_postfix() . '">';
                        if($num_index>=0)
                        {
                                include $this->app->get_plugin_path() . 'options/' . $this->tabs[$num_index]['content'];
                        }
                        echo '</div></div>';
	}


}}
