<?php
/*
	Author: Fakhri Alsadi
	Date: 16-7-2010
	Contact: www.clogica.com   info@clogica.com
	A simple class to create Drop down lists easily using PHP
	----------------------------------------------------------
	example:
	----------------------------------------------------------
	$drop = new dropdownlist('gendar');
	$drop->add('mail','mail');`
	$drop->add('femail','femail');
	$drop->run();
	$drop->select('femail');
	//////////////////////////////
	$drop = new dropdownlist('gendar');
	$drop->data_bind('data_status');
	$drop->run();
*/

if(!class_exists('dropdown_list')){
class dropdown_list{

	private $name='drop';
	private $options='';
	private $class='';
	private $onchange='';

	function __construct($str,$class='',$onchange='')
	{
		$this->name=$str;

		if($class!='')
		{
			$this->class=$class;
		}

		if($onchange!='')
		{
			$this->onchange=$onchange;
		}
	}

	public function add($name,$value,$data_icon='')
	{
		if($data_icon!='')
		{
			$this->options=$this->options. "<option data-icon='$data_icon' value='$value'>$name</option>";
		}else
		{
			$this->options=$this->options. "<option value='$value'>$name</option>";
		}
	}

	public function onchange($onchange)
	{
		$this->onchange=$onchange;
	}

	public function run(&$jforms=null)
	{
		if($this->onchange == '')
		{
			echo "<select data-size='5' class='selectpicker'  name='" . $this->name. "' id='" . $this->name. "' >" . $this->options . "</select>";

		}else
		{
			echo "<select data-size='5' class='selectpicker' name='" . $this->name. "' id='" . $this->name. "'  onchange='" . $this->onchange . "' >" . $this->options . "</select>";
		}

		if(!is_null($jforms))
		{
			$jforms->add_select_picker();
		}
	}

	public function select($str)
	{
		echo "<script>document.getElementById('" . $this->name . "').value='".$str."'</script>";
	}

	public function select_array_option($array,$key)
	{
		if(array_key_exists($key,$array))
		{
			$this->select($array[$key]);
		}
	}

	public function data_bind($tbl,$name="name",$id="id",$where="",$order="",$limit="")
	{
		global $wpdb;
		$res = $wpdb->get_results("select $id,$name from PREFIX_$tbl $where $order $limit ", ARRAY_A);
		foreach ( $res as $ar){
			$this->add($ar[1],$ar[0]);
		}
	}

	}}
