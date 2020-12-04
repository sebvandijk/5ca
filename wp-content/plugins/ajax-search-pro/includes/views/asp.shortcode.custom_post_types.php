<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

$hidden_types = array();
$displayed_custom_types = array();

foreach ( wd_asp()->front_filters->get('position', 'post_type') as $filter ) {
    include( ASP_Helpers::aspTemplateFilePath('filters/post_type/asp-post-type-header.php') );

    switch ( $filter->display_mode ) {
        case 'checkboxes':
            include(ASP_Helpers::aspTemplateFilePath('filters/post_type/asp-post-type-checkboxes.php'));
            break;
        case 'radio':
            include(ASP_Helpers::aspTemplateFilePath('filters/post_type/asp-post-type-radio.php'));
            break;
        default:
            include(ASP_Helpers::aspTemplateFilePath('filters/post_type/asp-post-type-dropdown.php'));
            break;
    }

    foreach ( $filter->get() as $item ) {
        $displayed_custom_types[] = $item->value;
    }

    include(ASP_Helpers::aspTemplateFilePath('filters/post_type/asp-post-type-footer.php'));
}

$hidden_types = array_diff($style['customtypes'], $displayed_custom_types);
if ( count($hidden_types) > 0 ) {
    foreach ($hidden_types as $k => $v) {
        ?>
        <input type="checkbox"
               style="display: none !important;"
               value="<?php echo $v; ?>"
               aria-label="<?php echo asp_icl_t('Hidden label', 'Hidden label'); ?>"
               aria-hidden="true"
               id="<?php echo $id; ?>customset_<?php echo $id . (100+$k); ?>"
               name="customset[]" checked="checked"/>
        <?php
    }
}