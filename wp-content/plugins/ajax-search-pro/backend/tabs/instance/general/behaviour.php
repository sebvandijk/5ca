<fieldset>
    <legend>
        <?php echo __('Logic and matching', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/search-logic"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsCustomSelect("keyword_logic", __('Primary keyword logic', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('OR', 'ajax-search-pro'), 'value' => 'or'),
                    array('option' => __('OR with exact word matches', 'ajax-search-pro'), 'value' => 'orex'),
                    array('option' => __('AND', 'ajax-search-pro'), 'value' => 'and'),
                    array('option' => __('AND with exact word matches', 'ajax-search-pro'), 'value' => 'andex')
                ),
                'value' => $sd['keyword_logic']
            ));
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsCustomSelect('secondary_kw_logic', __('Secondary logic', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Disabled', 'ajax-search-pro'), 'value' => 'none'),
                    array('option' => __('OR', 'ajax-search-pro'), 'value' => 'or'),
                    array('option' => __('OR with exact word matches', 'ajax-search-pro'), 'value' => 'orex'),
                    array('option' => __('AND', 'ajax-search-pro'), 'value' => 'and'),
                    array('option' => __('AND with exact word matches', 'ajax-search-pro'), 'value' => 'andex')
                ),
                'value' => $sd['secondary_kw_logic']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo sprintf( __('<strong>Secodary logic</strong> is used when the results count does not reach the limit. More <a href="%s" target="_blank">information about logics here</a>.', 'ajax-search-pro'), 'https://documentation.ajaxsearchpro.com/search-logic/search-logics-explained' ); ?>
        </div>
    </div>
    <div class="item item-flex-nogrow item-conditional" style="flex-wrap: wrap;">
        <?php
        $o = new wpdreamsYesNo("exactonly", __('Show exact matches only?', 'ajax-search-pro'),
            $sd['exactonly']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsCustomSelect('exact_match_location', __('..and match fields against the search phrase', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Anywhere', 'ajax-search-pro'), 'value' => 'anywhere'),
                    array('option' => __('Starting with phrase', 'ajax-search-pro'), 'value' => 'start'),
                    array('option' => __('Ending with phrase', 'ajax-search-pro'), 'value' => 'end')
                ),
                'value' => $sd['exact_match_location']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg" style="margin-top:4px;min-width: 100%;flex-wrap: wrap;flex-basis: auto;flex-grow: 1;box-sizing: border-box;">
        <?php
        $o = new wpdreamsYesNo("exact_m_secondary", __(' ..allow Secondary logic when exact matching?', 'ajax-search-pro'),
            $sd['exact_m_secondary']);
        $params[$o->getName()] = $o->getData();
        ?></div>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('If this is enabled, the Regular search engine is used. Index table engine doesn\'t support exact matches.', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsTextSmall("min_word_length", __('Minimum word length', 'ajax-search-pro'), $sd['min_word_length']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('Words shorter than this will not be treated as separate keywords. Higher value increases performance, lower increase accuracy. Recommended values: 2-5', 'ajax-search-pro'); ?>
        </p>
    </div>
</fieldset>
<fieldset>
    <legend>
        <?php echo __('Trigger and redirection behavior', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/behavior/return-key-and-magnifier-icon-click-actions"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("trigger_on_facet", __('Trigger <strong>live</strong> search when changing a facet on settings?', 'ajax-search-pro'),
            $sd['trigger_on_facet']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('Will trigger the search if the user changes a checkbox, radio button, slider on the frontend
            search settings panel.', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("triggerontype", __('Trigger <strong>live</strong> search when typing?', 'ajax-search-pro'),
            $sd['triggerontype']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsTextSmall("charcount", __('Minimal character count to trigger search', 'ajax-search-pro'), $sd['charcount']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsCustomSelect("click_action", __('Action when clicking <strong>the magnifier</strong> icon', 'ajax-search-pro'),
            array(
                'selects' => $_red_opts,
                'value' => $sd['click_action']
            ));
        $params[$o->getName()] = $o->getData();
        $o = new wpdreamsCustomSelect("click_action_location", __(' location: ', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Use same tab', 'ajax-search-pro'), 'value' => 'same'),
                    array('option' => __('Open new tab', 'ajax-search-pro'), 'value' => 'new')
                ),
                'value' => $sd['click_action_location']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsCustomSelect("return_action", __('Action when pressing <strong>the return</strong> button', 'ajax-search-pro'),
            array(
                'selects' => $_red_opts,
                'value' => $sd['return_action']
            ));
        $params[$o->getName()] = $o->getData();
        $o = new wpdreamsCustomSelect("return_action_location", __(' location: ', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Use same tab', 'ajax-search-pro'), 'value' => 'same'),
                    array('option' => __('Open new tab', 'ajax-search-pro'), 'value' => 'new')
                ),
                'value' => $sd['return_action_location']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wd_CPTSearchCallBack('redirect_elementor', __('Select a page with an Elementor Pro posts widget', 'ajax-search-pro'), array(
                'value'=>$sd['redirect_elementor'],
                'args'=> array(
                        'controls_position' => 'left',
                        'class'=>'wpd-text-right'
                )
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsText("redirect_url", __('Custom redirect URL', 'ajax-search-pro'),
            $sd['redirect_url']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo sprintf( __('You can use the <string>asp_redirect_url</string> filter to add more variables. See <a href="%s" target="_blank">this tutorial</a>.', 'ajax-search-pro'), 'http://wp-dreams.com/go/?to=kb-redirecturl' ); ?>
        </p>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsYesNo("override_default_results", __('<b>Override</b> the default WordPress search results with results from this search instance?', 'ajax-search-pro'),
            $sd['override_default_results']);
        $params[$o->getName()] = $o->getData();
        ?>
        <?php
        $o = new wpdreamsCustomSelect("override_method", __(' method ', 'ajax-search-pro'), array(
            "selects" =>array(
                array("option" => "Post", "value" => "post"),
                array("option" => "Get", "value" => "get")
            ),
            "value" => $sd['override_method']
        ));
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('If this is enabled, the plugin will try to replace the default results with it\'s own. Might not work with themes which temper the search query themselves (very very rare).', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsTextSmall("results_per_page", __('Results count per page?', 'ajax-search-pro'),
            $sd['results_per_page']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg"><?php echo __('The number of results per page, on the results page. Default: auto', 'ajax-search-pro'); ?></p>
        <p class="errorMsg">
            <?php echo __('<strong>WARNING:</strong> This should be set to the same as the number of results originally displayed on the results page!<br>
            Most themes use the system option found on the <strong>General Options -> Reading</strong> submenu, which is 10 by default. <br>
            If you set it differently, or your theme has a different option for that, then <strong>set this option to the same value</strong> as well.', 'ajax-search-pro'); ?>
        </p>
    </div>
</fieldset>
<fieldset id="res_live_search">
    <legend>
        <?php echo __('Elementor Posts Widget Live Filter', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/behavior/elementor-pro-posts-widget-live-filter"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="item">
        <label>
            <?php echo __('Add to Elementor Posts Widget class name to enable live filtering on that widget', 'ajax-search-pro'); ?>
            <input type="text" value="asp_es_<?php echo $search['id']; ?>" readonly="readonly">
        </label>
        <div class="descMsg">
            <?php echo sprintf(
                __('Please check the <a href="%s">Elementor Posts Live Loader documentation</a> for more details', 'ajax-search-pro'),
                'https://documentation.ajaxsearchpro.com/elementor-integration'); ?>
        </div>
    </div>
</fieldset>
<fieldset id="res_live_search">
    <legend>
        <?php echo __('Results page live loader', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/behavior/results_page_live_loader"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="errorMsg">
        <?php echo sprintf( __('<strong>Disclaimer:</strong> Live loading items to a page causes the script event handlers to detach on the affected elements - if there are
        interactive elements (pop-up buttons etc..) controlled by a script within the results, they will probably stop working after a live load.
        This cannot be prevented from this plugins perspective. <a href="%" target="_blank">More information here.</a>', 'ajax-search-pro'), 'https://documentation.ajaxsearchpro.com/behavior/results_page_live_loader' ); ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("res_live_search", __('Live load the results on the results page? <strong>(experimental)</strong>', 'ajax-search-pro'),
            $sd['res_live_search']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg">
            <?php echo __('If this is enabled, and the current page is the results page, the plugin will try to load the results there, without reloading the page.', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsText("res_live_selector", __('Results container jQuery element selector', 'ajax-search-pro'), $sd['res_live_selector']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg">
            <?php echo __('In many themes this is <strong>#main</strong>, but it can be different. This is very important to get right, or this will surely not work. The plugin will try other values as well, if this fails.', 'ajax-search-pro'); ?>
        </div>
    </div>
</fieldset>
<fieldset id="res_live_search_triggers">
    <legend><?php echo __('Results page live loader and Elementor post widget override triggers', 'ajax-search-pro'); ?></legend>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("res_live_trigger_type", __('Trigger live search when typing?', 'ajax-search-pro'),
            $sd['res_live_trigger_type']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg">
            <?php echo __('If enabled, on the results page (or custom Elementor posts widget page), overrides the default behavior.', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("res_live_trigger_facet", __('Trigger live search when changing a facet on settings?', 'ajax-search-pro'),
            $sd['res_live_trigger_facet']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg">
            <?php echo __('If enabled, on the results page (or custom Elementor posts widget page), overrides the default behavior.', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("res_live_trigger_click", __('Trigger live search when clicking the magnifier button?', 'ajax-search-pro'),
            $sd['res_live_trigger_click']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg">
            <?php echo __('If enabled, on the results page (or custom Elementor posts widget page), overrides the default behavior.', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("res_live_trigger_return", __('Trigger live search when hitting the return key?', 'ajax-search-pro'),
            $sd['res_live_trigger_return']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg">
            <?php echo __('If enabled, on the results page (or custom Elementor posts widget page), overrides the default behavior.', 'ajax-search-pro'); ?>
        </div>
    </div>
</fieldset>