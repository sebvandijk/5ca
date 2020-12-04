<span class="asp_legend_docs">
    <a target="_blank" href="https://documentation.ajaxsearchpro.com/frontend-search-settings/generic-selectors"><span class="fa fa-book"></span>
        <?php echo __('Documentation', 'ajax-search-pro'); ?>
    </a>
</span>
<div class="item">
    <?php
    $o = new wpdreamsText("generic_filter_label", __('Generic filters label text', 'ajax-search-pro'), $sd['generic_filter_label']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <div class="errorMsg hiddend" id="genericFilterErr">
        <?php echo __('<strong>WARNING:</strong> These options are not recommended to be used together with the <strong>index table</strong> engine.<br>
        You are seeing this message, because the Index Table engine is selected on the <strong>General Options</strong> panel.', 'ajax-search-pro'); ?>
    </div>
    <?php
    $o = new wd_DraggableFields("frontend_fields", __('Generic filters', 'ajax-search-pro'), array(
        "value"=>$sd['frontend_fields'],
        "args" => array(
            "show_checkboxes" => 1,
            "show_display_mode" => 1,
            "show_labels" => 1,
            'fields' => array(
                'exact'     => 'Exact matches only',
                'title'     => 'Search in title',
                'content'   => 'Search in content',
                'excerpt'   => 'Search in excerpt',
            ),
            'checked' => array('title', 'content', 'excerpt')
        )
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>