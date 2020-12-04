<?php

if(!class_exists('SR_custome_app')){
class SR_custome_app
{
    /*----------------------------------------------------------*/
    public static function hook_scripts()
    {
        add_action( 'admin_enqueue_scripts', array( 'SR_custome_app', 'hook_custom_scripts' ) );
        
        add_action('wp_ajax_search_posts_action' , array( 'SR_custome_app', 'search_posts_action' ));
        add_action('wp_ajax_redirect_404_form' , array( 'SR_custome_app', 'redirect_404_form' ));
        

    }
    
    /*----------------------------------------------------------*/
    public static function hook_custom_scripts()
    {
        if(SRP_PLUGIN::get_app()->get_plugin_slug()=='' || SRP_PLUGIN::get_app()->in_option_page())
        {
            wp_enqueue_style( 'clogica_custom_style', SRP_PLUGIN::get_app()->get_plugin_url()  . 'custom/css/' . "style.css?&t=".time() );
            
            if(array_key_exists('SR_tab', $_GET) && $_GET['SR_tab'] == '404_manager')
            {
                wp_enqueue_script('popup_js', SRP_PLUGIN::get_app()->get_plugin_url() . 'custom/js/popup_404.js', array('jquery'), '', true );
            }

            if(is_rtl())
            {
                //   wp_enqueue_style( 'clogica_rtl_custom_style', $this->get_plugin_url()  . 'custom/css/' . "style_rtl.css?&t=".time() );

            }
        }
    }
   
/*----------------------------------------------------------*/

    public static function search_posts_action(){
        
        global $wpdb, $table_prefix;
        $table_name = $table_prefix . "posts";
        $keyword = trim(SRP_PLUGIN::get_request()->post('keyword'));
        
        $rs_posts = $wpdb->get_results("select * from $table_name where post_type='post' and post_status='publish' and post_title like '%$keyword%' limit 10  ");
        if($wpdb->num_rows >0){
            $i =0;
            echo '<table class="table table-striped data_grid"><tbody>';
            foreach ( $rs_posts as $post ) 
            {
                $i++;
                $permalink = esc_url(SRP_PLUGIN::get_request()->make_relative_url(get_the_permalink($post->ID)));
                echo '<tr>
                <th scope="row" valign="center">' . $i . '</th>
                <td width="100%" valign="center"><a href="' . $permalink . '" target="_blank"><span class="fa fa-external-link-square"></span></a> ' . $post->post_title . '</td>
                <td><button onclick="use_link(' . "'" . $permalink . "'" . ')" type="button" class="btn btn-success btn-sm" title="Redirect this link"><span aria-hidden="true" class="glyphicon glyphicon-share-alt"></span> Use Link</button></td>
                </tr>';
            }
            echo '</tbody></table>';
            wp_reset_postdata();
        }  else {
            echo "<p style='text-align: center;'> No posts found! </p>";
        }
        exit();       
    }

/*----------------------------------------------------------*/

    public static function redirect_404_form()
    {
        global $wpdb;
        parse_str($_POST['formData'], $form);
        
        $nonce = "";
        $selected_ids = "";
        $redirect_to = "";
        if (array_key_exists('_wpnonce', $form)){
            $nonce = $form['_wpnonce'];
        }
        if (array_key_exists('selected_ids', $form)){
            $selected_ids = $form['selected_ids'];
        }
        if (array_key_exists('redirect_to', $form)){
            $redirect_to = $form['redirect_to'];
        }
        $data = array();
        $data['error_msg'] = "";
        $data['error'] = FALSE;
        
        if(!wp_verify_nonce($nonce, 'seoredirectionpremium'))
        {
            $data['error_msg'] = "Invalid nonce sent by the request!";
            $data['error'] = TRUE; 
        }else if($selected_ids == "")
        {
            $data['error_msg'] = "No selected items to redirect!";
            $data['error'] = TRUE;  
        }else if($redirect_to == "")
        {
            $data['error_msg'] = "No 'Redirect to' value recived!";
            $data['error'] = TRUE;  
        }
        if ($data['error'] == TRUE) {
            echo json_encode($data);
            exit();
        }
        else { 
            // try to save redirects ********************* 
            //SR_database::WP_SEO_404_links()
            
            $rs_links = $wpdb->get_results("select * from " . SR_database::WP_SEO_404_links() . " where ID in ($selected_ids)");
            if($wpdb->num_rows >0){
                
                $group_ID = 0;
                $blog_ID = get_current_blog_id();
                $group = $wpdb->get_row("select * from " . SR_database::WP_SEO_Groups() . " where group_type=1 and group_title='Redirected 404 links' and blog=" . $blog_ID);
                if($wpdb->num_rows > 0){
                   $group_ID = $group->ID; 
                }else
                {
                    $wpdb->query( "insert into " . SR_database::WP_SEO_Groups() . "(group_title,group_type,blog) values('Redirected 404 links',1," . $blog_ID . ")" );
                    $group_ID = $wpdb->insert_id;
                }
                
                foreach ( $rs_links as $link ) 
                {
                   $wpdb->query( "insert into " . SR_database::WP_SEO_Redirection() . "(redirect_from,redirect_from_type,redirect_to,redirect_to_type,redirect_type,grpID,blog) values('" . $link->link . "','Page', '" . $redirect_to . "', 'Page', '301', " . $group_ID . "," . $blog_ID . ")"); 
                }
                
                $wpdb->query("delete from " . SR_database::WP_SEO_404_links() . " where ID in ($selected_ids)");
                
            }else
            {
                $data['error_msg'] = "Could not find links in database!";
                $data['error'] = TRUE;  
            }
            
            // *******************************************
            echo json_encode($data);
            exit();   
        }
        
        exit();
    }
    

}}
