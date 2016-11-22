<?php

// For php-ssh extension
//require dirname(__FILE__).'/vendor/autoload.php';

require 'recipe/common.php';

use \Symfony\Component\Console\Input\InputOption;

define('BETAKILLER_CORE_PATH', 'core');

set('core_path', BETAKILLER_CORE_PATH);
set('core_repository', 'git@github.com:spotman/betakiller.git');

// Default application path
set('app_path', 'app');

// Use PHP SSH2 extension
//set('ssh_type', 'ext-ssh2');

// Option for GIT management
option('repo', null, InputOption::VALUE_OPTIONAL, 'Tag to deploy.', 'app');

// Option --branch
define('DEFAULT_BRANCH', 'master');
option('branch', 'b', InputOption::VALUE_OPTIONAL, 'GIT branch to checkout', DEFAULT_BRANCH);

// Option --to for migrations:down
option('to', 't', InputOption::VALUE_OPTIONAL, 'Target migration', null);

// Option for Minion tasks direct calls
option('task', null, InputOption::VALUE_OPTIONAL, 'Minion task name');


define('DEPLOYER_DEV_STAGE',        'development');
define('DEPLOYER_TESTING_STAGE',    'testing');
define('DEPLOYER_STAGING_STAGE',    'staging');
define('DEPLOYER_PRODUCTION_STAGE', 'production');

set('default_stage', DEPLOYER_DEV_STAGE);

// Local server for creating migrations, git actions, etc
localServer('dev')
    ->stage(DEPLOYER_DEV_STAGE);

// Local server for testing deployment tasks in dev environment
localServer('testing')
    ->env('deploy_path', sys_get_temp_dir().DIRECTORY_SEPARATOR.'deployer-testing')
    ->stage(DEPLOYER_TESTING_STAGE);

if (file_exists(getcwd().'/servers.yml')) {
    // Process app servers list
    serverList('servers.yml');
}

/**
 * Check environment before real deployment starts
 */
