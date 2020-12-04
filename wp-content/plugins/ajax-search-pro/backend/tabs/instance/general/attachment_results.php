<div class="item">
	<?php
	$o = new wpdreamsYesNo("return_attachments", __('Return attachments as results?', 'ajax-search-pro'),
		$sd['return_attachments']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item"><?php
	$o = new wpdreamsCustomSelect("attachments_use_index", __('Search engine for attachments', 'ajax-search-pro'),
		array(
			'selects' => array(
				array('option' => 'Regular engine', 'value' => 'regular'),
				array('option' => 'Index table engine', 'value' => 'index')
			),
			'value' => $sd['attachments_use_index']
		));
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg">
        <?php echo sprintf( __('Index table engine will only work if you have the
		<a href="%s">index table</a>
		generated. To learn more about the pros. and cons. of the index table read the
		<a href="%s" target="_blank">documentation about the index table</a>.', 'ajax-search-pro'),
            get_admin_url() . 'admin.php?page=asp_index_table',
        'https://documentation.ajaxsearchpro.com/index_table.html'
        ); ?>
	</p>
</div>
<div class="item hide_on_att_index">
	<?php
	$o = new wpdreamsYesNo("search_attachments_title", __('Search in attachment titles?', 'ajax-search-pro'),
		$sd['search_attachments_title']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item hide_on_att_index">
	<?php
	$o = new wpdreamsYesNo("search_attachments_content", __('Search in attachment description?', 'ajax-search-pro'),
		$sd['search_attachments_content']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item hide_on_att_index">
    <?php
    $o = new wpdreamsYesNo("search_attachments_caption", __('Search in attachment captions?', 'ajax-search-pro'),
        $sd['search_attachments_caption']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item hide_on_att_index">
	<?php
	$o = new wpdreamsYesNo("search_attachments_ids", __('Search in attachment IDs?', 'ajax-search-pro'),
			$sd['search_attachments_ids']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item hide_on_att_index">
    <?php
    $o = new wpdreamsYesNo("search_attachments_terms", __('Search in attachment terms (tags, etc..)?', 'ajax-search-pro'),
        $sd['search_attachments_terms']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg"><?php echo __('Will search in terms (categories, tags) related to the attachments.', 'ajax-search-pro'); ?></p>
    <p class="errorMsg"><?php echo __('WARNING: <strong>Search in terms</strong> can be database heavy operation. Not recommended for big databases.', 'ajax-search-pro'); ?></p>
</div>
<div class="item item-flex-nogrow item-conditional" style="flex-wrap: wrap;">
	<?php
	$o = new wpdreamsCustomSelect("attachment_link_to", __('Link the results to', 'ajax-search-pro'),
			array(
					'selects' => array(
							array("option" => __('attachment page', 'ajax-search-pro'), "value" => "page"),
							array("option" => __('attachment file directly', 'ajax-search-pro'), "value" => "file"),
							array("option" => __('attachment parent post', 'ajax-search-pro'), "value" => "parent")
					),
					'value' => $sd['attachment_link_to']
			));
	$params[$o->getName()] = $o->getData();
	$o = new wpdreamsCustomSelect("attachment_link_to_secondary", __(' ..and if parent does not exist then ', 'ajax-search-pro'),
		array(
			'selects' => array(
				array("option" => "attachment page", "value" => "page"),
				array("option" => "attachment file directly", "value" => "file")
			),
			'value' => $sd['attachment_link_to_secondary']
		));
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item hide_on_att_index">
	<?php
	$o = new wd_Textarea_B64("attachment_mime_types", __('Allowed mime types', 'ajax-search-pro'),
		$sd['attachment_mime_types']);
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg">
        <?php echo sprintf( __('<strong>Comma separated list</strong> of allowed mime types. List of <a href="%s"
	target="_blank">default allowed mime types</a> in WordPress.', 'ajax-search-pro'), 'https://codex.wordpress.org/Function_Reference/get_allowed_mime_types' ); ?>
    </p>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("attachment_use_image", __('Use the image of image mime types as the result image?', 'ajax-search-pro'),
		$sd['attachment_use_image']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("search_attachments_cf_filters", __('Allow custom field filters to apply on Attachment results as well?', 'ajax-search-pro'),
		$sd['search_attachments_cf_filters']);
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg">
        <?php echo __('This only has effect if you have use any custom field filters.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
	<?php
	$o = new wd_TextareaExpandable("attachment_exclude", __('Exclude attachment IDs', 'ajax-search-pro'),
		$sd['attachment_exclude']);
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg"><?php echo __('<strong>Comma separated list</strong> of attachment IDs to exclude.', 'ajax-search-pro'); ?></p>
</div>