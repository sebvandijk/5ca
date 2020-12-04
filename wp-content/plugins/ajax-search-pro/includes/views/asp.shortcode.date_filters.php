<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

foreach ( wd_asp()->front_filters->get('position', 'date') as $filter ) {
    include( ASP_Helpers::aspTemplateFilePath('filters/date/asp-date-header.php') );

    include( ASP_Helpers::aspTemplateFilePath('filters/date/asp-date-filter.php') );

    include( ASP_Helpers::aspTemplateFilePath('filters/date/asp-date-footer.php') );
}