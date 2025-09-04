const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const sourcemaps = require('gulp-sourcemaps');
const cleanCSS = require('gulp-clean-css');
const browserSync = require('browser-sync').create();
const path = require('path');
const fs = require('fs');

/**
 * Mengumpulkan semua path file PHP di dalam direktori tema.
 * Path yang dikumpulkan akan berupa array yang berisi path absolut dari file PHP.
 * Fungsi ini menggunakan rekursif untuk mengumpulkan file PHP,
 * sehingga tidak perlu khawatir jika file PHP berada di dalam direktori yang dalam.
 * @returns {string[]} Array yang berisi path absolut dari file PHP.
 */
function getPhpPaths() {
  const themeDir = './'; // atau sesuaikan dengan path tema kamu
  const phpFiles = [];

  function walk(dir) {
    const files = fs.readdirSync(dir);

    files.forEach(file => {
      const fullPath = path.join(dir, file);
      const stat = fs.statSync(fullPath);

      if (stat.isDirectory()) {
        walk(fullPath);
      } else if (file.endsWith('.php')) {
        phpFiles.push(fullPath);
      }
    });
  }

  walk(themeDir);
  return phpFiles;
}


// Path untuk global assets
const paths = {
  scss: 'assets/source/scss/**/*.scss',
  js: 'assets/source/js/**/*.js',
  cssDist: 'assets/css/',
  jsDist: 'assets/js/',
};


// Task: Compile global SCSS
gulp.task('scss', function () {
  return gulp.src(paths.scss, { allowEmpty: true })
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(cleanCSS())
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.cssDist))
    .pipe(browserSync.stream());
});

// Task: Compile global JS
gulp.task('js', function () {
  return gulp.src(paths.js, { allowEmpty: true })
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.jsDist))
    .pipe(browserSync.stream());
});

// Task: Serve dan Watch
gulp.task('serve', function () {
  browserSync.init({
    proxy: 'http://codewapic.local/', // ganti sesuai local dev site kamu
    open: false,
    notify: false
  });

  gulp.watch(paths.scss, gulp.series('scss'));
  gulp.watch(paths.js, gulp.series('js'));
  // gulp.watch(getPhpPaths()).on('change', browserSync.reload);
});

// Default Task
gulp.task('default', gulp.series('scss', 'js', 'serve'));
