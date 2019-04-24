(function ($) {

    $(document).on('click', 'input[name="type"]', function() {
        $('section.typeselect').toggleClass('hidden-js');
    });

}(jQuery));
