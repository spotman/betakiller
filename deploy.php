<?php
namespace Deployer;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Exception\RuntimeException;
//use function Deployer\{
//    after, ask, askConfirmation, cd, get, input, inventory, isVerbose, localhost, option, parse, run, set, task, write, writeln
//};

require 'recipe/common.php';

// For php-ssh extension
//require dirname(__FILE__).'/vendor/autoload.php';

set('git_tty', true); // [Optional] Allocate tty for git on first deployment

define('BETAKILLER_CORE_PATH', 'core');

$tzName = date_default_timezone_get();
$tz     = new DateTimeZone($tzName);

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

// Option --to for migrations:down
option('to', 't', InputOption::VALUE_OPTIONAL, 'Target migration');

// Option for Minion tasks direct calls
option('task', null, InputOption::VALUE_OPTIONAL, 'Minion task name');


define('DEPLOYER_DEV_STAGE', 'development');
define('DEPLOYER_TESTING_STAGE', 'testing');
define('DEPLOYER_STAGING_STAGE', 'staging');
define('DEPLOYER_PRODUCTION_STAGE', 'production');

set('default_stage', DEPLOYER_DEV_STAGE);

// Local server for creating migrations, git actions, etc
localhost('dev')
    ->stage(DEPLOYER_DEV_STAGE);

// Local server for testing deployment tasks in dev environment
localhost('testing')
    ->set('deploy_path', sys_get_temp_dir().DIRECTORY_SEPARATOR.'deployer-testing')
    ->stage(DEPLOYER_TESTING_STAGE);

$serversFile = getcwd().'/servers.yml';
if (file_exists($serversFile)) {
    // Process app servers list
    inventory($serversFile);
}

/**
 * Check environment before real deployment starts
 */
task('check', function () {
    if (stage() === DEPLOYER_DEV_STAGE) {
        throw new RuntimeException('Can not deploy to a local stage');
    }

    if (!get('app_repository')) {
        throw new RuntimeException('Please, set up GIT repo via set("app_repository")');
    }

    if (!get('app_path')) {
        throw new RuntimeException('Please, set up site path via set("app_path")');
    }

    // Store these parameters to env for using them in shared and writable dirs
//    env('app_repository', get('app_repository'));
//    env('app_path', get('app_path'));
//
//    env('core_repository', get('core_repository'));
//    env('core_path', get('core_path'));
});

// Prepare app env
task('deploy:repository:prepare:app', function () {
    set('repository', get('app_repository'));
    set('repository_path', get('app_path'));
})->desc('Prepare app repository');

// Prepare BetaKiller env
task('deploy:repository:prepare:betakiller', function () {
    set('repository', get('core_repository'));
    set('repository_path', get('core_path'));
})->desc('Prepare BetaKiller repository');

function cd_repository_path_cmd() {
    return 'cd {{release_path}}/{{repository_path}}';
}

// Clone repo task
task('deploy:repository:clone', function () {
    cd('{{release_path}}');
    run('git clone {{repository}} {{repository_path}}');
})->desc('Fetch repository'); //.env()->parse('{{repository_path}}')

// Update repo task
task('deploy:repository:update', function () {
    run(cd_repository_path_cmd().' && git pull && git submodule update --init --recursive');
})->desc('Update repository');    // .env()->parse('{{repository_path}}')

// Installing vendors in BetaKiller
task('deploy:vendors:betakiller', function () {
    process_vendors('core');
})->desc('Process Composer inside BetaKiller repository');

// Installing vendors in app
task('deploy:vendors:app', function () {
    process_vendors('app');
})->desc('Process Composer inside app repository');

function process_vendors($repo) {
    $composer = get('bin/composer');
    $envVars  = get('env_vars') ? 'export '.get('env_vars').' &&' : '';

    $path = getRepoPath($repo);

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
    '{{core_path}}/modules/error/media',

    // On some servers exception is thrown if these two lines are uncommented
    '{{core_path}}/application/logs',
    '{{app_path}}/logs',
]);

task('deploy:betakiller:shared', function () {
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

task('deploy:betakiller:writable', function () {
    set('writable_dirs', get('betakiller_writable_dirs'));
})->desc('Process BetaKiller writable dirs');

after('deploy:betakiller:writable', 'deploy:writable');

/**
 * PHP tasks
 */
task('php:version', function () {
    writeln(run('{{bin/php}} -v'));
})->desc('PHP version');

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
    gitConfig('user.name', $name);

    $email = ask('Enter git email:', 'no-reply@betakiller.ru');
    gitConfig('user.email', $email);
})->desc('set global git properties like user.email');

task('git:status', function () {
    gitStatus();
})->desc('git status');

task('git:add', function () {
    gitAdd();
})->desc('git add');

task('git:commit', function () {
    gitCommit();
})->desc('git commit -m "Commit message"');

task('git:commit:all', function () {
    gitCommitAll();
})->desc('git add . && git commit -m "Commit message"');

task('git:push', function () {
    gitPush();
})->desc('git push');

task('git:pull', function () {
    gitPull();
})->desc('git pull');

task('git:pull:all', function () {
    gitPullAll();
})->desc('git pull for all repositories');

/**
 * Makes git:check inside of {current} directory
 */
task('git:check', function () {
    $currentRevisionPath = get('current');
    $path                = getRepoPath(null, $currentRevisionPath);

    $out = gitStatus($path);

    if (stripos($out, 'nothing to commit (working directory clean)') !== false) {
        return;
    }

    if (askConfirmation('Commit changes?')) {
        gitCommitAll($path);
        gitPush($path);
    } else {
        writeln('Exiting...');
    }
})->desc('Checks for new files that not in GIT and commits them');

task('git:checkout', function () {
    gitCheckout();
})->desc('git checkout _branch_ (use --branch option)');

