<?php

/****************************************************************************
* pagination.class.php
*
* version 1.0
*
* This script can be used to generate dynamically pagination for any list of rows or items from MySQL Database
* 
* ----------------------------------------------------------------
*
* A demo example is included in demo folder.
*
* Copyright (C) 2012 Fakhri Alsadi <fakrhi.s@hotmail.com>
*
*******************************************************************************/

if(!class_exists('clogica_pagination')){
class clogica_pagination{

private $rows=15;					//The rows per page.
private $table_rows_count;			//The total rows to show.
private $pages_count;				//The pages number to generate.
private $current_page=1;			//The number of the current page displayed.
private $start=0;					//The start of the mysql limit keyword.
private $printed_pages=7;			//Number of pages to display.
private $parameter='page_num';			//The pgination paramerter name.
private $url_rewrite='';			//The URL rewrite structure.
private $data_source;				//The MySQL Table or Tables used.
private $filter;					//The MySQL Where clouse.
private $current_parameters;		//The existing query string parameters
private $prev_page;					//The previous page.
private $next_page;					//The next page
private $run_count=0;				//Times that the funtion run() invoked.
private $show_pages_number=true;	//Pages number box status.
private $show_prev_next=true;		//Showing pervious and next page buttons status.
private $show_first_last=true;		//Showing first and last page buttons status.
private $pages_number_text= "of"; //Set text of pages number

public function __construct($data_source='',$filter='',$rows=15,$parameter='page_num')
	{
	$this->data_source=$data_source;
	$this->set_filter($filter);
	$this->rows=$rows;
	$this->parameter= $parameter;
	}


//------------------------------------------------------------------------

private function run_code()
{	
global $wpdb;
	
	if($this->run_count == 0)
	{
		if($this->data_source== '')
		{
			echo __('You must set the Data Source using the function set_data_source','wsr').$data_source;			exit(0);
		}
		

		$sql=" select count(*) as cnt from {$this->data_source} {$this->filter} ";
		$result=$wpdb->get_row($sql);
		$this->table_rows_count = intval($result->cnt);

		$this->pages_count = intval($this->table_rows_count/$this->rows);
		if($this->table_rows_count%$this->rows !=0)
		$this->pages_count=$this->pages_count+1;
		if($this->pages_count ==0)
		$this->pages_count=1;
		
	    if(array_key_exists($this->parameter,$_GET))
		$this->current_page = intval($_GET[$this->parameter]);
		else
		$this->current_page =1;
		
		if($this->current_page<1)
		$this->current_page=1;
		else if($this->current_page > $this->pages_count)
		$this->current_page=$this->pages_count;	
			
		$this->current_parameters	= $this->get_current_parameters($this->parameter);
		$this->prev_page= $this->current_page -1;
		$this->next_page= $this->current_page +1;
		
		$this->start = (($this->current_page - 1) * $this->rows);
			
	}
	
	$this->run_count ++;

}


//------------------------------------------------------------------------



public function run()
	{
	
	$this->run_code();
	$this->pagination_header_html();
	
	if($this->current_page > 1)
	{
		if($this->show_first_last)
			echo $this->get_first_page(1);
		if($this->show_prev_next)
			echo $this->get_prev_page($this->prev_page );
	}
	
	$stop=0;
	$half_pages="";
	$frompage="";
	$topage ="";
	
	if($this->printed_pages>1){
	
		$half_pages= intval($this->printed_pages/2);
		if($this->printed_pages%$half_pages ==0)
		$half_pages--;
		
		$frompage=($this->current_page - $half_pages );
		$topage = ($this->current_page + $this->printed_pages);
		
	}
	
	if($this->printed_pages==1)
	{
		$frompage = $this->current_page;
		$topage = $this->current_page;
	}	

	if($this->printed_pages==3)
	{
		$frompage = $this->current_page -1;
		$topage = $this->current_page + 1 ;
	}	


	if($this->current_page > ( $this->pages_count - ($this->printed_pages - $half_pages ) ))
	{
		$topage =  $this->pages_count ;
		$frompage= ( $this->pages_count - $this->printed_pages +1 );
	}
	
	for($i=$frompage;$i<=$topage;$i++)
	{

		if($i>0 && $i<=$this->pages_count && $this->pages_count>1)
		{
			if($i==$this->current_page)
				echo  $this->get_current_page_html($i);
			else
				echo  $this->get_page_html($i,$i);
				
			$stop++;
			if($stop >= $this->printed_pages)
			break;
		}

	}
		if($this->pages_count>1){
			echo $this->get_pages_number_html();
		}

	
	if($this->current_page >= 1 && $this->pages_count>1 && $this->current_page< $this->pages_count)
		{
			if($this->show_prev_next)
				echo $this->get_next_page($this->next_page);
			if($this->show_first_last)
				echo $this->get_last_page($this->pages_count);
		}
	$this->pagination_footer_html();

	}


//------------------------------------------------------------------------

private function get_page_html($page,$name)
	{
		return '<a class="btn btn-default btn-sm" href="' . $this->get_page_url($page)  . '">' . $name . '</a>';
	}

//------------------------------------------------------------------------
private function get_first_page($page)
{
	return '<a class="btn btn-default btn-sm" href="' . $this->get_page_url($page)  . '"><span class="glyphicon glyphicon-backward"></span></a>';
}

//------------------------------------------------------------------------
private function get_prev_page($page)
{
	return '<a class="btn btn-default btn-sm" href="' . $this->get_page_url($page)  . '"><span class="glyphicon glyphicon-chevron-left"></span></a>';
}
//------------------------------------------------------------------------

private function get_last_page($page)
{
	return '<a class="btn btn-default btn-sm" href="' . $this->get_page_url($page)  . '"><span class="glyphicon glyphicon-forward"></span></a>';
}

//------------------------------------------------------------------------

private function get_next_page($page)
{
	return '<a class="btn btn-default btn-sm" href="' . $this->get_page_url($page)  . '"><span class="glyphicon glyphicon-chevron-right"></span></a>';
}
//------------------------------------------------------------------------

private function get_current_page_html($name)
	{

		return '<span class="btn btn-primary btn-sm">' . $name . '</span>';
	}
	
//------------------------------------------------------------------------

private function pagination_header_html()
	{ 

		
		echo '<div class="col-sm-8"><div class="btn-group">';
		
		
	}

//------------------------------------------------------------------------

private function pagination_footer_html()
	{
		echo '</div></div>';
	}
	
//------------------------------------------------------------------------

private function random_id()
	{
		srand ((double) microtime( )*1000000000);
		return rand();
	}

//------------------------------------------------------------------------

private function get_pages_number_html()
	{
		if($this->show_pages_number)
		echo '<span style="text-transform:none;" class="btn btn-default btn-sm disabled">' . $this->current_page . ' ' . $this->pages_number_text .  ' ' . $this->pages_count . '</span>';
	}	
//------------------------------------------------------------------------


public function get_page_url($page)
	{
		if($this->url_rewrite == '')
		{
			if($this->current_parameters =="")
			return "?" . $this->parameter . "=" . $page;
			else
			return $this->current_parameters . "&" . $this->parameter . "=" . $page;
		}else
		{
			return str_replace('{pagination}',$page, $this->url_rewrite);
		}
	}
	

//------------------------------------------------------------------------



private function get_page_url_parameter()
	{
		if($this->current_parameters =="")
		return "?" . $this->parameter . "=";
		else
		return $this->current_parameters . "&" . $this->parameter . "=";
	}

//------------------------------------------------------------------------

private function get_current_parameters($remove_parameter="")
{	
	
	if($_SERVER['QUERY_STRING']!='')
	{
		$qry = '?' . urldecode($_SERVER['QUERY_STRING']);
		if($remove_parameter!='')
		{
			if(array_key_exists($remove_parameter,$_GET))
			{
    			$string_remove = '&' . $remove_parameter . "=" . $_GET[$remove_parameter];
    			$qry=str_replace($string_remove,"",$qry);
    			$string_remove = '?' . $remove_parameter . "=" . $_GET[$remove_parameter];
    			$qry=str_replace($string_remove,"",$qry);
			}
		}
		
		return $qry;
	}else
	{
		return "";
	}
} 	

//------------------------------------------------------------------------


public function get_sql_limit()
	{
		$this->run_code();
		return " limit  {$this->start},{$this->rows} ";
	}
	
//------------------------------------------------------------------------

public function get_rows()
	{
		return $this->rows;
	}

//------------------------------------------------------------------------

public function set_rows($rows)
	{
		if(intval($rows)>0)
		{
			$this->rows=$rows;
			$this->run_count=0;
		}
	}

//------------------------------------------------------------------------

public function get_total_records()
	{
		return $this->table_rows_count;
	}


//------------------------------------------------------------------------

public function get_printed_pages()
	{
	 	return $this->printed_pages;
	}

//------------------------------------------------------------------------

public function set_printed_pages($printed_pages)
	{
		if(intval($printed_pages)>0)
		{
			$this->printed_pages=$printed_pages;
			$this->run_count=0;
		}
	}

//------------------------------------------------------------------------

public function get_parameter_name()
	{
	 	return $this->parameter;
	}

//------------------------------------------------------------------------

public function set_parameter_name($parameter)
	{
		$this->parameter=$parameter;
		$this->run_count=0;
	}
//------------------------------------------------------------------------

public function get_url_rewrite()
	{
	 	return $this->url_rewrite;
	}

//------------------------------------------------------------------------

public function set_url_rewrite($url_rewrite)
	{
		$this->url_rewrite=$url_rewrite;
		$this->run_count=0;
	}
//------------------------------------------------------------------------


public function get_data_source()
	{
	 	return $this->data_source;
	}

//------------------------------------------------------------------------

public function set_data_source($data_source)
	{
		$this->data_source=$data_source;
		$this->run_count=0;
	}

//------------------------------------------------------------------------

public function get_filter()
	{
	 	return $this->filter;
	}

//------------------------------------------------------------------------

public function set_filter($filter)
	{
		if($filter!='')
		$this->filter= $filter;
		$this->run_count=0;
	}
	
//------------------------------------------------------------------------

//------------------------------------------------------------------------

public function get_pages_number_text()
	{
	 	return $this->pages_number_text;
	}

//------------------------------------------------------------------------

public function set_pages_number_text($pages_number_text)
	{
		$this->pages_number_text=$pages_number_text;
		$this->run_count=0;
	}
//------------------------------------------------------------------------

public function show_pages_number($value)
	{
		
		if($value)
		$this->show_pages_number = true;
		else
		$this->show_pages_number = false;
		
		$this->run_count=0;
		
	}
//------------------------------------------------------------------------

public function can_show_pages_number()
	{
		return $this->show_pages_number;
	}


//------------------------------------------------------------------------

public function show_prev_next($value)
	{
		
		if($value)
		$this->show_prev_next = true;
		else
		$this->show_prev_next = false;
		
		$this->run_count=0;
		
	}

//------------------------------------------------------------------------

public function can_show_prev_next()
	{
		return $this->show_prev_next;
	}

//------------------------------------------------------------------------

public function show_first_last($value)
	{
		
		if($value)
		$this->show_first_last = true;
		else
		$this->show_first_last = false;
		
		$this->run_count=0;
		
	}

//------------------------------------------------------------------------

public function can_show_first_last()
	{
		return $this->show_first_last;
	}


//------------------------------------------------------------------------

}}
