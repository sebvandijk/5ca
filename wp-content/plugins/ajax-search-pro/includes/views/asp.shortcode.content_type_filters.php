<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

foreach ( wd_asp()->front_filters->get('position', 'content_type') as $filter ) {
    include( ASP_Helpers::aspTemplateFilePath('filters/content_type/asp-content_type-header.php') );

    switch ($filter->display_mode) {
        case 'checkboxes':
            include(ASP_Helpers::aspTemplateFilePath('filters/content_type/asp-content_type-checkboxes.php'));
            break;
        case 'radio':
            include(ASP_Helpers::aspTemplateFilePath('filters/content_type/asp-content_type-radio.php'));
            break;
        default:
            include(ASP_Helpers::aspTemplateFilePath('filters/content_type/asp-content_type-dropdown.php'));
            break;
    }

    include(ASP_Helpers::aspTemplateFilePath('filters/content_type/asp-content_type-footer.php'));
}