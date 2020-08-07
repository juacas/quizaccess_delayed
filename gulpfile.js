const gulp = require("gulp");
const babel = require("gulp-babel");
const uglify = require("gulp-uglify");
const rename = require("gulp-rename");
const sourcemaps = require("gulp-sourcemaps");
const GulpClient = require("gulp");
const watch = require("gulp-watch");

const paths = {
  source: "./amd/src",
  destination: "./amd/build",
};
gulp.task("watch-folder", function() {
    return gulp
      .src(paths.source + "/*.js", { base: paths.source })
      .pipe(watch(paths.source, { base: paths.source }))
      .pipe(
        rename(function (path) {
          path.extname = ".min.js";
        })
      )
      .pipe(gulp.dest(paths.destination));
});
gulp.task("copy", function() {
    return gulp
      .src(paths.source + "/*.js")
      .pipe(
        rename(function (path) {
          path.extname = ".min.js";
        })
      )
      .pipe(gulp.dest(paths.destination));
});
gulp.task("default", function () {
  return (
    gulp
      .src(paths.source + "/*.js")
      // .pipe(eslint())
      // .pipe(eslint.format())
      .pipe(sourcemaps.init({ loadMaps: true }))
      .pipe(
        babel({
          presets: ["@babel/preset-env"],
        })
      )
      // .pipe(gulp.dest(paths.destination + "/dist/babel"))
      .pipe(uglify())
      .pipe(
        rename(function (path) {
          path.extname = ".min.js";
        })
      )
      // .pipe(gulp.dest(paths.destination + "/dist/uglify"))
      .pipe(sourcemaps.write("./"))
      .pipe(gulp.dest(paths.destination))
  );
});
