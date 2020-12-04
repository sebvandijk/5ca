<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

foreach ( wd_asp()->front_filters->get('position', 'custom_field') as $k => $filter ) {
    // $filter variable is an instance of aspTaxFilter object
    // $filter->get() will return the array of filter objects (of stdClass)

    // Unique fieldset identifier ID, for cases, when there are multiple filters used on the same field
    //$fieldset_id = $id.$k;
    $fieldset_id = $id.$filter->id;

    // Field name, supporting brackets
    /*$field_name = str_replace(
        array('[', ']'),
        array('!_brktl_!', '!_brktr_!'),
        $filter->data['field']
    ) . "_" . $fieldset_id;*/
    $field_name = $filter->getUniqueFieldName(true);

    // Field name without white space
    $field_name_nws = preg_replace('/\s+/', '', $field_name);

    include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-header.php'));

    switch ($filter->display_mode) {
        case 'checkboxes':
            include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-checkboxes.php'));
            break;
        case 'dropdown':
            include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-dropdown.php'));
            break;
        case 'dropdownsearch':
            include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-dropdownsearch.php'));
            break;
        case 'multisearch':
            include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-multisearch.php'));
            break;
        case 'radio':
            include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-radio.php'));
            break;
        case 'slider':
            include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-slider.php'));
            break;
        case 'range':
            include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-range.php'));
            break;
        case 'datepicker':
            include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-datepicker.php'));
            break;
        case 'text':
            include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-text.php'));
            break;
        case 'hidden':
            include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-hidden.php'));
            break;
        default:
            include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-checkboxes.php'));
            break;
    }

    include(ASP_Helpers::aspTemplateFilePath('filters/custom_field/asp-cf-footer.php'));
}