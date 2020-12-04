<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_search", __('Enable search in users?', 'ajax-search-pro'),
        $sd['user_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_login_search", __('Search in user login names?', 'ajax-search-pro'),
        $sd['user_login_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_display_name_search", __('Search in user display names?', 'ajax-search-pro'),
        $sd['user_display_name_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_first_name_search", __('Search in user first names?', 'ajax-search-pro'),
        $sd['user_first_name_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_last_name_search", __('Search in user last names?', 'ajax-search-pro'),
        $sd['user_last_name_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_bio_search", __('Search in user bio?', 'ajax-search-pro'),
        $sd['user_bio_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_email_search", __('Search in user email addresses?', 'ajax-search-pro'),
        $sd['user_email_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item wd-primary-order item-flex-nogrow item-flex-wrap"><?php
    $o = new wpdreamsCustomSelect("user_orderby_primary", __('Primary user result ordering', 'ajax-search-pro'),
        array(
            'selects' => array(
                array('option' => __('Relevance', 'ajax-search-pro'), 'value' => 'relevance DESC'),
                array('option' => __('Title descending', 'ajax-search-pro'), 'value' => 'title DESC'),
                array('option' => __('Title ascending', 'ajax-search-pro'), 'value' => 'title ASC'),
                array('option' => __('Date descending', 'ajax-search-pro'), 'value' => 'date DESC'),
                array('option' => __('Date ascending', 'ajax-search-pro'), 'value' => 'date ASC'),
                array('option' => __('Random', 'ajax-search-pro'), 'value' => 'RAND()'),
                array('option' => __('Custom Field descending', 'ajax-search-pro'), 'value' => 'customfp DESC'),
                array('option' => __('Custom Field  ascending', 'ajax-search-pro'), 'value' => 'customfp ASC')
            ),
            'value' => $sd['user_orderby_primary']
        ));
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsText("user_orderby_primary_cf", __('custom field', 'ajax-search-pro'), $sd['orderby_primary_cf']);
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsCustomSelect("user_orderby_primary_cf_type", __('type', 'ajax-search-pro'),
        array(
            'selects' => array(
                array('option' => __('numeric', 'ajax-search-pro'), 'value' => 'numeric'),
                array('option' => __('string or date', 'ajax-search-pro'), 'value' => 'string')
            ),
            'value' => $sd['user_orderby_primary_cf_type']
        ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
    <div class="item wd-secondary-order item-flex-nogrow item-flex-wrap"><?php
        $o = new wpdreamsCustomSelect("user_orderby_secondary", __('Secondary result ordering', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Relevance', 'ajax-search-pro'), 'value' => 'relevance DESC'),
                    array('option' => __('Title descending', 'ajax-search-pro'), 'value' => 'title DESC'),
                    array('option' => __('Title ascending', 'ajax-search-pro'), 'value' => 'title ASC'),
                    array('option' => __('Date descending', 'ajax-search-pro'), 'value' => 'date DESC'),
                    array('option' => __('Date ascending', 'ajax-search-pro'), 'value' => 'date ASC'),
                    array('option' => __('Random', 'ajax-search-pro'), 'value' => 'RAND()'),
                    array('option' => __('Custom Field descending', 'ajax-search-pro'), 'value' => 'customfs DESC'),
                    array('option' => __('Custom Field ascending', 'ajax-search-pro'), 'value' => 'customfs ASC')
                ),
                'value' => $sd['user_orderby_secondary']
            ));
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsText("user_orderby_secondary_cf", __('custom field', 'ajax-search-pro'), $sd['orderby_secondary_cf']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsCustomSelect("user_orderby_secondary_cf_type", __('type', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('numeric', 'ajax-search-pro'), 'value' => 'numeric'),
                    array('option' => __('string or date', 'ajax-search-pro'), 'value' => 'string')
                ),
                'value' => $sd['user_orderby_secondary_cf_type']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('If two elements match the primary ordering criteria, the <b>Secondary ordering</b> is used.', 'ajax-search-pro'); ?>
        </div>
    </div>
<div class="item">
    <?php
    $o = new wpdreamsUserRoleSelect("user_search_exclude_roles", __('User roles exclude', 'ajax-search-pro'),
        $sd['user_search_exclude_roles']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wd_UserSelect("user_search_exclude_users", __('Exclude or Include users from results', 'ajax-search-pro'), array(
        "value"=>$sd['user_search_exclude_users'],
        'args'=> array(
            'show_type' => 1,
            'show_all_users_option' => 0
        )
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_search_display_images", __('Display user images?', 'ajax-search-pro'),
        $sd['user_search_display_images']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("user_search_image_source", __('Image source', 'ajax-search-pro'),
        array(
            'selects' => array(
                array('option' => 'Default', 'value' => 'default'),
                array('option' => 'BuddyPress avatar', 'value' => 'buddypress')
            ),
            'value' => $sd['user_search_image_source']
        ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wd_UserMeta("user_search_meta_fields", __('Search in following user meta fields', 'ajax-search-pro'), array(
        "value"=>$sd['user_search_meta_fields'],
        'args'=> array()
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBP_XProfileFields("user_bp_fields", __('Search in these BP Xprofile fields', 'ajax-search-pro'),
        $sd['user_bp_fields']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("user_search_title_field", __('Title field', 'ajax-search-pro'),
        array(
            'selects' => array(
                array('option' => 'Login Name', 'value' => 'login'),
                array('option' => 'Display Name', 'value' => 'display_name')
            ),
            'value' => $sd['user_search_title_field']
        ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("user_search_description_field", __('Description field', 'ajax-search-pro'),
        array(
            'selects' => array(
                array('option' => 'Biography', 'value' => 'bio'),
                array('option' => 'BuddyPress Last Activity', 'value' => 'buddypress_last_activity'),
                array('option' => 'Nothing', 'value' => 'nothing')
            ),
            'value' => $sd['user_search_description_field']
        ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wd_TextareaExpandable("user_search_advanced_title_field", __('Advanced title field', 'ajax-search-pro'),
        $sd['user_search_advanced_title_field']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Variable {titlefield} will be replaced with the Title field value. Use the format {meta_field} to get user meta.', 'ajax-search-pro'); ?><br>
        <a href="https://wp-dreams.com/go/?to=asp-doc-advanced-title-content" target="_blank"><?php echo __('More possibilities explained here!', 'ajax-search-pro'); ?></a>
    </p>
</div>
<div class="item">
    <?php
    $o = new wd_TextareaExpandable("user_search_advanced_description_field", __('Advanced description field', 'ajax-search-pro'),
        $sd['user_search_advanced_description_field']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Variable {descriptionfield} will be replaced with the Description field value. Use the format {meta_field} to get user meta.', 'ajax-search-pro'); ?><br>
        <a href="https://wp-dreams.com/go/?to=asp-doc-advanced-title-content" target="_blank"><?php echo __('More possibilities explained here!', 'ajax-search-pro'); ?></a>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("user_search_url_source", __('Result url source', 'ajax-search-pro'),
        array(
            'selects' => array(
                array('option' => 'Default', 'value' => 'default'),
                array('option' => 'BuddyPress profile', 'value' => 'bp_profile'),
                array('option' => 'Custom scheme', 'value' => 'custom')
            ),
            'value' => $sd['user_search_url_source']
        ));
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('This is the result URL destination. By default it\'s the author profile link.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("user_search_custom_url", __('Custom url scheme', 'ajax-search-pro'),
        $sd['user_search_custom_url']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('You can use these variables: {USER_ID}, {USER_LOGIN}, {USER_NICENAME}, {USER_DISPLAYNAME}', 'ajax-search-pro'); ?>
    </p>
</div>