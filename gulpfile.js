const { src, dest, watch, series, parallel } = require('gulp');
const sass = require('gulp-sass');
sass.compiler = require('node-sass');
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer');

function css() {
    return src('./application/views/scss/*.scss')
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(sass())
        .pipe(autoprefixer())
        .pipe(sourcemaps.write('.'))
        .pipe(dest('./application/views/css'));
}

// Watch files
function watchFiles() {
    watch(['./application/views/scss'], css);
}

exports.default = css;
exports.watch = watchFiles;