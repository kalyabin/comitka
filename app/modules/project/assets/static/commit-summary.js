/**
 * Commit summary page plugin
 *
 * options:
 *
 * @param {String} fileDetailsUrl Detail URL to file view
 * @param {String} fileViewModalId Modal window ID for file view
 * @param {String} fileLinkSelector Selector to link for open file view
 */
(function($) {
    $.fn.commitSummary = function(options) {
        $(document).ready(function() {
            var $fileModal = $('#' + options.fileViewModalId);
            var fileDetailsUrl = options.fileDetailsUrl;

            var openFileViewModal = function(pageParams) {
                pageParams = pageParams.replace(/^#(.*)/g, '$1');
                $.ajax({
                    'dataType'  : 'json',
                    'type'      : 'get',
                    'url'       : fileDetailsUrl,
                    'data'      : yii.getCsrfParam() + '=' + yii.getCsrfToken() + '&' + pageParams,
                    'success'   : function(response) {
                        if (response.html && response.diff) {
                            $fileModal.find('.js-revision-title').html(response.diff);
                            $fileModal.find('.modal-body').html(response.html);
                            $fileModal.modal('show');
                        }
                    }
                });
            };

            /**
             * Open a file view modal
             */
            $(options.fileLinkSelector).click(function(e) {
                openFileViewModal($(this).attr('href'));
            });

            /**
             * Bootstrap hash
             */
            if (location.hash) {
                openFileViewModal(location.hash);
            }
        });

        return this;
    };
}) (jQuery);
