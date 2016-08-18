/**
 * Scripts on user VCS bindings update page
 */
$(document).ready(function() {
    /**
     * Click on remove binding button
     */
    $(document).on('click', '.js-remove-binding', function(e) {
        e.preventDefault();

        $(this).closest('.form-group').find('input:checkbox').prop('checked', true);
        $('.js-binding-row[data-row-id="' + $(this).data('row-id') + '"]').hide();
        $('.js-binding-row-deleted[data-row-id="' + $(this).data('row-id') + '"]').show();
    });

    /**
     * Undo remove binding
     */
    $(document).on('click', '.js-binding-row-undo', function(e) {
        e.preventDefault();

        $('.js-binding-row[data-row-id="' + $(this).data('row-id') + '"] input:checkbox').prop('checked', false);
        $('.js-binding-row[data-row-id="' + $(this).data('row-id') + '"]').show();
        $('.js-binding-row-deleted[data-row-id="' + $(this).data('row-id') + '"]').hide();
    });
});
