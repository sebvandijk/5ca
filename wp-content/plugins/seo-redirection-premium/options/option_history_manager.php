<?php

$phptabs= SRP_PLUGIN::get_tabs();
$phptabs->init();
$phptabs->set_parameter("history_manager_tab");
$phptabs->set_sub_type();
$phptabs->set_ignore_parameter(array('del','search','page_num','add','edit','page404','grpID','shown','sort','rsrc','link_type','link'));
$phptabs->add_file_tab('history_list',__('History List','wsr'),'option_history_list.php','file');
$phptabs->add_file_tab('history_options',__('History Options','wsr'),'option_history_options.php','file');
//$phptabs->add_file_tab('test','test','test.php','file');
$phptabs->run();