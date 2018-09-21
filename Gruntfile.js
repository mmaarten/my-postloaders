module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    sass: {
      // compiles specific scss files and puts them inside assets/css
      dist: {
        options: {
          style: 'expanded'
        },
        files: {
          // 'destination': 'source'
          'assets/css/post-loader.css': 'src/scss/post-loader.scss', 
        }
      }
    },
    cssmin: {
      // minifies all css files in assets/css
      dist: {
        files: [{
          expand: true,
          cwd: 'assets/css',
          src: ['*.css', '!*.min.css'],
          dest: 'assets/css',
          ext: '.min.css'
        }]
      }
    },
    postcss: {
      options: {
        map: true, // inline sourcemaps

        processors: [
          require('autoprefixer')({browsers: 'last 2 versions'}), // add vendor prefixes
        ]
      },
      // adds vendor prefixes for all css files in assets/css
      dist: {
        src: 'assets/css/*.css'
      }
    },
    concat: {
      // merges specific js files into assets/js/post-loader.js
      core: {
        src: [
          'src/js/post-loader.js',
        ],
        dest: 'assets/js/post-loader.js',
      }
    },
    uglify:
    {
      options: {
        compress: {
          drop_console: true
        }
      },
      // minifies all js files inside assets/js
      dist: {
        files: [{
          expand: true,
          cwd: 'assets/js',
          src: ['**/*.js', '!**/*.min.js'],
          dest: 'assets/js',
          rename: function (dst, src) {
            return dst + '/' + src.replace('.js', '.min.js');
          }
        }]
      }
    },
    watch: {
      css: {
        files: 'src/**/*.scss',
        tasks: ['sass', 'postcss', 'cssmin'],
        options: {
          livereload: true,
        },
      },
      scripts: {
        files: ['src/js/**/*.js'],
        tasks: [ 'concat', 'copy', 'uglify'],
        options: {
          interrupt: true
        }
      }
    }
  });

  // tasks.
  grunt.loadNpmTasks( 'grunt-contrib-sass' );
  grunt.loadNpmTasks( 'grunt-postcss' );
  grunt.loadNpmTasks( 'grunt-contrib-uglify' );
  grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
  grunt.loadNpmTasks( 'grunt-contrib-concat' );
  grunt.loadNpmTasks( 'grunt-contrib-watch' );

  // Default task(s).
  grunt.registerTask( 'default', ['watch'] );

  // run: `grunt dist` to built assets folder
  grunt.registerTask( 'dist', [
    'sass:dist',        // compiles src/scss to assets/css
    'postcss:dist',     // adds vendor prefixes
    'concat:core',      // creates 1 js file in assets/js
    'cssmin:dist',      // minifies css files in assets/css
    'uglify:dist'       // minifies js files in assets/js
  ]);
};