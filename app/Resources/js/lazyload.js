define(['jquery-1.9'], function ($) {

    var Lazyload = function (options) {
        this.setOptions(options);
    };

    Lazyload.prototype = {
        options: {
            context: $('body'),
            lazy_module_css: 'lazy-module',
            lazy_css_state: {
                loading: 'lazy-module--loading',
                complete: 'lazy-module--complete',
                error: 'lazy-module--error'
            },
            data_always_lazyload: 'data-lazyload-always',
            data_inc_path: 'data-lazyload-inc'
        },
        setOptions: function (options) {
            this.options = $.extend({}, this.options, options);
        },
        init: function () {
            var _this = this;

            this.loadContents();

            $('body').on('lazyload-refresh', function (event) {
                _this.loadContents();
            });
        },
        getLazyContents: function (context) {
            var context = (context) ? context : this.options.context,
                lazy_contents = $('.' + this.options.lazy_module_css, context).not('.' + this.options.lazy_css_state.loading);

            return (lazy_contents.length) ? lazy_contents : false;
        },
        alwaysLoadContent: function (context) {
            return (context.getAttribute(this.options.data_always_lazyload) == "true") ? true : false;
        },
        loadContents: function (context) {
            var contents = this.getLazyContents(context),
                content;

            for (var i = 0; (content = contents[i]) != null; i++) {
                if (this.alwaysLoadContent(content)) {
                    this.loadAjaxContent(content);
                }
            }
        },
        loadAjaxContent: function (content) {
            var _this = this,
                content = $(content);
            content.addClass(this.options.lazy_css_state.loading);
            return $.ajax({
                url: content.attr(_this.options.data_inc_path),
                dataType: 'html',
                success: function (data) {
                    var loaded_content = $($.parseHTML(data, document, true)).addClass(_this.options.lazy_css_state.loading);
                    content.replaceWith(loaded_content);
                    setTimeout(function () {
                        loaded_content.trigger("lazyloadComplete", {
                            content: loaded_content
                        });
                        $('body').trigger("lazyloadComplete");
                    }, 1);
                },
                error: function (request, status, error) {
                    content.removeClass(_this.options.lazy_css_state.loading);
                    content.addClass(_this.options.lazy_css_state.error);
                }
            });
        }
    };
    return Lazyload;
});
