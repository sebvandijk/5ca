/**
 * An initialization wrapper for Ajax Search Pro
 *
 * This solution gets rid off the nasty inline script declarations once and for all.
 * Instead the search instance params are stored in a hidden div element. This baby here
 * parses through them and does a very simple initialization process.
 * Also, the ASP variable now provides a way for developers to manually initialize the instances
 * anytime, anywhere.
 */

// Use the window to make sure it is in the main scope, I do not trust IE
window.ASP = window.ASP || {};

window.ASP.getScope = function() {
    /**
     * Explanation:
     * If the sript is scoped, the first argument is always passed in a localized jQuery
     * variable, while the actual parameter can be aspjQuery or jQuery (or anything) as well.
     */
    if (typeof jQuery !== "undefined") return jQuery;

    // The code should never reach this point, but sometimes magic happens (unloaded or undefined jQuery??)
    // .. I am almost positive at this point this is going to fail anyways, but worth a try.
    if (typeof window[ASP.js_scope] !== "undefined")
        return window[ASP.js_scope];
    else
        return eval(ASP.js_scope);
};

window.ASP.initialized = false;

// Call this function if you need to initialize an instance that is printed after an AJAX call
// Calling without an argument initializes all instances found.
window.ASP.initialize = function(id) {
    // this here is either window.ASP or window._ASP
    var _this = this;

    // Some weird ajax loader problem prevention
    if ( typeof _this.getScope == 'undefined' )
        return false;

    // Yeah I could use $ or jQuery as the scope variable, but I like to avoid magical errors..
    var scope = _this.getScope();
    var selector = ".asp_init_data";

    var b64_utf8_decode = function(utftext) {
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

    var b64_decode = function(input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;
        var _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = _keyStr.indexOf(input.charAt(i++));
            enc2 = _keyStr.indexOf(input.charAt(i++));
            enc3 = _keyStr.indexOf(input.charAt(i++));
            enc4 = _keyStr.indexOf(input.charAt(i++));

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
        output = b64_utf8_decode(output);
        return output;
    }

    if ((typeof ASP_INSTANCES != "undefined") && Object.keys(ASP_INSTANCES).length > 0) {
        scope.each(ASP_INSTANCES, function(k, v){
            if ( typeof v == "undefined" ) return false;
            // Return if it is already initialized
            if ( scope("#ajaxsearchpro" + k).hasClass("hasASP") )
                return false;
            else
                scope("#ajaxsearchpro" + k).addClass("hasASP");

            return scope("#ajaxsearchpro" + k).ajaxsearchpro(v);
        });
    } else {
        if (typeof id !== 'undefined')
            selector = "div[id*=asp_init_id_" + id + "]";

        /**
         * Getting around inline script declarations with this solution.
         * So these new, invisible divs contains a JSON object with the parameters.
         * Parse all of them and do the declaration.
         */
        scope(selector).each(function(index, value){
            var rid =  scope(this).attr('id').match(/^asp_init_id_(.*)/)[1];
            var jsonData = scope(this).data("aspdata");
            if (typeof jsonData === "undefined") return true;   // Do not return false, it breaks the loop!

            jsonData = b64_decode(jsonData);
            if (typeof jsonData === "undefined" || jsonData == "") return true; // Do not return false, it breaks the loop!

            // Return if it is already initialized
            if ( scope("#ajaxsearchpro" + rid).hasClass("hasASP") )
                return true; // Do not return false, it breaks the loop!
            else
                scope("#ajaxsearchpro" + rid).addClass("hasASP");

            var args = JSON.parse(jsonData);

            return scope("#ajaxsearchpro" + rid).ajaxsearchpro(args);
        });
    }

    _this.initialized = true;
};

window.ASP.fixClones = function() {
    var _this = this;
    _this.fix_duplicates = _this.fix_duplicates || 0;
    if ( _this.fix_duplicates == 0 )
        return false;

    if ( typeof _this.getScope == 'undefined' )
        return false;
    var scope = _this.getScope();

    var inst = {};
    var selector = ".asp_init_data";

    scope(selector).each(function(){
        var rid =  scope(this).attr('id').match(/^asp_init_id_(.*)/)[1];
        var ida =  rid.match(/^(.*)_(.*)/);
        if ( typeof inst[rid] == 'undefined' ) {
            inst[rid] = {
                'rid'  : rid,
                'id'   : ida[1],
                'inst' : ida[2],
                'count': 1
            };
        } else {
            inst[rid].count++;
        }
    });

    scope.each(inst, function(k, v){
        // Same instance, but more copies
        if ( v.count > 1 ) {
            scope('.asp_m_' + v.rid).each(function(kk, vv){
                if ( kk == 0 ) return true;
                var parent = scope(this).parent();
                var n_ins = 2;
                var n_rid = v.id + '_' + n_ins;
                while ( scope('#ajaxsearchpro' + n_rid).length != 0 ) {
                    ++n_ins;
                    n_rid = v.id + '_' + n_ins;
                }
                // Main box
                scope(this).attr('id', 'ajaxsearchpro' + n_rid);
                scope(this).attr('data-instance', n_ins);
                scope(this).removeClass('asp_m_' + v.rid).addClass('asp_m_' + n_rid);
                scope(this).removeClass('hasASP');
                // Results box
                // Check if the cloning did make a copy before init, if not, make a results box
                if ( scope('.asp_r_'+v.rid, this).length == 0 ) {
                    scope('.asp_r_'+v.rid).clone().appendTo(scope(this));
                }
                scope('.asp_r_'+v.rid, this).attr('id', 'ajaxsearchprores'+n_rid);
                scope('.asp_r_'+v.rid, this).attr('data-instance', n_ins);
                // Settings box
                // Check if the cloning did make a copy before init, if not, make a settings box
                if ( scope('.asp_s_'+v.rid, this).length == 0 && scope('.asp_s_'+v.rid).length != 0 ) {
                    scope('.asp_s_'+v.rid).clone().appendTo(scope(this));
                }
                if ( scope('.asp_sb_'+v.rid, this).length == 0 && scope('.asp_sb_'+v.rid).length != 0 ) {
                    scope('.asp_sb_'+v.rid).clone().appendTo(scope(this));
                }
                scope('.asp_s_'+v.rid, this).attr('id', 'ajaxsearchprosettings'+n_rid);
                scope('.asp_sb_'+v.rid, parent).attr('id', 'ajaxsearchprobsettings'+n_rid);
                scope('.asp_s_'+v.rid, this).attr('data-instance', n_ins);
                scope('.asp_sb_'+v.rid, this).attr('data-instance', n_ins);
                // Other data
                if ( scope('.asp_hidden_data', parent).length > 0 )
                    scope('.asp_hidden_data', parent).attr('id', 'asp_hidden_data_'+n_rid);
                if ( scope('.asp_init_data', parent).length > 0 )
                    scope('.asp_init_data', parent).attr('id', 'asp_init_id_'+n_rid);

                _this.initialize(v.id, parseInt(v.inst) + kk);
            });
        }
    });
};

window.ASP.ready = function() {
    var _this = this;
    var scope = _this.getScope();
    var t = null;
    var iv = null;
    var ivc = 0;

    iv = setInterval(function(){
        ivc++;
        if ( _this.css_loaded == 1 || ivc > 80 ) {
            scope(document).ready(function () {
                _this.initialize();
                setTimeout(function(){
                    _this.fixClones();
                }, 2500);
            });

            // Redundancy for safety
            scope(window).on('load', function () {
                // It should be initialized at this point, but you never know..
                if (!_this.initialized) {
                    _this.initialize();
                    setTimeout(function(){
                        _this.fixClones();
                    }, 2500);
                    console.log("ASP initialized via window.load");
                }
            });
            clearInterval(iv);
        }
    }, 50);

    // DOM tree modification detection to re-initialize automatically if enabled
    if (typeof(ASP.detect_ajax) != "undefined" && ASP.detect_ajax == 1) {
        scope("body").bind("DOMSubtreeModified", function() {
            clearTimeout(t);
            t = setTimeout(function(){
                _this.initialize();
            }, 500);
        });
    }

    var tt;
    scope(window).on('resize', function(){
        clearTimeout(tt);
        tt = setTimeout(function(){
            _this.fixClones();
        }, 2000);
    });

    var ttt;
    // Known slide-out and other type of menus to initialize on click
    var triggerSelectors = '#menu-item-search, .fa-search, .fa, .fas';
    // Avada theme
    triggerSelectors = triggerSelectors + ', .fusion-flyout-menu-toggle, .fusion-main-menu-search-open';
    // Be theme
    triggerSelectors = triggerSelectors + ', #search_button';
    // The 7 theme
    triggerSelectors = triggerSelectors + ', .mini-search.popup-search';
    // Flatsome theme
    triggerSelectors = triggerSelectors + ', .icon-search';
    // Enfold theme
    triggerSelectors = triggerSelectors + ', .menu-item-search-dropdown';
    // Uncode theme
    triggerSelectors = triggerSelectors + ', .mobile-menu-button';
    // Newspaper theme
    triggerSelectors = triggerSelectors + ', .td-icon-search, .tdb-search-icon';
    // Bridge theme
    triggerSelectors = triggerSelectors + ', .side_menu_button, .search_button';
    // Jupiter theme
    triggerSelectors = triggerSelectors + ', .raven-search-form-toggle';
    // Elementor trigger lightbox & other elementor stuff
    triggerSelectors = triggerSelectors + ', [data-elementor-open-lightbox], .elementor-button-link, .elementor-button';

    // Attach this to the document ready, as it may not attach if this is loaded early
    scope(function(){
        scope('body').on('click touchend', triggerSelectors, function(){
            clearTimeout(ttt);
            ttt = setTimeout(function(){
                _this.initialize();
            }, 500);
        });
    });
};

window.ASP.eventsList = [
    {"name": "asp_init_search_bar", "args": "id, instance"},
    {"name": "asp_search_start", "args": "id, instance, phrase"},
    {"name": "asp_search_end", "args": "id, instance, phrase, results_info"},
    {"name": "asp_results_show", "args": "id, instance"},
    {"name": "asp_results_hide", "args": "id, instance"},
    {"name": "asp_settings_show", "args": "id, instance"},
    {"name": "asp_settings_hide", "args": "id, instance"}
];
window.ASP.printEventsList = function() {
    var el = window.ASP.eventsList;
    for (var i=0; i<el.length; i++) {
        if ( typeof el[i].args!= "undefined" )
            console.log(el[i].name + " | args: " + el[i].args);
        else
            console.log(el[i].name)
    }
};

window.ASP.functionsList = [
    {"name": "exists", "args": "id"},
    {"name": "searchFor", "args": "id, (optional) instance, 'phrase'"},
    {"name": "searchRedirect", "args": "id, (optional) instance, 'phrase'"},
    {"name": "toggleSettings", "args": "id, (optional) instance, (optional) 'show' | 'hide'"},
    {"name": "closeResults", "args": "id, (optional)instance"},
    {"name": "getStateURL", "args": "id, (optional)instance"},
    {"name": "resetSearch", "args": "id, (optional)instance"},
    {"name": "filtersInitial", "args": "id, (optional)instance"},
    {"name": "filtersChanged", "args": "id, (optional)instance"}
];
window.ASP.printFunctionsList = function() {
    var el = window.ASP.functionsList;
    for (var i=0; i<el.length; i++) {
        if ( typeof el[i].args!= "undefined" )
            console.log(el[i].name + " | args: " + el[i].args);
        else
            console.log(el[i].name)
    }
};



window.ASP.api = (function() {
    var fourParams = function(id, instance, func, args) {
        var _this = this;
        var scope = _this.getScope();
        if ( scope("#ajaxsearchpro" + id + "_" + instance).length > 0 )
            return scope("#ajaxsearchpro" + id + "_" + instance).ajaxsearchpro(func, args);
    };

    var threeParams = function(id, func, args) {
        var _this = this;
        var scope = _this.getScope();
        var ret = false;

        if ( !isNaN(parseFloat(func)) && isFinite(func) ) {
            if ( scope("#ajaxsearchpro" + id + "_" + func).length > 0 ) {
                return scope("#ajaxsearchpro" + id + "_" + func).ajaxsearchpro(args);
            }
        } else {
            if ( id == 0 ) {
                if ( scope(".asp_main_container.hasASP").length > 0 ) {
                    scope(".asp_main_container.hasASP").each(function(){
                        ret = scope(this).ajaxsearchpro(func, args);
                    });
                }
            } else {
                if ( scope("div.asp_m_" + id).length > 0 ) {
                    scope("div.asp_m_" + id).each(function(){
                        ret = scope(this).ajaxsearchpro(func, args);
                    });
                }
            }
        }

        return ret;
    };

    var twoParams = function(id, func) {
        var _this = this;
        var scope = _this.getScope();
        var ret = false;

        if ( func == 'exists' ) {
            return scope("div.asp_m_" + id).length > 0;
        }

        if ( id == 0 ) {
            if ( scope(".asp_main_container.hasASP").length > 0 ) {
                scope(".asp_main_container.hasASP").each(function(){
                    ret = scope(this).ajaxsearchpro(func);
                });
            }
        } else {
            if ( scope("div.asp_m_" + id).length > 0 ) {
                scope("div.asp_m_" + id).each(function () {
                    ret = scope(this).ajaxsearchpro(func);
                });
            }
        }

        return ret;
    };


    if ( arguments.length == 4 ){
        return(
            fourParams.apply( this, arguments )
        );
    } else if ( arguments.length == 3 ) {
        return(
            threeParams.apply( this, arguments )
        );
    } else if ( arguments.length == 2 ) {
        return(
            twoParams.apply( this, arguments )
        );
    } else if ( arguments.length == 0 ) {
        console.log("Usage: ASP.api(id, [optional]instance, function, [optional]args);");
        console.log("---------------------------------");
        console.log("functions list:");
        return window.ASP.printFunctionsList();
    }
});

// Make a reference clone, just in case if an ajax page loader decides to override
window._ASP = ASP;

// Call the ready method
window._ASP.ready();