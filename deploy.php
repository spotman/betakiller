<?php
namespace Deployer;

use Deployer\Exception\Exception;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Exception\RuntimeException;

require 'recipe/common.php';

set('git_tty', true); // [Optional] Allocate tty for git on first deployment

\define('BETAKILLER_CORE_PATH', 'core');

$tzName = date_default_timezone_get();
$tz     = new \DateTimeZone($tzName);

set('core_path', BETAKILLER_CORE_PATH);
set('core_repository', 'git@github.com:spotman/betakiller.git');

// Default application path
set('app_path', 'app');

// Option for GIT management
option('repo', null, InputOption::VALUE_OPTIONAL, 'Tag to deploy.', 'app');

// Option --branch
\define('DEFAULT_BRANCH', 'master');

// Option --to for migrations:down
option('to', 't', InputOption::VALUE_OPTIONAL, 'Target migration');

// Option for Minion tasks direct calls
option('task', null, InputOption::VALUE_OPTIONAL, 'Minion task name');


\define('DEPLOYER_DEV_STAGE', 'development');
\define('DEPLOYER_TESTING_STAGE', 'testing');
\define('DEPLOYER_STAGING_STAGE', 'staging');
\define('DEPLOYER_PRODUCTION_STAGE', 'production');

set('default_stage', DEPLOYER_DEV_STAGE);

// Local server for creating migrations, git actions, etc
localhost('dev')
    ->stage(DEPLOYER_DEV_STAGE);

// Local server for testing deployment tasks in dev environment
localhost('testing')
    ->set('deploy_path', sys_get_temp_dir().DIRECTORY_SEPARATOR.'deployer-testing')
    ->stage(DEPLOYER_TESTING_STAGE);

$serversFile = getcwd().'/hosts.yml';
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

    // Check is assets:build task defined
    task('assets:build');
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

