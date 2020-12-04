<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

foreach ( wd_asp()->front_filters->get('position', 'post_tags') as $k => $filter ) {
    // $filter variable is an instance of aspPostTagsFilter object
    // $filter->get() will return the array of filter objects (of stdClass)

    // Some local variables for ease of use within the theme
    $taxonomy = 'post_tag';

    include(ASP_Helpers::aspTemplateFilePath('filters/taxonomy/asp-tax-header.php'));

    switch ($filter->display_mode) {
        case 'checkboxes':
            include(ASP_Helpers::aspTemplateFilePath('filters/taxonomy/asp-tax-checkboxes.php'));
            break;
        case 'dropdown':
            include(ASP_Helpers::aspTemplateFilePath('filters/taxonomy/asp-tax-dropdown.php'));
            break;
        case 'dropdownsearch':
            include(ASP_Helpers::aspTemplateFilePath('filters/taxonomy/asp-tax-dropdownsearch.php'));
            break;
        case 'multisearch':
            include(ASP_Helpers::aspTemplateFilePath('filters/taxonomy/asp-tax-multisearch.php'));
            break;
        case 'radio':
            include(ASP_Helpers::aspTemplateFilePath('filters/taxonomy/asp-tax-radio.php'));
            break;
        default:
            include(ASP_Helpers::aspTemplateFilePath('filters/taxonomy/asp-tax-checkboxes.php'));
            break;
    }

    include(ASP_Helpers::aspTemplateFilePath('filters/taxonomy/asp-tax-footer.php'));
}