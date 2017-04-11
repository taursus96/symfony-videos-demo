"use strict";
(function($) {
    var init = function() {
        $(window).on('hashchange', hashChanged);
        location.hash = '#free-top-voted';
    };

    var hashChanged = function() {
        var hash = location.hash.split('#')[1];

        $('#movies-lists-nav')
            .children()
            .removeClass('active');
        $('#movies-lists-nav')
            .find('#movies-list-' + hash + '-nav')
            .addClass('active');

        getMoviesList(hash);
    };

    var getMoviesList = function(hash) {
        $.get('/movies/list/' + hash).done(updateMoviesList);
    };

    var updateMoviesList = function(data) {
        $('#movies-list').html(data);
    }

    $(document).ready(init);
})(jQuery)
