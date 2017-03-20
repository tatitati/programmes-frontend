'use strict';

const gulp = require('gulp');
const args = require('yargs').argv;
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');

const staticPathSrc = 'src/Resources';
const staticPathDist = 'web/assets';
const sassMatch = '/sass/**/*.scss';

const env = args.env || 'dev';

gulp.task('images', function() {
    gulp.src(staticPathSrc + '/images/**/*.*')
        .pipe(gulp.dest(staticPathDist + '/images/'));
});

gulp.task('sass', function() {
    if (env === 'dev') {
        return gulp.src(staticPathSrc + sassMatch)
            .pipe(sourcemaps.init())
            .pipe(sass().on('error', sass.logError))
            .pipe(sourcemaps.write())
            .pipe(gulp.dest(staticPathDist + '/css/'));
    }

    return gulp.src(staticPathSrc + sassMatch)
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(gulp.dest(staticPathDist + '/css/'));
});

gulp.task('watch',function() {
    gulp.watch(staticPathSrc + sassMatch,['sass']);
});

gulp.task('default', ['sass', 'images']);
