<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("js_source", __('Javascript source', 'ajax-search-pro'), array(
            'selects'   => wd_asp()->o['asp_compatibility_def']['js_source_def'],
            'value'     => $com_options['js_source']
        )
    );
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <ul style="float:right;text-align:left;width:50%;">
            <li><?php echo __('<b>Non minified</b> - Optimal Compatibility, Medium space', 'ajax-search-pro'); ?></li>
            <li><?php echo __('<b>Minified</b> - Optimal Compatibility, Low space (recommended)', 'ajax-search-pro'); ?></li>
            <li><?php echo __('<b>Non minified Scoped</b> - High Compatibility, High space', 'ajax-search-pro'); ?></li>
            <li><?php echo __('<b>Minified Scoped</b> - High Compatibility, Medium space', 'ajax-search-pro'); ?></li>
        </ul>
        <div class="clear"></div>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("js_init", __('Javascript init method', 'ajax-search-pro'), array(
            'selects'=>array(
                array('option'=>__('Dynamic (default)', 'ajax-search-pro'), 'value'=>'dynamic'),
                array('option'=>__('Blocking', 'ajax-search-pro'), 'value'=>'blocking')
            ),
            'value'=>$com_options['js_init']
        )
    );
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Try to choose <strong>Blocking</strong> if the search bar is not responding to anything.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("js_prevent_body_scroll", __('Try preventing body touch scroll on mobile devices, when using the vertical results layout?', 'ajax-search-pro'),
        $com_options['js_prevent_body_scroll']
    ); ?>
    <p class='descMsg'>
        <?php echo __('When reaching the top or bottom of the results list via touch devices, the scrolling is automatically propagated to the parent element. This function will try to prevent that.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <p class='infoMsg'>
        <?php echo __('You can turn some of these off, if you are not using them.', 'ajax-search-pro'); ?>
    </p>
    <?php $o = new wpdreamsYesNo("js_retain_popstate", __('Remember search phrase and options when using the Browser Back button?', 'ajax-search-pro'),
        $com_options['js_retain_popstate']
    ); ?>
    <p class='descMsg'>
        <?php echo __('Whenever the user clicks on a live search result, and decides to navigate back, the search will re-trigger and reset the previous options.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("js_fix_duplicates", __('Try fixing DOM duplicates of the search bar if they exist?', 'ajax-search-pro'),
        $com_options['js_fix_duplicates']
    ); ?>
    <p class='descMsg'>
        <?php echo __('Some menu or widgets scripts tend to <strong>clone</strong> the search bar completely for Mobile viewports, causing a malfunctioning search bar with no event handlers. When this is active, the plugin script will try to fix that, if possible.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("detect_ajax", __('Try to re-initialize if the page was loaded via ajax?', 'ajax-search-pro'),
        $com_options['detect_ajax']
    ); ?>
    <p class='descMsg'>
        <?php echo __('Will try to re-initialize the plugin in case an AJAX page loader is used, like Polylang language switcher etc..', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("load_in_footer", __('Load scripts in footer?', 'ajax-search-pro'),
        $com_options['load_in_footer']
    ); ?>
    <p class='descMsg'>
        <?php echo __('Will load the scripts in the footer for better performance.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("css_compatibility_level", __('CSS compatibility level', 'ajax-search-pro'), array(
            'selects'=>array(
                array('option'=>'Optimal (recommended)', 'value'=>'low'),
                array('option'=>'Medium', 'value'=>'medium'),
                array('option'=>'Maximum', 'value'=>'maximum')
            ),
            'value'=>$com_options['css_compatibility_level']
        )
    );
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
    <ul style="float:right;text-align:left;width:50%;">
        <li><?php echo __('<b>Optimal</b> - Good compabibility, smallest size', 'ajax-search-pro'); ?></li>
        <li><?php echo __('<b>Medium</b> - Better compatibility, bigger size', 'ajax-search-pro'); ?></li>
        <li><?php echo __('<b>Maximum</b> - High compatibility, very big size', 'ajax-search-pro'); ?></li>
    </ul>
    <div class="clear"></div>
    </p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("css_minify", __('Minify the generated CSS?', 'ajax-search-pro'),
        $com_options['css_minify']
    ); ?>
    <p class='descMsg'>
        <?php echo __('When enabled, the generated stylesheet files will be minified before saving. Can save ~10% CSS file size.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <p class='infoMsg'>
        <?php echo __('Set to yes if you are experiencing issues with the <b>search styling</b>, or if the styles are <b>not saving</b>!', 'ajax-search-pro'); ?>
    </p>
    <?php $o = new wpdreamsYesNo("forceinlinestyles", __('Force inline styles?', 'ajax-search-pro'),
        $com_options['forceinlinestyles']
    ); ?>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("css_async_load", __('Load CSS files conditionally? (asnychronous, <b>experimental!</b>)', 'ajax-search-pro'),
        $com_options['css_async_load']
    ); ?>
    <p class='descMsg'>
        <?php echo __('Will save every search instance CSS file separately and load them with Javascript on the document load event.', 'ajax-search-pro'); ?>
        <?php echo __('Only loads them if it finds the search instance on the page. Huge performance saver, however it might not work so test it seriously!', 'ajax-search-pro'); ?>
        <?php echo sprintf( __('Check the <a target="_blank" href="%s">Visual Performance</a> section of the documentation for more info.'),
            'https://documentation.ajaxsearchpro.com/performance-tuning/visual-performance' ); ?>
    </p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("load_google_fonts", __('Load the <strong>google fonts</strong> used in the search options?', 'ajax-search-pro'),
        $com_options['load_google_fonts']
    ); ?>
    <p class='descMsg'>
        <?php echo __('When <strong>turned off</strong>, the google fonts <strong>will not be loaded</strong> via this plugin at all.<br>Useful if you already have them loaded, to avoid mutliple loading times.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <p class='infoMsg'>
        <?php echo __('This might speed up the search, but also can cause incompatibility issues with other plugins.', 'ajax-search-pro'); ?>
    </p>
    <?php $o = new wpdreamsYesNo("usecustomajaxhandler", __('Use the custom ajax handler?', 'ajax-search-pro'),
        $com_options['usecustomajaxhandler']
    ); ?>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("old_browser_compatibility", __('Display the default search box on old browsers? (IE<=8)', 'ajax-search-pro'),
        $com_options['old_browser_compatibility']
    ); ?>
</div>