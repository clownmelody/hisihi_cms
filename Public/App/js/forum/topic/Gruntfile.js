/**
 * Created by airbreak on 2016/4/8.
 */
module.exports = function (grunt) {
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-csscomb');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    //自定义任务
    grunt.registerTask('build', 'require demo', function () {
        //任务列表
        var tasks = ['requirejs'];
        //源码文件
        var srcDir = 'js';
        //目标文件
        var destDir = 'dest';
        //设置参数
        grunt.config.set('config', {
            srcDir: srcDir,
            destDir: destDir
        });
        //设置requireJs的信息
        var taskCfg = grunt.file.readJSON('gruntCfg.json'),
            version=taskCfg.version,
            requireShareTask = taskCfg.requirejs,
            options = requireShareTask.main.options,
            platformCfg = options.web,
            includes = platformCfg.include,
            paths = options.paths;
        options.path = paths;
        options.out = platformCfg.out+version+'.js';
        options.include = includes;
        //运行任务
        grunt.task.run(tasks);
        grunt.config.set("requirejs", requireShareTask);
    });
}