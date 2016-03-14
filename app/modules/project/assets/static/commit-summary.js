/**
 * Commit summary page plugin
 *
 * options:
 *
 * @param {String} fileDetailsUrl Detail URL to file view
 * @param {String} fileContentSelector Selector to file content wrappers
 * @param {String} fileLinkSelector Selector to link for open file view
 * @param {String} fileLinkActiveClass Active file link class
 */
(function($) {
    $.fn.commitSummary = function(options) {
        $(document).ready(function() {
            /**
             * Open file view in container.
             * Using AJAX to load content.
             *
             * @param {string} pageParams HTTP query
             * @param {string} containerId File view container id
             */
            var openFileView = function(pageParams, containerId) {
                $.ajax({
                    'dataType'  : 'json',
                    'type'      : 'get',
                    'url'       : options.fileDetailsUrl,
                    'data'      : yii.getCsrfParam() + '=' + yii.getCsrfToken() + '&' + pageParams,
                    'success'   : function(response) {
                        if (response.html && response.diff) {
                            $('#' + containerId).html(response.html);
                            $('#' + containerId).show();
                            $('body').animate({
                                'scrollTop' : $('#' + containerId).offset().top
                            });
                        }
                    }
                });
            };

            /**
             * Open a file view modal
             */
            $(options.fileLinkSelector).click(function(e) {
                // clear other details
                $(options.fileContentSelector).html('');
                openFileView($(this).data('params'), $(this).data('container'));
                location.hash = $(this).data('mode') + '/' + $(this).data('container');
                // mark link as single active
                $(options.fileLinkSelector).removeClass(options.fileLinkActiveClass);
                $(this).addClass(options.fileLinkActiveClass);
                e.preventDefault();
            });

            /**
             * Bootstrap hash
             */
            if (location.hash) {
                var data = location.hash.replace(/^#(.*)/g, '$1').split('/');
                if (data[0] && data[1]) {
                    $(options.fileLinkSelector).each(function() {
                        if ($(this).data('container') === data[1] && $(this).data('mode') === data[0]) {
                            openFileView($(this).data('params'), $(this).data('container'));
                            $(this).addClass(options.fileLinkActiveClass);
                        }
                    });
                }
            }
        });

        return this;
    };
}) (jQuery);
