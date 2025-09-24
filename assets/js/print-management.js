jQuery(document).ready(function ($) {
    // Parent to children
    $('.category-parent').on('change', function () {
        var isChecked = $(this).is(':checked');
        $(this).closest('tr').nextUntil('.category-parent-row').find('.category-child').prop('checked', isChecked);
    });

    // Children to parent
    $('.category-child').on('change', function () {
        var parentRow = $(this).closest('tr').prevAll('.category-parent-row:first');
        var children = parentRow.nextUntil('.category-parent-row').find('.category-child');
        var allChecked = children.length > 0 && children.filter(':checked').length === children.length;
        parentRow.find('.category-parent').prop('checked', allChecked);
    });
});