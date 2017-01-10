var gulp        = require('gulp');
var browserSync = require('browser-sync').create();
var reload      = browserSync.reload;

// Save a reference to the `reload` method

gulp.task('default', function () {
    // Serve files from the root of this project
    browserSync.init({
        open: false,
        reloadOnRestart: true,
        notify: false,
        proxy: "localhost/sig-report",
        ws: true,
        ghostMode: {
            clicks: true,
            forms: true,
            scroll: false
        },
    });
    gulp.watch(["public/**", "app/**", "resources/**"]).on("change", reload);
});
