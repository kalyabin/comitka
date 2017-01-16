(function($) {
    /**
     * Commit summary page plugin
     *
     * @param {String[]} options
     * @param {String} options.fileDetailsUrl Detail URL to file view
     * @param {String} options.fileContentSelector Selector to file content wrappers
     * @param {String} options.fileLinkSelector Selector to link for open file view
     * @param {String} options.fileLinkActiveClass Active file link class
     * @param {String} options.reviewButtonSelector Button to manage commit review
     * @param {String} options.commitPanelSelector Selector of commit head panel
     * @param {String} options.commitRowSelector Selector of commit row
     * @param {String} options.selectedCommitRowClass Class for selected row
     *
     * @returns {jQuery}
     */
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
             * @param {int=} rowNumber Row number
             */
            var openFileView = function(pageParams, containerId, rowNumber) {
                $.ajax({
                    'dataType'  : 'json',
                    'type'      : 'get',
                    'url'       : options.fileDetailsUrl,
                    'data'      : yii.getCsrfParam() + '=' + yii.getCsrfToken() + '&' + pageParams,
                    'success'   : function(response) {
                        if (response.html && response.diff) {
                            var $container = $('#' + containerId);
                            $container.html(response.html).show();
                            if (rowNumber) {

                                $container.find('.js-commit-row[data-row-number=' + rowNumber + ']')
                                    .addClass(options.selectedCommitRowClass);
                            }

                            $('body').animate({
                                'scrollTop' : $container.offset().top
                            });
                        }
                    }
                });
            };

            /**
             * Get parts of current hash
             *
             * @returns {Array}
             */
            var getHashParam = function () {
                return location.hash.replace(/^#(.*)/g, '$1').split('/');
            };

            /**
             * Create new hash
             *
             * @param {string} mode Mode of show type (diff, compare)
             * @param {string} container Container identity
             * @param {int=} rowNumber Row number
             *
             * @returns {string}
             */
            var createHash = function (mode, container, rowNumber) {
                var hash = mode + '/' + container;
                if (rowNumber) {
                    hash += '/' + rowNumber;
                }

                return hash;
            };

            /**
             * Change review state: finis review or set reviewer
             */
            $(document).on('click', options.reviewButtonSelector, function(e) {
                e.preventDefault();
                changeContributionReviewState($(this).data('url'));
            });

            /**
             * Select/Unselect commit row
             */
            $(document).on('click', options.commitRowSelector, function(e) {
                e.preventDefault();

                //Set new hash
                var hashParams = getHashParam();
                if (hashParams[0] && hashParams[1]) {
                    location.hash = createHash(hashParams[0], hashParams[1], $(this).data('row-number'));
                }

                $(this).siblings().removeClass(options.selectedCommitRowClass);
                $(this).toggleClass(options.selectedCommitRowClass);
            });

            /**
             * Open a file view modal
             */
            $(options.fileLinkSelector).click(function(e) {

                // clear other details
                $(options.fileContentSelector).html('');
                var hash = createHash($(this).data('mode'), $(this).data('container'));
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
                var data = getHashParam();
                if (data[0] && data[1]) {
                    $(options.fileLinkSelector).each(function() {
                        if ($(this).data('container') === data[1] && $(this).data('mode') === data[0]) {
                            openFileView($(this).data('params'), $(this).data('container'), data[2]);
                            $(this).addClass(options.fileLinkActiveClass);
                        }
                    });
                }
            }
        });

        return this;
    };
}) (jQuery);
