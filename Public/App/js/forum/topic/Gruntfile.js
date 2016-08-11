/**
 * Created by airbreak on 2016/4/8.
 */
module.exports = function (grunt) {
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-csscomb');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    //�Զ�������
    grunt.registerTask('build', 'require demo', function () {
        //�����б�
        var tasks = ['requirejs'];
        //Դ���ļ�
        var srcDir = 'js';
        //Ŀ���ļ�
        var destDir = 'dest';
        //���ò���
        grunt.config.set('config', {
            srcDir: srcDir,
            destDir: destDir
        });
        //����requireJs����Ϣ
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
        //��������
        grunt.task.run(tasks);
        grunt.config.set("requirejs", requireShareTask);
    });
}