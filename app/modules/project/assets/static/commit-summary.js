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
                $.ajax({
                    'dataType'  : 'json',
                    'type'      : 'get',
                    'url'       : fileDetailsUrl,
                    'data'      : yii.getCsrfParam() + '=' + yii.getCsrfToken() + '&' + pageParams,
                    'success'   : function(response) {
                        if (response.html) {
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
                var pageParams = $(this).attr('href').replace(/^#(.*)/g, '$1');
                openFileViewModal(pageParams);
            });
        });

        return this;
    };
}) (jQuery);