/**
 * Custom Minion tasks
 */
task('minion', function () {
    if (!input()->hasOption('task')) {
        throw new Exception('Specify task name via --task option');
    }

    $name = input()->getOption('task');

    runMinionTask($name);
})->desc('Run Minion task by its name');


/**
 * Create table with migrations
 */
task('migrations:install', function () {
    runMinionTask('migrations:install');
})->desc('Install migrations table');

/**
 * Create new migration
 */
task('migrations:create', function () {
    $scope = ask('Enter scope (app, core, app:module:module_name or core:module:module_name)', 'app');

    if (!$scope) {
        throw new Exception('Migration scope is required');
    }

    $name = ask('Enter migration short name (3-128 characters, [A-Za-z0-9-_]+)');

    if (!$name) {
        throw new Exception('Migration name is required');
    }

    $desc = ask('Enter migration description', '');

    $output = runMinionTask("migrations:create --name=$name --description=$desc --scope=$scope");

    $outArr = explode('Done! Check ', $output);

    if (count($outArr) === 2) {
        $filePath = trim($outArr[1]);

        if (file_exists($filePath)) {
            $dir = dirname($filePath);
            runGitCommand('add .', $dir);
        } else {
            writeln('Can not parse output, add migration file to git by yourself');
        }
    } else {
        writeln('Can not parse output, add migration file to git by yourself');
    }
})->onStage(DEPLOYER_DEV_STAGE)->desc('Create migration');

/**
 * Apply migrations
 */
task('migrations:up', function () {
    runMinionTask('migrations:up');
})->desc('Apply migrations');

/**
 * Rollback migrations
 */
task('migrations:down', function () {
    $to = input()->getOption('to');

    runMinionTask('migrations:down --to='.$to);
})->desc('Rollback migrations; use "--to" option to set new migration base');

/**
 * Show migrations history
 */
task('migrations:history', function () {
    runMinionTask('migrations:history');
})->desc('Show migrations list');

/**
 * Warm up cache by making internal HTTP request to every IFace
 */
task('cache:warmup', function () {
    runMinionTask('cache:warmup');
})->desc('Warm up cache by making internal HTTP request to every IFace');

/**
 * Success message
 */
task('done', function () use ($tz) {
    $dateTime = new DateTimeImmutable();
    writeln('<info>Successfully deployed at '.$dateTime->setTimezone($tz)->format('H:i:s T').'!</info>');
});

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
    'cache:warmup',

    // Finalize
    'deploy:symlink',
    'cleanup',
    'done',
])->desc('Deploy app bundle')->onStage(
    DEPLOYER_STAGING_STAGE,
    DEPLOYER_PRODUCTION_STAGE,
    DEPLOYER_TESTING_STAGE);


/**
 * Run minion-task and echo result to console
 *
 * @param string $name
 *
 * @return \Deployer\Type\Result
 * @throws Exception
 */
function runMinionTask($name) {
    $currentPath = getcwd();
    $path        = getRepoPath();

    if (strpos($currentPath, BETAKILLER_CORE_PATH) === false) {
        $path .= '/public';
    }

    $stage = stage();

    $cmd = "cd $path && {{bin/php}} index.php --task=$name --stage=$stage";

    if (isVerbose()) {
        $cmd .= ' --debug';
    }

    $response = run($cmd);
    $text     = $response->toString();

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
 *
 * @return \Deployer\Type\Result
 * @throws Exception
 */
function runGitCommand($gitCmd, $path = null) {
    if (!$path) {
        $path = getRepoPath();
    }

    $result = run("cd $path && git $gitCmd");
    write($result);

    return $result;
}

function gitStatus($path = null) {
    return runGitCommand('status', $path);
}

function gitAdd($basePath = null) {
    $addPath = ask('Path to add files:', '.');

    return runGitCommand('add '.$addPath, $basePath);
}

function gitCommit($path = null) {
    $message = ask('Enter commit message:', 'Commit from production');

    return runGitCommand('commit -m "'.$message.'"', $path);
}

function gitCommitAll($path = null) {
    return gitAdd($path).gitCommit($path);
}

function gitCheckout($path = null) {
    $branch = input()->getOption('branch') ?: DEFAULT_BRANCH;

    return runGitCommand('checkout '.$branch, $path);
}

function gitPush($path = null) {
    return runGitCommand('push', $path);
}

function gitPull($path = null) {
    return runGitCommand('pull', $path);
}

function gitPullAll() {
    return gitPull(getRepoPath('core')).gitPull(getRepoPath('app'));
}

function gitConfig($key, $value) {
    return runGitCommand("config --global $key \"$value\"");
}

/**
 * Get current stage
 *
 * @return string
 */
function stage() {
    return input()->getArgument('stage') ?: get('default_stage');
}

function getLatestReleasePath() {
    /** @var string[] $list */
    $list    = get('releases_list');
    $release = $list[0];

    if (isVerbose()) {
        writeln(PHP_EOL.'Releases are:'.PHP_EOL);

        foreach ($list as $key => $item) {
            writeln($key.' => '.$item);
        }

        writeln('');
    }

    return parse('{{deploy_path}}/releases/'.$release);
}

function getRepoPath(?string $repo = null, ?string $base_path = null) {
    if (stage() === DEPLOYER_DEV_STAGE) {
        return getcwd();
    }

    $allowed = ['core', 'app'];

    if (!$repo) {
        $repo = input()->getOption('repo') ?: 'core';
    }

    if (!in_array($repo, $allowed, true)) {
        throw new Exception('Unknown repo '.$repo);
    }

    if (!$base_path) {
        $base_path = getLatestReleasePath();
    }

    return $base_path.'/'.get($repo.'_path');
}
