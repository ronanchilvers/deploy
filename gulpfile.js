// Include gulp
var gulp = require('gulp');
var hub = require('gulp-hub');

gulp.registry(new hub(['./resources/tasks/*.js']));

// Watch Files For Changes
gulp.task('watch', function() {
    gulp.watch('./resources/sass/**/*.scss', gulp.parallel('sass'));
    gulp.watch('./resources/js/**/*.js', gulp.parallel('js'));
});

// Default Task
gulp.task('default', gulp.series(
    'sass',
    'js',
    'watch'
));
