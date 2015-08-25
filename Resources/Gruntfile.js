module.exports = function(grunt) {
   grunt.initConfig({
      pkg: grunt.file.readJSON('package.json'),

      chillperson: {
         folders: {
            pub: './public',
            css: '<%= chillperson.folders.pub %>/css/',
            sass: '<%= chillperson.folders.pub %>/sass/',
         }
      },
      sass: {
         dist: {
            options: {
               debugInfo: true,
            },
            files: [{
               expand: true,
               cwd: '<%= chillperson.folders.sass.src %>',
               src: ['*.scss'],
               dest: '<%= chillperson.folders.css %>',
               ext: '.css'
            }]
         }
      },
      watch: {
         css: {
            files: [ '<%= chillperson.folders.sass %>/*.scss', '<%= chillperson.folders.sass %>/**/*.scss' ],
            tasks: ['generatecss'],
            /*
            options: {
               spawn: false,
               interrupt: true,
            }
            */
         }
      },
   });

   grunt.loadNpmTasks('grunt-contrib-sass');
   grunt.loadNpmTasks('grunt-contrib-watch');

   grunt.registerTask('generatecss', 'sass');

   grunt.registerTask('default', ['generatecss']);
};