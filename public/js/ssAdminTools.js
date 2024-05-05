
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

    checkForExistingGameTitle: function(idOfFieldToCheck, gameId) {

        textToCheck = $('#' + idOfFieldToCheck).val();
        if (textToCheck == '') {
            return false;
        }

        $.getJSON('/api/game/get-by-exact-title-match', {title: textToCheck, gameId: gameId}, function(data) {
            existingGameId = data.gameId;
            existingDSItemId = data.dsItemId;
            console.log(data.dsItemId);
            if (existingGameId != null) {
                //console.log('Found game: ' + existingGameId)
                $('#js-game-title-pass').hide();
                $('#js-game-title-fail-desc').html('Title exists. <a href="/staff/games/detail/' + existingGameId + '">View detail</a>.');
                $('#js-game-title-fail').show();
            } else if (existingDSItemId != null) {
                $('#js-game-title-pass').hide();
                $('#js-game-title-fail-desc').html('Unlinked data item exists. <a href="/staff/data-sources/nintendo-co-uk/add-game/' + existingDSItemId + '">Add this game</a>.');
                $('#js-game-title-fail').show();
            } else {
                //console.log('No game found');
                $('#js-game-title-pass').show();
                $('#js-game-title-fail').hide();
            }
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