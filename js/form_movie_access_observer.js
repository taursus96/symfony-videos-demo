"use strict";
(function($, MOVIE_ACCESS_TYPE_PAID) {
    var init = function() {
        $('#movie_access').on('change', onAccessChange).change();
    };

    var onAccessChange = function() {
        var accessType = $(this).val();
        changePriceState(
            isPriceRequried(accessType)
        );
    };

    var isPriceRequried = function(accessType) {
        return accessType == MOVIE_ACCESS_TYPE_PAID;
    };

    var changePriceState = function(state) {
        if (state) {
            $('#form-movie-price-group')
                .show()
                .children('#movie_price')
                .prop('required', true);
        } else {
            $('#form-movie-price-group')
                .hide()
                .children('#movie_price')
                .prop('required', false);
        }
    };

    $(document).ready(init);
})(jQuery, MOVIE_ACCESS_TYPE_PAID)
