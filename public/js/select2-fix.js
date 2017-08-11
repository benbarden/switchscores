$('select').on('select2:close', (
    function() {
        $(this).focus();
    }
));
