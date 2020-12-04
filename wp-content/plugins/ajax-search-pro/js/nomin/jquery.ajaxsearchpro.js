/*! Ajax Search pro 4.11.10 js */
(function ($) {
    var instData = {};
    var prevState;
    var firstIteration = true;
    var methods = {

        "errors": {
            "noui": {
                "msg": "NOUI script is not loaded, try saving the search settings and clearing the cache",
                "raised": false,
                "repeat": false
            },
            "isotope": {
                "msg": "Isotope script is not loaded, try saving the search settings and clearing the cache",
                "raised": false,
                "repeat": false
            },
            "polaroid": {
                "msg": "Polaroid script is not loaded, try saving the search settings and clearing the cache",
                "raised": false,
                "repeat": false
            },
            "datepicker": {
                "msg": "Datepicker script is not loaded, try saving the search settings and clearing the cache",
                "raised": false,
                "repeat": false
            },
            "chosen": {
                "msg": "Chosen script is not loaded, try saving the search settings and clearing the cache",
                "raised": false,
                "repeat": false
            },
            "missing_response": {
                "msg": "The response data is missing from the ajax request!\n" +
                "This could mean a server related issue.\n\n" +
                "Check your .htaccess configuration and try disabling all other plugins to see if the problem persists.",
                "raised": false,
                "repeat": true,
                "force": true
            }
        },

        raiseError: function( error ) {
            var $this = this;
            // Prevent alert and console flooding
            if ( !$this.errors[error].raised || $this.errors[error].repeat ) {
                var msg = 'Ajax Search Pro Warning: ' + $this.errors[error].msg;
                if ( ASP.debug || typeof $this.errors[error].force != 'undefined' )
                    alert(msg);
                console.log(msg);
                $this.errors[error].raised = true;
            }
        },

        init: function (options, elem) {
            var $this = this;

            this.elem = elem;
            this.$elem = $(elem);

            $this.searching = false;
            $this.o = $.extend({}, options);
            $this.n = {};
            $this.n.container =  $(this.elem);
            $this.n.c =  $this.n.container;

            var idArr = $this.n.container.attr('id').match(/^ajaxsearchpro(.*)_(.*)/);
            $this.o.rid = idArr[1] + "_" + idArr[2];
            $this.o.iid = idArr[2];
            $this.o.id = idArr[1];
            $this.o.name = $this.n.container.data('name');

            instData[$this.o.rid] = this;
            ASP.instances = instData;

            $this.n.probox = $('.probox', $this.n.container);
            $this.n.proinput = $('.proinput', $this.n.container);
            $this.n.text = $('.proinput input.orig', $this.n.container);
            $this.n.textAutocomplete = $('.proinput input.autocomplete', $this.n.container);
            $this.n.loading = $('.proinput .loading', $this.n.container);
            $this.n.proloading = $('.proloading', $this.n.container);
            $this.n.proclose = $('.proclose', $this.n.container);
            $this.n.promagnifier = $('.promagnifier', $this.n.container);
            $this.n.prosettings = $('.prosettings', $this.n.container);
            $this.n.searchsettings = $('#ajaxsearchprosettings' + $this.o.rid);
            $this.n.trythis = $("#asp-try-" + $this.o.rid);
            $this.o.blocking = false;
            $this.resultsOpened = false;
            if ($this.n.searchsettings.length <= 0) {
                $this.n.searchsettings = $('#ajaxsearchprobsettings' + $this.o.rid);
                $this.o.blocking = true;
            }
            $this.n.resultsDiv = $('#ajaxsearchprores' + $this.o.rid);
            $this.n.hiddenContainer = $('#asp_hidden_data');
            $this.n.hiddenContainer2 = $('#asp_hidden_data_' + $this.o.rid);
            $this.n.aspItemOverlay = $('.asp_item_overlay', $this.n.hiddenContainer2);

            $this.resizeTimeout = null;
            $this.triggerPrevState = false;
            $this.isAutoP = false;
            $this.settingsChanged = false;

            if ( typeof($.browser) != 'undefined' &&
                typeof($.browser.mozilla) != 'undefined' &&
                typeof($.browser.version) != 'undefined' &&
                parseInt($.browser.version) > 13
            )
                $this.n.searchsettings.addClass('asp_firefox');

            $this.n.showmore = $('.showmore', $this.n.resultsDiv);
            $this.n.items = $('.item', $this.n.resultsDiv).length > 0 ? $('.item', $this.n.resultsDiv) : $('.photostack-flip', $this.n.resultsDiv);
            $this.n.results = $('.results', $this.n.resultsDiv);
            $this.n.resdrg = $('.resdrg', $this.n.resultsDiv);

            // Result page live loader disabled for compact layout modes
            $this.o.resPage.useAjax = $this.o.compact.enabled ? 0 : $this.o.resPage.useAjax;

            // Mobile changes
            if ( isMobile() ) {
                $this.o.trigger.type = $this.o.mobile.trigger_on_type;
                $this.o.trigger.click = $this.o.mobile.click_action;
                $this.o.trigger.click_location = $this.o.mobile.click_action_location;
                $this.o.trigger.return = $this.o.mobile.return_action;
                $this.o.trigger.return_location = $this.o.mobile.return_action_location;
                $this.o.trigger.redirect_url = $this.o.mobile.redirect_url;
                $this.o.trigger.elementor_url = $this.o.mobile.elementor_url;
            }
            $this.o.redirectOnClick = $this.o.trigger.click != 'ajax_search' && $this.o.trigger.click != 'nothing';
            $this.o.redirectOnEnter = $this.o.trigger.return != 'ajax_search' && $this.o.trigger.return != 'nothing';
            $this.usingLiveLoader = ( $this.o.resPage.useAjax && $($this.o.resPage.selector).length > 0 ) || $('.asp_es_' + $this.o.id).length > 0;
            if ( $this.usingLiveLoader ) {
                $this.o.trigger.type = $this.o.resPage.trigger_type;
                $this.o.trigger.facet = $this.o.resPage.trigger_facet;
                $this.o.redirectOnClick = $this.o.resPage.trigger_magnifier == 0;
                if ( !$this.o.redirectOnClick ) {
                    $this.o.trigger.click = 'ajax_search';
                }
                $this.o.redirectOnEnter = $this.o.resPage.trigger_return == 0;
                if ( !$this.o.redirectOnEnter ) {
                    $this.o.trigger.return = 'ajax_search';
                }
            }

            /**
             * on IOS touch (iPhone, iPad etc..) the 'click' event does not fire, when not bound to a clickable element
             * like a link, so instead, use touchend
             * Stupid solution, but it works..
             */
            if ( detectIOS() && isMobile() && is_touch_device() ) {
                $this.clickTouchend = 'touchend';
                $this.mouseupTouchend = 'touchend';
            } else {
                $this.clickTouchend = 'click touchend';
                $this.mouseupTouchend = 'mouseup touchend';
            }

            // Move the try-this keywords to the correct position
            $this.n.trythis.detach().insertAfter($this.n.container);

            // Isotopic Layout variables
            $this.il = {
                columns: 3,
                rows: $this.o.isotopic.pagination ? $this.o.isotopic.rows : 10000,
                itemsPerPage: 6,
                lastVisibleItem: -1
            };

            // NoUiSliders storage
            $this.noUiSliders = [];

            // An object to store various timeout events across methods
            $this.timeouts = {
                "compactBeforeOpen": null,
                "compactAfterOpen": null,
                "searchWithCheck": null
            };

            $this.firstClick = true;
            $this.post = null;
            $this.postAuto = null;
            $this.cleanUp();

            $this.n.textAutocomplete.val('');
            $this.o.resultitemheight = parseInt($this.o.resultitemheight);
            $this.scroll = {};
            $this.savedScrollTop = 0;   // Save the window scroll on IOS devices
            $this.savedContainerTop = 0;
            $this.is_scroll = typeof asp_SimpleBar != "undefined";
            // Force noscroll on minified version
            if ( typeof ASP.scrollbar != "undefined" && ASP.scrollbar == 0 )
                $this.is_scroll = false;
            $this.settScroll = null;
            $this.n.resultsAppend = $('#wpdreams_asp_results_' + $this.o.id);
            $this.n.settingsAppend = $('#wpdreams_asp_settings_' + $this.o.id);
            $this.currentPage = 1;
            $this.isotopic = null;
            $this.sIsotope = null;
            $this.lastSuccesfulSearch = ''; // Holding the last phrase that returned results
            $this.lastSearchData = {};      // Store the last search information
            $this.supportTransform = getSupportedTransform();
            $this._no_animations = false; // Force override option to show animations

            // Results information box original texts
            $this.resInfoBoxTxt =
                $this.n.resultsDiv.find('.asp_results_top p.asp_rt_phrase').length > 0 ?
                    $this.n.resultsDiv.find('.asp_results_top p.asp_rt_phrase').html() : '';
            $this.resInfoBoxTxtNoPhrase =
                $this.n.resultsDiv.find('.asp_results_top p.asp_rt_nophrase').length > 0 ?
                    $this.n.resultsDiv.find('.asp_results_top p.asp_rt_nophrase').html() : '';

            // Repetitive call related
            $this.call_num = 0;
            $this.results_num = 0;

            // Make parsing the animation settings easier
            if ( isMobile() )
                $this.animOptions = $this.o.animations.mob;
            else
                $this.animOptions = $this.o.animations.pc;

            // Lazy plugin instance store
            $this.asp_lazy = false;

            // A weird way of fixing HTML entity decoding from the parameter
            $this.o.trigger.redirect_url = decodeHTMLEntities($this.o.trigger.redirect_url);
            $this.o.trigger.elementor_url = decodeHTMLEntities($this.o.trigger.elementor_url);


            /**
             * Default animation opacity. 0 for IN types, 1 for all the other ones. This ensures the fluid
             * animation. Wrong opacity causes flashes.
             * @type {number}
             */
            $this.animationOpacity = $this.animOptions.items.indexOf("In") < 0 ? "opacityOne" : "opacityZero";

            $this.filterFns = {
                number: function () {
                    var $parent = $(this).parent();
                    while (!$parent.hasClass('isotopic')) {
                        $parent = $parent.parent();
                    }
                    var number = $(this).attr('data-itemnum');
                    //var currentPage = parseInt($('nav>ul li.asp_active span', $parent).html(), 10);
                    var currentPage = $this.currentPage;
                    //var itemsPerPage = parseInt($parent.data("itemsperpage"));
                    var itemsPerPage = $this.il.itemsPerPage;

                    if ( ( number % ($this.il.columns * $this.il.rows) ) < ($this.il.columns * ($this.il.rows-1) ))
                        $(this).addClass('asp_gutter_bottom');
                    else
                        $(this).removeClass('asp_gutter_bottom');

                    return (
                        (parseInt(number, 10) < itemsPerPage * currentPage) &&
                        (parseInt(number, 10) >= itemsPerPage * (currentPage - 1))
                    );
                }
            };

            // Extend the easing functions
            $.easing.easeOutQuad = function (x, t, b, c, d) {
                return -c *(t/=d)*(t-2) + b;
            }

            if ( $this.o.compact.overlay == 1 && $("#asp_absolute_overlay").length <= 0 )
                $("<div id='asp_absolute_overlay'></div>").appendTo("body");

            $this.disableMobileScroll = false;

            $this.originalFormData = formData($('form', $this.n.searchsettings));

            // Browser back button detection and
            if ( ASP.js_retain_popstate == 1 )
                $this.initPrevState();

            // Fixes the fixed layout mode if compact mode is active and touch device fixes
            $this.initCompact();

            // Make corrections if needed for the settings box
            $this.initSettingsBox();

            // Make corrections if needed for the results box
            $this.initResultsBox();

            // Try detecting a parent fixed position, and change the results and settings position accordingly
            $this.detectAndFixFixedPositioning();

            // Sets $this.dragging to true if the user is dragging on a touch device
            $this.monitorTouchMove();

            // Yea, go figure...
            if (detectOldIE())
                $this.n.container.addClass('asp_msie');

            // Calculates the settings animation attributes
            $this.initSettingsAnimations();

            // Calculates the results animation attributes
            $this.initResultsAnimations();

            // Rest of the events
            $this.initEvents();

            // Auto populate init
            $this.initAutop();

            // Etc stuff..
            $this.initEtc();

            // Init infinite scroll
            $this.initInfiniteScroll();

            // Fix any initial Accessibility issues
            $this.fixAccessibility();

            // After the first execution, this stays false
            firstIteration = false;

            // Init complete event trigger
            $this.n.c.trigger("asp_init_search_bar", [$this.o.id, $this.o.iid]);

            return this;
        },

        initPrevState: function() {
            var $this = this;

            // Browser back button check first, only on first init iteration
            if ( firstIteration && prevState == null ) {
                prevState = localStorage.getItem('asp-' + Base64.encode(location.href));
                if ( prevState != null ) {
                    prevState = JSON.parse(prevState);
                    prevState.settings = Base64.decode(prevState.settings);
                }
            }
            if ( prevState != null && typeof prevState.id != 'undefined' ) {
                if ( prevState.id == $this.o.id && prevState.instance == $this.o.iid ) {
                    if (prevState.phrase != '') {
                        $this.triggerPrevState = true;
                        $this.n.text.val(prevState.phrase);
                    }
                    if ( formData($('form', $this.n.searchsettings)) != prevState.settings ) {
                        $this.triggerPrevState = true;
                        formData( $('form', $this.n.searchsettings), prevState.settings );
                    }
                }
            }

            // Reset storage
            localStorage.removeItem('asp-' + Base64.encode(location.href));
            // Set the event
            $this.n.resultsDiv.on('click', '.results .item', function() {
                var phrase = $this.n.text.val();
                if ( phrase != '' || $this.settingsChanged ) {
                    var stateObj = {
                        'id': $this.o.id,
                        'instance': $this.o.iid,
                        'phrase': phrase,
                        'settings': Base64.encode( formData($('form', $this.n.searchsettings)) )
                    };
                    localStorage.setItem('asp-' + Base64.encode(location.href), JSON.stringify(stateObj));
                }
            });
        },

        initCompact: function() {
            var $this = this;

            // Reset the overlay no matter what, if the is not fixed
            if ( $this.o.compact.enabled == 1 && $this.o.compact.position != 'fixed' )
                $this.o.compact.overlay = 0;

            if ( $this.o.compact.enabled == 1 )
                $this.n.trythis.css({
                    display: "none"
                });

            if ( $this.o.compact.enabled == 1 && $this.o.compact.position == 'fixed' ) {

                /**
                 * If the conditional CSS loader is enabled, the required
                 * search CSS file is not present when this code is executed.
                 * Therefore the search box is not in position and the
                 * originalContainerOffTop will equal 0
                 * The solution is to run this code in intervals and check
                 * if the container position is changed to fixed. If so, the
                 * search CSS is loaded.
                 */
                var iv = setInterval( function() {

                    // Not fixed yet, the CSS file is not loaded, continue
                    if ( $this.n.container.css('position') != "fixed" )
                        return;

                    $this.n.container.detach().appendTo("body");
                    $this.n.trythis.detach().appendTo("body");

                    // Fix the container position to a px value, even if it is set to % value initially, for better compatibility
                    $this.n.container.css({
                        top: $this.n.container.offset().top - $(document).scrollTop()
                    });
                    clearInterval(iv);

                }, 200);

            }
        },

        initSettingsBox: function() {
            var $this = this;

            if ( isMobile() && $this.o.mobile.force_sett_hover == 1) {
                $this.n.searchsettings.attr(
                    "id",
                    $this.n.searchsettings.attr("id").replace('probsettings', 'prosettings')
                );
                $this.n.searchsettings.detach().appendTo("body");
                $this.n.searchsettings.css({
                    'position': 'absolute'
                });
                $this.o.blocking = false;
                $this.detectAndFixFixedPositioning();
                return true;
            }

            if ($this.n.settingsAppend.length > 0) {
                /*
                 When the search settings is set to hovering, but the settings
                 shortcode is used, we need to force the blocking behavior,
                 since the user expects it.
                 */

                // There is already a results box there
                if ( $this.n.settingsAppend.find('.asp_w').length > 0 ) {
                    $this.n.searchsettings = $this.n.settingsAppend.find('.asp_w');
                } else {
                    if ( $this.o.blocking == false ) {
                        $this.n.searchsettings.attr(
                            "id",
                            $this.n.searchsettings.attr("id").replace('prosettings', 'probsettings')
                        );
                        $this.o.blocking = true;
                    }
                    $this.n.searchsettings.detach().appendTo($this.n.settingsAppend);
                }

            } else if ($this.o.blocking == false) {
                $this.n.searchsettings.detach().appendTo("body");
            }
        },

        initResultsBox: function() {
            var $this = this;

            if ( isMobile() && $this.o.mobile.force_res_hover == 1) {
                $this.o.resultsposition = 'hover';
                $this.n.resultsDiv.detach().appendTo("body");
                $this.n.resultsDiv.css({
                    'position': 'absolute'
                });
                $this.detectAndFixFixedPositioning();
            } else {
                // Move the results div to the correct position
                if ($this.o.resultsposition == 'hover' && $this.n.resultsAppend.length <= 0) {
                    $this.n.resultsDiv.detach().appendTo("body");
                } else  {
                    $this.o.resultsposition = 'block';
                    $this.n.resultsDiv.css({
                        'position': 'static'
                    });
                    if ( $this.n.resultsAppend.length > 0  ) {
                        if ( $this.n.resultsAppend.find('.asp_w').length > 0 ) {
                            $this.n.resultsDiv = $this.n.resultsAppend.find('.asp_w');
                            $this.n.showmore = $('.showmore', $this.n.resultsDiv);
                            $this.n.items = $('.item', $this.n.resultsDiv).length > 0 ? $('.item', $this.n.resultsDiv) : $('.photostack-flip', $this.n.resultsDiv);
                            $this.n.results = $('.results', $this.n.resultsDiv);
                            $this.n.resdrg = $('.resdrg', $this.n.resultsDiv);
                        } else {
                            $this.n.resultsDiv.detach().appendTo($this.n.resultsAppend);
                        }
                    } else {
                        $this.n.resultsDiv.detach().insertAfter($this.n.container);
                    }

                }
            }

            if ($this.o.resultstype == 'polaroid')
                $this.n.results.addClass('photostack');
        },

        initInfiniteScroll: function() {
            // NOTE: Custom Scrollbar triggers are under the scrollbar script callbacks -> OnTotalScroll callbacks
            var $this = this;

            if ( $this.o.show_more.infinite && $this.o.resultstype != 'polaroid' ) {
                // Vertical & Horizontal: Regular scroll + when custom scrollbar scroll is not present
                // Isotopic: Regular scroll on non-paginated layout
                var t;
                $(window).add($this.n.results).on('scroll', function () {
                    clearTimeout(t);
                    t = setTimeout(function(){
                        $this.checkAndTriggerInfiniteScroll('window');
                    }, 80);
                });

                var tt;
                $this.n.resultsDiv.on('nav_switch', function (e) {
                    // Delay this a bit, in case the user quick-switches
                    clearTimeout(tt);
                    tt = setTimeout(function(){
                        $this.checkAndTriggerInfiniteScroll('isotopic');
                    }, 800);
                });
            }
        },

        monitorTouchMove: function() {
            var $this = this;
            $this.dragging = false;
            $("body").on("touchmove", function(){
                $this.dragging = true;
            });
            $("body").on("touchstart", function(){
                $this.dragging = false;
            });
        },

        duplicateCheck: function() {
            var $this = this;
            var duplicateChk = {};

            $('div[id*=ajaxsearchpro]').each (function () {
                if (duplicateChk.hasOwnProperty(this.id)) {
                    $(this).remove();
                } else {
                    duplicateChk[this.id] = 'true';
                }
            });
        },

        gaPageview: function(term) {
            var $this = this;
            var tracking_id = $this.gaGetTrackingID();

            if ( typeof ASP.analytics == 'undefined' || ASP.analytics.method != 'pageview' )
                return false;

            if ( ASP.analytics.string != '' ) {
                // YOAST uses __gaTracker, if not defined check for ga, if nothing go null, FUN EH??
                var _ga = typeof __gaTracker == "function" ? __gaTracker : (typeof ga == "function" ? ga : false);
                var _gtag = typeof gtag == "function" ? gtag : false;

                if (!window.location.origin) {
                    window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port : '');
                }
                // Multisite Subdirectory (if exists)
                var url = $this.o.homeurl.replace(window.location.origin, '');

                // GTAG bypass pageview tracking method
                if ( _gtag !== false ) {
                    if ( tracking_id !== false ) {
                        _gtag('config', tracking_id, {'page_path': url + ASP.analytics.string.replace("{asp_term}", term)});
                    }
                } else if ( _ga !== false ) {
                    if ( tracking_id !== false ) {
                        _ga('create', tracking_id, 'auto');
                    }
                    _ga('send', 'pageview', {
                        'page': url + ASP.analytics.string.replace("{asp_term}", term),
                        'title': 'Ajax Search'
                    });
                }
            }
        },

        gaEvent: function(which, data) {
            var $this = this;
            var tracking_id = $this.gaGetTrackingID();

            if ( typeof ASP.analytics == 'undefined' || ASP.analytics.method != 'event' )
                return false;

            // Get the scope
            var _gtag = typeof gtag == "function" ? gtag : false;
            var _ga = typeof __gaTracker == "function" ? __gaTracker : (typeof ga == "function" ? ga : false);

            if ( _gtag === false && _ga === false )
                return false;

            if (
                typeof (ASP.analytics.event[which]) != 'undefined' &&
                ASP.analytics.event[which].active == 1 &&
                typeof 'gtag' != 'undefined'
            ) {
                var def_data = {
                    "search_id": $this.o.id,
                    "search_name": $this.o.name,
                    "phrase": $this.n.text.val(),
                    "option_name": '',
                    "option_value": '',
                    "result_title": '',
                    "result_url": '',
                    "results_count": ''
                };
                var event = {
                    'event_category': ASP.analytics.event[which].category,
                    'event_label': ASP.analytics.event[which].label,
                    'value': ASP.analytics.event[which].value
                };
                data = $.extend(def_data, data);
                $.each(data, function (k, v) {
                    v = String(v).replace(/[\s\n\r]+/g, " ").trim();
                    $.each(event, function (kk, vv) {
                        var regex = new RegExp('\{' + k + '\}', 'gmi');
                        event[kk] = vv.replace(regex, v);
                    });
                });
                if ( _gtag === false ) {
                    if ( tracking_id !== false ) {
                        _ga('create', tracking_id, 'auto');
                    }
                    _ga('send', 'event',
                        event.event_category,
                        ASP.analytics.event[which].action,
                        event.event_label,
                        event.value
                    );
                } else {
                    if ( tracking_id !== false ) {
                        event.send_to = tracking_id;
                    }
                    _gtag('event', ASP.analytics.event[which].action, event);
                }
            }
        },

        gaGetTrackingID: function() {
            var $this = this;
            var ret = false;

            if ( typeof ASP.analytics == 'undefined' )
                return ret;

            if ( typeof ASP.analytics.tracking_id != 'undefined' && ASP.analytics.tracking_id != '' ) {
                return ASP.analytics.tracking_id;
            } else {
                // GTAG bypass pageview tracking method
                var _gtag = typeof gtag == "function" ? gtag : false;
                if ( _gtag !== false && typeof ga != 'undefined' && typeof ga.getAll != 'undefined' ) {
                    var id = false;
                    ga.getAll().forEach( function(tracker) {
                        id = tracker.get('trackingId');
                    });
                    return id;
                }
            }

            return ret;
        },

        createResultsScroll: function(type) {
            var $this = this;
            var t;
            var $resScroll = $this.n.results;
            type = typeof type == 'undefined' ? 'vertical' : type;
            if ($this.is_scroll && typeof $this.scroll.recalculate === 'undefined') {
                // New Scrollbar
                $this.scroll = new asp_SimpleBar($this.n.results.get(0), {
                    direction: $('body').hasClass('rtl') ? 'rtl' : 'ltr',
                    autoHide: $this.o.scrollBar.vertical.autoHide
                });
                $resScroll = $resScroll.add($this.scroll.getScrollElement());
            }
            $resScroll.on('scroll', function() {
                if ( typeof $this.asp_lazy.update != 'undefined' ) {
                    $this.asp_lazy.update();
                }
                if ( $this.o.show_more.infinite ) {
                    clearTimeout(t);
                    t = setTimeout(function () {
                        $this.checkAndTriggerInfiniteScroll(type);
                    }, 60);
                }
            });
        },

        createVerticalScroll: function () {
            var $this = this;
            $this.createResultsScroll('vertical')
        },

        createHorizontalScroll: function () {
            var $this = this;
            $this.createResultsScroll('horizontal')
        },

        checkAndTriggerInfiniteScroll: function( caller ) {
            var $this = this;
            var $r = $('.item', $this.n.resultsDiv);
            caller = typeof caller == 'undefined' ? 'window' : caller;

            // Show more might not even visible
            if ($this.n.showmore.length == 0 || $this.n.showmore.css('display') == 'none') {
                return false;
            }

            if ( caller == 'window' || caller == 'horizontal' ) {
                // Isotopic pagination present? Abort.
                if (
                    $this.o.resultstype == 'isotopic' &&
                    $('nav.asp_navigation', $this.n.resultsDiv).css('display') != 'none'
                ) {
                    return false;
                }

                var onViewPort = $r.last().is(':in-viewport(0, .asp_r_' + $this.o.rid + ')');
                var onScreen = $r.last().is(':in-viewport(0)');
                if (
                    !$this.searching &&
                    $r.length > 0 &&
                    onViewPort && onScreen
                ) {
                    $this.n.showmore.find('a.asp_showmore').trigger('click');
                }
            } else if ( caller == 'vertical' ) {
                var $scrollable = $this.n.resultsDiv.find('.asp_simplebar-content-wrapper').length > 0 ?
                    $this.n.resultsDiv.find('.asp_simplebar-content-wrapper') : $this.n.results;
                if ( isScrolledToBottom($scrollable.get(0), 20) ) {
                    $this.n.showmore.find('a.asp_showmore').trigger('click');
                }
            } else if ( caller == 'isotopic' ) {
                var $r = $('.item', $this.n.resultsDiv);
                if (
                    !$this.searching &&
                    $r.length > 0 &&
                    $this.n.resultsDiv.find('nav.asp_navigation ul li').last().hasClass('asp_active')
                ) {
                    $this.n.showmore.find('a.asp_showmore').trigger('click');
                }
            }
        },

        initAutop: function () {
            var $this = this;

            // Trigger the prevState here, as it is kind of auto-populate
            if ( prevState != null && !$this.o.compact.enabled && $this.triggerPrevState ) {
                $this.searchWithCheck(800);
                prevState = null;
                return false; // Terminate at this point, to prevent auto-populate
            }
            // -------------------------------

            if ( $this.o.autop.state == "disabled" ) return false;

            var location = window.location.href;
            // Correct previous query arguments (in case of paginated results)
            var stop = location.indexOf('asp_ls=') > -1 || location.indexOf('asp_ls&') > -1;

            if ( stop ) {
                return false;
            }

            var count = $this.o.show_more.enabled && $this.o.show_more.action == 'ajax' ? false : $this.o.autop.count;
            var i = 0;
            var x = setInterval(function(){
                if ( ASP.css_loaded == true ) {
                    $this.isAutoP = true;
                    if ($this.o.autop.state == "phrase") {
                        $this.n.text.val($this.o.autop.phrase);
                        $this.search(count);
                    } else if ($this.o.autop.state == "latest") {
                        $this.search(count, 1);
                    } else {
                        $this.search(count, 2);
                    }
                    clearInterval(x);
                }

                i++;
                if ( i > 6 )
                    clearInterval(x);
            }, 500);
        },

        initEtc: function() {
            var $this = this;
            var t = null;

            // Make the try-these keywords visible, this makes sure that the styling occurs before visibility
            $this.n.trythis.css({
                visibility: "visible"
            });

            // Emulate click on checkbox on the whole option
            //$('div.asp_option', $this.n.searchsettings).on('mouseup touchend', function(e){
            $('div.asp_option', $this.n.searchsettings).on($this.mouseupTouchend, function(e){
                e.preventDefault(); // Stop firing twice on mouseup and touchend on mobile devices
                e.stopImmediatePropagation();

                if ( $this.dragging ) {
                    return false;
                }
                $('input[type="checkbox"]', this).prop("checked", !$('input[type="checkbox"]', this).prop("checked"));
                // Trigger a custom change event, for max compatibility
                // .. the original change is buggy for some installations.
                clearTimeout(t);
                var _this = this;
                t = setTimeout(function() {
                    $('input[type="checkbox"]', _this).trigger('asp_chbx_change');
                }, 50);

            });

            $('div.asp_option label', $this.n.searchsettings).click(function(e){
                e.preventDefault(); // Let the previous handler handle the events, disable this
            });

            // Change the state of the choose any option if all of them are de-selected
            $('fieldset.asp_checkboxes_filter_box', $this.n.searchsettings).each(function(){
                var all_unchecked = true;
                $('.asp_option:not(.asp_option_selectall) input[type="checkbox"]', this).each(function(){
                    if ($(this).prop('checked') == true) {
                        all_unchecked = false;
                        return false;
                    }
                });
                if ( all_unchecked ) {
                    $('.asp_option_selectall input[type="checkbox"]', this).prop('checked', false);
                }
            });

            // Mark last visible options
            $('fieldset' ,$this.n.searchsettings).each(function(){
                $('.asp_option:not(.hiddend)', this).last().addClass("asp-o-last");
            });

            // Select all checkboxes
            $('.asp_option_cat input[type="checkbox"], .asp_option_cff input[type="checkbox"]', $this.n.searchsettings).on('asp_chbx_change', function(e){
                var className = $(this).data("targetclass");
                if ( typeof className == 'string' && className != '')
                    $("input." + className, $this.n.searchsettings).prop("checked", $(this).prop("checked"));
            });

            // GTAG on results click
            $this.n.resultsDiv.on('click', '.results .item', function() {
                $this.gaEvent('result_click', {
                   'result_title': $(this).find('a.asp_res_url').text(),
                   'result_url': $(this).find('a.asp_res_url').attr('href')
                });
            });

            // Elementor fixes
            $this.fixElementorPostPagination(location.href);
        },

        initEvents: function () {
            var $this = this;
            $this.initInputEvents();
            $this.initSettingsEvents();
            $this.initResultsEvents();
            $this.initOtherEvents();
            $this.initTryThisEvents();
            $this.initNavigationEvent();
            $this.initMagnifierEvent();
            $this.initAutocompleteEvent();
            $this.initPagerEvent();
            $this.initOverlayEvent();
            $this.initNoUIEvents();
            $this.initDatePicker();
            $this.initCFDatePicker();
            $this.initChosen();
            $this.initFacetEvents();
        },

        initInputEvents: function() {
            var $this = this;
            // Some kind of crazy rev-slider fix
            $this.n.text.click(function(e){
                $(this).focus();
                $this.gaEvent('focus');
            });
            $this.n.text.on('focus input', function(e){;
                if ( $this.searching ) return;
                if ( $(this).val() != '' ) {
                    $this.n.proclose.css('display', 'block');
                } else {
                    $this.n.proclose.css({
                        display: "none"
                    });
                }
            });
            // Handle the submit/mobile search button event
            $($this.n.text.closest('form')).submit(function (e, args) {
                e.preventDefault();
                // Mobile keyboard search icon and search button
                if ( isMobile() ) {
                    if ( $this.o.redirectOnEnter ) {
                        var _e = jQuery.gaEvent("keyup");
                        _e.keyCode = _e.which = 13;
                        $this.n.text.trigger(_e);
                    } else {
                        $this.search();
                        document.activeElement.blur();
                    }
                } else if (typeof(args) != 'undefined' && args == 'ajax') {
                    $this.search();
                }
            });
        },

        initSettingsEvents: function() {
            var $this = this;

            // Note if the settings have changed
            $this.n.searchsettings.on('click', function(){
                $this.settingsChanged = true;
            });

            $this.n.searchsettings.on($this.clickTouchend, function (e) {
                /**
                 * Stop propagation on settings clicks, except the noUiSlider handler event.
                 * If noUiSlider event propagation is stopped, then the: set, end, change events does not fire properly.
                 */
                if ( typeof e.target != 'undefined' && !$(e.target).hasClass('noUi-handle') ) {
                    e.stopImmediatePropagation();
                } else {
                    // For noUI case, still cancel if this is a click (desktop device)
                    if ( e.type == 'click' )
                        e.stopImmediatePropagation();
                }
            });

            $this.n.prosettings.on("click", function () {
                if ($this.n.prosettings.data('opened') == 0) {
                    $this.showSettings();
                } else {
                    $this.hideSettings();
                }
            });

            if ( isMobile() && $this.o.mobile.force_sett_hover == 1 ) {
                if ( $this.o.mobile.force_sett_state == "open" )
                    $this.n.prosettings.click();
            } else if ($this.o.settingsVisible == 1) {
                $this.n.prosettings.click();
            }

            // Category level automatic checking and hiding
            $('.asp_option_cat input[type="checkbox"]', $this.n.searchsettings).on('asp_chbx_change', function(e){
                $this.settingsCheckboxToggle( $(this).closest('.asp_option_cat') );
            });
            // Init the hide settings
            $('.asp_option_cat', $this.n.searchsettings).each(function(){
                $this.settingsCheckboxToggle( $(this), false );
            });
        },

        initResultsEvents: function() {
            var $this = this;

            $this.n.resultsDiv.css({
                opacity: 0
            });
            $(document).on($this.clickTouchend, function (e) {
                var keycode =  e.keyCode || e.which;
                var ktype = e.type;

                if ( $(e.target).closest('.ui-datepicker').length == 0 && $(e.target).closest('.noUi-handle').length == 0 ) {
                    if ( $this.o.blocking == false ) $this.hideSettings();
                }

                $this.hideOnInvisibleBox();

                // Any hints
                $this.hideArrowBox();

                // If not right click
                if( ktype != 'click' || ktype != 'touchend' || keycode != 3 ) {
                    if ($this.o.compact.enabled) {
                        var compact = $this.n.container.attr('asp-compact') || 'closed';
                        if ($this.o.compact.closeOnDocument == 1 && compact == 'open' && !$this.resultsOpened) {
                            $this.closeCompact();
                            $this.searchAbort();
                            $this.hideLoader();
                        }
                    } else {
                        if ($this.resultsOpened == false || $this.o.closeOnDocClick != 1) return;
                    }

                    if ( !$this.dragging ) {
                        $this.hideLoader();
                        $this.searchAbort();
                        $this.hideResults();
                    }
                }
            });
            // Isotope results swipe event
            if ( $this.o.resultstype == "isotopic" && typeof $this.n.resultsDiv.swipe != "undefined" ) {
                $this.n.resultsDiv.swipe({
                    //Generic swipe handler for all directions
                    excludedElements: "button, input, select, textarea, .noSwipe",
                    preventDefaultEvents: (!detectIOS() && !detectIE()),
                    // Params: e, direction, distance, duration, fingerCount, fingerData
                    swipeLeft: function () {
                        if ( $this.visiblePagination() )
                            $("a.asp_next", $this.n.resultsDiv).click();
                    },
                    // Params: e, direction, distance, duration, fingerCount, fingerData
                    swipeRight: function () {
                        if ( $this.visiblePagination() )
                            $("a.asp_prev", $this.n.resultsDiv).click();
                    }
                });
                $this.n.resultsDiv.bind("click", function (e) {
                    e.stopImmediatePropagation();
                });
            } else {
                // Only cancel on touch, if the swipe is not enabled
                $this.n.resultsDiv.bind($this.clickTouchend, function (e) {
                    e.stopImmediatePropagation();
                });
            }
        },

        initOtherEvents: function() {
            var $this = this;

            if ( isMobile() && detectIOS() ) {
                /**
                 * Memorize the scroll top when the input is focused on IOS
                 * as fixed elements scroll freely, resulting in incorrect scroll value
                 */
                $this.n.text.on('touchstart', function () {
                    $this.savedScrollTop = $(window).scrollTop();
                    $this.savedContainerTop = $this.n.container.offset().top;
                });
            }

            $this.n.proclose.bind($this.clickTouchend, function (e) {
                //if ($this.resultsOpened == false) return;
                e.preventDefault();
                e.stopImmediatePropagation();
                $this.n.text.val("");
                $this.n.textAutocomplete.val("");
                $this.hideResults();
                $this.n.text.focus();

                $this.n.proloading.css('display', 'none');
                $this.hideLoader();
                $this.searchAbort();
            });
            $($this.elem).bind($this.clickTouchend, function (e) {
                e.stopImmediatePropagation();
            });

            if ( isMobile() ) {
                $(window).on("orientationchange", function () {
                    $this.orientationChange();
                    // Fire once more a bit delayed, some mobile browsers need to re-zoom etc..
                    setTimeout(function(){
                        $this.orientationChange();
                    }, 600);
                });
            } else {
                var resizeTimer;
                $(window).on("resize", function () {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function () {
                        $this.resize();
                    }, 100);
                });
            }

            var scrollTimer;
            $(window).on("scroll", function () {
                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(function () {
                    $this.scrolling(false);
                }, 400);
            });

            // Prevent zoom on IOS
            if ( detectIOS() && isMobile() && is_touch_device() ) {
                if ( parseInt($this.n.text.css('font-size')) < 16 ) {
                    $this.n.text.data('fontSize', $this.n.text.css('font-size')).css('font-size', '16px');
                    $this.n.textAutocomplete.css('font-size', '16px');
                    $('<style>#ajaxsearchpro'+$this.o.rid+' input.orig::-webkit-input-placeholder{font-size: 16px !important;}</style>').appendTo('head');
                }
            }
        },

        initTryThisEvents: function() {
            var $this = this;
            // Try these search button events
            $this.n.trythis.find('a').on('click touchend', function(e){
                e.preventDefault();
                e.stopImmediatePropagation();

                if ( $this.o.compact.enabled ) {
                    var state = $this.n.container.attr('asp-compact')  || 'closed';
                    if ( state == 'closed' )
                        $this.n.promagnifier.click();
                }
                document.activeElement.blur();
                $this.n.textAutocomplete.val('');
                $this.n.text.val($(this).html());
                $this.gaEvent('try_this');
                setTimeout(function(){
                    $this.n.text.trigger('input');
                }, 50);
            });
        },

        detectAndFixFixedPositioning: function() {
            var $this = this;
            var fixedp = $this.n.container.parents().filter(
                function() {
                    return $(this).css('position') == 'fixed';
                }
            );
            if ( fixedp.length > 0 || $this.n.container.css('position') == 'fixed' ) {
                if ( $this.n.resultsDiv.css('position') == 'absolute' )
                    $this.n.resultsDiv.css('position', 'fixed');
                if ( !$this.o.blocking )
                    $this.n.searchsettings.css('position', 'fixed');
            }
        },

        initNavigationEvent: function () {
            var $this = this;

            $($this.n.resultsDiv).on('mouseenter', '.item',
                function () {
                    $('.item', $this.n.resultsDiv).removeClass('hovered');
                    $(this).addClass('hovered');
                }
            );
            $($this.n.resultsDiv).on('mouseleave', '.item',
                function () {
                    $('.item', $this.n.resultsDiv).removeClass('hovered');
                }
            );

            $(document).keydown(function (e) {
                var keycode =  e.keyCode || e.which;
                if (
                    $('.item', $this.n.resultsDiv).length > 0 && $this.n.resultsDiv.css('display') != 'none' &&
                    $this.o.resultstype == "vertical"
                ) {
                    if ( keycode == 40 || keycode == 38 ) {
                        if (keycode == 40) {
                            $this.n.text.blur();
                            if ($('.item.hovered', $this.n.resultsDiv).length == 0) {
                                $('.item', $this.n.resultsDiv).first().addClass('hovered');
                            } else {
                                $('.item.hovered', $this.n.resultsDiv).removeClass('hovered').next('.item').addClass('hovered');
                            }
                        }
                        if (keycode == 38) {
                            $this.n.text.blur();
                            if ($('.item.hovered', $this.n.resultsDiv).length == 0) {
                                $('.item', $this.n.resultsDiv).last().addClass('hovered');
                            } else {
                                $('.item.hovered', $this.n.resultsDiv).removeClass('hovered').prev('.item').addClass('hovered');
                            }
                        }
                        e.stopPropagation();
                        e.preventDefault();
                        var $container = $this.is_scroll ? $( $this.scroll.getScrollElement() ) : $this.n.results;
                        var $scrollTo = $this.n.resultsDiv.find('.resdrg .item.hovered');
                        if ( $scrollTo.length == 0 ) {
                            $scrollTo = $this.n.resultsDiv.children().first();
                        }
                        $container.animate({
                            "scrollTop": $scrollTo.offset().top - $container.offset().top + $container.scrollTop()
                        }, {
                            "duration": 120
                        });
                    }

                    // Trigger click on return key
                    if ( keycode == 13 && $('.item.hovered', $this.n.resultsDiv).length > 0 ) {
                        e.stopPropagation();
                        e.preventDefault();
                        $('.item.hovered a.asp_res_url', $this.n.resultsDiv).get(0).click();
                    }

                }
            });
        },

        initMagnifierEvent: function () {
            var $this = this;

            if ($this.o.compact.enabled == 1)
                $this.initCompactEvents();

            var t;
            var rt, enterRecentlyPressed = false;

            $this.n.searchsettings.find('button.asp_s_btn').on('click', function(e){
                $this.ktype = 'button';
                e.preventDefault();

                if ( $this.n.text.val().length >= $this.o.charcount ) {
                    if ( $this.o.sb.redirect_action != 'ajax_search' ) {
                        if ($this.o.sb.redirect_action != 'first_result') {
                            $this.doRedirectToResults('button');
                        } else {
                            $this.search();
                        }
                    } else {
                        if (
                            ($('form', $this.n.searchsettings).serialize() + $this.n.text.val().trim()) != $this.lastSuccesfulSearch ||
                            !$this.resultsOpened
                        ) {
                            $this.search();
                        }
                    }
                    clearTimeout(t);
                }
            });

            $this.n.searchsettings.find('button.asp_r_btn').on('click', function(e){
                e.preventDefault();
                $this.resetSearchFilters();
                // Reset category tree toggle
                $('.asp_option_cat', $this.n.searchsettings).each(function(){
                    $this.settingsCheckboxToggle( $(this) );
                });
            });

            // The return event has to be dealt with on a keyup event, as it does not trigger the input event
            $this.n.text.on('keyup', function(e) {
                $this.keycode =  e.keyCode || e.which;
                $this.ktype = e.type;

                // Prevent rapid enter key pressing
                if ( $this.keycode == 13 ) {
                    clearTimeout(rt);
                    rt = setTimeout(function(){
                        enterRecentlyPressed = false;
                    }, 300);
                    if ( enterRecentlyPressed ) {
                        return false;
                    } else {
                        enterRecentlyPressed = true;
                    }
                }

                var isInput = $(this).hasClass("orig");

                if ( $this.n.text.val().length >= $this.o.charcount && isInput && $this.ktype == 'keyup' && $this.keycode == 13 ) {
                    $this.gaEvent('return');

                    if ( $this.o.redirectOnEnter == 1 ) {
                        if ($this.o.trigger.return != 'first_result') {
                            $this.doRedirectToResults($this.ktype);
                        } else {
                            $this.search();
                        }
                    } else if ( $this.o.trigger.return == 'ajax_search' ) {
                        if (
                            ($('form', $this.n.searchsettings).serialize() + $this.n.text.val().trim()) != $this.lastSuccesfulSearch ||
                            !$this.resultsOpened
                        ) {
                            $this.search();
                        }
                    }
                    clearTimeout(t);
                }
            });

            var previousInputValue = $this.n.text.val();
            $this.n.promagnifier.add($this.n.text).on('click input', function (e) {
                $this.keycode =  e.keyCode || e.which;
                $this.ktype = e.type;

                // IE <= 11, on click, triggers the input event, and starts the search automatically
                if ( $this.ktype == 'input' && detectIE() ) {
                    if ( previousInputValue == $this.n.text.val() ) {
                        return false;
                    } else {
                        previousInputValue = $this.n.text.val();
                    }
                }

                var isInput = $(this).hasClass("orig");
                $this.hideArrowBox();
                // Show the results if the query does not change
                if ( isInput && $this.ktype == 'click' ) {
                    if (
                        ($('form', $this.n.searchsettings).serialize() + $this.n.text.val().trim()) == $this.lastSuccesfulSearch
                    ) {
                        if ( !$this.resultsOpened && !$this.usingLiveLoader ) {
                            $this._no_animations = true;
                            $this.showResults();
                            $this._no_animations = false;
                        }
                        return;
                    }
                }

                if ( !isInput && $this.ktype == 'click' ) {
                    $this.gaEvent('magnifier');
                }

                if (isInput && $this.ktype == 'click') return;
                // Ignore submit and any other events
                if ( $this.ktype != 'click' && $this.ktype != 'input' ) return;

                var compact = $this.n.container.attr('asp-compact')  || 'closed';
                // Click on magnifier in opened compact mode, when closeOnMagnifier enabled
                if (
                    $this.o.compact.enabled == 1 &&
                    ($this.ktype == 'click' || $this.ktype == 'touchend') &&
                    $this.o.compact.closeOnMagnifier == 1 &&
                    compact == 'open'
                ) return;

                // Click on magnifier in closed compact mode, when closeOnMagnifier disabled
                if (
                    $this.o.compact.enabled == 1 &&
                    ($this.ktype == 'click' || $this.ktype == 'touchend') &&
                    compact == 'closed'
                ) return;

                // If redirection is set to the results page, or custom URL
                if (
                    $this.n.text.val().length >= $this.o.charcount &&
                    (!isInput && $this.o.redirectOnClick == 1 && $this.ktype == 'click' && $this.o.trigger.click != 'first_result')
                ) {
                    $this.doRedirectToResults($this.ktype);
                    clearTimeout(t);
                    return;
                }

                // ..if no redirection, then check if specific actions are not forbidden
                if ($this.ktype == 'input' && $this.o.trigger.type == 0) {
                    return;
                } else if ( $this.ktype == 'click' && !( $this.o.trigger.click == 'ajax_search' || $this.o.trigger.click == 'first_result' ) ) {
                    return;
                }

                //if (($this.o.trigger.type == 0 && $this.ktype == 'keyup') || ($this.ktype == 'keyup' && is_touch_device())) return;

                if ( $this.n.text.val().length < $this.o.charcount ) {
                    $this.n.proloading.css('display', 'none');
                    if ($this.o.blocking == false) $this.hideSettings();
                    $this.hideResults(false);
                    $this.searchAbort();
                    clearTimeout(t);
                    return;
                }

                $this.searchAbort();
                clearTimeout(t);
                $this.n.proloading.css('display', 'none');

                t = setTimeout(function () {
                    // If the user types and deletes, while the last results are open
                    if (
                        ($('form', $this.n.searchsettings).serialize() + $this.n.text.val().trim()) != $this.lastSuccesfulSearch ||
                        (!$this.resultsOpened && !$this.usingLiveLoader)
                    ) {
                        $this.search();
                    } else {
                        if ( $this.isRedirectToFirstResult() )
                            $this.doRedirectToFirstResult();
                        else
                            $this.n.proclose.css('display', 'block');
                    }
                }, $this.o.trigger.delay);
            });
        },

        isRedirectToFirstResult: function() {
            var $this = this;
            if (
                $('.asp_res_url', $this.n.resultsDiv).length > 0 &&
                (
                    ($this.o.redirectOnClick == 1 && $this.ktype == 'click' && $this.o.trigger.click == 'first_result' ) ||
                    ($this.o.redirectOnEnter == 1 && ($this.ktype == 'input' || $this.ktype == 'keyup') && $this.keycode == 13 && $this.o.trigger.return == 'first_result' ) ||
                    ($this.ktype == 'button' && $this.o.sb.redirect_action == 'first_result' )
                )
            ) {
                return true;
            }
            return false;
        },

        doRedirectToFirstResult: function() {
            var $this = this;
            var _loc;

            if ( $this.ktype == 'click' ) {
                _loc = $this.o.trigger.click_location;
            } else if ( $this.ktype == 'button' ) {
                _loc = $this.o.sb.redirect_location;
            } else {
                _loc = $this.o.trigger.return_location;
            }

            if ( _loc == 'same' )
                location.href = $( $('.asp_res_url', $this.n.resultsDiv).get(0)).attr('href');
            else
                open_in_new_tab( $( $('.asp_res_url', $this.n.resultsDiv).get(0)).attr('href') );

            $this.hideLoader();
            $this.hideResults();
            return false;
        },

        doRedirectToResults: function( ktype ) {
            var $this = this;
            var _loc;

            if ( !$this.reportSettingsValidity() ) {
                $this.showNextInvalidFacetMessage();
                return false;
            }

            if ( ktype == 'click' ) {
                _loc = $this.o.trigger.click_location;
            } else if ( ktype == 'button' ) {
                _loc = $this.o.sb.redirect_location;
            } else {
                _loc = $this.o.trigger.return_location;
            }
            var url = $this.getRedirectURL(ktype);

            if ($this.o.overridewpdefault) {
                if ( $this.o.resPage.useAjax == 1 ) {
                    $this.hideResults();
                    $this.liveLoad(url, $this.o.resPage.selector);
                    $this.showLoader();
                    if ($this.o.blocking == false) $this.hideSettings();
                    return false;
                } else if ( $this.o.override_method == "post") {
                    asp_submit_to_url(url, 'post', {
                        asp_active: 1,
                        p_asid: $this.o.id,
                        p_asp_data: $('form', $this.n.searchsettings).serialize()
                    }, _loc);
                } else {
                    if ( _loc == 'same' )
                        location.href = url;
                    else
                        open_in_new_tab( url );
                }
            } else {
                // The method is not important, just send the data to memorize settings
                asp_submit_to_url(url, 'post', {
                    np_asid: $this.o.id,
                    np_asp_data: $('form', $this.n.searchsettings).serialize()
                }, _loc);
            }

            $this.n.proloading.css('display', 'none');
            $this.hideLoader();
            if ($this.o.blocking == false) $this.hideSettings();
            $this.hideResults();
            $this.searchAbort();
        },

        initCompactEvents: function () {
            var $this = this;

            var scrollTopx = 0;

            $this.n.promagnifier.click(function(){
                var compact = $this.n.container.attr('asp-compact')  || 'closed';

                scrollTopx = $(window).scrollTop();
                $this.hideSettings();
                $this.hideResults();

                if (compact == 'closed') {
                    $this.openCompact();
                    $this.n.text.focus();
                } else {
                    if ($this.o.compact.closeOnMagnifier != 1) return;
                    $this.closeCompact();
                    $this.searchAbort();
                    $this.n.proloading.css('display', 'none');
                }
            });

        },

        openCompact: function() {
            var $this = this;

            if ( !$this.n.container.is("[asp-compact-w]") ) {
                $this.n.probox.attr('asp-compact-w', $this.n.probox.width());
                $this.n.container.attr('asp-compact-w', $this.n.container.width());
            }

            if ($this.o.compact.enabled == 1 && $this.o.compact.position != 'static') {
                $this.n.trythis.css({
                    top: $this.n.container.position().top + $this.n.container.outerHeight(true),
                    left: $this.n.container.offset().left
                });

                // In case of a mobile device, the top needs to be adjusted as well
                // because the mobile browser shows the top-bar which might cause
                // shifting upwards
                /*if ( isMobile() )
                    $this.n.container.css({
                        top: $this.n.container.position().top
                    });*/
            }

            $this.n.container.css({
                "width": $this.n.container.width()
            });

            $this.n.probox.css({width: "auto"});

            // halftime delay on showing the input, etc.. for smoother animation
            setTimeout(function(){
                $('>:not(.promagnifier)', $this.n.probox).removeClass('hiddend');
            }, 80);

            // Clear this timeout first, in case of fast clicking..
            clearTimeout($this.timeouts.compactBeforeOpen);
            $this.timeouts.compactBeforeOpen = setTimeout(function(){
                var width;
                if ( deviceType() == 'phone' ) {
                    width = $this.o.compact.width_phone;
                } else if ( deviceType() == 'tablet' ) {
                    width = $this.o.compact.width_tablet;
                } else {
                    width = $this.o.compact.width;
                }

                $this.n.container.css({
                    "max-width": width,
                    "width": width
                });

                if ($this.o.compact.overlay == 1) {
                    $this.n.container.css('z-index', 999999);
                    $this.n.searchsettings.css('z-index', 999999);
                    $this.n.resultsDiv.css('z-index', 999999);
                    $this.n.trythis.css('z-index', 999998);
                    $('#asp_absolute_overlay').css({
                        'opacity': 1,
                        'width': "100%",
                        "height": "100%",
                        "z-index": 999990
                    });
                }

                $this.n.container.attr('asp-compact', 'open');
            }, 50);

            // Clear this timeout first, in case of fast clicking..
            clearTimeout($this.timeouts.compactAfterOpen);
            $this.timeouts.compactAfterOpen = setTimeout(function(){
                $this.resize();
                $this.n.trythis.css({
                    display: 'block'
                });
                $this.n.text.focus();
                $this.scrolling();
            }, 500);
        },

        closeCompact: function() {
            var $this = this;

            /**
             * Clear every timeout from the opening script to prevent issues
             */
            clearTimeout($this.timeouts.compactBeforeOpen);
            clearTimeout($this.timeouts.compactAfterOpen);

            $this.timeouts.compactBeforeOpen = setTimeout(function(){
                $this.n.container.attr('asp-compact', 'closed');
            }, 50);

            $('>:not(.promagnifier)', $this.n.probox).addClass('hiddend');

            $this.n.container.css({width: "auto"});
            $this.n.probox.css({width: $this.n.probox.attr('asp-compact-w')});
            //$this.n.container.velocity({width: $this.n.container.attr('asp-compact-w')}, 300);

            $this.n.trythis.css({
                left: $this.n.container.position().left,
                display: "none"
            });


            if ($this.o.compact.overlay == 1) {
                $this.n.container.css('z-index', '');
                $this.n.searchsettings.css('z-index', '');
                $this.n.resultsDiv.css('z-index', '');
                $this.n.trythis.css('z-index', '');
                $('#asp_absolute_overlay').css({
                    'opacity': 0,
                    'width': 0,
                    "height": 0,
                    "z-index": 0
                });
            }
        },

        initAutocompleteEvent: function () {
            var $this = this;
            var tt;
            if (
                ($this.o.autocomplete.enabled == 1 && !isMobile()) ||
                ($this.o.autocomplete.mobile == 1 && isMobile())
            ) {
                $this.n.text.keyup(function (e) {
                    $this.keycode =  e.keyCode || e.which;
                    $this.ktype = e.type;

                    var thekey = 39;
                    // Lets change the keykode if the direction is rtl
                    if ($('body').hasClass('rtl'))
                        thekey = 37;
                    if ($this.keycode == thekey && $this.n.textAutocomplete.val() != "") {
                        e.preventDefault();
                        $this.n.text.val($this.n.textAutocomplete.val());
                        if ( $this.o.trigger.type != 0 ) {
                            $this.searchAbort();
                            $this.search();
                        }
                    } else {
                        clearTimeout(tt);
                        if ($this.postAuto != null) $this.postAuto.abort();
                        //This delay should be greater than the post-result delay..
                        //..so the
                        if ($this.o.autocomplete.googleOnly == 1) {
                            $this.autocompleteGoogleOnly();
                        } else {
                            tt = setTimeout(function () {
                                $this.autocomplete();
                                tt = null;
                            }, $this.o.trigger.autocomplete_delay);
                        }
                    }
                });
            }
        },

        initPagerEvent: function () {
            var $this = this;
            //$this.n.resultsDiv.on('click touchend click_trigger', 'nav>a', function (e) {
            $this.n.resultsDiv.on($this.clickTouchend + ' click_trigger', 'nav>a', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                var _this = this;
                var etype = e.type;
                var timeout = 1;
                if ( $this.n.text.is(':focus') && isMobile() ) {
                    $this.n.text.blur();
                    timeout = 300;
                }
                setTimeout( function() {
                    $this.currentPage = parseInt( $(_this).closest('nav').find('li.asp_active span').html(), 10 );
                    if ($(_this).hasClass('asp_prev') && !$('body').hasClass('rtl')) { // Revert on RTL
                        $this.currentPage = $this.currentPage == 1 ? Math.ceil($this.n.items.length / $this.il.itemsPerPage) : --$this.currentPage;
                    } else {
                        $this.currentPage = $this.currentPage == Math.ceil($this.n.items.length / $this.il.itemsPerPage) ? 1 : ++$this.currentPage;
                    }
                    $('nav>ul li', $this.n.resultsDiv).removeClass('asp_active');
                    $('nav', $this.n.resultsDiv).each(function(){
                        $($('ul li', this).get($this.currentPage - 1)).addClass('asp_active');
                    });
                    if ( etype === 'click_trigger' )
                        $this.isotopic.arrange({
                            transitionDuration: 0,
                            filter: $this.filterFns['number']
                        });
                    else
                        $this.isotopic.arrange({
                            transitionDuration: 400,
                            filter: $this.filterFns['number']
                        });

                    $this.isotopicPagerScroll();
                    $this.removeAnimation();

                    // Trigger lazy load refresh
                    if ( typeof $.fn.asp_lazy != 'undefined' ) {
                        $(window).trigger('scroll');
                    }

                    $this.n.resultsDiv.trigger('nav_switch');
                }, timeout);
            });
            //$this.n.resultsDiv.on('click touchend click_trigger', 'nav>ul li', function (e) {
            $this.n.resultsDiv.on($this.clickTouchend + ' click_trigger', 'nav>ul li', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                var etype = e.type;
                var _this = this;
                var timeout = 1;
                if ( $this.n.text.is(':focus') && isMobile() ) {
                    $this.n.text.blur();
                    timeout = 300;
                }
                setTimeout( function() {
                    $this.currentPage = parseInt($('span', _this).html(), 10);
                    $('nav>ul li', $this.n.resultsDiv).removeClass('asp_active');
                    $('nav', $this.n.resultsDiv).each(function () {
                        $($('ul li', this).get($this.currentPage - 1)).addClass('asp_active');
                    });
                    if ( etype === 'click_trigger' )
                        $this.isotopic.arrange({
                            transitionDuration: 0,
                            filter: $this.filterFns['number']
                        });
                    else
                        $this.isotopic.arrange({
                            transitionDuration: 400,
                            filter: $this.filterFns['number']
                        });
                    $this.isotopicPagerScroll();
                    $this.removeAnimation();

                    // Trigger lazy load refresh
                    if ( typeof $.fn.asp_lazy != 'undefined' ) {
                        $(window).trigger('scroll');
                    }

                    $this.n.resultsDiv.trigger('nav_switch');
                }, timeout);
            });
        },

        isotopicPagerScroll: function () {
            var $this = this;

            if ( $('nav>ul li.asp_active', $this.n.resultsDiv).length <= 0 )
                return false;

            var $activeLeft = $('nav>ul li.asp_active', $this.n.resultsDiv).offset().left;
            var $activeWidth = $('nav>ul li.asp_active', $this.n.resultsDiv).outerWidth(true);
            var $nextLeft = $('nav>a.asp_next', $this.n.resultsDiv).offset().left;
            var $prevLeft = $('nav>a.asp_prev', $this.n.resultsDiv).offset().left;

            if ( $activeWidth <= 0) return;

            var toTheLeft = Math.ceil( ( $prevLeft - $activeLeft + 2 * $activeWidth ) / $activeWidth );

            if (toTheLeft > 0) {
                // If the active is the first, go to the beginning
                if ( $('nav>ul li.asp_active', $this.n.resultsDiv).prev().length == 0) {
                    $('nav>ul', $this.n.resultsDiv).css({
                        "left": $activeWidth + "px"
                    });
                    return;
                }

                // Otherwise go left
                $('nav>ul', $this.n.resultsDiv).css({
                    "left": "+=" + $activeWidth * toTheLeft + "px"
                });
            } else {

                // One step if it is the last element, 2 steps for any other
                if ( $('nav>ul li.asp_active', $this.n.resultsDiv).next().length == 0 )
                    var toTheRight = Math.ceil( ( $activeLeft - $nextLeft + $activeWidth ) / $activeWidth );
                else
                    var toTheRight = Math.ceil( ( $activeLeft - $nextLeft + 2 * $activeWidth ) / $activeWidth );

                if (toTheRight > 0) {
                    $('nav>ul', $this.n.resultsDiv).css({
                        "left": "-=" + $activeWidth * toTheRight + "px"
                    });
                }
            }
        },

        initOverlayEvent: function () {
            var $this = this;
            if ($this.o.resultstype == "isotopic") {
                if ($this.o.isotopic.showOverlay) {
                    // IOS does not trigget mouseup after mouseenter, so the user has to tap again to redirect
                    if ( !detectIOS() ) {
                        $this.n.resultsDiv.on('mouseenter', 'div.item', function () {
                            $('.asp_item_overlay', this).stop().fadeIn();
                            if ($(".asp_image", this).length > 0) {
                                if ($this.o.isotopic.blurOverlay)
                                    $('.asp_item_overlay_img', this).stop().fadeIn();
                                if ($this.o.isotopic.hideContent)
                                    $('.asp_content', this).stop().slideUp(100);
                            }
                        });
                        $this.n.resultsDiv.on('mouseleave', 'div.item', function () {
                            $('.asp_item_overlay', this).stop().fadeOut();
                            if ($(".asp_image", this).length > 0) {
                                if ($this.o.isotopic.blurOverlay)
                                    $('.asp_item_overlay_img', this).stop().fadeOut();
                                if ($this.o.isotopic.hideContent && $(".asp_image", this).length > 0)
                                    $('.asp_content', this).stop().slideDown(100);
                            }
                        });
                        $this.n.resultsDiv.on('mouseenter', 'div.asp_item_inner', function (e) {
                            $(this).addClass('animated pulse');
                        });
                        $this.n.resultsDiv.on('mouseleave', 'div.asp_item_inner', function (e) {
                            $(this).removeClass('animated pulse');
                        });
                    }
                    $this.n.resultsDiv.on('mouseup', '.asp_isotopic_item', function(e){
                        // Method to preserve _blank, jQuery click() method only triggers event handlers
                        var link = $('.asp_content h3 a', this).get(0);
                        if (typeof link != "undefined") {
                            if (e.which == 2)
                                $(link).attr('target','_blank');
                            link.click();
                        }
                    });
                }
            }

        },

        initNoUIEvents: function () {
            var $this = this;

            $(".noui-slider-json" + $this.o.rid).each(function(index, el){

                var uid = $(this).attr('id').match(/^noui-slider-json(.*)/)[1];
                var jsonData = $(this).data("aspnoui");
                if (typeof jsonData === "undefined") return false;

                jsonData = Base64.decode(jsonData);
                if (typeof jsonData === "undefined" || jsonData == "") return false;

                var args = JSON.parse(jsonData);
                if ( $(args.node).length > 0 )
                    var slider = $(args.node).get(0);

                // Initialize the main
                if (typeof noUiSlider !== 'undefined') {
                    noUiSlider.create(slider, args.main);
                } else {
                    // NoUiSlider is not included within the scripts, alert the user!
                    $this.raiseError( "noui");
                    return false;
                }

                $this.noUiSliders[index] = slider;

                slider.noUiSlider.on('update', function( values, handle ) {
                    var value = values[handle];
                    if ( handle ) { // true when 1, if upper
                        // Params: el, i, arr
                        args.links.forEach(function(el){
                            var wn = wNumb(el.wNumb);
                            if ( el.handle == "upper") {
                                if ( $(el.target).is('input') )
                                    $(el.target).val(value);
                                else
                                    $(el.target).html( wn.to(parseFloat(value)) );
                            }
                            $(args.node).on('slide', function(e) { e.preventDefault(); } );
                        });
                    } else {        // 0, lower
                        // Params: el, i, arr
                        args.links.forEach(function(el){
                            var wn = wNumb(el.wNumb);
                            if ( el.handle == "lower") {
                                if ( $(el.target).is('input') )
                                    $(el.target).val(value);
                                else
                                    $(el.target).html( wn.to(parseFloat(value)) );
                            }
                            $(args.node).on('slide', function(e) { e.preventDefault(); } );
                        });
                    }
                });
            });

        },

        initDatePicker: function() {
            var $this = this;
            // We need jQuery UI here, pure jQuery scope
            var _$ = window.jQuery;

            if ( _$(".asp_datepicker", $this.n.searchsettings).length > 0 &&
                typeof(_$.fn.datepicker) == "undefined" )
            {
                // Datepicker is not included within the scripts, alert the user!
                $this.raiseError("datepicker");
                return false;
            }

            function onSelectEvent( dateText, inst, _this, nochange ) {
                if ( _this != null )
                    var obj = _$(_this);
                else
                    var obj = _$("#" + inst.id);

                var prevValue = _$(".asp_datepicker_hidden", _$(obj).parent()).val();
                var newValue = '';

                if ( obj.datepicker("getDate") == null ) {
                    _$(".asp_datepicker_hidden", _$(obj).parent()).val('');
                } else {
                    var d = String( obj.datepicker("getDate") );
                    var date = new Date( d.match(/(.*?)00\:/)[1].trim() );
                    var year = String( date.getFullYear() );
                    var month = ("0" + (date.getMonth() + 1)).slice(-2);
                    var day = ("0" + String(date.getDate()) ).slice(-2);
                    newValue = year +'-'+ month +'-'+ day;
                    _$(".asp_datepicker_hidden", _$(obj).parent()).val(newValue);
                }

                // Trigger change event. $ scope is used ON PURPOSE
                // ..otherwise scoped version would not trigger!
                if ( (typeof(nochage) == "undefined" || nochange == null) && newValue != prevValue )
                    $(obj).change();
            }

            _$(".asp_datepicker", $this.n.searchsettings).each(function(){
                var format = _$(".asp_datepicker_format", _$(this).parent()).val();
                var _this = this;
                var origValue = _$(this).val();

                _$(this).datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: 'yy-mm-dd',
                    onSelect: onSelectEvent,
                    beforeShow: function(input, inst) {
                        _$('#ui-datepicker-div').addClass("asp-ui");
                    }
                });
                // Set to empty date if the field is empty
                if ( origValue == "")
                    _$(this).datepicker("setDate", "");
                else
                    _$(this).datepicker("setDate", origValue );

                _$(this).datepicker( "option", "dateFormat", format );

                // Call the select event to refresh the date pick value
                onSelectEvent(null, null, _this, true);

                // Assign the no change select event to a new triggerable event
                $(this).on('selectnochange', function(){
                    onSelectEvent(null, null, _this, true);
                });

                // When the user deletes the value, empty the hidden field as well
                $(this).on('keyup', function(){
                    if ( $(_this).datepicker("getDate") == null ) {
                        _$(".asp_datepicker_hidden", $(_this).parent()).val('');
                    }
                    $(_this).datepicker("hide");
                });
            });
        },

        initChosen: function() {
            var $this = this;
            if ( $('select.asp_gochosen', $this.n.searchsettings).length > 0 ) {
                if (typeof $.fn.asp_chosen == 'undefined') {
                    $this.raiseError("chosen");
                    return false;
                } else {
                    $('select.asp_gochosen', $this.n.searchsettings).each(function () {
                        $(this).asp_chosen({
                            'no_results_text': $this.o.chosen.nores,
                            'search_contains': true,
                            'width': '100%'
                        });
                    });
                    setTimeout(function(){
                        $('.chosen-container', $this.n.searchsettings).each(function(i,o){
                            $(this).css('zIndex', 1040 - i);
                        });
                    }, 1000);
                }
            }
        },

        initCFDatePicker: function() {
            var $this = this;
            // We need jQuery UI here, pure jQuery scope
            var _$ = window.jQuery;

            if ( _$(".asp_datepicker_field", $this.n.searchsettings).length > 0 &&
                typeof(_$.fn.datepicker) == "undefined" )
            {
                // Datepicker is not included within the scripts, alert the user!
                $this.raiseError("datepicker");
                return false;
            }

            // Define a global to the function
            //var _this = null;
            function onSelectEvent( dateText, inst, _this, nochange ) {
                if ( _this != null )
                    var obj = _$(_this);
                else
                    var obj = _$("#" + inst.id);

                var prevValue = _$(".asp_datepicker_hidden", _$(obj).parent()).val();
                var newValue = '';

                if ( obj.datepicker("getDate") == null ) {
                    _$(".asp_datepicker_hidden", _$(obj).parent()).val('');
                } else {
                    var d = String( obj.datepicker("getDate") );
                    var date = new Date( d.match(/(.*?)00\:/)[1].trim() );
                    var year = String( date.getFullYear() );
                    var month = ("0" + (date.getMonth() + 1)).slice(-2);
                    var day = ("0" + String(date.getDate()) ).slice(-2);
                    newValue = year + month + day;
                    _$(".asp_datepicker_hidden", _$(obj).parent()).val(newValue);
                }

                // Trigger change event. $ scope is used ON PURPOSE
                // ..otherwise scoped version would not trigger!
                if ( (typeof nochange == "undefined" || nochange == null) && newValue != prevValue )
                    $(obj).change();
            }

            _$(".asp_datepicker_field", $this.n.searchsettings).each(function(){
                var format = _$(".asp_datepicker_format", _$(this).parent()).val();
                var _this = this;
                var origValue = _$(this).val();

                _$(this).datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: 'dd/mm/yy',
                    onSelect: onSelectEvent,
                    beforeShow: function(input, inst) {
                        _$('#ui-datepicker-div').addClass("asp-ui");
                    }
                });
                // Set to empty date if the field is empty
                if ( origValue == "")
                    _$(this).datepicker("setDate", "");
                else
                    _$(this).datepicker("setDate", origValue );
                // Call the selec event to refresh the date pick value

                _$(this).datepicker( "option", "dateFormat", format );
                onSelectEvent(null, null, _this, true);

                // Assign the no change select event to a new triggerable event
                $(this).on('selectnochange', function(){
                    onSelectEvent(null, null, _this, true);
                });

                // When the user deletes the value, empty the hidden field as well
                $(this).on('keyup', function(){
                    if ( $(_this).datepicker("getDate") == null ) {
                        _$(".asp_datepicker_hidden", $(_this).parent()).val('');
                    }
                    $(_this).datepicker("hide");
                });
            });
        },

        initFacetEvents: function() {
            var $this = this;
            var t = null;
            // Prevent the return submit event, and trigger a change
            var it = null;
            var gtagTimer = null;

            $('.asp_custom_f input[type=text]:not(.chosen-search-input):not(.asp_datepicker_field):not(.asp_datepicker)', $this.n.searchsettings).on('keydown', function(e) {
                var code = e.keyCode || e.which;
                var _this = this;
                if ( code == 13 ) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
                clearTimeout(gtagTimer);
                gtagTimer = setTimeout(function(){
                    $this.gaEvent('facet_change', {
                        'option_label': $(_this).closest('fieldset').find('legend').text(),
                        'option_value': $(_this).val()
                    });
                }, 1400);
                $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                $this.setFilterStateInput(65);
                if ( $this.o.trigger.facet != 0 )
                    $this.searchWithCheck(240);
            });

            // This needs to be here, submit prevention on input text fields is still needed
            if ($this.o.trigger.facet == 0) return;

            // Dropdown
            $('select', $this.n.searchsettings).on('change slidechange', function(){
                $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                $this.gaEvent('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).find('option:selected').toArray().map(function(item){return item.text;}).join()
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });

            // Any other
            $('input[type!=checkbox][type!=text][type!=radio]', $this.n.searchsettings).on('change slidechange', function(){
                $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                $this.gaEvent('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).val()
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });

            // Radio
            $('input[type=radio]', $this.n.searchsettings).on('change slidechange', function(){
                $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                $this.gaEvent('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).closest('label').text()
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });

            $('input[type=checkbox]', $this.n.searchsettings).on('asp_chbx_change', function(){
                $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                $this.gaEvent('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).closest('.asp_option').find('.asp_option_label').text() + ($(this).prop('checked') ? '(checked)' : '(unchecked)')
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });
            $('input.asp_datepicker, input.asp_datepicker_field', $this.n.searchsettings).on('change', function(){
                $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                $this.gaEvent('facet_change', {
                    'option_label': $(this).closest('fieldset').find('legend').text(),
                    'option_value': $(this).val()
                });
                $this.setFilterStateInput(65);
                $this.searchWithCheck(80);
            });
            $('div[id*="-handles"]', $this.n.searchsettings).each(function(){
                if ( typeof this.noUiSlider != 'undefined') {
                    this.noUiSlider.on('change', function(values) {
                        var target = typeof this.target != 'undefined' ? this.target : this;
                        $this.gaEvent('facet_change', {
                            'option_label': $(target).closest('fieldset').find('legend').text(),
                            'option_value': values
                        });
                        $this.n.searchsettings.find('input[name=filters_changed]').val(1);
                        // Gtag analytics is handled on the update event, not here
                        $this.setFilterStateInput(65);
                        $this.searchWithCheck(80);
                    });
                }
            });
        },

        reportSettingsValidity: function() {
            var $this = this;
            var valid = true;

            // Automatically valid, when settings can be closed, or are hidden
            if ( $this.n.searchsettings.css('visibility') == 'hidden' )
                return true;

            $this.n.searchsettings.find('fieldset.asp_required').each(function(){
                var $_this = $(this);
                var fieldset_valid = true;
                // Text input
                $_this.find('input[type=text]:not(.chosen-search-input)').each(function(){
                    if ( $(this).val() == '' ) {
                        fieldset_valid = false;
                    }
                });
                // Select drop downs
                $_this.find('select').each(function(){
                    if (
                        $(this).val() == null || $(this).val() == '' ||
                        ( $(this).closest('fieldset').is('.asp_filter_tax, .asp_filter_content_type') && $(this).val() == '-1')
                    ) {
                        fieldset_valid = false;
                    }
                });
                // Check for checkboxes
                if ( $_this.find('input[type=checkbox]').length > 0 ) {
                    // Check if all of them are checked
                    if ( !$_this.find('input[type=checkbox]').is(':checked') ) {
                        fieldset_valid = false;
                    } else if (
                        $_this.find('input[type=checkbox]:checked').length === 1 &&
                        $_this.find('input[type=checkbox]:checked').val() === ''
                    ) {
                        // Select all checkbox
                        fieldset_valid = false;
                    }
                }
                // Check for checkboxes
                if ( $_this.find('input[type=radio]').length > 0 ) {
                    // Check if all of them are checked
                    if ( !$_this.find('input[type=radio]').is(':checked') ) {
                        fieldset_valid = false;
                    }
                    if ( fieldset_valid ) {
                        $_this.find('input[type=radio]').each(function () {
                            if (
                                $(this).is(':checked') &&
                                ( $(this).val() == '' || ( $(this).closest('fieldset').is('.asp_filter_tax, .asp_filter_content_type') && $(this).val() == '-1') )
                            ) {
                                fieldset_valid = false;
                            }
                        });
                    }
                }

                if ( !fieldset_valid ) {
                    $_this.addClass('asp-invalid');
                    valid = false;
                } else {
                    $_this.removeClass('asp-invalid');
                }
            });

            if ( !valid ) {
                $this.n.searchsettings.find('button.asp_s_btn').attr('disabled', 'disabled');
            } {
                $this.n.searchsettings.find('button.asp_s_btn').removeAttr('disabled');
            }

            return valid;
        },

        showArrowBox: function(element, text) {
            var $this = this;
            var offsetTop, left;
            if ( $('body').find('.asp_arrow_box').length === 0 ) {
                 $('body').append( "<div class='asp_arrow_box'></div>" );
                 $('body').find('.asp_arrow_box').on('mouseout', function(){
                     $this.hideArrowBox();
                 });
            }
            var $box = $('body').find('.asp_arrow_box');
            // getBoundingClientRect() is not giving correct values, use different method
            var space = $(element).offset().top - $(window).scrollTop();
            var fixedp = $(element).parents().filter(
                function() {
                    return $(this).css('position') == 'fixed';
                }
            );
            if ( fixedp.length > 0 ) {
                $box.css('position', 'fixed');
                offsetTop = 0;
            } else {
                $box.css('position', 'absolute');
                offsetTop = $(window).scrollTop();
            }
            $box.html(text);
            // Count after text is added
            left = (element.getBoundingClientRect().left + ($(element).outerWidth() / 2) - ($box.outerWidth() / 2) ) + 'px';

            if ( space > 100 ) {
                $box.removeClass('asp_arrow_box_bottom');
                $box.css({
                    top: offsetTop + element.getBoundingClientRect().top - $box.outerHeight() - 4 + 'px',
                    left: left
                });
            } else {
                $box.addClass('asp_arrow_box_bottom');
                $box.css({
                    top: offsetTop + element.getBoundingClientRect().bottom + 4 + 'px',
                    left: left
                });
            }
            $box.css('display', 'block');
        },

        hideArrowBox: function() {
            $('body').find('.asp_arrow_box').css('display', 'none');
        },

        showNextInvalidFacetMessage: function() {
            var $this = this;
            if ( $this.n.searchsettings.find('.asp-invalid').length > 0 ) {
                $this.showArrowBox(
                    $this.n.searchsettings.find('.asp-invalid').first().get(0),
                    $this.n.searchsettings.find('.asp-invalid').first().data('asp_invalid_msg')
                );
            }
        },

        destroy: function () {
            return this.each(function () {
                var $this = $.extend({}, this, methods);
                $(window).unbind($this);
            })
        },

        autocomplete: function () {
            var $this = this;

            var val = $this.n.text.val();
            if ($this.n.text.val() == '') {
                $this.n.textAutocomplete.val('');
                return;
            }
            var autocompleteVal = $this.n.textAutocomplete.val();
            if (autocompleteVal != '' && autocompleteVal.indexOf(val) == 0) {
                return;
            } else {
                $this.n.textAutocomplete.val('');
            }
            var data = {
                action: 'ajaxsearchpro_autocomplete',
                asid: $this.o.id,
                sauto: $this.n.text.val(),
                asp_inst_id: $this.o.rid,
                options: $('form', $this.n.searchsettings).serialize()
            };
            $this.postAuto = $.post(ASP.ajaxurl, data, function (response) {
                if (response.length > 0) {
                    response = $('<textarea />').html(response).text();
                    var part1 = val;
                    var part2 = response.substr(val.length);
                    response = part1 + part2;
                }
                $this.n.textAutocomplete.val(response);
            });
        },

        // If only google source is used, this is much faster..
        autocompleteGoogleOnly: function () {
            var $this = this;

            var val = $this.n.text.val();
            if ($this.n.text.val() == '') {
                $this.n.textAutocomplete.val('');
                return;
            }
            var autocompleteVal = $this.n.textAutocomplete.val();
            if (autocompleteVal != '' && autocompleteVal.indexOf(val) == 0) {
                return;
            } else {
                $this.n.textAutocomplete.val('');
            }

            var lang = $this.o.autocomplete.lang;
            $.each(['wpml_lang', 'polylang_lang', 'qtranslate_lang'], function(i, v){
                if (
                    $('input[name="'+v+'"]', $this.n.searchsettings).length > 0 &&
                    $('input[name="'+v+'"]', $this.n.searchsettings).val().length > 1
                ) {
                    lang = $('input[name="' + v + '"]', $this.n.searchsettings).val();
                    return false;
                }
            });

            $.ajax({
                url: 'https://clients1.google.com/complete/search',
                dataType: 'jsonp',
                data: {
                    q: val,
                    hl: lang,
                    nolabels: 't',
                    client: 'hp',
                    ds: ''
                },
                success: function(data) {
                    if (data[1].length > 0) {
                        response = data[1][0][0].replace(/(<([^>]+)>)/ig,"");
                        response = $('<textarea />').html(response).text();
                        response = response.substr(val.length);
                        $this.n.textAutocomplete.val(val + response);
                    }
                }
            });
        },

        isDuplicateSearchTriggered: function() {
            var $this = this;
            for (var i=0;i<25;i++) {
                var id = $this.o.id + '_' + i;
                if ( id != $this.o.rid ) {
                    if ( typeof ASP.instances[id] != 'undefined' ) {
                        return ASP.instances[id].searching;
                    }
                }
            }
            return false;
        },

        searchAbort: function() {
            var $this = this;
            if ( $this.post != null ) {
                $this.post.abort();
            }
        },

        searchWithCheck: function( timeout ) {
            var $this = this;
            if ( typeof timeout == 'undefined' )
                timeout = 50;

            if ($this.n.text.val().length < $this.o.charcount) return;
            $this.searchAbort();

            clearTimeout($this.timeouts.searchWithCheck);
            $this.timeouts.searchWithCheck = setTimeout(function() {
                $this.search();
            }, timeout);
        },

        search: function ( count, order, recall, apiCall, supressInvalidMsg ) {
            var $this = this;
            var abort = false;

            if ( $this.isDuplicateSearchTriggered() )
                return false;

            recall = typeof recall == "undefined" ? false : recall;
            apiCall = typeof apiCall == "undefined" ? false : apiCall;
            supressInvalidMsg = typeof supressInvalidMsg == "undefined" ? false : supressInvalidMsg;

            var data = {
                action: 'ajaxsearchpro_search',
                aspp: $this.n.text.val(),
                asid: $this.o.id,
                asp_inst_id: $this.o.rid,
                options: $('form', $this.n.searchsettings).serialize()
            };

            $this.hideArrowBox();
            if ( !$this.isAutoP && !$this.reportSettingsValidity() ) {
                if ( !supressInvalidMsg )
                    $this.showNextInvalidFacetMessage();
                abort = true;
            }

            if ( $this.isAutoP ) {
                data.autop = 1;
                $this.isAutoP = false;
            }


            if ( !recall && !apiCall && (JSON.stringify(data) === JSON.stringify($this.lastSearchData)) ) {
                if ( !$this.resultsOpened && !$this.usingLiveLoader )
                    $this.showResults();
                if ( $this.isRedirectToFirstResult() ) {
                    $this.doRedirectToFirstResult();
                    return false;
                }
                abort = true;
            }

            if ( abort ) {
                $this.hideLoader();
                $this.searchAbort();
                return false;
            }

            $this.n.c.trigger("asp_search_start", [$this.o.id, $this.o.iid, $this.n.text.val()]);

            $this.searching = true;

            $this.n.proclose.css({
                display: "none"
            });

            $this.showLoader( recall );

            // If blocking, or hover but facetChange activated, dont hide the settings for better UI
            if ( $this.o.blocking == false && $this.o.trigger.facet == 0 ) $this.hideSettings();

            if ( recall ) {
                $this.call_num++;
                data.asp_call_num = $this.call_num;
            } else {
                $this.call_num = 0;
            }

            if ( $('form[name="asp_data"]').length > 0 ) {
                data.asp_preview_options = $('form[name="asp_data"]').serialize();
            }

            if ( typeof count != "undefined" && count !== false ) {
                data.options += "&force_count=" + parseInt(count);
            }
            if ( typeof order != "undefined" && order !== false ) {
                data.options += "&force_order=" + parseInt(order);
            }

            $this.gaEvent('search_start');

            if ( $('.asp_es_' + $this.o.id).length > 0 ) {
                $this.liveLoad('.asp_es_' + $this.o.id, $this.getCurrentLiveURL(), false);
            } else if ( $this.o.resPage.useAjax ) {
                $this.liveLoad($this.o.resPage.selector, $this.getRedirectURL());
            } else {
                $this.post = $.post(ASP.ajaxurl, data, function (response) {
                    $this.gaPageview($this.n.text.val());

                    $this.searching = false;
                    response = response.replace(/^\s*[\r\n]/gm, "");
                    var html_response = response.match(/!!ASPSTART_HTML!!(.*[\s\S]*)!!ASPEND_HTML!!/);
                    var data_response = response.match(/!!ASPSTART_DATA!!(.*[\s\S]*)!!ASPEND_DATA!!/);

                    if (html_response == null || typeof(html_response) != "object" || typeof(html_response[1]) == "undefined") {
                        $this.hideLoader();
                        $this.raiseError("missing_response");
                        return false;
                    } else {
                        html_response = html_response[1];
                    }
                    data_response = JSON.parse(data_response[1]);
                    $this.n.c.trigger("asp_search_end", [$this.o.id, $this.o.iid, $this.n.text.val(), data_response]);

                    if ( !recall ) {
                        $this.n.resdrg.html("");
                        $this.n.resdrg.html(html_response);
                        $this.results_num = data_response.results_count;
                        if ( $this.o.statistics )
                            $this.stat_addKeyword($this.o.id, $this.n.text.val());
                    } else {
                        $this.updateResults(html_response);
                        $this.results_num += data_response.results_count;
                    }
                    $(".asp_keyword", $this.n.resdrg).on('click', function () {
                        $this.n.text.val( decodeHTMLEntities($(this).text()) );
                        $this.n.textAutocomplete.val('');
                        // Is any ajax trigger enabled?
                        if ( $this.o.redirectOnClick == 0 ||
                            $this.o.redirectOnEnter == 0 ||
                            $this.o.trigger.type == 1) {
                            $this.search();
                        }
                    });
                    $this.n.items = $('.item', $this.n.resultsDiv).length > 0 ? $('.item', $this.n.resultsDiv) : $('.photostack-flip', $this.n.resultsDiv);

                    $this.gaEvent('search_end', {'results_count':$this.n.items.length});

                    if ( $this.isRedirectToFirstResult() ) {
                        $this.doRedirectToFirstResult();
                        return false;
                    }

                    $this.hideLoader();
                    $this.showResults();
                    $this.scrollToResults();
                    $this.lastSuccesfulSearch = $('form', $this.n.searchsettings).serialize() + $this.n.text.val().trim();
                    $this.lastSearchData = data;

                    $this.updateInfoHeader(data_response.full_results_count);

                    if ( $this.n.showmore.length > 0 ) {
                        if (
                            $('span', $this.n.showmore).length > 0 &&
                            data_response.results_count > 0 &&
                            (data_response.full_results_count - $this.results_num) > 0
                        ) {
                            $this.n.showmore.css("display", "block");
                            $('span', $this.n.showmore).html("(" + (data_response.full_results_count - $this.results_num) + ")");

                            $('a', $this.n.showmore).attr('href', "");
                            $('a', $this.n.showmore).off();
                            $('a', $this.n.showmore).on($this.clickTouchend, function(e){
                                e.preventDefault();
                                e.stopImmediatePropagation();   // Stopping either click or touchend

                                if ( $this.o.show_more.action == "ajax" ) {
                                    // Prevent duplicate triggering, don't use .off, as re-opening the results box this will fail
                                    if ( $this.searching )
                                        return false;
                                    $this.showMoreResLoader();
                                    $this.search(false, false, true);
                                } else {
                                    // Prevent duplicate triggering
                                    $(this).off();
                                    if ( $this.o.show_more.action == 'results_page' ) {
                                        var url = '?s=' + asp_nice_phrase( $this.n.text.val() );
                                    } else if ( $this.o.show_more.action == 'woo_results_page' ) {
                                        var url = '?post_type=product&s=' + asp_nice_phrase( $this.n.text.val() );
                                    } else {
                                        if ( $this.o.show_more.action == 'elementor_page' )
                                            var url = $this.parseCustomRedirectURL($this.o.show_more.elementor_url, $this.n.text.val());
                                        else
                                            var url = $this.parseCustomRedirectURL($this.o.show_more.url, $this.n.text.val());
                                        url = $('<textarea />').html(url).text();
                                    }

                                    // Is this an URL like xy.com/?x=y
                                    if ( $this.o.show_more.action != 'elementor_page' && $this.o.homeurl.indexOf('?') > 1 && url.indexOf('?') == 0 ) {
                                        url = url.replace('?', '&');
                                    }

                                    var base_url =  $this.o.show_more.action == 'elementor_page' ? url : $this.o.homeurl + url;
                                    if ($this.o.overridewpdefault) {
                                        if ( $this.o.override_method == "post") {
                                            asp_submit_to_url(base_url, 'post', {
                                                asp_active: 1,
                                                p_asid: $this.o.id,
                                                p_asp_data: $('form', $this.n.searchsettings).serialize()
                                            },  $this.o.show_more.location);
                                        } else {
                                            var final = base_url + "&asp_active=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + $('form', $this.n.searchsettings).serialize();
                                            if ( $this.o.show_more.location == 'same' ) {
                                                location.href = final;
                                            } else {
                                                open_in_new_tab(final);
                                            }
                                        }
                                    } else {
                                        // The method is not important, just send the data to memorize settings
                                        asp_submit_to_url(base_url, 'post', {
                                            np_asid: $this.o.id,
                                            np_asp_data: $('form', $this.n.searchsettings).serialize()
                                        }, $this.o.show_more.location);
                                    }
                                }
                            });
                        } else {
                            $this.n.showmore.css("display", "none");
                            $('span', $this.n.showmore).html("");
                        }
                    }
                }, "text").fail(function(jqXHR, textStatus, errorThrown){
                    if ( jqXHR.aborted || textStatus == 'abort' )
                        return;
                    $this.n.resdrg.html("");
                    $this.n.resdrg.html('<div class="asp_nores">The request failed. Please check your connection! Status: ' + jqXHR.status + '</div>');
                    $this.n.items = $('.item', $this.n.resultsDiv).length > 0 ? $('.item', $this.n.resultsDiv) : $('.photostack-flip', $this.n.resultsDiv);
                    $this.results_num = 0;
                    $this.searching = false;
                    $this.hideLoader();
                    $this.showResults();
                    $this.scrollToResults();
                });
            }
        },

        updateResults: function( html ) {
            var $this = this;
            if (
                html.replace(/^\s*[\r\n]/gm, "") === "" ||
                $(html).hasClass('asp_nores') ||
                $(html).find('.asp_nores').length > 0
            ) {
                // Something went wrong, as the no-results container was returned
                $this.n.showmore.css("display", "none");
                $('span', $this.n.showmore).html("");
            } else {
                if (
                    $this.o.resultstype == 'isotopic' &&
                    $this.call_num > 0 &&
                    $this.isotopic != null &&
                    typeof $this.isotopic.appended != 'undefined' &&
                    $this.n.items.length > 0
                ) {
                    var $items = $(html);
                    var $last = $this.n.items.last();
                    var last = parseInt( $this.n.items.last().attr('data-itemnum') );
                    $.each($items, function(k,o){
                        $($items[k]).attr('data-itemnum', ++last);
                        $($items[k]).css({
                            'width': $last.css('width'),
                            'height': $last.css('height')
                        })
                    });
                    $this.n.resdrg.append( $items );

                    $this.isotopic.appended( $items );
                    $this.n.items = $('.item', $this.n.resultsDiv).length > 0 ? $('.item', $this.n.resultsDiv) : $('.photostack-flip', $this.n.resultsDiv);
                } else {
                    if ( $this.call_num > 0 && $this.o.resultstype == 'vertical' ) {
                        $this.n.resdrg.html($this.n.resdrg.html() + '<div class="asp_v_spacer"></div>' + html);
                    } else {
                        $this.n.resdrg.html($this.n.resdrg.html() + html);
                    }
                }
            }
        },

        showResults: function( ) {
            var $this = this;

            // Create the scrollbars if needed
            if ($this.o.resultstype == 'horizontal') {
                $this.createHorizontalScroll();
            } else if ($this.o.resultstype == 'vertical') {
                $this.createVerticalScroll();
            }

            switch ($this.o.resultstype) {
                case 'horizontal':
                    $this.showHorizontalResults();
                    break;
                case 'vertical':
                    $this.showVerticalResults();
                    break;
                case 'polaroid':
                    $this.showPolaroidResults();
                    //$this.disableMobileScroll = true;
                    break;
                case 'isotopic':
                    $this.showIsotopicResults();
                    break;
                default:
                    $this.showHorizontalResults();
                    break;
            }

            $this.showAnimatedImages();
            $this.hideLoader();

            $this.n.proclose.css({
                display: "block"
            });

            // When opening the results box only
            if ( isMobile() && $this.o.mobile.hide_keyboard && !$this.resultsOpened )
                document.activeElement.blur();

            if ( $this.o.settingsHideOnRes && $this.o.blocking == false )
                $this.hideSettings();

            if ( typeof $.fn.asp_lazy != 'undefined' ) {
                setTimeout(function(){
                    $this.asp_lazy = $this.n.resultsDiv.find('.asp_lazy').asp_lazy({
                        chainable: false,
                        visibleOnly: $this.o.resultstype == 'isotopic'
                    });
                }, 100)
            }

            if ( $this.is_scroll && typeof $this.scroll.recalculate !== 'undefined' ) {
                setTimeout(function(){
                    $this.scroll.recalculate();
                }, 500);
            }

            $this.fixAccessibility();
            $this.resultsOpened = true;
        },

        showAnimatedImages: function() {
            var $this = this;
            $this.n.items.each(function () {
                var $image = $(this).find('.asp_image[data-src]');
                var src = $image.data('src');
                if (typeof src != 'undefined' && src !== '' && src.indexOf('.gif') > -1) {
                    if ($image.find('canvas').length == 0) {
                        $('<div class="asp_item_canvas"><canvas></canvas></div>').prependTo($image);
                        var c = $(this).find('canvas').get(0);
                        var $cc = $(this).find('.asp_item_canvas');
                        var ctx = c.getContext("2d");
                        var img = new Image;
                        img.crossOrigin = "anonymous";
                        img.onload = function () {
                            $(c).attr({
                                "width": img.width,
                                "height": img.height
                            });
                            ctx.drawImage(img, 0, 0, img.width, img.height); // Or at whatever offset you like
                            $cc.css({
                                "background-image": 'url(' + c.toDataURL() + ')'
                            });
                        };
                        img.src = src;
                    }
                }
            });
        },

        updateInfoHeader: function( totalCount ) {
            var $this = this;
            var content;
            var $rt = $this.n.resultsDiv.find('.asp_results_top');
            var phrase = $this.n.text.val().trim();

            if ( $rt.length > 0 ) {
                if ( $this.n.items.length <= 0 ) {
                    $rt.css('display', 'none');
                } else {
                    if ( phrase !== '' && $this.resInfoBoxTxt !== '' ) {
                        content = $this.resInfoBoxTxt;
                    } else if ( phrase === '' && $this.resInfoBoxTxtNoPhrase !== '') {
                        content = $this.resInfoBoxTxtNoPhrase;
                    }
                    if ( content !== '' ) {
                        content = content.replace('{phrase}', $this.n.text.val());
                        content = content.replace('{results_count}', $this.n.items.length);
                        content = content.replace('{results_count_total}', totalCount);
                        $rt.html(content);
                        $rt.css('display', 'block');
                    } else {
                        $rt.css('display', 'none');
                    }
                }
            }

        },

        hideResults: function( blur ) {
            var $this = this;
            blur = typeof blur == 'undefined' ? true : blur;

            if ( !$this.resultsOpened ) return false;

            $this.n.resultsDiv.removeClass($this.resAnim.showClass).addClass($this.resAnim.hideClass);
            setTimeout(function(){
                $this.n.resultsDiv.css($this.resAnim.hideCSS);
            }, $this.resAnim.duration);

            $this.n.proclose.css({
                display: "none"
            });

            if ( isMobile() && blur )
                document.activeElement.blur();

            $this.resultsOpened = false;
            // Re-enable mobile scrolling, in case it was disabled
            //$this.disableMobileScroll = false;

            if ( typeof $this.ptstack != "undefined" )
                delete $this.ptstack;

            $this.hideArrowBox();

            $this.n.c.trigger("asp_results_hide", [$this.o.id, $this.o.iid]);
        },

        showMoreResLoader: function( ) {
            var $this = this;
            $this.n.resultsDiv.addClass('asp_more_res_loading');
        },

        showLoader: function( recall ) {
            var $this = this;
            recall = typeof recall !== 'undefined' ? recall : false;

            if ( $this.o.loaderLocation == "none" ) return;

            if ( !$this.n.container.hasClass("hiddend")  && ( $this.o.loaderLocation != "results" )  ) {
                $this.n.proloading.css({
                    display: "block"
                });
            }

            // stop at this point, if this is a 'load more' call
            if ( recall !== false ) {
                return false;
            }

            if ( ( $this.n.container.hasClass("hiddend") && $this.o.loaderLocation != "search" ) ||
                ( !$this.n.container.hasClass("hiddend") && ( $this.o.loaderLocation == "both" || $this.o.loaderLocation == "results" ) )
            ) {
                if ( $this.n.resultsDiv.find('.asp_results_top').length > 0 )
                    $this.n.resultsDiv.find('.asp_results_top').css('display', 'none');
                $this.showResultsBox();
                $(".asp_res_loader", $this.n.resultsDiv).removeClass("hiddend");
                $this.n.results.css("display", "none");
                $this.n.showmore.css("display", "none");
                $this.hidePagination();
            }
        },

        hideLoader: function( ) {
            var $this = this;

            $this.n.proloading.css({
                display: "none"
            });
            $(".asp_res_loader", $this.n.resultsDiv).addClass("hiddend");
            $this.n.results.css("display", "");
            $this.n.resultsDiv.removeClass('asp_more_res_loading');
        },


        scrollToResults: function( ) {
            var $this = this;
            if (this.o.scrollToResults!=1 || this.$elem.parent().hasClass("asp_preview_data") || this.o.compact.enabled == 1) return;
            if ($this.o.resultsposition == "hover")
                var stop = $this.n.probox.offset().top - 20;
            else
                var stop = $this.n.resultsDiv.offset().top - 20;
            if ($("#wpadminbar").length > 0)
                stop -= $("#wpadminbar").height();
            stop = stop < 0 ? 0 : stop;
            $('body, html').animate({
                "scrollTop": stop
            }, {
                duration: 500
            });
        },

        showVerticalResults: function () {
            var $this = this;

            $this.showResultsBox();

            if ($this.n.items.length > 0) {
                var count = (($this.n.items.length < $this.o.itemscount) ? $this.n.items.length : $this.o.itemscount);
                count = count <= 0 ? 9999 : count;
                var groups = $('.asp_group_header', $this.n.resultsDiv);

                // So if the result list is short, we dont even need to do the math
                if ($this.n.items.length <= $this.o.itemscount) {
                    $this.n.results.css({
                        height: 'auto'
                    });
                } else {

                    // Set the height to a fictive value to refresh the scrollbar
                    // .. otherwise the height is not calculated correctly, because of the scrollbar width.
                    if ( $this.call_num < 1 )
                        $this.n.results.css({
                            height: 30
                        });

                    if ( $this.call_num < 1 ) {
                        // Here now we have the correct item height values with the scrollbar enabled
                        var i = 0;
                        var h = 0;
                        var final_h = 0;
                        var highest = 0;

                        $this.n.items.each(function () {
                            h += $(this).outerHeight(true);
                            if ($(this).outerHeight(true) > highest)
                                highest = $(this).outerHeight(true);
                            i++;
                        });

                        // Get an initial height based on the highest item x viewport
                        final_h = highest * count;
                        // Reduce the final height to the overall height if exceeds it
                        if (final_h > h)
                            final_h = h;

                        // Count the average height * viewport size
                        i = i < 1 ? 1 : i;
                        h = h / i * count;

                        /*
                         Groups need a bit more calculation
                         - determine group position by index and occurence
                         - one group consists of group header, items + item spacers per item
                         - only groups within the viewport are calculated
                         */
                        if (groups.length > 0) {
                            groups.each(function (occurence) {
                                // -1 for the spacer
                                var group_position = $(this).index() - occurence - Math.floor($(this).index() / 3);
                                if (group_position < count) {
                                    final_h += $(this).outerHeight(true);
                                }
                            });
                        }

                        $this.n.results.css({
                            height: final_h
                        });

                    }
                }

                // Mark the last item
                $this.n.items.last().addClass('asp_last_item');
                // Before groups as well
                $this.n.results.find('.asp_group_header').prev('.item').addClass('asp_last_item');

                if ($this.o.highlight == 1) {
                    var wholew = (($this.o.highlightwholewords == 1) ? true : false);
                    $("div.item", $this.n.resultsDiv).highlight($this.n.text.val().split(" "), { element: 'span', className: 'highlighted', wordsOnly: wholew });
                }
            }
            $this.resize();
            if ($this.n.items.length == 0) {
                $this.n.results.css({
                    height: 'auto'
                });
            }
            $this.n.results.css({
                'overflowY': 'auto'
            });

            if ( $this.call_num < 1 ) {
                // Scroll to top
                var $container = $this.is_scroll ? $($this.scroll.getScrollElement()) : $this.n.results;
                $container.scrollTop(0);
            }

            // Preventing body touch scroll
            if ( $this.o.preventBodyScroll ) {
                var t;
                var bodyOverflow = $('body').css('overflow');
                var bodyHadNoStyle = typeof $('body').attr('style') === 'undefined';
                $this.n.results.off("touchstart");
                $this.n.results.off("touchend");
                $this.n.results.on("touchstart", function (e) {
                    clearTimeout(t);
                    $('body').css('overflow', 'hidden');
                }).on('touchend', function (e) {
                    clearTimeout(t);
                    t = setTimeout(function () {
                        if (bodyHadNoStyle) {
                            $('body').removeAttr('style');
                        } else {
                            $('body').css('overflow', bodyOverflow);
                        }
                    }, 300);
                });
            }

            $this.addAnimation();
            $this.fixResultsPosition(true);
            $this.searching = false;
        },

        showHorizontalResults: function () {
            var $this = this;

            $this.n.resultsDiv.css('display', 'block');
            $this.fixResultsPosition(true);

            $this.n.items.css("opacity", $this.animationOpacity);

            if ($this.o.resultsposition == 'hover') {
                $this.n.resultsDiv.css('width', $this.n.container.width() - ($this.n.resultsDiv.outerWidth(true) - $this.n.resultsDiv.innerWidth()));
            }

            if ($this.n.items.length > 0) {
                var el_m = parseInt($this.n.items.css("marginLeft"));
                var el_w = $this.n.items.outerWidth() + el_m * 2;
                $this.n.results.css("overflowX", "auto");
                $this.n.resdrg.css("width", $this.n.items.length * el_w + el_m * 2 + "px");
            } else {
                $this.n.results.css("overflowX", "hidden");
                $this.n.resdrg.css("width", "auto");
            }

            if ($this.o.highlight == 1) {
                var wholew = (($this.o.highlightwholewords == 1) ? true : false);
                $("div.item", $this.n.resultsDiv).highlight($this.n.text.val().split(" "), { element: 'span', className: 'highlighted', wordsOnly: wholew });
            }

            if ( $this.call_num < 1 ) {
                // Scroll to the beginning
                var $container = $this.is_scroll ? $($this.scroll.getScrollElement()) : $this.n.results;
                $container.scrollLeft(0);

                var prevDelta = 0;
                var prevTime = Date.now();
                $container.off('mousewheel');
                $container.on('mousewheel', function(e){
                    var deltaFactor = typeof e.deltaFactor != 'undefined' ? e.deltaFactor : 65;
                    var diff = Date.now() - prevTime;
                    var speed = diff > 100 ? 1 : 3 - (2 * diff/100);
                    if ( prevDelta != e.deltaY )
                        speed = 1;
                    $(this).stop(true).animate({
                        "scrollLeft": "-=" + (e.deltaY * deltaFactor * 2 * speed) + "px"
                    }, {
                        "duration": 250,
                        "easing" : "easeOutQuad"
                    });
                    prevDelta = e.deltaY;
                    prevTime = Date.now();
                    if (!((isScrolledToRight($container.get(0)) && e.deltaY == -1) || (isScrolledToLeft($container.get(0)) && e.deltaY == 1)))
                        e.preventDefault();
                });
            }

            $this.showResultsBox();
            $this.addAnimation();
            $this.searching = false;
        },

        showIsotopicResults: function () {
            var $this = this;

            // When re-opening existing results, just stop here
            if ( $this._no_animations == true ) {
                $this.showResultsBox();
                $this.addAnimation();
                $this.searching = false;
                return true;
            }

            $this.preProcessIsotopicResults();
            $this.showResultsBox();

            if ($this.n.items.length > 0) {
                $this.n.results.css({
                    height: "auto"
                });
                if ($this.o.highlight == 1) {
                    var wholew = $this.o.highlightwholewords == 1;
                    $("div.item", $this.n.resultsDiv).highlight($this.n.text.val().split(" "), { element: 'span', className: 'highlighted', wordsOnly: wholew });
                }
            }

            if ( $this.call_num == 0 )
                $this.calculateIsotopeRows();

            $this.showPagination();
            $this.isotopicPagerScroll();

            if ($this.n.items.length == 0) {
                var h = ($('.nores', $this.n.results).outerHeight(true) > ($this.o.resultitemheight) ? ($this.o.resultitemheight) : $('.nores', $this.n.results).outerHeight(true));
                $this.n.results.css({
                    height: 11110
                });
                $this.n.results.css({
                    height: 'auto'
                });
                $this.n.resdrg.css({
                    height: 'auto'
                });
            } else {
                // Initialize the main
                if (typeof rpp_isotope !== 'undefined') {
                    if ( $this.isotopic != null && typeof $this.isotopic.destroy != 'undefined' && $this.call_num == 0 )
                        $this.isotopic.destroy();
                    if ( $this.call_num == 0 || $this.isotopic == null )
                        $this.isotopic = new rpp_isotope('#ajaxsearchprores' + $this.o.rid + " .resdrg", {
                            // options
                            isOriginLeft: !$('body').hasClass('rtl'),
                            itemSelector: 'div.item',
                            layoutMode: 'masonry',
                            filter: $this.filterFns['number'],
                            masonry: {
                                "gutter": $this.o.isotopic.gutter
                            }
                        });
                } else {
                    // Isotope is not included within the scripts, alert the user!
                    $this.raiseError("isotope");
                    return false;
                }
            }
            $this.addAnimation();
            $this.searching = false;
        },

        preProcessIsotopicResults: function() {
            var $this = this;
            var j = 0;
            var overlay = "";

            // In some cases the hidden data is not present for some reason..
            if ($this.o.isotopic.showOverlay && $this.n.aspItemOverlay.length > 0)
                overlay = $this.n.aspItemOverlay[0].outerHTML;

            $.grep($this.n.items, function (el, i) {

                var image = "";
                var overlayImage = "";
                var hasImage = $('.asp_image', el).length > 0 ? true : false;
                var $img = $('.asp_image', el);

                if (hasImage) {
                    var src = $img.data('src');
                    var filter = $this.o.isotopic.blurOverlay && !isMobile() ? "aspblur" : "no_aspblur";

                    overlayImage = $("<div data-src='"+src+"' ></div>");
                    if ( typeof $.fn.asp_lazy == 'undefined' ) {
                        overlayImage.css({
                            "background-image": "url(" + src + ")"
                        });
                    }
                    overlayImage.css({
                        "filter": "url(#" + filter + ")",
                        "-webkit-filter": "url(#" + filter + ")",
                        "-moz-filter": "url(#" + filter + ")",
                        "-o-filter": "url(#" + filter + ")",
                        "-ms-filter": "url(#" + filter + ")"
                    }).addClass('asp_item_overlay_img asp_lazy');
                    overlayImage = overlayImage.get(0).outerHTML;
                }

                $(overlayImage + overlay + image).prependTo(el);
                $(el).attr('data-itemnum', j);

                j++;
            });

        },

        showPagination: function ( force_refresh ) {
            var $this = this;
            force_refresh = typeof force_refresh !== 'undefined' ? force_refresh : false;

            if ( !$this.o.isotopic.pagination ) {
                // On window resize event, simply rearrange without transition
                if ( $this.isotopic != null && force_refresh )
                    $this.isotopic.arrange({
                        transitionDuration: 0,
                        filter: $this.filterFns['number']
                    });
                return false;
            }

            if ( $this.call_num < 1 || force_refresh)
                $('nav.asp_navigation ul li', $this.n.resultsDiv).remove();
            $('nav.asp_navigation', $this.n.resultsDiv).css('display', 'none');

            //$('nav.asp_navigation ul', $this.n.resultsDiv).removeAttr("style");

            if ($this.n.items.length > 0) {
                var start = 1;
                if ($this.call_num > 0 && !force_refresh) {
                    // Because the nav can be both top and bottom, make sure to get only 1 to calculate, not both
                    start = $('li', $('nav.asp_navigation ul', $this.n.resultsDiv).get(0)).length + 1;
                }
                var pages = Math.ceil($this.n.items.length / $this.il.itemsPerPage);
                if (pages > 1) {

                    // Calculate which page to activate, after a possible orientation change
                    var newPage = force_refresh && $this.il.lastVisibleItem > 0 ? Math.ceil($this.il.lastVisibleItem/$this.il.itemsPerPage) : 1;
                    newPage = newPage <= 0 ? 1 : newPage;

                    for (var i = start; i <= pages; i++) {
                        if (i == newPage)
                            $('nav.asp_navigation ul', $this.n.resultsDiv).append("<li class='asp_active'><span>" + i + "</span></li>");
                        else
                            $('nav.asp_navigation ul', $this.n.resultsDiv).append("<li><span>" + i + "</span></li>");
                    }
                    $('nav.asp_navigation', $this.n.resultsDiv).css('display', 'block');

                    /**
                     * Always trigger the pagination!
                     * This will make sure that the isotope.arrange method is triggered in this case as well.
                     */
                    if ( force_refresh )
                        $('nav.asp_navigation ul li.asp_active', $this.n.resultsDiv).trigger('click_trigger');
                    else
                        $('nav.asp_navigation ul li.asp_active', $this.n.resultsDiv).click();

                } else {
                    // No pagination, but the pagination is enabled
                    // On window resize event, simply rearrange without transition
                    if ( $this.isotopic != null && force_refresh )
                        $this.isotopic.arrange({
                            transitionDuration: 0,
                            filter: $this.filterFns['number']
                        });
                }
            }
        },

        hidePagination: function () {
            var $this = this;
            $('nav.asp_navigation', $this.n.resultsDiv).css('display', 'none');
        },

        visiblePagination: function() {
            var $this = this;
            return $('nav.asp_navigation', $this.n.resultsDiv).css('display') != 'none';
        },

        calculateIsotopeRows: function () {
            var $this = this;
            var itemWidth, itemHeight;
            var containerWidth = parseFloat($this.n.results.innerWidth());
            //var itemWidth = Math.floor( parseInt($('.asp_isotopic_item', $this.n.results).outerWidth()) );
            if ( deviceType() === 'desktop' ) {
                itemWidth = getWidthFromCSSValue($this.o.isotopic.itemWidth, containerWidth);
                itemHeight = getWidthFromCSSValue($this.o.isotopic.itemHeight, containerWidth);
            } else if ( deviceType() === 'tablet' ) {
                itemWidth = getWidthFromCSSValue($this.o.isotopic.itemWidthTablet, containerWidth);
                itemHeight = getWidthFromCSSValue($this.o.isotopic.itemHeightTablet, containerWidth);
            } else {
                itemWidth = getWidthFromCSSValue($this.o.isotopic.itemWidthPhone, containerWidth);
                itemHeight = getWidthFromCSSValue($this.o.isotopic.itemHeightPhone, containerWidth);
            }
            var realColumnCount = containerWidth / itemWidth;
            var gutterWidth = $this.o.isotopic.gutter;
            var floorColumnCount = Math.floor(realColumnCount);
            if (floorColumnCount <= 0)
                floorColumnCount = 1;

            if (Math.abs(containerWidth / floorColumnCount - itemWidth) >
                Math.abs(containerWidth / (floorColumnCount + 1) - itemWidth)) {
                floorColumnCount++;
            }

            var newItemW = containerWidth / floorColumnCount - ( (floorColumnCount-1) * gutterWidth  / floorColumnCount );
            var newItemH = (newItemW / itemWidth) * itemHeight;

            $this.il.columns = floorColumnCount;
            $this.il.itemsPerPage = floorColumnCount * $this.il.rows;
            $this.il.lastVisibleItem = $this.n.results.find('.asp_isotopic_item:visible').first().index() + 1;

            // This data needs do be written to the DOM, because the isotope arrange can't see the changes
            $this.n.resultsDiv.data({
                "colums": $this.il.columns,
                "itemsperpage": $this.il.itemsPerPage
            });

            $this.currentPage = 1;

            $this.n.items.css({
                width: Math.floor(newItemW),
                height: Math.floor(newItemH)
            });
        },


        showPolaroidResults: function () {
            var $this = this;

            $('.photostack>nav', $this.n.resultsDiv).remove();
            var figures = $('figure', $this.n.resultsDiv);
            $this.n.resultsDiv.css({
                display: 'block',
                height: 'auto'
            });

            $this.showResultsBox();

            if (figures.length > 0) {
                $this.n.results.css({
                    height: $this.o.prescontainerheight
                });

                if ($this.o.highlight == 1) {
                    var wholew = (($this.o.highlightwholewords == 1) ? true : false);
                    $("figcaption", $this.n.resultsDiv).highlight($this.n.text.val().split(" "), { element: 'span', className: 'highlighted', wordsOnly: wholew });
                }

                // Initialize the main
                if (typeof Photostack !== 'undefined') {
                    $this.ptstack = new Photostack($this.n.results.get(0), {
                        callback: function (item) {
                        }
                    });
                } else {
                    // PhotoStack is not included within the scripts, alert the user!
                    $this.raiseError("polaroid");
                    return false;
                }


            }
            if (figures.length == 0) {
                $this.n.results.css({
                    height: 11110
                });
                $this.n.results.css({
                    height: "auto"
                });
            }
            $this.addAnimation();
            $this.fixResultsPosition(true);
            $this.searching = false;
            $this.initPolaroidEvents(figures);
        },

        initPolaroidEvents: function (figures) {
            var $this = this;

            var i = 1;
            figures.each(function () {
                if (i > 1)
                    $(this).removeClass('photostack-current');
                $(this).attr('idx', i);
                i++;
            });

            figures.click(function (e) {
                if ($(this).hasClass("photostack-current")) return;
                e.preventDefault();
                var idx = $(this).attr('idx');
                $('.photostack>nav span:nth-child(' + idx + ')', $this.n.resultsDiv).click();
            });

            figures.bind('mousewheel', function (event, delta) {
                event.preventDefault();
                if (delta >= 1) {
                    if ($('.photostack>nav span.current', $this.n.resultsDiv).next().length > 0) {
                        $('.photostack>nav span.current', $this.n.resultsDiv).next().click();
                    } else {
                        $('.photostack>nav span:nth-child(1)', $this.n.resultsDiv).click();
                    }
                } else {
                    if ($('.photostack>nav span.current', $this.n.resultsDiv).prev().length > 0) {
                        $('.photostack>nav span.current', $this.n.resultsDiv).prev().click();
                    } else {
                        $('.photostack>nav span:nth-last-child(1)', $this.n.resultsDiv).click();
                    }
                }
            });

            if ( typeof figures.swipe != "undefined" )
                $this.n.resultsDiv.swipe( {
                    //Generic swipe handler for all directions
                    excludedElements: "button, input, select, textarea, .noSwipe",
                    preventDefaultEvents: !detectIOS(),
                    swipeLeft: function(e, direction, distance, duration, fingerCount, fingerData) {
                        if ($('.photostack>nav span.current', $this.n.resultsDiv).next().length > 0) {
                            $('.photostack>nav span.current', $this.n.resultsDiv).next().click();
                        } else {
                            $('.photostack>nav span:nth-child(1)', $this.n.resultsDiv).click();
                        }
                    },
                    swipeRight:function(e, direction, distance, duration, fingerCount, fingerData) {
                        if ($('.photostack>nav span.current', $this.n.resultsDiv).prev().length > 0) {
                            $('.photostack>nav span.current', $this.n.resultsDiv).prev().click();
                        } else {
                            $('.photostack>nav span:nth-last-child(1)', $this.n.resultsDiv).click();
                        }
                    }
                });
        },

        addAnimation: function () {
            var $this = this;

            var i = 0;
            var j = 1;
            var delay = 25;
            var checkViewport = true;

            // No animation for the new elements via more results link
            if ( $this.call_num > 0 || $this._no_animations ) {
                $this.n.results.find('.item, .asp_group_header').removeClass("opacityZero").removeClass("asp_an_" + $this.animOptions.items);
                return false;
            }

            $this.n.results.find('.item, .asp_group_header').each(function () {
                var x = this;
                // The first item must be in the viewport, if not, then we won't use this at all
                if ( j === 1) {
                    checkViewport = $(x).is(':in-viewport(0)');
                }

                // No need to animate everything
                if (
                    ( j > 1 && checkViewport && !$(x).is(':in-viewport(0)') ) ||
                    j > 80
                ) {
                    $(x).removeClass("opacityZero");
                    return true;
                }

                if ($this.o.resultstype == 'isotopic' && j>$this.il.itemsPerPage) {
                    // Remove this from the ones not affected by the animation
                    $(x).removeClass("opacityZero");
                    return;
                }

                setTimeout(function () {
                    $(x).addClass("asp_an_" + $this.animOptions.items);
                    /**
                     * The opacityZero class must be removed just a little bit after
                     * the animation starts. This way the opacity is not reset to 1 yet,
                     * and not causing flashing effect on the results.
                     *
                     * If the opacityZero is not removed, the after the removeAnimation()
                     * call the opacity flashes back to 0 - window rezise or pagination events
                     */
                    $(x).removeClass("opacityZero");
                }, (i + delay));
                i = i + 45;
                j++;
            });

        },

        removeAnimation: function () {
            var $this = this;
            $this.n.items.each(function () {
                var x = this;
                $(x).removeClass("asp_an_" + $this.animOptions.items);
            });
        },

        initSettingsAnimations: function() {
            var $this = this;

            $this.settAnim = {
                "showClass": "",
                "showCSS": {
                    "visibility": "visible",
                    "display": "block",
                    "opacity": 1,
                    "animation-duration": $this.animOptions.settings.dur
                },
                "hideClass": "",
                "hideCSS": {
                    "visibility": "hidden",
                    "opacity": 0,
                    "display": "none"
                },
                "duration": $this.animOptions.settings.dur
            };

            if ($this.animOptions.settings.anim == "fade") {
                $this.settAnim.showClass = "asp_an_fadeIn";
                $this.settAnim.hideClass = "asp_an_fadeOut";
            }

            if ($this.animOptions.settings.anim == "fadedrop" &&
                !$this.o.blocking &&
                $this.supportTransform !== false ) {
                $this.settAnim.showClass = "asp_an_fadeInDrop";
                $this.settAnim.hideClass = "asp_an_fadeOutDrop";
            } else if ( $this.animOptions.settings.anim == "fadedrop" ) {
                // If does not support transitio, or it is blocking layout
                // .. fall back to fade
                $this.settAnim.showClass = "asp_an_fadeIn";
                $this.settAnim.hideClass = "asp_an_fadeOut";
            }

            $this.n.searchsettings.css({
                "-webkit-animation-duration": $this.settAnim.duration + "ms",
                "animation-duration": $this.settAnim.duration + "ms"
            });
        },

        initResultsAnimations: function() {
            var $this = this;

            $this.resAnim = {
                "showClass": "",
                "showCSS": {
                    "visibility": "visible",
                    "display": "block",
                    "opacity": 1,
                    "animation-duration": $this.animOptions.results.dur
                },
                "hideClass": "",
                "hideCSS": {
                    "visibility": "hidden",
                    "opacity": 0,
                    "display": "none"
                },
                "duration": $this.animOptions.results.dur
            };

            var rpos = $this.n.resultsDiv.css('position');
            var blocking = rpos != 'fixed' && rpos != 'absolute';

            if ($this.animOptions.results.anim == "fade") {
                $this.resAnim.showClass = "asp_an_fadeIn";
                $this.resAnim.hideClass = "asp_an_fadeOut";
            }

            if ( $this.animOptions.results.anim == "fadedrop" &&
                !blocking &&
                $this.supportTransform !== false ) {
                $this.resAnim.showClass = "asp_an_fadeInDrop";
                $this.resAnim.hideClass = "asp_an_fadeOutDrop";
            } else if ( $this.animOptions.results.anim == "fadedrop" ) {
                // If does not support transition, or it is blocking layout
                // .. fall back to fade
                $this.resAnim.showClass = "asp_an_fadeIn";
                $this.resAnim.hideClass = "asp_an_fadeOut";
            }

            $this.n.resultsDiv.css({
                "-webkit-animation-duration": $this.settAnim.duration + "ms",
                "animation-duration": $this.settAnim.duration + "ms"
            });
        },

        showSettings: function () {
            var $this = this;

            $this.n.c.trigger("asp_settings_show", [$this.o.id, $this.o.iid]);

            $this.n.searchsettings.css($this.settAnim.showCSS);
            $this.n.searchsettings.removeClass($this.settAnim.hideClass).addClass($this.settAnim.showClass);

            if ($this.settScroll == null && ($this.is_scroll) ) {
                $this.settScroll = [];
                $('.asp_sett_scroll', $this.n.searchsettings).each(function(i, o){
                    var _this = this;
                    // Small delay to fix a rendering issue
                    setTimeout(function(){
                        $this.settScroll[i] = new asp_SimpleBar($(_this).get(0), {
                            direction: $('body').hasClass('rtl') ? 'rtl' : 'ltr',
                            autoHide: $this.o.scrollBar.settings.autoHide
                        });
                    }, 15);
                });
            }

            if ( $this.o.fss_layout == "masonry" && $this.sIsotope == null ) {
                if (typeof rpp_isotope !== 'undefined') {
                    setTimeout(function () {
                        var id = $this.n.searchsettings.attr('id');
                        $this.n.searchsettings.css("width", "100%");
                        $this.sIsotope = new rpp_isotope("#" + id + " form", {
                            isOriginLeft: !$('body').hasClass('rtl'),
                            itemSelector: 'fieldset',
                            layoutMode: 'masonry',
                            transitionDuration: 0
                        });
                    }, $this.settAnim.duration);
                } else {
                    // Isotope is not included within the scripts, alert the user!
                    $this.raiseError("isotope");
                    return false;
                }
            }

            $this.n.prosettings.data('opened', 1);
            $this.fixSettingsPosition(true);
            $this.fixAccessibility();
        },

        showResultsBox: function() {
            var $this = this;

            $this.n.c.trigger("asp_results_show", [$this.o.id, $this.o.iid]);

            $this.n.resultsDiv.css({
                display: 'block',
                height: 'auto'
            });
            $this.n.results.find('.item, .asp_group_header').addClass($this.animationOpacity);

            $this.fixResultsPosition(true);

            $this.n.resultsDiv.css($this.resAnim.showCSS);
            $this.n.resultsDiv.removeClass($this.resAnim.hideClass).addClass($this.resAnim.showClass);
        },

        hideSettings: function () {
            var $this = this;

            $this.n.c.trigger("asp_settings_hide", [$this.o.id, $this.o.iid]);

            $this.n.searchsettings.removeClass($this.settAnim.showClass).addClass($this.settAnim.hideClass);
            setTimeout(function(){
                $this.n.searchsettings.css($this.settAnim.hideCSS);
            }, $this.settAnim.duration);

            $this.n.prosettings.data('opened', 0);

            if ( $this.sIsotope != null ) {
                setTimeout(function () {
                    $this.sIsotope.destroy();
                    $this.sIsotope = null;
                }, $this.settAnim.duration);
            }

            $this.hideArrowBox();
        },

        cleanUp: function () {
            var $this = this;

            if ($('.searchsettings', $this.n.container).length > 0) {
                $('body>#ajaxsearchprosettings' + $this.o.rid).remove();
                $('body>#ajaxsearchprores' + $this.o.rid).remove();
            }
        },

        orientationChange: function() {
            var $this = this;
            $this.fixSettingsPosition();
            $this.fixResultsPosition();
            $this.fixTryThisPosition();

            if ( $this.o.resultstype == "isotopic" && $this.n.resultsDiv.css('visibility') == 'visible' ) {
                $this.calculateIsotopeRows();
                $this.showPagination(true);
                $this.removeAnimation();
            }
        },

        resize: function () {
            var $this = this;
            $this.fixSettingsPosition();
            $this.fixResultsPosition();
            $this.fixTryThisPosition();
            $this.hideArrowBox();

            if ( $this.o.resultstype == "isotopic" && $this.n.resultsDiv.css('visibility') == 'visible' ) {
                $this.calculateIsotopeRows();
                $this.showPagination(true);
                $this.removeAnimation();
            }
        },

        scrolling: function (ignoreVisibility) {
            var $this = this;
            $this.hideOnInvisibleBox();
            $this.fixSettingsPosition(ignoreVisibility);
            $this.fixResultsPosition(ignoreVisibility);
        },

        fixAccessibility: function() {
            var $this = this;
            /**
             * These are not translated on purpose!!
             * These are invisible to any user. The only purpose is to bypass false-positive WAVE tool errors.
             */
            $this.n.searchsettings.find('input.chosen-search-input').attr('aria-label', 'Chosen search');
        },

        fixTryThisPosition: function() {
            var $this = this;
            $this.n.trythis.css({
                left: $this.n.container.position().left
            });
        },

        fixResultsPosition: function(ignoreVisibility) {
            ignoreVisibility = typeof ignoreVisibility == 'undefined' ? false : ignoreVisibility;
            var $this = this;

            var rpos = $this.n.resultsDiv.css('position');
            if ( rpos != 'fixed' && rpos != 'absolute' )
                return;

            var bodyTop = 0;
            if ( $("body").css("position") != "static" )
                bodyTop = $("body").offset().top;

            if (ignoreVisibility == true || $this.n.resultsDiv.css('visibility') == 'visible') {
                var _roffset_top = 0;
                var _roffset_left = 0;
                var _rposition = $this.n.container.offset();

                if ( rpos == 'fixed' ) {
                    bodyTop = 0;
                    _roffset_top = $(document).scrollTop();
                    _roffset_left = $(document).scrollLeft();
                    if ( isMobile() && detectIOS() && $this.n.text.is(':focus') ) {
                        _roffset_top = $this.savedScrollTop;
                        _rposition.top = $this.savedContainerTop;
                    }
                }

                if ( typeof _rposition != 'undefined' ) {
                    var rwidth = $this.n.container.outerWidth() < 240 ? 240 : $this.n.container.outerWidth();
                    var vwidth;
                    if ( deviceType() == 'phone' ) {
                        vwidth = $this.o.results.width_phone;
                    } else if ( deviceType() == 'tablet' ) {
                        vwidth = $this.o.results.width_tablet;
                    } else {
                        vwidth = $this.o.results.width;
                    }
                    if ( vwidth == 'auto')
                        $this.n.resultsDiv.outerWidth(rwidth);
                    var adjust = 0;
                    if ( $this.o.resultsSnapTo == 'right' ) {
                        adjust = $this.n.resultsDiv.outerWidth() - $this.n.container.outerWidth();
                    } else if (( $this.o.resultsSnapTo == 'center' )) {
                        adjust = parseInt( ($this.n.resultsDiv.outerWidth() - $this.n.container.outerWidth()) / 2 );
                    }
                    $this.n.resultsDiv.css({
                        top: _rposition.top + $this.n.container.outerHeight(true) - bodyTop - _roffset_top,
                        left: _rposition.left - _roffset_left - adjust
                    });
                }
            }
        },

        fixSettingsPosition: function(ignoreVisibility) {
            ignoreVisibility = typeof ignoreVisibility == 'undefined' ? false : ignoreVisibility;
            var $this = this;
            var bodyTop = 0;
            if ( $("body").css("position") != "static" )
                bodyTop = $("body").offset().top;

            if ( ( ignoreVisibility == true || $this.n.prosettings.data('opened') != 0 ) && $this.o.blocking != true ) {
                $this.fixSettingsWidth();

                if ( $this.n.prosettings.css('display') != 'none' ) {
                    var _node = $this.n.prosettings;
                } else {
                    var _node = $this.n.promagnifier;
                }
                var _sposition = _node.offset();
                var _soffset_top = 0;
                var _soffset_left = 0;
                if ( $this.n.searchsettings.css('position') == 'fixed' ) {
                    _soffset_top = $(window).scrollTop();
                    _soffset_left = $(window).scrollLeft();
                    if ( isMobile() && detectIOS() && $this.n.text.is(':focus') ) {
                        _sposition.top = $this.savedContainerTop;
                        _soffset_top = $this.savedScrollTop;
                    }
                }

                if ($this.o.settingsimagepos == 'left') {
                    $this.n.searchsettings.css({
                        display: "block",
                        top: _sposition.top + _node.height() - 2 - bodyTop - _soffset_top,
                        left: _sposition.left - _soffset_left
                    });
                } else {
                    $this.n.searchsettings.css({
                        display: "block",
                        top: _sposition.top + _node.height() - 2 - bodyTop - _soffset_top,
                        left: _sposition.left + _node.width() - $this.n.searchsettings.width() - _soffset_left
                    });
                }
            }
        },

        fixSettingsWidth: function () {
            var $this = this;

            if ( $this.o.blocking || $this.o.fss_layout == 'masonry') return;
            $this.n.searchsettings.css({"width": "100%"});
            if ( ($this.n.searchsettings.innerWidth() % $("fieldset", $this.n.searchsettings).outerWidth(true)) > 10 ) {
                var newColumnCount = parseInt( $this.n.searchsettings.innerWidth() / $("fieldset", $this.n.searchsettings).outerWidth(true) );
                newColumnCount = newColumnCount <= 0 ? 1 : newColumnCount;
                $this.n.searchsettings.css({
                    "width": newColumnCount * $("fieldset", $this.n.searchsettings).outerWidth(true) + 8
                });
            }
        },

        // -----------------------------------------------------------------------
        // ------------------------------ HELPERS --------------------------------
        // -----------------------------------------------------------------------
        liveLoad: function(selector, url, updateLocation) {
            if ( selector == 'body' || selector == 'html' ) {
                console.log('Ajax Search Pro: Do not use html or body as the live loader selector.');
                return false;
            }

            updateLocation = typeof updateLocation == 'undefined' ? true : updateLocation;

            // Alternative possible selectors from famous themes
            var altSel = [
                '.search-content',
                '#content', '#Content', 'div[role=main]',
                'main[role=main]', 'div.theme-content', 'div.td-ss-main-content',
                'main.l-content', '#primary'
            ];
            if ( selector != '#main' )
                altSel.unshift('#main');

            if ( $(selector).length < 1 ) {
                $.each(altSel, function(i, s){
                   if ( $(s).length > 0 ) {
                       selector = s;
                       return false;
                   }
                });
                if ( $(selector).length < 1 ) {
                    console.log('Ajax Search Pro: The live search selector does not exist on the page.');
                    return false;
                }
            }

            var $el = $(selector).first();
            var $this = this;

            $this.searchAbort();
            $el.css('opacity', 0.4);
            $this.post = $.ajax({
                url: url,
                success: function(data){
                    if ( data != '' && $(data).length > 0 && $(data).find(selector).length > 0 ) {
                        data = data.replace(/&asp_force_reset_pagination=1/gmi, '');
                        data = data.replace(/%26asp_force_reset_pagination%3D1/gmi, '');
                        data = data.replace(/&#038;asp_force_reset_pagination=1/gmi, '');

                        $el.replaceWith($(data).find(selector).first());
                        if ( updateLocation ) {
                            document.title = $(data).filter('title').text();
                            history.pushState({}, null, url);
                        }
                        if (
                            selector.indexOf('asp_es_') !== false &&
                            typeof elementorFrontend != 'undefined' &&
                            typeof elementorFrontend.init != 'undefined'
                        ) {
                            elementorFrontend.init();
                            // Fix Elementor Pagination
                            $this.fixElementorPostPagination(url);
                        }

                        // WooCommerce ordering fix
                        $(selector).first().find(".woocommerce-ordering").on("change","select.orderby", function(){
                            $(this).closest("form").submit();
                        });

                        ASP.fixClones();
                        ASP.initialize();
                        $this.lastSuccesfulSearch = $('form', $this.n.searchsettings).serialize() + $this.n.text.val().trim();
                        $this.lastSearchData = data;
                    } else {
                        // In case of an elementor widget, replace with the no results text on no match
                        if (
                            $(data).find(selector).length == 0 &&
                            selector.indexOf('asp_es_') !== false
                        ) {
                            $el.html('');
                            $this.lastSuccesfulSearch = $('form', $this.n.searchsettings).serialize() + $this.n.text.val().trim();
                            $this.lastSearchData = data;
                        }
                    }
                    $this.n.c.trigger("asp_search_end", [$this.o.id, $this.o.iid, $this.n.text.val(), data]);
                    $this.gaEvent('search_end', {'results_count': 'unknown'});
                    $this.hideLoader();
                    $el.css('opacity', 1);
                    $this.searching = false;
                    $this.n.proclose.css({
                        display: "block"
                    });
                },
                dataType: 'html'
            }).fail(function(jqXHR, textStatus, errorThrown){
                $el.css('opacity', 1);
                if ( jqXHR.aborted || textStatus == 'abort' ) {
                    return;
                }
                $el.html("This request has failed. Please check your connection.");
                $this.hideLoader();
                $this.searching = false;
                $this.n.proclose.css({
                    display: "block"
                });
            });
        },

        fixElementorPostPagination: function(url) {
            var $this = this;
            if ( $('.asp_es_' + $this.o.id).length > 0 ) {
                var i = url.indexOf('?');
                if ( i >= 0 ) {
                    var queryString = url.substring(i+1);
                    if ( queryString ) {
                        queryString = queryString.replace(/&asp_force_reset_pagination=1/gmi, '');
                        $('.asp_es_' + $this.o.id).find('.elementor-pagination a').each(function(){
                            var a = $(this).attr('href');
                            if ( a.indexOf('asp_ls=') < 0 ) {
                                if ( a.indexOf('?') < 0 ) {
                                    $(this).attr('href', a + '?' + queryString);
                                } else {
                                    $(this).attr('href', a + '&' + queryString);
                                }
                            }
                        });
                    }
                }
            }
        },

        getCurrentLiveURL: function() {
            var $this = this;
            var url = 'asp_ls=' + asp_nice_phrase( $this.n.text.val() );
            var start = '&';
            var location = window.location.href;

            // Correct previous query arguments (in case of paginated results)
            location = location.indexOf('asp_ls=') > -1 ? location.slice(0, location.indexOf('asp_ls=')) : location;
            location = location.indexOf('asp_ls&') > -1 ? location.slice(0, location.indexOf('asp_ls&')) : location;

            if ( location.indexOf('?') === -1 ) {
                start = '?';
            }

            var final = location + start + url + "&asp_active=1&asp_force_reset_pagination=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + $('form', $this.n.searchsettings).serialize();
            // Possible issue when the URL ends with '?' and the start is '&'
            final = final.replace('?&', '?');

            return final;
        },

        getRedirectURL: function(ktype) {
            var $this = this;
            var url, source, final, base_url;
            ktype = typeof ktype !== 'undefined' ? ktype : 'enter';

            if ( ktype == 'click' ) {
                source = $this.o.trigger.click;
            } else if ( ktype == 'button' ) {
                source = $this.o.sb.redirect_action;
            } else {
                source = $this.o.trigger.return;
            }

            if ( source == 'results_page' ) {
                url = '?s=' + asp_nice_phrase( $this.n.text.val() );
            } else if ( source == 'woo_results_page' ) {
                url = '?post_type=product&s=' + asp_nice_phrase( $this.n.text.val() );
            } else {
                if ( ktype == 'button' ) {
                    base_url = source == 'elementor_page' ? $this.o.sb.elementor_url : $this.o.sb.redirect_url;
                    url = $this.parseCustomRedirectURL(base_url, $this.n.text.val());
                } else {
                    base_url = source == 'elementor_page' ? $this.o.trigger.elementor_url : $this.o.trigger.redirect_url;
                    url = $this.parseCustomRedirectURL(base_url, $this.n.text.val());
                }
            }
            // Is this an URL like xy.com/?x=y
            if ( $this.o.homeurl.indexOf('?') > 1 && url.indexOf('?') === 0 ) {
                url = url.replace('?', '&');
            }

            if ( $this.o.overridewpdefault && $this.o.override_method != 'post' ) {
                // We are about to add a query string to the URL, so it has to contain the '?' character somewhere.
                // ..if not, it has to be added
                var start = '&';
                if ( ( $this.o.homeurl.indexOf('?') === -1 || source == 'elementor_page' ) && url.indexOf('?') === -1 ) {
                    start = '?';
                }
                var addUrl = url + start + "asp_active=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + $('form', $this.n.searchsettings).serialize();
                if ( source == 'elementor_page' ) {
                    final = addUrl;
                } else {
                    final = $this.o.homeurl + addUrl;
                }
            } else {
                if ( source == 'elementor_page' ) {
                    final = url;
                } else {
                    final = $this.o.homeurl + url;
                }
            }

            // Double backslashes - negative lookbehind (?<!:) is not supported in all browsers yet, ECMA2018
            // This section should be only: final.replace(//(?<!:)\/\//g, '/');
            // Bypass solution, but it works at least everywhere
            final = final.replace('https://', 'https:///');
            final = final.replace('http://', 'http:///');
            return final.replace(/\/\//g, '/');
        },
        parseCustomRedirectURL: function(url ,phrase) {
            var $this = this;

            var u = url.replace(/\{phrase\}/g, asp_nice_phrase(phrase));
            var items = u.match(/\{(.*?)\}/g);
            if ( items !== null ) {
                $.each(items, function(i, v){
                    v = v.replace(/[{}]/g, '');
                    var node = $('input[type=radio][name*="aspf\[' +  v + '_"]:checked', $this.n.searchsettings);
                    if ( node.length == 0 )
                        node =  $('input[type=text][name*="aspf\[' +  v + '_"]', $this.n.searchsettings);
                    if ( node.length == 0 )
                        node =  $('input[type=hidden][name*="aspf\[' +  v + '_"]', $this.n.searchsettings);
                    if ( node.length == 0 )
                        node =  $('select[name*="aspf\[' +  v + '_"]:not([multiple])', $this.n.searchsettings);
                    if ( node.length == 0 )
                        node =  $('input[type=radio][name*="termset\[' +  v + '"]:checked', $this.n.searchsettings);
                    if ( node.length == 0 )
                        node =  $('input[type=text][name*="termset\[' +  v + '"]', $this.n.searchsettings);
                    if ( node.length == 0 )
                        node =  $('input[type=hidden][name*="termset\[' +  v + '"]', $this.n.searchsettings);
                    if ( node.length == 0 )
                        node =  $('select[name*="termset\[' +  v + '"]:not([multiple])', $this.n.searchsettings);
                    if ( node.length == 0 )
                        return true; // Continue

                    var val = node.val();
                    val = "" + val; // Convert anything to string, okay-ish method
                    u = u.replace('{' + v + '}', val);
                });
            }
            return u;
        },

        setFilterStateInput: function( timeout ) {
            var $this = this;
            if ( typeof timeout == 'undefined' )
                timeout = 65;
            // Need a timeout > 50, as some checkboxes are delayed (parent-child selection)
            setTimeout(function(){
                if ( JSON.stringify($this.originalFormData) != JSON.stringify(formData($('form', $this.n.searchsettings)) ) )
                    $this.n.searchsettings.find('input[name=filters_initial]').val(0);
                else
                    $this.n.searchsettings.find('input[name=filters_initial]').val(1);
            }, timeout);
        },

        resetSearchFilters: function() {
            var $this = this;
            var currentFormData = formData($('form', $this.n.searchsettings));

            // Reset the sliders first
            if ( $this.noUiSliders.length > 0 ) {
                $.each($this.noUiSliders, function (index, slider){
                    if ( typeof slider.noUiSlider != 'undefined')
                        slider.noUiSlider.reset();
                });
            }
            // Reset the rest
            $this.n.searchsettings.find('fieldset').find('option, input').each(function(){
                var tag = $(this).prop('tagName').toLowerCase();
                if ( tag == 'option' ) {
                    $(this).removeAttr('selected');
                } else if ( tag == 'input' ) {
                    var type = $(this).attr('type').toLowerCase();
                    if ( type == 'radio' ) {
                        $(this).parent().parent().find('input[type=radio]').attr('checked', false);
                    } else if ( type == 'checkbox' )  {
                        $(this).parent().parent().find('input[type=checkbox]').attr('checked', false);
                    } else if ( type == 'hidden' || type == 'text' )  {
                        // Exclude some of the items
                        if ( !$(this).is('.asp_slider_hidden, .asp_slider') )
                            $(this).val('');
                    }
                }
            });
            // Set up the new data
            $this.n.searchsettings.find('fieldset *[data-origvalue]').each(function(){
                var tag = $(this).prop('tagName').toLowerCase();
                if ( tag == 'option' ) {
                    $(this).attr('selected', 'selected');
                } else if ( tag == 'input' ) {
                    var type = $(this).attr('type').toLowerCase();
                    if ( type == 'radio' ) {
                        $(this).attr('checked', true);
                    } else if ( type == 'checkbox' )  {
                        $(this).attr('checked', true);
                    } else if ( type == 'hidden' || type == 'text' )  {
                        $(this).val($(this).data('origvalue'));
                        if (
                            $(this).hasClass('asp_datepicker_field') || $(this).hasClass('asp_datepicker')
                        ) {
                            var _$ = window.jQuery;
                            var format = _$($(this).get(0)).datepicker("option", 'dateFormat' );
                            var origValue = $(this).data('origvalue');
                            _$($(this).get(0)).datepicker("option", 'dateFormat', 'yy-mm-dd');
                            // Default values are different for the regular date filter and CF date filter
                            if ( $(this).hasClass('asp_datepicker_field') ) {
                                origValue = origValue == "0" ? "" : (origValue == "" ? "+0" : origValue);
                            }
                            _$($(this).get(0)).datepicker("setDate", origValue );
                            _$($(this).get(0)).datepicker("option", 'dateFormat', format);
                        }
                    }
                }
            });

            $this.n.searchsettings.find('.asp_gochosen').trigger("chosen:updated");
            $this.n.text.val('');
            $this.n.proloading.css('display', 'none');
            $this.hideLoader();
            $this.searchAbort();
            if ( $this.o.rb.action == 'live' &&
                JSON.stringify(currentFormData) != JSON.stringify(formData($('form', $this.n.searchsettings)))
            ) {
                $this.search(false, false, false, false, true);
            } else if ( $this.o.rb.action == 'close' ) {
                $this.hideResults();
            }
        },

        stat_addKeyword: function(id, keyword) {
            var data = {
                action: 'ajaxsearchpro_addkeyword',
                id: id,
                keyword: keyword
            };
            $.post(ASP.ajaxurl, data, function (response) {});
        },

        hideOnInvisibleBox: function() {
            var $this = this;
            if (
                $this.o.detectVisibility == 1 &&
                $this.o.compact.enabled == 0 &&
                !$this.n.container.hasClass('hiddend') &&
                ($this.n.container.is(':hidden') || !$this.n.container.is(':visible'))
            ) {
                $this.hideSettings();
                $this.hideResults();
            }
        },

        settingsCheckboxToggle: function( $node, checkState ) {
            var $this = this;
            checkState = typeof checkState == 'undefined' ? true : checkState;
            var $parent = $node;
            var $checkbox = $node.find('input[type="checkbox"]');
            var lvl = parseInt($node.data("lvl")) + 1;
            var i = 0;
            while (true) {
                $parent = $parent.next();
                if ( $parent.length > 0 &&
                    typeof $parent.data("lvl") != "undefined" &&
                    parseInt($parent.data("lvl")) >= lvl
                ) {
                    if ( checkState )
                        $parent.find('input[type="checkbox"]').prop("checked", $checkbox.prop("checked"));
                    if ( $this.o.settings.hideChildren ) {
                        if ( $checkbox.prop("checked") ) {
                            $parent.removeClass("hiddend");
                        } else {
                            $parent.addClass("hiddend");
                        }
                    }
                }
                else
                    break;
                i++;
                if ( i > 400 ) break; // safety first
            }
        },

        // -----------------------------------------------------------------------
        // ---------------------- SEARCH JS API METHODS --------------------------
        // -----------------------------------------------------------------------
        searchFor: function( phrase ) {
            var rid = $(this).attr('id').match(/^ajaxsearchpro(.*)/)[1];
            phrase = (typeof phrase !== 'undefined') ? phrase : '';
            if ( typeof instData[rid] != 'undefined' ) {
                var $this = instData[rid];
                $this.n.text.val(phrase);
                $this.n.textAutocomplete.val('');
                $this.search(false, false, false, true);
            } else {
                console.log('This instance: ' + rid + ' does not exist :(');
            }
        },

        searchRedirect: function( phrase ) {
            var rid = $(this).attr('id').match(/^ajaxsearchpro(.*)/)[1];
            if ( typeof instData[rid] != 'undefined' ) {
                var $this = instData[rid];
                var url = $this.parseCustomRedirectURL($this.o.trigger.redirect_url, phrase);

                // Is this an URL like xy.com/?x=y
                if ( $this.o.homeurl.indexOf('?') > 1 && url.indexOf('?') == 0 ) {
                    url = url.replace('?', '&');
                }

                if ($this.o.overridewpdefault) {
                    if ( $this.o.override_method == "post") {
                        asp_submit_to_url($this.o.homeurl + url, 'post', {
                            asp_active: 1,
                            p_asid: $this.o.id,
                            p_asp_data: $('form', $this.n.searchsettings).serialize()
                        });
                    } else {
                        location.href = $this.o.homeurl + url + "&asp_active=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + $('form', $this.n.searchsettings).serialize();
                    }
                } else {
                    // The method is not important, just send the data to memorize settings
                    asp_submit_to_url($this.o.homeurl + url, 'post', {
                        np_asid: $this.o.id,
                        np_asp_data: $('form', $this.n.searchsettings).serialize()
                    });
                }
            } else {
                console.log('This instance: ' + rid + ' does not exist :(');
            }
        },

        toggleSettings: function( state ) {
            var rid = $(this).attr('id').match(/^ajaxsearchpro(.*)/)[1];
            if ( typeof instData[rid] != 'undefined' ) {
                var $this = instData[rid];

                // state explicitly given, force behavior
                if (typeof state != 'undefined') {
                    if ( state == "show") {
                        $this.showSettings();
                    } else {
                        $this.hideSettings();
                    }
                } else {
                    if ( $this.n.prosettings.data('opened') ) {
                        $this.hideSettings();
                    } else {
                        $this.showSettings();
                    }
                }
            } else {
                console.log('This instance: ' + rid + ' does not exist :(');
            }
        },

        closeResults: function( clear ) {
            var rid = $(this).attr('id').match(/^ajaxsearchpro(.*)/)[1];
            if ( typeof instData[rid] != 'undefined' ) {
                var $this = instData[rid];
                if (typeof(clear) != 'undefined' && clear) {
                    $this.n.text.val("");
                    $this.n.textAutocomplete.val("");
                }
                $this.hideResults();
                $this.n.proloading.css('display', 'none');
                $this.hideLoader();
                $this.searchAbort();
            } else {
                console.log('This instance: ' + rid + ' does not exist :(');
            }
        },

        getStateURL: function(trigger) {
            var rid = $(this).attr('id').match(/^ajaxsearchpro(.*)/)[1];
            if ( typeof instData[rid] != 'undefined' ) {
                var $this = instData[rid];
                var url = location.href;
                url = url.split('p_asid');
                url = url[0];
                url = url.replace('&asp_active=1', '');
                url = url.replace('?asp_active=1', '');
                url = url.slice(-1) == '?' ? url.slice(0, -1) : url;
                url = url.slice(-1) == '&' ? url.slice(0, -1) : url;
                var sep = url.indexOf('?') > 1 ? '&' :'?';
                return url + sep + "p_asid=" + $this.o.id + "&p_asp_data=1&" + $('form', $this.n.searchsettings).serialize();
            } else {
                console.log('This instance: ' + rid + ' does not exist :(');
                return false;
            }
        },

        resetSearch: function() {
            var rid = $(this).attr('id').match(/^ajaxsearchpro(.*)/)[1];
            if ( typeof instData[rid] != 'undefined' ) {
                var $this = instData[rid];
                $this.resetSearchFilters();
            } else {
                console.log('This instance: ' + rid + ' does not exist :(');
                return false;
            }
        },

        filtersInitial: function() {
            var rid = $(this).attr('id').match(/^ajaxsearchpro(.*)/)[1];
            if ( typeof instData[rid] != 'undefined' ) {
                var $this = instData[rid];
                return $this.n.searchsettings.find('input[name=filters_initial]').val() == 1;
            } else {
                console.log('This instance: ' + rid + ' does not exist :(');
                return false;
            }
        },

        filtersChanged: function() {
            var rid = $(this).attr('id').match(/^ajaxsearchpro(.*)/)[1];
            if ( typeof instData[rid] != 'undefined' ) {
                var $this = instData[rid];
                return $this.n.searchsettings.find('input[name=filters_changed]').val() == 1;
            } else {
                console.log('This instance: ' + rid + ' does not exist :(');
                return false;
            }
        }
    };

    function asp_nice_phrase(s) {
        return encodeURIComponent(s).replace(/\%20/g, '+');
    }

    function asp_submit_to_url(action, method, input, target) {
        'use strict';
        var form;
        form = $('<form />', {
            action: action,
            method: method,
            style: 'display: none;'
        });
        if (typeof input !== 'undefined' && input !== null) {
            $.each(input, function (name, value) {
                $('<input />', {
                    type: 'hidden',
                    name: name,
                    value: value
                }).appendTo(form);
            });
        }
        if ( typeof (target) != 'undefined' && target == 'new')
            form.attr('target', '_blank');
        form.appendTo('body').submit();
    }

    function open_in_new_tab(url) {
        $('<a href="' + url + '" target="_blank">').get(0).click();
    }

    function getWidthFromCSSValue(width, containerWidth) {
        var min = 100; // Can't get lower than this
        var ret = 0;

        width = width + '';
        // Pixel value
        if ( width.indexOf('px') > -1 ) {
            ret = parseInt(width, 10);
        } else if ( width.indexOf('%') > -1 ) {
            // % value, calculate against the container
            if ( typeof containerWidth != 'undefined' && containerWidth != null ) {
                ret = parseInt(parseInt(width, 10) / 100 * containerWidth, 10);
            } else {
                ret = parseInt(width, 10);
            }
        } else {
            ret = parseInt(width, 10);
        }

        return ret < 100 ? min : ret;
    }

    function isScrolledToTop(el, tolerance) {
        return $(el).scrollTop() < tolerance;
    }

    function isScrolledToBottom(el, tolerance) {
        return el.scrollHeight - $(el).scrollTop() - $(el).outerHeight() < tolerance;
    }

    function isScrolledToRight(el) {
        if ( el.scrollWidth - $(el).outerWidth() === $(el).scrollLeft() ){
            return true;
        }
        return false;
    }

    function isScrolledToLeft(el) {
        if ( $(el).scrollLeft() === 0 ){
            return true;
        }
        return false;
    }

    function is_touch_device() {
        return !!("ontouchstart" in window) ? 1 : 0;
    }

    function detectIE() {
        var ua = window.navigator.userAgent;
        var msie = ua.indexOf('MSIE ');         // <10
        var trident = ua.indexOf('Trident/');   // 11

        if ( msie > 0 || trident > 0 )
            return true;

        // other browser
        return false;
    }

    function detectEdge() {
        return window.navigator.userAgent.indexOf('Edge/') > 0;
    }

    function detectIOS() {
        if (
            typeof window.navigator != "undefined" &&
            typeof window.navigator.userAgent != "undefined"
        )
            return window.navigator.userAgent.match(/(iPod|iPhone|iPad)/) != null;
        return false;
    }

    function detectOldIE() {
        var ua = window.navigator.userAgent;

        var msie = ua.indexOf('MSIE ');
        if (msie > 0) {
            return true;
        }

        return false;
    }

    function getSupportedTransform() {
        var prefixes = 'transform WebkitTransform MozTransform OTransform msTransform'.split(' ');
        var div = document.createElement('div');
        for(var i = 0; i < prefixes.length; i++) {
            if(div && div.style[prefixes[i]] !== undefined) {
                return prefixes[i];
            }
        }
        return false;
    }

    function decodeHTMLEntities(str) {
        var element = document.createElement('div');
        if(str && typeof str === 'string') {
            // strip script/html tags
            str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
            str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
            element.innerHTML = str;
            str = element.textContent;
            element.textContent = '';
        }
        return str;
    }

    // Object.create support test, and fallback for browsers without it
    if (typeof Object.create !== 'function') {
        Object.create = function (o) {
            function F() {
            }

            F.prototype = o;
            return new F();
        };
    }

    /* Mobile detection - Touch desktop device safe! */
    function isMobile() {
        try{ document.createEvent("TouchEvent"); return true; }
        catch(e){ return false; }
    }

    function deviceType() {
        var w = $(window).width();
        if ( w <= 640 ) {
            return 'phone';
        } else if ( w <= 1024 ) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    function formData(form, data) {
        var els = form.find(':input').get();
        if(arguments.length === 1) {
            // return all data
            data = {};

            $.each(els, function() {
                /**
                 * Hidden inputs are ignored, except the date filter hidden inputs, via their class name
                 */
                if (this.name && !this.disabled && (this.checked
                    || /select|textarea/i.test(this.nodeName)
                    || /text/i.test(this.type)
                    || $(this).hasClass('asp_datepicker_hidden')
                    || $(this).hasClass('asp_slider_hidden')) /*&&
                    !$(this).hasClass('asp_datepicker_field') &&
                    !$(this).hasClass('asp_datepicker')*/
                ) {
                    if(data[this.name] == undefined){
                        data[this.name] = [];
                    }
                    data[this.name].push($(this).val());
                }
            });
            return JSON.stringify(data);
        } else {
            if ( typeof data != "object" )
                data = JSON.parse(data);
            //form.find(':input')
            $.each(els, function() {
                if (this.name) {
                    if (data[this.name]) {
                        var names = data[this.name];
                        var $this = $(this);
                        if(Object.prototype.toString.call(names) !== '[object Array]'){
                            names = [names]; //backwards compat to old version of this code
                        }
                        if(this.type == 'checkbox' || this.type == 'radio') {
                            var val = $this.val();
                            var found = false;
                            for(var i = 0; i < names.length; i++){
                                if(names[i] == val){
                                    found = true;
                                    break;
                                }
                            }
                            $this.attr("checked", found);
                        } else {
                            $this.val(names[0]);
                            if ( $(this).hasClass('asp_gochosen') ) {
                                $(this).trigger("chosen:updated");
                            }

                            if (
                                $(this).hasClass('asp_datepicker_field') || $(this).hasClass('asp_datepicker')
                            ) {
                                if ( data[this.name.replace('_real', '')] ) {
                                    var _$ = window.jQuery;
                                    var value = data[this.name.replace('_real', '')][0];
                                    setTimeout(function(){
                                        var format = _$($this.get(0)).datepicker("option", 'dateFormat' );
                                        _$($this.get(0)).datepicker("option", 'dateFormat', 'yy-mm-dd');
                                        _$($this.get(0)).datepicker("setDate", value );
                                        _$($this.get(0)).datepicker("option", 'dateFormat', format);
                                        _$($this.get(0)).trigger('selectnochange');
                                    }, 50);
                                }
                            }
                        }
                    } else {
                        if(this.type == 'checkbox' || this.type == 'radio') {
                            $(this).attr("checked", false);
                        }
                    }
                }
            });

            return form;
        }
    }


    // Create a plugin based on a defined object
    $.plugin = function (name, object) {
        $.fn[name] = function (options) {
            if ( typeof(options) != 'undefined' && object[options] ) {
                return object[options].apply( this, Array.prototype.slice.call( arguments, 1 ));
            } else {
                return this.each(function () {
                    if (!$.data(this, name)) {
                        $.data(this, name, Object.create(object).init(
                            options, this));
                    }
                });
            }

        };
    };

    $.plugin('ajaxsearchpro', methods);

    // ------- Helpers --------
    /**
     *
     *  Base64 encode / decode
     *  http://www.webtoolkit.info/
     *
     **/
    var Base64 = {

// private property
        _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

// public method for encoding
        encode : function (input) {
            var output = "";
            var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
            var i = 0;

            input = Base64._utf8_encode(input);

            while (i < input.length) {

                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);

                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;

                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }

                output = output +
                    this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                    this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

            }

            return output;
        },

// public method for decoding
        decode : function (input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

            while (i < input.length) {

                enc1 = this._keyStr.indexOf(input.charAt(i++));
                enc2 = this._keyStr.indexOf(input.charAt(i++));
                enc3 = this._keyStr.indexOf(input.charAt(i++));
                enc4 = this._keyStr.indexOf(input.charAt(i++));

                chr1 = (enc1 << 2) | (enc2 >> 4);
                chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                chr3 = ((enc3 & 3) << 6) | enc4;

                output = output + String.fromCharCode(chr1);

                if (enc3 != 64) {
                    output = output + String.fromCharCode(chr2);
                }
                if (enc4 != 64) {
                    output = output + String.fromCharCode(chr3);
                }

            }

            output = Base64._utf8_decode(output);

            return output;

        },

// private method for UTF-8 encoding
        _utf8_encode : function (string) {
            string = string.replace(/\r\n/g,"\n");
            var utftext = "";

            for (var n = 0; n < string.length; n++) {

                var c = string.charCodeAt(n);

                if (c < 128) {
                    utftext += String.fromCharCode(c);
                }
                else if((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                    utftext += String.fromCharCode((c & 63) | 128);
                }
                else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                    utftext += String.fromCharCode((c & 63) | 128);
                }

            }

            return utftext;
        },

// private method for UTF-8 decoding
        _utf8_decode : function (utftext) {
            var string = "";
            var i = 0;
            var c = c1 = c2 = 0;

            while ( i < utftext.length ) {

                c = utftext.charCodeAt(i);

                if (c < 128) {
                    string += String.fromCharCode(c);
                    i++;
                }
                else if((c > 191) && (c < 224)) {
                    c2 = utftext.charCodeAt(i+1);
                    string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                    i += 2;
                }
                else {
                    c2 = utftext.charCodeAt(i+1);
                    c3 = utftext.charCodeAt(i+2);
                    string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                    i += 3;
                }

            }

            return string;
        }

    }
    //  ------- End of Helpers  -------
})(jQuery);