<div class="item">
    <?php
    $o = new wpdreamsYesNo("keywordsuggestions", __('Keyword suggestions on no results?', 'ajax-search-pro'),
        $sd['keywordsuggestions']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsDraggable("keyword_suggestion_source", __('Keyword suggestion sources', 'ajax-search-pro'), array(
        'selects'=> $sugg_select_arr,
        'value'=>$sd['keyword_suggestion_source'],
        'description'=>'Select which sources you prefer for keyword suggestions. Order counts.'
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item hiddend"><?php
    $o = new wpdreamsText("kws_google_places_api", __('Google places API key', 'ajax-search-pro'), $sd['kws_google_places_api']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="errorMsg">
        <?php echo sprintf( __('This is required for the Google Places API to work. You can <a href="%s" target="_blank">get your API key here</a>.', 'ajax-search-pro'),
            'https://developers.google.com/places/web-service/autocomplete' ); ?>
    </p>
</div>
<div class="item"><?php
    $o = new wpdreamsTextSmall("keyword_suggestion_count", __('Max. suggestion count', 'ajax-search-pro'),
        $sd['keyword_suggestion_count']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('The number of possible suggestions.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item"><?php
    $o = new wpdreamsTextSmall("keyword_suggestion_length", __('Max. suggestion length', 'ajax-search-pro'),
        $sd['keyword_suggestion_length']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('The length of each suggestion in characters. 30-50 is a good number to avoid too long suggestions.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item"><?php
    $o = new wpdreamsLanguageSelect("keywordsuggestionslang", __('Google keyword suggestions language', 'ajax-search-pro'),
        $sd['keywordsuggestionslang']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>