<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

foreach ( wd_asp()->front_filters->get('position', 'generic') as $filter ) {
    include( ASP_Helpers::aspTemplateFilePath('filters/generic/asp-generic-header.php') );

    switch ($filter->display_mode) {
        case 'checkboxes':
            include(ASP_Helpers::aspTemplateFilePath('filters/generic/asp-generic-checkboxes.php'));
            break;
        case 'radio':
            include(ASP_Helpers::aspTemplateFilePath('filters/generic/asp-generic-radio.php'));
            break;
        default:
            include(ASP_Helpers::aspTemplateFilePath('filters/generic/asp-generic-dropdown.php'));
            break;
    }

    foreach ( $_st['frontend_fields']['unselected'] as $fe_field ) {
        $_chkd = '';
        switch ($fe_field) {
            case 'title':
                $_chkd = $_st['searchintitle'] == 1 ? ' checked="checked"' : "";
                break;
            case 'content':
                $_chkd = $_st['searchincontent'] == 1 ? ' checked="checked"' : "";
                break;
            case 'excerpt':
                $_chkd = $_st['searchinexcerpt'] == 1 ? ' checked="checked"' : "";
                break;
            case 'exact':
                $_chkd = $_st['exactonly'] == 1 ? ' checked="checked"' : "";
                break;
        }
        ?>
        <div class="asp_option hiddend" aria-hidden="true">
            <div class="asp_option_inner">
                <input type="checkbox" value="<?php echo esc_attr($fe_field); ?>" id="set_<?php echo esc_attr($fe_field).$id; ?>"
                       name="asp_gen[]" <?php echo $_chkd; ?>/>
                <label for="set_<?php echo esc_attr($fe_field).$id; ?>">
                    <?php echo asp_icl_t('Hidden label', 'Hidden label'); ?>
                </label>
            </div>
        </div>
    <?php }

    include(ASP_Helpers::aspTemplateFilePath('filters/generic/asp-generic-footer.php'));
}