function cd_repository_path_cmd()
{
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

/**
 * @param string $repo
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function process_vendors(string $repo)
{
    $path = getRepoPath($repo);

    $result = run("cd $path && {{bin/composer}} {{composer_options}}");

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


/**
 * BetaKiller shared dirs
 */
set('betakiller_shared_dirs', [
    // On some servers exception is thrown if these two lines are uncommented
    '{{core_path}}/application/logs',
    '{{app_path}}/logs',
]);

set('betakiller_shared_files', []);

task('deploy:betakiller:shared', function () {
    set('shared_dirs', get('betakiller_shared_dirs'));
    add('shared_files', get('betakiller_shared_files'));
})->desc('Process BetaKiller shared files and dirs');

after('deploy:betakiller:shared', 'deploy:shared');


/**
 * BetaKiller writable dirs
 */
set('betakiller_writable_dirs', [
    '{{core_path}}/application/cache',
    '{{core_path}}/application/logs',

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
    // TODO https://ubuntuforums.org/showthread.php?t=1505075
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
    $scope = ask('Enter scope (app, core, app:module:moduleName or core:module:moduleName)', 'app');

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

    if (\count($outArr) === 2) {
        $filePath = trim($outArr[1]);

        if (file_exists($filePath)) {
            $dir = \dirname($filePath);
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

task('cache:warmup', function () {
    runMinionTask('cache:warmup');
})->desc('Warm up cache by making internal HTTP request to every IFace');

task('assets:deploy', function () {
    runMinionTask('assets:deploy');
})->desc('Collect assets from all static-files directories');

task('deploy:dotenv:migrate', function () {
    $globalDotEnv  = '{{deploy_path}}/.env';
    $targetDotEnv  = '{{release_path}}/{{app_path}}/.env';
    $exampleDotEnv = '{{release_path}}/{{app_path}}/.env.example';

    if (!test("[ -f $globalDotEnv ]")) {
        if (test("[ -f $exampleDotEnv ]")) {
            run("cp $exampleDotEnv $globalDotEnv");
        } else {
            throw new Exception('Can not find .env file');
        }
    }

    run("cp $globalDotEnv $targetDotEnv");
})->desc('Copy .env file from deploy path');

task('deploy:dotenv:revision', function () {
    $revision = gitRevision('app').gitRevision('core');
    runMinionTask('storeAppRevision --revision='.$revision);
})->desc('Set APP_REVISION env variable from current git revision');

/**
 * Maintenance mode
 */
task('maintenance:on', function () {
    runMinionTask('maintenance:on --for=deploy');
})->desc('Enable maintenance mode');

task('maintenance:off', function () {
    runMinionTask('maintenance:off');
})->desc('Disable maintenance mode');

/**
 * Daemons maintenance
 */
task('daemon:supervisor:restart', [
    'daemon:supervisor:stop',
    'daemon:supervisor:start',
])->desc('Restart supervisor');

task('daemon:supervisor:start', function() {
    runMinionTask('daemon:start --name="supervisor"');
})->desc('Start supervisor');

task('daemon:supervisor:stop', function() {
    runMinionTask('daemon:stop --name="supervisor"');
})->desc('Stop supervisor');

task('daemon:supervisor:reload', function() {
    runMinionTask('daemon:supervisor:reload');
})->desc('Reload daemons in supervisor');

/**
 * Import data
 */
task('import:roles', function () {
    runMinionTask('importRoles');
})->desc('Import ACL roles data');

task('import:zones', function () {
    runMinionTask('importZones');
})->desc('Import UrlElement zones');

task('import:i18n', function () {
    runMinionTask('import:i18n');
})->desc('Import localization data');

task('import:notification', function () {
    runMinionTask('notification:importGroups');
})->desc('Import notifications data');

/**
 * Success message
 */
task('deploy:done', function () use ($tz) {
    $dateTime = new \DateTimeImmutable();
    writeln('<info>Successfully deployed at '.$dateTime->setTimezone($tz)->format('H:i:s T').'!</info>');
});

task('migrate', [
    // Migrate DB
    'migrations:up',

    // Import data
    'import:zones',
    'import:roles',
    'import:notification', // Depends on roles
    'import:i18n', // Depends on roles and notification
])->setPrivate();

task('deploy', [
    // Check app configuration
    'check',

    'deploy:lock',

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

    // Copy .env file from previous revision to the new one
    'deploy:dotenv:migrate',

    // Store APP_REVISION hash (leads to cache reset)
    'deploy:dotenv:revision',

    // Deploy assets (locally or on remote host)
    'assets:build',

    // Enable maintenance mode before any DB processing
    'maintenance:on',

    // Migrate and import data
    'migrate',

    // Prepare
    'assets:deploy',
    'cache:warmup',

    // Restart daemons and workers
    'daemon:supervisor:restart',

    // Switch to new version
    'deploy:symlink',

    // Disable maintenance mode
    'maintenance:off',

    // Finalize
    'cleanup',
    'deploy:unlock',
    'deploy:done',
])->desc('Deploy app bundle')->onStage(
    DEPLOYER_STAGING_STAGE,
    DEPLOYER_PRODUCTION_STAGE,
    DEPLOYER_TESTING_STAGE
);

task('update', [
    'migrate',
])->desc('Update local workspace')->onHosts('dev')->onStage(\DEPLOYER_DEV_STAGE);

/**
 * Run minion-task and echo result to console
 *
 * @param string $name
 *
 * @return string
 * @throws Exception
 */
function runMinionTask(string $name)
{
    $currentPath = getcwd();
    $path        = getRepoPath();

    if (strpos($currentPath, BETAKILLER_CORE_PATH) === false) {
        $path .= '/public';
    }

    $stage = stage();

    $cmd = "cd $path && {{bin/php}} index.php --task=$name --stage=$stage";

    if (isVeryVerbose()) {
        $cmd .= ' --debug';
    }

    $text = run($cmd);

    if ($text) {
        write($text);
    }

    return $text;
}

/**
 * Run git command and echo result to console
 *
 * @param string    $gitCmd
 * @param string    $path
 * @param bool|null $silent
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function runGitCommand($gitCmd, $path = null, ?bool $silent = null)
{
    $path   = $path ?: getRepoPath();
    $silent = $silent ?? false;

    $result = run("cd $path && git $gitCmd");

    if (!$silent) {
        write($result);
    }

    return $result;
}

/**
 * @param null|string $path
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function gitStatus(?string $path = null)
{
    return runGitCommand('status', $path);
}

/**
 * @param null|string $basePath
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function gitAdd(?string $basePath = null)
{
    $addPath = ask('Path to add files:', '.');

    return runGitCommand('add '.$addPath, $basePath);
}

/**
 * @param null|string $path
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function gitCommit(?string $path = null)
{
    $message = ask('Enter commit message:', 'Commit from production');

    return runGitCommand('commit -m "'.$message.'"', $path);
}

/**
 * @param null|string $path
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function gitCommitAll(?string $path = null)
{
    return gitAdd($path).gitCommit($path);
}

/**
 * @param null|string $path
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function gitCheckout(?string $path = null)
{
    $branch = input()->getOption('branch') ?: DEFAULT_BRANCH;

    return runGitCommand('checkout '.$branch, $path);
}

/**
 * @param null|string $path
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function gitPush(?string $path = null)
{
    return runGitCommand('push', $path);
}

/**
 * @param null|string $path
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function gitPull(?string $path = null)
{
    return runGitCommand('pull', $path);
}

/**
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function gitPullAll()
{
    return gitPull(getRepoPath('core')).gitPull(getRepoPath('app'));
}

/**
 * @param string $key
 * @param string $value
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function gitConfig(string $key, string $value)
{
    return runGitCommand("config --global $key \"$value\"");
}

/**
 * @param string $repo
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function gitRevision(string $repo)
{
    return trim(runGitCommand('rev-parse --short HEAD', getRepoPath($repo), true));
}

/**
 * Get current stage
 *
 * @return string
 */
function stage()
{
    return input()->getArgument('stage') ?: get('default_stage');
}

function getLatestReleasePath()
{
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

/**
 * @param null|string $repo
 * @param null|string $basePath
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function getRepoPath(?string $repo = null, ?string $basePath = null)
{
    if (stage() === DEPLOYER_DEV_STAGE) {
        return getcwd();
    }

    $allowed = ['core', 'app'];

    if (!$repo) {
        $repo = input()->getOption('repo') ?: 'core';
    }

    if (!\in_array($repo, $allowed, true)) {
        throw new Exception('Unknown repo '.$repo);
    }

    if (!$basePath) {
        $basePath = getLatestReleasePath();
    }

    return $basePath.'/'.get($repo.'_path');
}
