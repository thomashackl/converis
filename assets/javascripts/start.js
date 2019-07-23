$(function () {

    $('input[name="type"]').on('click', function() {
        $('section.typeselect').addClass('hidden-js');
        $('#type-' + $(this).val()).removeClass('hidden-js');
    });

    $(document).on('dialog-open', function() {
        $('.ui-datepicker').hide();
        $('#start-date').on('click', function() {
            $('.ui-datepicker').show();
        });
        $('#template').focus();
    });

});
