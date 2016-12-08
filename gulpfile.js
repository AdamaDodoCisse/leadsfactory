var gulp = require ("gulp");
var react = require ("gulp-react");
var concat = require ("gulp-concat");

gulp.task ('default', function () {
   return gulp.src('Resources/public/jsx/**')
       .pipe (react())
       .pipe (concat ('Resources/public/app/application.js'))
       .pipe (gulp.dest ('./'))
});