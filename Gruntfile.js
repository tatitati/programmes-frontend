module.exports = function (grunt) {
    // This Grunt config is hilariously out of date. Update it or move to gulp
    // or some other new hotness
    var coreSassFilesArray = [{
        expand: true,
        cwd: 'src/AppBundle/Resources/sass',
        src: ['**/*.scss', '!**/_*.scss'],
        rename: function(destBase, destPath, options) {
            return options.cwd + '/../../../../web/assets/css/' + destPath.replace(/\.scss/, '.css');
        },
    }];

    // Project configuration.
    grunt.initConfig({
        assetsPath: 'src/AppBundle/Resources',
        outputPath: 'web/assets',

        // Store your Package file so you can reference its specific data whenever necessary
        pkg: grunt.file.readJSON('package.json'),

        sass: {
            options: {
                outputStyle: 'compressed',
                sourceMap: false,
            },
            dev: {
                files: coreSassFilesArray,
                options: {
                    sourceMap: true
                },
            },
            dist: {
                files: coreSassFilesArray
            }
        },

        copy: {
            assets: {
                expand: true,
                cwd: '<%=assetsPath%>',
                src: [
                    'images/**'
                ],
                dest: '<%=outputPath%>'
            }
        },

        // Run: `grunt watch` from command line for this section to take effect
        watch: {
            options: {
                nospawn: true,
                livereload: true
            },
            sass: {
                files: ['<%=assetsPath%>/sass/**/*.scss'],
                tasks: ['sass:dev']
            }
        }
    });

    // Load NPM Tasks
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-sass');


    // Default Task
    grunt.registerTask('default', ['sass:dev', 'copy:assets']);

    // CI Task
    grunt.registerTask('ci', ['sass:dist', 'copy:assets']);
};
