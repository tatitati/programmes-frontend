'use strict';

const gulp = require('gulp');
const sass = require('gulp-sass');
const { gulpSassError } = require('gulp-sass-error');
const sourcemaps = require('gulp-sourcemaps');
const rev = require('gulp-rev');
const revdelOriginal = require('gulp-rev-delete-original');
const del = require('del');
const requirejsOptimize = require('gulp-requirejs-optimize');

const staticPathSrc = 'app/Resources';
const staticPathDist = 'web/assets';
const sassMatch = '/sass/**/*.scss';
const jsMatch = '/js/**/*.js';
const imageMatch = '/images/*';

var throwError = true;

gulp.task('js:clean', function () {
    return del([staticPathDist + '/js']);
});

gulp.task('js', ['js:clean'], function () {

    const modulesToOptimize = [
        staticPathSrc + '/js/**/rv-bootstrap.js'
    ];

    const config = {
        "baseUrl": "app/Resources/js",
        "paths": {
            "jquery-1.9": "empty:",
            "jquery.appear": "../../../node_modules/jquery.appear/jquery.appear"
        },
        "shim": {
            "jquery.appear": {
                "deps": ["jquery"],
                "exports": "jquery.appear"
            }
        },
        "map": {
            "*": {
                "jquery": "jquery-1.9"
            }
        }
    };

    return gulp.src(modulesToOptimize)
        .pipe(requirejsOptimize(config))
        .pipe(gulp.dest(staticPathDist + '/js'));
});

// ------

gulp.task('sass:clean', function() {
    return del([staticPathDist + '/css']);
});

gulp.task('sass', ['sass:clean'], function() {
    return gulp.src(staticPathSrc + sassMatch)
        .pipe(sourcemaps.init())
        .pipe(sass({outputStyle: 'compressed', includePaths: [
            'src',
            'node_modules'
        ]}).on('error', gulpSassError(throwError)))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(staticPathDist + '/css/'));
});

// ------

gulp.task('images:clean', function() {
    return del([staticPathDist + '/images']);
});

gulp.task('images', ['images:clean'], function() {
    return gulp.src(staticPathSrc + '/images/**/*')
        .pipe(gulp.dest(staticPathDist + '/images/'));
});

// ------

gulp.task('rev', ['sass', 'images', 'js'], function() {
    return gulp.src([staticPathDist + '/**/*', '!' + staticPathDist + '/**/rev-manifest.json'])
        .pipe(rev())
        .pipe(gulp.dest(staticPathDist))
        .pipe(revdelOriginal()) // delete no-revised file
        .pipe(rev.manifest('rev-manifest.json'))
        .pipe(gulp.dest(staticPathDist));
});

/*
 * Entry tasks
 */
gulp.task('watch',function() {
    // When watching we don't want to throw an error, because then we have to
    // go and restart the watch task if we ever write invalid sass, which would
    // be really annoying.
    throwError = false;

    gulp.watch(
        [staticPathSrc + sassMatch, 'src/**/*.scss'],
        ['sass']
    );

    gulp.watch([staticPathSrc + imageMatch], ['images']);
    gulp.watch([staticPathSrc + jsMatch], ['js']);
});

gulp.task('default', ['sass', 'images', 'js']);
gulp.task('distribution', ['rev']);