task('check', function() {
    if (stage() == DEPLOYER_DEV_STAGE)
        throw new \Symfony\Component\Process\Exception\RuntimeException('Can not deploy to a local stage');

    if ( !has('app_repository') )
        throw new \Symfony\Component\Process\Exception\RuntimeException('Please, set up GIT repo via env("app_repository")');

    if ( !has('app_path') )
        throw new \Symfony\Component\Process\Exception\RuntimeException('Please, set up site path via env("app_path")');

    // Store these parameters to env for using them in shared and writable dirs
    env('app_repository', get('app_repository'));
    env('app_path', get('app_path'));

    env('core_repository', get('core_repository'));
    env('core_path', get('core_path'));
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

// Installing vendors in BetaKiller
task('deploy:vendors:betakiller', function() {
    process_vendors('core');
})->desc('Process Composer inside BetaKiller repository');

// Installing vendors in app
task('deploy:vendors:app', function () {
    process_vendors('app');
})->desc('Process Composer inside app repository');

function process_vendors($repo) {
    $composer = env('bin/composer');
    $envVars = env('env_vars') ? 'export ' . env('env_vars') . ' &&' : '';

    $path = get_repo_path($repo);

    $result = run("cd $path && $envVars $composer {{composer_options}}");

    if (isVerbose()) {
        write($result);
    }

    return $result;
}

// Deploy app
task('deploy:app', [
    'deploy:repository:prepare:app',
    'deploy:repository:clone',
    'deploy:repository:update',
    'deploy:vendors:app',
])->desc('Deploy app repository');

// Deploy BetaKiller
task('deploy:betakiller', [
    'deploy:repository:prepare:betakiller',
    'deploy:repository:clone',
    'deploy:repository:update',
    'deploy:vendors:betakiller',
])->desc('Deploy BetaKiller repository');


// TODO Сборка статики перед деплоем (build:require.js)

/**
 * BetaKiller shared dirs
 */
set('betakiller_shared_dirs', [
    '{{core_path}}/modules/error/media/php_traces',

    // On some servers exception is thrown if these two lines are uncommented
    '{{core_path}}/application/logs',
    '{{app_path}}/logs',
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


/**
 * GIT tasks
 */
task('git:config:user', function () {

    $name = ask('Enter git name:', stage());
    git_config('user.name', $name);

    $email = ask('Enter git email:', 'no-reply@betakiller.ru');
    git_config('user.email', $email);

})->desc('set global git properties like user.email');


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

task('git:pull:all', function () {
    git_pull_all();
})->desc('git pull for all repositories');

/**
 * Makes git:check inside of {current} directory
 */
task('git:check', function () {
    $current_revision_path = env('current');
    $path = get_repo_path(NULL, $current_revision_path);

    $out = git_status($path);

    if (stripos($out, 'nothing to commit (working directory clean)') !== FALSE)
        return;

    if (askConfirmation('Commit changes?', false)) {
        git_commit_all($path);
        git_push($path);
    } else {
        writeln('Exiting...');
    }
})->desc('Checks for new files that not in GIT and commits them');

task('git:checkout', function () {
    git_checkout();
})->desc('git checkout _branch_ (use --branch option)');

/**
 * Custom Minion tasks
 */
task('minion', function () {
    if (!input()->hasOption('task'))
        throw new Exception('Specify task name via --task option');

    $name = input()->getOption('task');

    run_minion_task($name);
})->desc('Run Minion task by its name');


/**
 * Create table with migrations
 */
task('migrations:install', function () {
    run_minion_task('migrations:install');
})->desc('Install migrations table');

/**
 * Create new migration
 */
task('migrations:create', function () {
    $scope = ask('Enter scope (app, core, app:module:module_name or core:module:module_name)', 'app');

    if (!$scope)
        throw new Exception('Migration scope is required');

    $name = ask('Enter migration short name (3-128 characters, [A-Za-z0-9-_]+)');

    if (!$name)
        throw new Exception('Migration name is required');

    $desc = ask('Enter migration description', '');

    $output = run_minion_task("migrations:create --name=$name --description=$desc --scope=$scope");

    $out_arr = explode('Done! Check ', $output);

    if (count($out_arr) == 2) {
        $file_path = trim($out_arr[1]);

        if (file_exists($file_path)) {
            $dir = dirname($file_path);
            run_git_command('add .', $dir);
        } else {
            writeln('Can not parse output, add migration file to git by yourself');
        }
    } else {
        writeln('Can not parse output, add migration file to git by yourself');
    }
})->onlyForStage(DEPLOYER_DEV_STAGE)->desc('Create migration');

/**
 * Apply migrations
 */
task('migrations:up', function () {
    run_minion_task('migrations:up');
})->desc('Apply migrations');

/**
 * Rollback migrations
 */
task('migrations:down', function () {
    $to = input()->getOption('to');

    run_minion_task('migrations:down --to='.$to);
})->desc('Rollback migrations; use "--to" option to set new migration base');

/**
 * Show migrations history
 */
task('migrations:history', function () {
    run_minion_task('migrations:history');
})->desc('Show migrations list');


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

    'migrations:up',
    //'cache:warmup',

    // Finalize
    'deploy:symlink',
    'cleanup',
])->desc('Deploy app bundle')->onlyForStage([DEPLOYER_STAGING_STAGE, DEPLOYER_PRODUCTION_STAGE, DEPLOYER_TESTING_STAGE]);


/**
 * Run minion-task and echo result to console
 *
 * @param string $name
 * @return \Deployer\Type\Result
 * @throws Exception
 */
function run_minion_task($name) {
    $current_path = getcwd();
    $path = get_repo_path();

    if (strpos($current_path, BETAKILLER_CORE_PATH) === FALSE) {
        $path .= '/public';
    }

    $stage = stage();

    $cmd = "cd $path && php index.php --task=$name --stage=$stage";

    if (isVerbose()) {
        $cmd .= ' --debug';
    }

    $response = run($cmd);
    $text = $response->toString();

    if ($text) {
        write($text);
    }

    return $response;
}

/**
 * Run git command and echo result to console
 *
 * @param string $gitCmd
 * @param string $path
 * @return \Deployer\Type\Result
 * @throws Exception
 */
function run_git_command($gitCmd, $path = null) {
    if (!$path) {
        $path = get_repo_path();
    }

    $result = run("cd $path && git $gitCmd");
    write($result);

    return $result;
}

function git_status($path = NULL) {
    return run_git_command('status', $path);
}

function git_commit_all($path = NULL) {
    $message = ask('Enter commit message:', 'Commit from production');
    return run_git_command('add .', $path) . run_git_command('commit -am "'.$message.'"', $path);
}

function git_checkout($path = NULL) {
    $branch = input()->getOption('branch');
    return run_git_command('checkout '.$branch, $path);
}

function git_push($path = NULL) {
    return run_git_command('push', $path);
}

function git_pull($path = NULL) {
    return run_git_command('pull', $path);
}

function git_pull_all() {
    return git_pull(get_repo_path('core')).git_pull(get_repo_path('app'));
}

function git_config($key, $value) {
    return run_git_command("config --global $key \"$value\"");
}

/**
 * Get current stage
 *
 * @return string
 */
function stage() {
    return input()->getArgument('stage') ?: get('default_stage');
}

function get_latest_release_path() {
    $list = env('releases_list');
    $release = $list[0];

    if (output()->isVerbose()) {
        output()->writeln(PHP_EOL.'Releases are:'.PHP_EOL);

        foreach ($list as $key => $item) {
            output()->writeln($key.' => '.$item);
        }

        output()->writeln('');
    }

    return env()->parse('{{deploy_path}}/releases/'.$release);
}

function get_repo_path($repo = NULL, $base_path = NULL) {
    if (stage() == DEPLOYER_DEV_STAGE) {
        return getcwd();
    }

    $allowed = ['core', 'app'];

    if (!$repo) {
        $repo = input()->getOption('repo') ?: 'core';
    }

    if (!in_array($repo, $allowed))
        throw new Exception('Unknown repo '.$repo);

    if (!$base_path) {
        $base_path = get_latest_release_path();
    }

    return $base_path.'/'.get($repo.'_path');
}
