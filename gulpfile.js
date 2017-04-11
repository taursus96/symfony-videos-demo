var gulp = require('gulp');
var concat = require('gulp-concat');
var less = require('gulp-less');
var watch = require('gulp-watch');
var cleanCSS = require('gulp-clean-css');
var minify = require('gulp-minify');

gulp.task('less', function() {
    return gulp.src('less/*.less')
        .pipe(less())
        .pipe(concat('main.css'))
        .pipe(cleanCSS({
            compatibility: 'ie8'
        }))
        .pipe(gulp.dest('web/dist/css'))
});

gulp.task('js', function() {
    return gulp.src('js/*.js')
        .pipe(minify({
            ext: {
                src: '-debug.js',
                min: '.js'
            }
        }))
        .pipe(gulp.dest('web/dist/js'))
});

gulp.task('watch', function() {
    gulp.watch(['less/*.less'], ['less']);
    gulp.watch(['js/*.js'], ['js']);
});

gulp.task('default', ['js', 'less', 'watch']);
