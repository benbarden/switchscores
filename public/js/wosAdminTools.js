
var wosAdminTools = {

    convertToLinkTitle: function(idOfFieldToCheck, idOfFieldToUpdate) {

        if ($('#' + idOfFieldToUpdate).val() != '') {
            return false;
        }
        textToConvert = $('#' + idOfFieldToCheck).val();
        if (textToConvert == '') {
            $('#' + idOfFieldToUpdate).val('');
            return false;
        }

        $.getJSON('/api/url/link-text', {title: textToConvert}, function(data) {
            linkText = data.linkText;
            //console.log(linkText);
            $('#' + idOfFieldToUpdate).val(linkText);
        });

    }

};