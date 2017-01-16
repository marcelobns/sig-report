var host = "localhost/sig-report";
var gulp = require('gulp');
var run = require('gulp-run')
var merge = require('merge-stream');
var browserSync = require('browser-sync').create();
var reload = browserSync.reload;

var mainBowerFiles = require('main-bower-files');

gulp.task('server', function () {
    browserSync.init({
        proxy: host,
        open: false,
        reloadOnRestart: true,
        notify: false,
        ghostMode: {
            clicks: true,
            forms: true,
            scroll: false
        },
    });
    gulp.watch(["public/**", "app/**", "resources/**", "routes/**"]).on("change", reload);
});
gulp.task('bower-install', function() {
  return run('bower install').exec();
});
gulp.task('bower', ['bower-install'], function(){
    var css = gulp.src(mainBowerFiles({
            overrides: {
                "jquery": {ignore : true},
                "tether": {
                    main: ['./dist/css/tether.min.css']
                },
                "bootstrap": {
                    main: ['./dist/css/bootstrap.min.*']
                },
                "components-font-awesome" : {
                    main: ['./css/font-awesome.min.*']
                }
            }
        }))
    .pipe(gulp.dest('public/css'));

    var js = gulp.src(mainBowerFiles({
            overrides: {
                "jquery": {
                    main : ['./dist/jquery.min.js']
                },
                "tether": {
                    main: ['./dist/js/tether.min.js']
                },
                "bootstrap": {
                    main: ['./dist/js/bootstrap.min.js']
                },
                "components-font-awesome" : {ignore: true}
            }
        }))
    .pipe(gulp.dest('public/js'));

    var fonts = gulp.src(mainBowerFiles({
            overrides: {
                "jquery": {ignore: true},
                "tether": {ignore: true},
                "bootstrap": {ignore: true},
                "components-font-awesome" : {
                    main: [
                        './fonts/fontawesome-webfont.woff',
                        './fonts/fontawesome-webfont.woff2',
                    ]
                }
            }
        }))
    .pipe(gulp.dest('public/fonts'));

    return merge(css, js, fonts);
});
