<?php

foreach ( wd_asp()->front_filters->get('position', 'button') as $k => $filter ) {
    include(ASP_Helpers::aspTemplateFilePath('filters/button/asp-button-header.php'));

    include(ASP_Helpers::aspTemplateFilePath('filters/button/asp-button-filter.php'));

    include(ASP_Helpers::aspTemplateFilePath('filters/button/asp-button-footer.php'));
}

return;