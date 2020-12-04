<fieldset>
    <legend>Global loading options</legend>
    <div class="item">
        <?php
        $o = new wpdreamsCustomSelect("load_mcustom_js", __('Load the scrollbar script?', 'ajax-search-pro'), array(
                'selects'=>array(
                    array('option'=>'Yes', 'value'=>'yes'),
                    array('option'=>'No', 'value'=>'no')
                ),
                'value'=>$com_options['load_mcustom_js']
            )
        );
        $params[$o->getName()] = $o->getData();
        ?>
        <p class='descMsg'>
        <ul>
            <li><?php echo __('When set to <strong>No</strong>, the custom scrollbar will <strong>not be used at all</strong>.', 'ajax-search-pro'); ?></li>
        </ul>
        </p>
    </div>
    <div class="item">
        <?php $o = new wpdreamsYesNo("load_lazy_js", __('Use the Lazy Loader jQuery script to load the images?', 'ajax-search-pro'),
            $com_options['load_lazy_js']
        ); ?>
        <p class='descMsg'>
            <?php echo sprintf( __('Will load an use a modified version of <a href="%s" target="_blank">Lazy Load</a> script to load the images of results.', 'ajax-search-pro'), 'http://jquery.eisbehr.de/lazy/' ); ?>
        </p>
    </div>
</fieldset>
<fieldset>
    <legend>Selective loading options</legend>
    <div class="item">
        <?php $o = new wpdreamsYesNo("selective_enabled", __('Enable selective script & style loading?', 'ajax-search-pro'),
            $com_options['selective_enabled']
        ); ?>
        <p class='descMsg'><?php echo __('It enables the rules below, so the scritps and styles can be excluded from specific parts of your website.', 'ajax-search-pro'); ?></p>
    </div>
    <div class="item item_selective_load">
        <?php $o = new wpdreamsYesNo("selective_front", __('Load scripts & styles on the front page?', 'ajax-search-pro'),
            $com_options['selective_front']
        ); ?>
    </div>
    <div class="item item_selective_load">
        <?php $o = new wpdreamsYesNo("selective_archive", __('Load scripts & styles on archive pages?', 'ajax-search-pro'),
            $com_options['selective_front']
        ); ?>
    </div>
    <div class="item item_selective_load item-flex-nogrow item-flex-wrap">
        <div style="margin: 0;">
        <?php
        $o = new wpdreamsCustomSelect("selective_exin_logic", "",
            array(
                'selects' => array(
                    array('option' => 'Exclude on pages', 'value' => 'exclude'),
                    array('option' => 'Include on pages', 'value' => 'include')
                ),
                'value' => $com_options['selective_exin_logic']
            ));
        ?>
        </div>
        <?php
        $o = new wd_TextareaExpandable("selective_exin", " ids ", $com_options['selective_exin']);
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('Comma separated list of Post/Page/CPT IDs.', 'ajax-search-pro'); ?>
        </div>
    </div>
</fieldset>