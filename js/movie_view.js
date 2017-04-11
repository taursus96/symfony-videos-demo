"use strict";
(function($) {
    var init = function() {
        $('.movie-vote-button').on('click', movieVoteClicked);
        $('.movie-comment-vote-button').on('click', movieCommentVoteClicked);
    };

    var movieVoteClicked = function() {
        var voteType = $(this).data('vote-type');
        var movieId = $(this).parents('.movie').data('movie-id');

        movieVote(movieId, voteType);
    };

    var movieVote = function(movieId, voteType) {
        $.get('/movie/vote/' + movieId + '/' + voteType).done(updateMovieVotes);
    };

    var updateMovieVotes = function(data) {
        $('#movie-vote-thumbs-up-count').text(data.thumbsUp);
        $('#movie-vote-thumbs-down-count').text(data.thumbsDown);
    };


    var movieCommentVoteClicked = function() {
        var voteType = $(this).data('vote-type');
        var commentId = $(this).parents('.movie-comment-container').data('comment-id');

        movieCommentVote(commentId, voteType);
    };

    var movieCommentVote = function(commentId, voteType) {
        $.get('/movie/comment/vote/' + commentId + '/' + voteType).done(updateMovieCommentVotes);
    };

    var updateMovieCommentVotes = function(data) {
        var commentEl = $('.movie-comment-container[data-comment-id=' + data.commentId + ']');
        commentEl.find('.movie-comment-vote-thumbs-up-count').text(data.thumbsUp);
        commentEl.find('.movie-comment-vote-thumbs-down-count').text(data.thumbsDown);
    };

    $(document).ready(init);
})(jQuery)
