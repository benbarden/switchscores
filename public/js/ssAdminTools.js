
var ssAdminTools = {

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

    },

    generateNewsUrl: function(idOfFieldToCheck, idOfFieldToUpdate, newsId) {

        if ($('#' + idOfFieldToUpdate).val() != '') {
            return false;
        }
        textToConvert = $('#' + idOfFieldToCheck).val();
        if (textToConvert == '') {
            $('#' + idOfFieldToUpdate).val('');
            return false;
        }

        $.getJSON('/api/url/news-url', {title: textToConvert, newsId: newsId}, function(data) {
            linkText = data.linkText;
            //console.log(linkText);
            $('#' + idOfFieldToUpdate).val(linkText);
        });

    },

    removeGameTag: function(gameId, elemId, gameTagId) {

        if (gameTagId == '') {
            $('#js-admin-notify').text('Failed to load gameTagId');
            $('#js-admin-notify').show();
            return false;
        }

        if (!window.confirm('Remove this tag from the game?')) {
            return false;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.getJSON('/staff/categorisation/tag/game/' + gameId + '/remove', {gameTagId: gameTagId}, function(data) {
            $('#js-admin-notify').text('Tag removed!');
            $('#js-admin-notify').show();
            $('#tag-list option:selected').val('');
            setTimeout("$('#js-admin-notify').fadeOut(); window.location.reload(true);", 1000);
        })
        .fail(function(data) {
            $('#js-admin-notify').text('An error occurred: ' + data.responseJSON.error);
            $('#js-admin-notify').show();
            $('#tag-list option:selected').val('');
        });

    }

};