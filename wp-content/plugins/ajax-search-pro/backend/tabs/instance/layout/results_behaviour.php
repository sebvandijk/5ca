<div class="item">
    <?php
    $o = new wpdreamsYesNo("results_click_blank", __('When clicking on a result, open it in a new window?', 'ajax-search-pro'), $sd['results_click_blank']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("scroll_to_results", __('Sroll the browser window to the result list, after the search is finished?', 'ajax-search-pro'), $sd['scroll_to_results']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("resultareaclickable", __('Make the whole result area clickable?', 'ajax-search-pro'), $sd['resultareaclickable']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("close_on_document_click", __('Close results when the search looses focus?', 'ajax-search-pro'), $sd['close_on_document_click']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Closes the results list when clicking outside the search elements.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wd_TextareaExpandable("noresultstext", __('No results text', 'ajax-search-pro'), $sd['noresultstext']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Supports HTML and variable {phrase}', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wd_TextareaExpandable("didyoumeantext", __('Did you mean text', 'ajax-search-pro'), $sd['didyoumeantext']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Supports HTML', 'ajax-search-pro'); ?>
    </p>
</div>