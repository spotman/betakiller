<?php

// For php-ssh extension
//require dirname(__FILE__).'/vendor/autoload.php';

require 'recipe/common.php';

use \Symfony\Component\Console\Input\InputOption;

set('core_path', 'core');
set('core_repository', 'git@github.com:spotman/betakiller.git');

// Use PHP SSH2 extension
//set('ssh_type', 'ext-ssh2');

// Process server list
serverList('servers.yml');


// Option for GIT management
option('repo', null, InputOption::VALUE_OPTIONAL, 'Tag to deploy.', 'app');

task('check', function() {
    if ( !has('app_repository') )
        throw new \Symfony\Component\Process\Exception\RuntimeException('Please, set up GIT repo via env("app_repository")');

    if ( !has('app_path') )
        throw new \Symfony\Component\Process\Exception\RuntimeException('Please, set up site path via env("app_path")');

    // Store these parameters to env for using them in shared and writable dirs
    env('app_repository', get('app_repository'));
    env('app_path', get('app_path'));

    env('core_repository', get('core_repository'));
    env('core_path', get('core_path'));

//    writeln('Deploying to '.implode(', ', env('server.stages')));
});

// Prepare app env
task('deploy:repository:prepare:app', function() {
    env('repository', get('app_repository'));
    env('repository_path', get('app_path'));
})->desc('Prepare app repository');

// Prepare BetaKiller env
task('deploy:repository:prepare:betakiller', function() {
    env('repository', get('core_repository'));
    env('repository_path', get('core_path'));
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


function run_git_command($gitCmd) {
    $allowed = ['core', 'app'];

    $repo = input()->getOption('repo');

    if (!in_array($repo, $allowed))
        throw new Exception('Unknown git repo '.$repo);

    $key = $repo.'_path';
    $path = get($key);

    $cmd = 'cd {{current}}/'.$path.' && git '.$gitCmd;

    $result = run($cmd);
    write($result);

    return $result;
}

function git_status() {
    return run_git_command('status');
}

function git_commit_all() {
    $message = ask('Enter commit message:', 'Commit from production');
    return run_git_command('add .') . run_git_command('commit -am "'.$message.'"');
}

function git_push() {
    return run_git_command('push');
}

function git_pull() {
    return run_git_command('pull');
}

task('git:status', function () {
    git_status();
})->desc('git status');

task('git:commit:all', function () {
    git_commit_all();
})->desc('git commit -a');

task('git:push', function () {
    git_push();
})->desc('git push');

task('git:pull', function () {
    git_pull();
})->desc('git pull');

task('git:check', function () {
    $out = git_status();

    if (strpos($out, 'nothing to commit (working directory clean)') !== FALSE)
        return;

    if (askConfirmation('Commit changes?', false)) {
        git_commit_all();
        git_push();
    } else {
        writeln('Exiting...');
    }
})->desc('Checks for new files that not in GIT and commits them');

task('deploy', [
    // Check app configuration
    'check',

    // Prepare directories
    'deploy:prepare',

    // Check for new untracked files and commit them
    //'git:check',

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
