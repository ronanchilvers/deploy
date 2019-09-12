var gulp = require('gulp');
var sass = require('gulp-sass');
var minify = require('gulp-clean-css');
var prefix = require('gulp-autoprefixer');
var notify = require('gulp-notify');

var sassIncludes = [
    './resources/sass/',
    './vendor/',
    './node_modules/',
];

gulp.task('sass', function() {
    return gulp.src('./resources/sass/*.scss')
        .pipe(
            sass({
                sourceStyle: 'nested',
                errLogToConsole: true,
                includePaths: sassIncludes
            })
            .on('error', notify.onError())
        )
        .pipe(
            prefix({ cascade: true })
        )
        .pipe(minify())
        .pipe(gulp.dest('./web/css'))
        .pipe(notify({message: "SASS compilation complete", onLast: true}))
        .on('error', notify.onError())
        ;
});
