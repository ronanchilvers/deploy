var gulp = require('gulp');
var notify = require('gulp-notify');
var uglify = require('gulp-uglify');
var include = require('gulp-include');

var jsIncludes = [
    './resources/js/',
    './node_modules/'
];

gulp.task('js', function() {
    return gulp.src(['./resources/js/*.js'])
        .pipe(include({
            includePaths: jsIncludes
        }))
        .pipe(uglify())
        .pipe(gulp.dest('./web/js'))
        .on('error', notify.onError())
        ;
});
