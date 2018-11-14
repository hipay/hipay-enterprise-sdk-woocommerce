module.exports = function(grunt) {

  require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

  var configFileCreationNeeded = function() {
    return !grunt.file.exists('test/config.js')
  };

  grunt.initConfig({
    clean: ['dist'],

    pkg: grunt.file.readJSON('package.json'),

    concat: {
      options: {
        separator: ';',
      },
      dist: {
        src: ['src/reqwest.js', 'src/json3.js', 'src/hipay-fullservice-sdk.js'],
        dest: 'dist/hipay-fullservice-sdk.js',
      },
    },  

    uglify: {
      my_target: {
        files: {
          'dist/hipay-fullservice-sdk.min.js': ['dist/hipay-fullservice-sdk.js']
        }
      }
    }

  });

  grunt.registerTask('default', ['sync', 'clean', 'concat', 'uglify']);

};