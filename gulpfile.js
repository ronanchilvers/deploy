// Include gulp
var gulp = require('gulp');

// Include Our Plugins
var sass = require('gulp-sass'),
    prefix = require('gulp-autoprefixer'),
    notify = require('gulp-notify'),
    uglify = require('gulp-uglify'),
    include = require('gulp-include');

var sassIncludes = [
    'resources/sass/',
    'vendor/',
    'node_modules/',
];

var jsIncludes = [
    'resources/js/',
    'node_modules/'
];

gulp.task('sass', function() {
    return gulp.src(__dirname + '/resources/sass/*.scss')
        .pipe(sass({
            sourceStyle: 'compressed',
            includePaths: sassIncludes,
            errLogToConsole: false,
            onError: function(err) {
                console.log(err);
                return notify().write(err);
            }
        }))
        .on('error', notify.onError())
        .pipe(
            prefix({
              browsers: ['last 2 versions', 'ie >= 9', 'Android >= 2.3', 'ios >= 7']
            })
        )
        .pipe(gulp.dest('./web/css'))
        ;
});

gulp.task('js', function() {
    return gulp.src([__dirname + '/resources/js/*.js'])
        .pipe(include({
            includePaths: jsIncludes
        }))
        .pipe(uglify())
        .on('error', notify.onError())
        .pipe(gulp.dest('./web/js'))
        .on('error', notify.onError())
        ;
});

// Watch Files For Changes
gulp.task('watch', function() {
    gulp.watch('./resources/sass/**/*.scss', ['sass']);
    gulp.watch('./resources/js/**/*.js', ['js']);
});

// Default Task
gulp.task('default', [
    'sass',
    'js',
    'watch'
]);
