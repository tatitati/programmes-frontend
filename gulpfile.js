'use strict';

const gulp = require('gulp');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const rev = require('gulp-rev');
const revdelOriginal = require('gulp-rev-delete-original');
const del = require('del');

const staticPathSrc = 'app/Resources';
const staticPathDist = 'web/assets';
const sassMatch = '/sass/**/*.scss';
const imageMatch = '/images/*';

gulp.task('css:clean', function() {
    return del([staticPathDist + '/css']);
});

gulp.task('images:clean', function() {
    return del([staticPathDist + '/images']);
});

gulp.task('sass', ['css:clean'], function() {
    return gulp.src(staticPathSrc + sassMatch)
        .pipe(sourcemaps.init())
        .pipe(sass({outputStyle: 'compressed', includePaths: [
            'node_modules'
        ]}).on('error', sass.logError))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(staticPathDist + '/css/'));
});

gulp.task('images', ['images:clean'], function() {
    return gulp.src(staticPathSrc + '/images/**/*')
        .pipe(gulp.dest(staticPathDist + '/images/'));
});

gulp.task('rev', ['sass', 'images'], function() {
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
    gulp.watch([
        staticPathSrc + sassMatch
    ],['sass']);

    gulp.watch(staticPathSrc + imageMatch,['images']);
});

gulp.task('default', ['sass', 'images']);
gulp.task('distribution', ['rev']);
