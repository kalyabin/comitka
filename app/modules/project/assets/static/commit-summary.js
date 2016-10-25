/**
 * Commit summary page plugin
 *
 * options:
 *
 * @param {String} fileDetailsUrl Detail URL to file view
 * @param {String} fileContentSelector Selector to file content wrappers
 * @param {String} fileLinkSelector Selector to link for open file view
 * @param {String} fileLinkActiveClass Active file link class
 * @param {String} reviewButtonSelector Button to manage commit review
 * @param {String} commitPanelSelector Selector of commit head panel
 */
(function($) {
    $.fn.commitSummary = function(options) {
        $(document).ready(function() {
            /**
             * Finish review or set current user as reviewer.
             *
             * @param {String} url URL for action (from panel buttons, AJAX)
             */
            var changeContributionReviewState = function(url) {
                $(options.reviewButtonSelector).prop('disabled', true);
                $.ajax({
                    'dataType'  : 'json',
                    'type'      : 'post',
                    'url'       : url,
                    'data'      : yii.getCsrfParam() + '=' + yii.getCsrfToken(),
                    'success'   : function(response) {
                        $(options.reviewButtonSelector).prop('disabled', false);
                        if (response.html) {
                            $(options.commitPanelSelector).html(response.html);
                        }
                        if (!response.success && response.message) {
                            alert(response.message);
                        }
                    }
                });
            };

            /**
             * Open file view in container.
             * Using AJAX to load content.
             *
             * @param {String} pageParams HTTP query
             * @param {String} containerId File view container id
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
             * Change review state: finis review or set reviewer
             */
            $(document).on('click', options.reviewButtonSelector, function(e) {
                e.preventDefault();
                changeContributionReviewState($(this).data('url'));
            });

            /**
             * Open a file view modal
             */
            $(options.fileLinkSelector).click(function(e) {

                // clear other details
                $(options.fileContentSelector).html('');
                var hash = $(this).data('mode') + '/' + $(this).data('container');
                // mark link as single active
                $(options.fileLinkSelector).removeClass(options.fileLinkActiveClass);

                if (location.hash === '#' + hash) {
                    //Close review block
                    location.hash = '';
                } else {
                    //Open review block
                    openFileView($(this).data('params'), $(this).data('container'));
                    location.hash = $(this).data('mode') + '/' + $(this).data('container');
                    $(this).addClass(options.fileLinkActiveClass);
                }

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
