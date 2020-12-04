<?php

$phptabs= SRP_PLUGIN::get_tabs();
$phptabs->init();
$phptabs->set_parameter('404_manager_tab');
$phptabs->set_sub_type();
$phptabs->set_ignore_parameter(array('del','search','page_num','add','edit','page404','grpID','shown','sort','link_type','link'));
$phptabs->add_file_tab('discovered_404_errors',__('Discovered 404 Errors','wsr'),'option_404_list.php','file');
$phptabs->add_file_tab('404_rules',__('404 Rules','wsr'),'option_404_rules.php','file');
$phptabs->add_file_tab('general_rules',__('General Rules','wsr'),'option_404_general_rules.php','file');
$phptabs->add_file_tab('404_options',__('404 Options','wsr'),'option_404_options.php','file');
$phptabs->run();


