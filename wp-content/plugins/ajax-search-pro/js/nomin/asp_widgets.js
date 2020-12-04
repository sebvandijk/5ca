jQuery(function($) {
    // Top and latest searches widget
    $(".ajaxsearchprotop").each(function () {

        var params = $(this).data("aspdata");
        var id = params.id;

        if (params.action == 0) {
            $('a', this).click(function (e) {
                e.preventDefault();
            });
        } else if (params.action == 2) {
            $('a', this).click(function (e) {
                e.preventDefault();
                ASP.api(id, 'searchFor', $(this).html());
                $('html,body').animate({
                        scrollTop: $('div[id*=ajaxsearchpro' + id + '_]').first().offset().top - 40
                }, 'slow');
            });
        } else if (params.action == 1) {
            $('a', this).click(function (e) {
                if ( ASP.api(id, 'exists') ) {
                    e.preventDefault();
                    return ASP.api(id, 'searchRedirect', $(this).html());
                }
            });
        }
    });
});