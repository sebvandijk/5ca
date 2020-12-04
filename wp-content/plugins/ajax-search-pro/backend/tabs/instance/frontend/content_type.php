<span class="asp_legend_docs">
    <a target="_blank" href="https://documentation.ajaxsearchpro.com/frontend-search-settings/content-type-filters"><span class="fa fa-book"></span>
        <?php echo __('Documentation', 'ajax-search-pro'); ?>
    </a>
</span>
<div class="item">
    <?php
    $o = new wpdreamsText("content_type_filter_label", __('Content type filter label text', 'ajax-search-pro'), $sd['content_type_filter_label']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wd_DraggableFields("content_type_filter", __('Content Type filter', 'ajax-search-pro'), array(
        "value"=>$sd['content_type_filter'],
        "args" => array(
            "show_checkboxes" => 1,
            "show_display_mode" => 1,
            "show_labels" => 1,
            "show_required" => 1,
            'fields' => array(
                'any'           => 'Choose One/Select all',
                'cpt'           => 'Custom post types',
                'comments'      => 'Comments',
                'taxonomies'    => 'Taxonomy terms',
                'users'         => 'Users',
                'blogs'         => 'Multisite blogs',
                'buddypress'    => 'BuddyPress content',
                'attachments'   => 'Attachments'
            ),
            'checked' => array()
        )
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>