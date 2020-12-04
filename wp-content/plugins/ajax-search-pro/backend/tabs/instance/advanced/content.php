<div class="item<?php echo class_exists('SitePress') ? "" : " hiddend"; ?>">
    <?php
    $o = new wpdreamsYesNo("wpml_compatibility", __('WPML compatibility', 'ajax-search-pro'),  $sd['wpml_compatibility']);
    $params[$o->getName()] = $o->getData();
    ?>
	<p class="descMsg">
        <?php echo __('If turned <strong>ON</strong>: return results from current language. If turned <strong>OFF</strong>: return results from any language.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item<?php echo function_exists("pll_current_language") ? "" : " hiddend"; ?>">
	<?php
	$o = new wpdreamsYesNo("polylang_compatibility", __('Polylang compatibility', 'ajax-search-pro'),  $sd['polylang_compatibility']);
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg">
        <?php echo __('If turned <strong>ON</strong>: return results from current language. If turned <strong>OFF</strong>: return results from any language.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("shortcode_op", __('What to do with shortcodes in results content?', 'ajax-search-pro'),  array(
        'selects'=>array(
            array("option"=>__('Remove them, keep the content', 'ajax-search-pro'), "value" => "remove"),
            array("option"=>__('Execute them (can by really slow)', 'ajax-search-pro'), "value" => "execute")
        ),
        'value'=>$sd['shortcode_op']
    ));
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Removing shortcode is usually <strong>much faster</strong>, especially if you have many of them within posts.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
	<?php
	$o = new wpdreamsText("striptagsexclude", __('HTML Tags exclude from stripping content', 'ajax-search-pro'),  $sd['striptagsexclude']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item item-flex-nogrow item-flex-wrap">
	<?php
	$o = new wpdreamsCustomSelect("primary_titlefield", __('Primary Title Field for Posts/Pages/CPT', 'ajax-search-pro'),  array(
		'selects'=>array(
            array('option' => 'Post Title', 'value' => 0),
            array('option' => 'Post Excerpt', 'value' => 1),
			array('option' => 'Custom Field', 'value' => 'c__f')
        ),
		'value'=>$sd['primary_titlefield']
	));
	$params[$o->getName()] = $o->getData();
	$o = new wd_CFSearchCallBack('primary_titlefield_cf', '', array(
			'value'=>$sd['primary_titlefield_cf'],
			'args'=> array(
					'controls_position' => 'left',
					'class'=>'wpd-text-right'
			)
	));
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item item-flex-nogrow item-flex-wrap">
    <?php
    $o = new wpdreamsCustomSelect("secondary_titlefield", __('Secondary Title Field for Posts/Pages/CPT', 'ajax-search-pro'),  array(
        'selects'=>array(
            array('option' => 'Disabled', 'value' => -1),
            array('option' => 'Post Title', 'value' => 0),
            array('option' => 'Post Excerpt', 'value' => 1),
			array('option' => 'Custom Field', 'value' => 'c__f')
		),
        'value'=>$sd['secondary_titlefield']
    ));
	$params[$o->getName()] = $o->getData();
	$o = new wd_CFSearchCallBack('secondary_titlefield_cf', '', array(
			'value'=>$sd['secondary_titlefield_cf'],
			'args'=> array(
					'controls_position' => 'left',
					'class'=>'wpd-text-right'
			)
	));
	$params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item item-flex-nogrow item-flex-wrap">
	<?php
	$o = new wpdreamsCustomSelect("primary_descriptionfield", __('Primary Description Field for Posts/Pages/CPT', 'ajax-search-pro'),  array(
		'selects'=>array(
            array('option' => 'Post Content', 'value' => 0),
            array('option' => 'Post Excerpt', 'value' => 1),
            array('option' => 'Post Title', 'value' => 2),
			array('option' => 'Custom Field', 'value' => 'c__f')
        ),
		'value'=>$sd['primary_descriptionfield']
	));
	$params[$o->getName()] = $o->getData();
	$o = new wd_CFSearchCallBack('primary_descriptionfield_cf', '', array(
			'value'=>$sd['primary_descriptionfield_cf'],
			'args'=> array(
					'controls_position' => 'left',
					'class'=>'wpd-text-right'
			)
	));
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item item-flex-nogrow item-flex-wrap">
    <?php
    $o = new wpdreamsCustomSelect("secondary_descriptionfield", __('Secondary Description Field for Posts/Pages/CPT', 'ajax-search-pro'),  array(
        'selects'=>array(
            array('option' => 'Disabled', 'value' => -1),
            array('option' => 'Post Content', 'value' => 0),
            array('option' => 'Post Excerpt', 'value' => 1),
            array('option' => 'Post Title', 'value' => 2),
			array('option' => 'Custom Field', 'value' => 'c__f')
        ),
        'value'=>$sd['secondary_descriptionfield']
    ));
	$params[$o->getName()] = $o->getData();
	$o = new wd_CFSearchCallBack('secondary_descriptionfield_cf', '', array(
			'value'=>$sd['secondary_descriptionfield_cf'],
			'args'=> array(
					'controls_position' => 'left',
					'class'=>'wpd-text-right'
			)
	));
	$params[$o->getName()] = $o->getData();
    ?>
</div>
<fieldset>
	<legend><?php echo __('Advanced fields', 'ajax-search-pro'); ?></legend>
	<p class='infoMsg'>
        <?php echo __('Example: <b>{titlefield} - {_price}</b> will show the title and price for a woocommerce product.', 'ajax-search-pro'); ?>&nbsp;
        <?php echo sprintf( __('For more info and more advanced uses please <a href="%s" target="_blank">check this documentation chapter</a>.', 'ajax-search-pro'), 'https://wp-dreams.com/go/?to=asp-doc-advanced-title-content' ); ?>
    </p>
	<div class="item">
		<?php
		$o = new wd_TextareaExpandable("advtitlefield", __('Advanced Title Field (default: {titlefield})', 'ajax-search-pro'),  $sd['advtitlefield']);
		$params[$o->getName()] = $o->getData();
		?>
        <p class="descMsg">
			<?php echo __('HTML is supported! Use {custom_field_name} format to have custom field values.', 'ajax-search-pro'); ?>&nbsp;
			<a href="https://wp-dreams.com/go/?to=asp-doc-advanced-title-content" target="_blank">
                <?php echo __('More possibilities explained here!', 'ajax-search-pro'); ?>
            </a>
		</p>
	</div>
	<div class="item">
		<?php
		$o = new wd_TextareaExpandable("advdescriptionfield", __('Advanced Description Field (default: {descriptionfield})', 'ajax-search-pro'),  $sd['advdescriptionfield']);
		$params[$o->getName()] = $o->getData();
		?>
        <p class="descMsg">
			<?php echo __('HTML is supported! Use {custom_field_name} format to have custom field values.', 'ajax-search-pro'); ?>&nbsp;
			<a href="https://wp-dreams.com/go/?to=asp-doc-advanced-title-content" target="_blank">
                <?php echo __('More possibilities explained here!', 'ajax-search-pro'); ?>
            </a>
		</p>
	</div>
</fieldset>