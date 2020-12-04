<fieldset>
    <legend>
        <?php echo __('More results text and behavior', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/layout-settings/more-results-link"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsYesNo("showmoreresults", __('Show \'More results..\' text in the bottom of the search box?', 'ajax-search-pro'), $sd['showmoreresults']);
        $params[$o->getName()] = $o->getData();
        $o = new wpdreamsCustomSelect("more_results_action", __(' action', 'ajax-search-pro'), array(
            'selects'=>array(
                array('option' => __('Load more ajax results', 'ajax-search-pro'), 'value' => 'ajax'),
                array('option' => __('Redirect to Results Page', 'ajax-search-pro'), 'value' => 'results_page'),
                array('option' => __('Redirect to WooCommerce Results Page', 'ajax-search-pro'), 'value' => 'woo_results_page'),
                array('option' => __('Redirect to Elementor post widget page', 'ajax-search-pro'), 'value' => 'elementor_page'),
                array('option' => __('Redirect to custom URL', 'ajax-search-pro'), 'value' => 'redirect')
            ),
            'value'=>$sd['more_results_action']
        ));
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('"Load more ajax results" option will not work if Polaroid layout or Grouping is activated, or if results are removed when no images are present.', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item">
        <?php
        $o = new wd_CPTSearchCallBack('more_redirect_elementor', __('Select a page with an Elementor Pro posts widget', 'ajax-search-pro'), array(
                'value'=>$sd['more_redirect_elementor'],
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
        $o = new wpdreamsText("more_redirect_url", __('\' Show more results..\' url', 'ajax-search-pro'), $sd['more_redirect_url']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("more_results_infinite", __('<strong>Infinite scroll</strong> - Trigger loading more results on srolling near the end of results list', 'ajax-search-pro'), $sd['more_results_infinite']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsText("showmoreresultstext", __('\' Show more results..\' text', 'ajax-search-pro'), $sd['showmoreresultstext']);
        $params[$o->getName()] = $o->getData();
        $o = new wpdreamsCustomSelect("more_redirect_location", __(' location: ', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => 'Use same tab', 'value' => 'same'),
                    array('option' => 'Open new tab', 'value' => 'new')
                ),
                'value' => $sd['more_redirect_location']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>
<fieldset>
    <legend>
        <?php echo __('Results text keyword highlighter', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/layout-settings/search-phrase-highlighter"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="item"><?php
        $o = new wpdreamsYesNo("highlight", __('Highlight search text in results?', 'ajax-search-pro'), $sd['highlight']);
        $params[$o->getName()] = $o->getData();
        ?></div>
    <div class="item"><?php
        $o = new wpdreamsYesNo("highlightwholewords", __('Highlight only whole words?', 'ajax-search-pro'), $sd['highlightwholewords']);
        $params[$o->getName()] = $o->getData();
        ?></div>
    <div class="item"><?php
        $o = new wpdreamsColorPicker("highlightcolor", __('Highlight text color', 'ajax-search-pro'), $sd['highlightcolor']);
        $params[$o->getName()] = $o->getData();
        ?></div>
    <div class="item"><?php
        $o = new wpdreamsColorPicker("highlightbgcolor", __('Highlight-text background color', 'ajax-search-pro'), $sd['highlightbgcolor']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>