<?php

// For php-ssh extension
//require dirname(__FILE__).'/vendor/autoload.php';

require 'recipe/common.php';


env('core_path', 'core');

// Use PHP SSH2 extension
//set('ssh_type', 'ext-ssh2');

// Process server list
serverList('servers.yml');

task('check', function() {
    if ( !has('app_repository') )
        throw new \Symfony\Component\Process\Exception\RuntimeException('Please, set up GIT repo via env("app_repository")');

    if ( !has('app_path') )
        throw new \Symfony\Component\Process\Exception\RuntimeException('Please, set up site path via env("app_path")');

    // Store these parameters to env for using them in shared and writable dirs
    env('app_repository', get('app_repository'));
    env('app_path', get('app_path'));

//    writeln('Deploying to '.implode(', ', env('server.stages')));
});

// Prepare app env
task('deploy:repository:prepare:app', function() {
    env('repository', get('app_repository'));
    env('repository_path', get('app_path'));
})->desc('Prepare app repository');

// Prepare BetaKiller env
task('deploy:repository:prepare:betakiller', function() {
    env('repository', 'git@github.com:spotman/betakiller.git');
    env('repository_path', env('core_path'));
})->desc('Prepare BetaKiller repository');

function cd_repository_path_cmd() {
    return 'cd {{release_path}}/{{repository_path}}';
};

// Clone repo task
task('deploy:repository:clone', function() {
    cd('{{release_path}}');
    run('git clone {{repository}} {{repository_path}}');
})->desc('Fetch repository'); //.env()->parse('{{repository_path}}')

// Update repo task
task('deploy:repository:update', function() {
    run(cd_repository_path_cmd().' && git pull && git submodule update --init --recursive');
})->desc('Update repository');    // .env()->parse('{{repository_path}}')

task('deploy:betakiller:vendors', function() {
    if (commandExist('composer')) {
        $composer = 'composer';
    } else {
        run(cd_repository_path_cmd()." && curl -sS https://getcomposer.org/installer | php");
        $composer = 'php composer.phar';
    }
    run(cd_repository_path_cmd()." && $composer {{composer_options}}");
})->desc('Process Composer inside repository');    // .env()->parse('{{repository_path}}')

//// Complex task for fetching repo
//task('deploy:repository', [
//    'deploy:repository:clone',
//    'deploy:repository:update',
//]);

// Deploy app
task('deploy:app', [
    'deploy:repository:prepare:app',
    'deploy:repository:clone',
    'deploy:repository:update',
])->desc('Deploy app repository');

// Deploy BetaKiller
task('deploy:betakiller', [
    'deploy:repository:prepare:betakiller',
    'deploy:repository:clone',
    'deploy:repository:update',
    'deploy:betakiller:vendors',
])->desc('Deploy BetaKiller repository');


// TODO Сборка статики перед деплоем (build:require.js)
// TODO Перенести всю статику в публичную директорию (cache:warmup)

//task('deploy:git-update', function() {
//    cd(env()->getReleasePath());
//    run('git pull && git submodule update --init --recursive');
//})->desc('Updating git submodules');;

/**
 * BetaKiller shared dirs
 */
set('betakiller_shared_dirs', [
    '{{core_path}}/modules/error/media/php_traces',

// TODO deal with shared logs (exception is thrown if these two lines are uncommented)
//    '{{core_path}}/application/logs',
//    '{{app_path}}/logs',
]);

task('deploy:betakiller:shared', function() {
    set('shared_dirs', get('betakiller_shared_dirs'));
})->desc('Process BetaKiller shared files and dirs');

after('deploy:betakiller:shared', 'deploy:shared');


/**
 * BetaKiller writable dirs
 */
set('betakiller_writable_dirs', [
    '{{core_path}}/application/logs',
    '{{core_path}}/application/cache',
    '{{core_path}}/modules/twig/cache',

    '{{app_path}}/logs',
    '{{app_path}}/cache',
    '{{app_path}}/public/assets',
    '{{app_path}}/assets',
]);

task('deploy:betakiller:writable', function() {
    set('writable_dirs', get('betakiller_writable_dirs'));
})->desc('Process BetaKiller writable dirs');

after('deploy:betakiller:writable', 'deploy:writable');


/**
 * Apache tasks
 */
task('httpd:reload', function () {
    run('sudo service httpd reload');
})->desc('Reload Apache config');

task('httpd:restart', function () {
    run('sudo service httpd restart');
})->desc('Restart Apache');


task('deploy', [
    // Check app configuration
    'check',

    // Prepare directories
    'deploy:prepare',
    'deploy:release',

    // Deploy code
    'deploy:betakiller',
    'deploy:app',

    // app shared and writable dirs
    'deploy:shared',
    'deploy:writable',

    // BetaKiller shared and writable dirs
    'deploy:betakiller:shared',
    'deploy:betakiller:writable',

    // Finalize
    'deploy:symlink',
    'cleanup',
])->desc('Deploy app bundle');
