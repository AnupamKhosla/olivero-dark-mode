// Run gulp and visit yoursite.ddev.com:3001 to see changes live.

// Modified by Anupam khosla 5 Feb 2026 from the original barrio theme. 

// If you want to run Gulp  inside the docker container.

// make sure your ddev config contains 

// web_extra_exposed_ports:
//   - name: browsersync
//     container_port: 3000
//     http_port: 3000
//     https_port: 3001

// https://dp.ddev.site:3001 -- replace with https://yoursite.ddev.site:3001

// may be redundant to have scriptPath function below.

// Sass warnings have been silenced to avoid confusion with warnings from dependencies.
// If you want to see sass warnings, remove the 'logger: dartSass.Logger.silent' line
// and the 'silenceDeprecations' array and quietDeps: true, from the sass options in both the styles and createCssComponent functions.


const DDEV_HOSTNAME = 'dp.ddev.site';
const HTTPS_PORT    = '3001';

let gulp = require('gulp'),
  sass = require('gulp-sass')(require('sass')),
  sourcemaps = require('gulp-sourcemaps'),
  $ = require('gulp-load-plugins')(),
  cleanCss = require('gulp-clean-css'),
  rename = require('gulp-rename'),
  postcss = require('gulp-postcss'),
  autoprefixer = require('autoprefixer'),
  postcssInlineSvg = require('postcss-inline-svg'),
  browserSync = require('browser-sync').create(),
  pxtorem = require('postcss-pxtorem'),
  postcssProcessors = [
    postcssInlineSvg({
      removeFill: true,
      paths: ['./node_modules/bootstrap-icons/icons'],
    }),
    pxtorem({
      propList: [
        'font',
        'font-size',
        'line-height',
        'letter-spacing',
        '*margin*',
        '*padding*',
      ],
      mediaQuery: true,
    }),
  ];


const dartSass = require('sass');


const paths = {
  scss: {
    src: './scss/style.scss',
    dest: './css',
    watch: './scss/**/*.scss',
    bootstrap: './node_modules/bootstrap/scss/bootstrap.scss',
    components: './components/**/*.scss',
    componentsWatch: './components/**/*.scss',
  },
  js: {
    bootstrap: './node_modules/bootstrap/dist/js/bootstrap.min.js',
    popper: './node_modules/@popperjs/core/dist/umd/popper.min.js',
    barrio: '../../contrib/bootstrap_barrio/js/barrio.js',
    dest: './js',
  },
};

// Compile sass into CSS & auto-inject into browsers
function styles() {
  return gulp
    .src([paths.scss.bootstrap, paths.scss.src])
    .pipe(sourcemaps.init())
    .pipe(
      sass({
        includePaths: [
          './node_modules/bootstrap/scss',
          '../../contrib/bootstrap_barrio/scss',
        ],
        quietDeps: true,  // <--- (Silences dependency warnings)
        silenceDeprecations: [ // <--- (Silences dependency warnings)
                'legacy-js-api', 
                'mixed-decls', 
                'color-functions', 
                'global-builtin', 
                'import', 
                'slash-div'
              ],
        logger: dartSass.Logger.silent, // hides *all* sass warnings
      }).on('error', sass.logError)
    )
    .pipe($.postcss(postcssProcessors))
    .pipe(
      postcss([
        autoprefixer({
          browsers: [
            'Chrome >= 35',
            'Firefox >= 38',
            'Edge >= 12',
            'Explorer >= 10',
            'iOS >= 8',
            'Safari >= 8',
            'Android 2.3',
            'Android >= 4',
            'Opera >= 12',
          ],
        }),
      ])
    )
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(cleanCss())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(browserSync.stream());
}

function createCssComponent(fileWithLocation) {
  return gulp
    .src([paths.scss.components])
    .pipe(sourcemaps.init())
    .pipe(
      sass({
        outputStyle: 'compressed',
        quietDeps: true,
        silenceDeprecations: ['legacy-js-api', 'import', 'global-builtin', 'color-functions', 'mixed-decls'],
        logger: dartSass.Logger.silent, // hides *all* sass warnings
      }).on('error', sass.logError)
    )
    .pipe($.postcss(postcssProcessors))
    .pipe(
      postcss([
        autoprefixer({
          browsers: [
            'Chrome >= 35',
            'Firefox >= 38',
            'Edge >= 12',
            'Explorer >= 10',
            'iOS >= 8',
            'Safari >= 8',
            'Android 2.3',
            'Android >= 4',
            'Opera >= 12',
          ],
        }),
      ])
    )
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('./components/' + '.'))
    .pipe(cleanCss())
    .pipe(browserSync.stream());
}

// Move the javascript files into our js folder
function js() {
  return gulp
    .src([paths.js.bootstrap, paths.js.popper, paths.js.barrio])
    .pipe(gulp.dest(paths.js.dest))
    .pipe(browserSync.stream());
}

// Static Server + watching scss/html files

function serve(done) {
  browserSync.init({
    proxy: {
      target: 'http://localhost',

      reqHeaders: {
        'Host': DDEV_HOSTNAME,
        'X-Forwarded-Host': DDEV_HOSTNAME,
        'X-Forwarded-Proto': 'https',
        'X-Forwarded-Port': HTTPS_PORT,
        // 1. DISABLE COMPRESSION (Critical for Injection)
        // If Drupal sends GZIP content, BrowserSync can't read/inject the script.
        'Accept-Encoding': 'identity' 
      },

      proxyRes: [function (proxyRes, req, res) {
        if (proxyRes.headers.location) {
          var original = proxyRes.headers.location;
          var safeHost = DDEV_HOSTNAME.replace(/\./g, '\\.');
          var regex = new RegExp('https?:\/\/(localhost|' + safeHost + ')(:\\d+)?');
          var replacement = 'https://' + DDEV_HOSTNAME + ':' + HTTPS_PORT;
          proxyRes.headers.location = original.replace(regex, replacement);
        }
      }]
    },

    // 2. SOCKET CONFIGURATION (Restored)
    // explicitly tells the browser to use the Secure URL for the socket
    socket: {
        domain: 'https://' + DDEV_HOSTNAME + ':' + HTTPS_PORT
    },

    listen: '0.0.0.0',
    port: 3000,
    ui: false,
    open: false,    
    online: false,
    logLevel: "silent"

  }, function(err, bs) {
    console.log('\n');
    console.log('\x1b[32m%s\x1b[0m', '---------------------------------------------------');
    console.log('\x1b[36m%s\x1b[0m', ' DDEV BrowserSync Ready!');
    console.log(' Access URL: \x1b[35mhttps://' + DDEV_HOSTNAME + ':' + HTTPS_PORT + '\x1b[0m');
    console.log('\x1b[32m%s\x1b[0m', '---------------------------------------------------');
    console.log('\n');
    done();
  });

  gulp.watch([paths.scss.watch, paths.scss.bootstrap], styles).on('change', browserSync.reload);
  gulp.watch(paths.scss.componentsWatch, createCssComponent);
}

// ... exports ...

const build = gulp.series(styles, gulp.parallel(js, serve));

exports.styles = styles;
exports.js = js;
exports.serve = serve;

exports.default = build;
