<?php
namespace Deployer;

use Deployer\Exception\Exception;
use Deployer\Task\Context;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Exception\RuntimeException;

require 'recipe/common.php';

set('git_tty', true); // [Optional] Allocate tty for git on first deployment
set('ssh_multiplexing', true);

$tzName = date_default_timezone_get();
$tz     = new \DateTimeZone($tzName);

set('core_path', 'core');
set('core_repository', 'git@github.com:spotman/betakiller.git');

// Default application path
set('app_path', 'app');
set('app_git_root', 'app');

set('git_exec', '%s'); // Nothing by default, just change dir on the server and call git comand directly

set('repo_base_path', function () {
    return getLatestReleasePath();
});

// Option for GIT management
option('repo', null, InputOption::VALUE_OPTIONAL, 'Tag to deploy.', 'app');

// Option --to for migrations:down
option('to', null, InputOption::VALUE_OPTIONAL, 'Target migration');

// Option for Minion tasks direct calls
//option('task', null, InputOption::VALUE_OPTIONAL, 'Minion task name');


\define('DEPLOYER_STAGE_DEV', 'development');
\define('DEPLOYER_STAGE_TESTING', 'testing');
\define('DEPLOYER_STAGE_STAGING', 'staging');
\define('DEPLOYER_STAGE_PRODUCTION', 'production');

set('default_stage', DEPLOYER_STAGE_DEV);

// Local server for creating migrations, git actions, etc
localhost('dev')
    ->stage(DEPLOYER_STAGE_DEV);

// Local server for testing deployment tasks in dev environment
localhost('testing')
    ->set('deploy_path', sys_get_temp_dir().DIRECTORY_SEPARATOR.'deployer-testing')
    ->stage(DEPLOYER_STAGE_TESTING);

$serversFile = getcwd().'/hosts.yml';
if (file_exists($serversFile)) {
    // Process app servers list
    inventory($serversFile);
}

set('keep_releases', 3);

/**
 * Check environment before real deployment starts
 */
task('check', static function () {
    if (stage() === DEPLOYER_STAGE_DEV) {
        throw new RuntimeException('Can not deploy to a local stage');
    }

    if (!get('app_repository')) {
        throw new RuntimeException('Please, set up GIT repo via set("app_repository")');
    }

    if (!get('app_path')) {
        throw new RuntimeException('Please, set up site path via set("app_path")');
    }

    $host = Context::get()->getHost();

    $appBranch  = $host->has('app_branch') ? $host->get('app_branch') : null;
    $coreBranch = $host->has('core_branch') ? $host->get('core_branch') : null;

    if (!$appBranch) {
        $appBranch = askForLocalBranch();
    }

    if (!$coreBranch) {
        throw new RuntimeException('Core target branch must be set in hosts.yml');
    }

    set('app_branch', $appBranch);
    set('core_branch', $coreBranch);

    // Check is assets:build task defined
//    task('assets:build');
});

// Prepare app env
task('deploy:repository:prepare:app', static function () {
    set('repository', get('app_repository'));
    set('repository_path', get('app_path'));
    set('repository_branch', get('app_branch'));
})->desc('Prepare app repository');

// Prepare BetaKiller env
task('deploy:repository:prepare:betakiller', static function () {
    set('repository', get('core_repository'));
    set('repository_path', get('core_path'));
    set('repository_branch', get('core_branch'));
})->desc('Prepare BetaKiller repository');

function askForLocalBranch(): string
{
    // Fetch branches from remote
    runLocally('git fetch --all');

    // List all branches
    $listResult = runLocally('git branch -r');

    $branches = explode(PHP_EOL, $listResult);

    // Filter real branches only, reset indexes
    $branches = array_values(array_filter($branches, static function (string $name) {
        return $name && strpos($name, 'HEAD') === false;
    }));

    // Map names (cut "origin/")
    $branches = array_map(static function (string $name) {
        return mb_substr($name, mb_strpos($name, '/') + 1);
    }, $branches);

    $selected = ask('Select target branch', $branches[0], $branches);

    if (!$selected) {
        throw new Exception('Target branch is required');
    }

    return $selected;
}

function cd_repository_path_cmd(): string
{
    return 'cd {{release_path}}/{{repository_path}}';
}

// Clone repo task
task('deploy:repository:clone', static function () {
    cd('{{release_path}}');
    run('git clone {{repository}} {{repository_path}}');
})->desc('Fetch repository'); //.env()->parse('{{repository_path}}')

// Fetch repo branch task
task('deploy:repository:checkout', static function () {
    run(cd_repository_path_cmd().' && git checkout {{repository_branch}}');
})->desc('Checkout repository branch');    // .env()->parse('{{repository_path}}')

// Update repo task
task('deploy:repository:update', static function () {
    run(cd_repository_path_cmd().' && git pull && git submodule update --init --recursive');
})->desc('Update repository');    // .env()->parse('{{repository_path}}')

// Installing vendors in BetaKiller
task('deploy:vendors:betakiller', static function () {
    process_vendors('core');
})->desc('Process Composer inside BetaKiller repository');

// Installing vendors in app
task('deploy:vendors:app', static function () {
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
    'deploy:repository:checkout',
    'deploy:repository:update',
    'deploy:vendors:app',
])->desc('Deploy app repository');

// Deploy BetaKiller
task('deploy:betakiller', [
    'deploy:repository:prepare:betakiller',
    'deploy:repository:clone',
    'deploy:repository:checkout',
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

task('deploy:betakiller:shared', static function () {
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
    '{{app_path}}/public/assets/static',
    '{{app_path}}/assets',
]);

task('deploy:betakiller:writable', static function () {
    set('writable_dirs', get('betakiller_writable_dirs'));
})->desc('Process BetaKiller writable dirs');

after('deploy:betakiller:writable', 'deploy:writable');

/**
 * PHP tasks
 */
task('php:version', static function () {
    writeln(run('{{bin/php}} -v'));
})->desc('PHP version');

/**
 * Apache tasks
 */
task('httpd:reload', static function () {
    // TODO https://ubuntuforums.org/showthread.php?t=1505075
    run('sudo service httpd reload');
})->desc('Reload Apache config');

task('httpd:restart', static function () {
    run('sudo service httpd restart');
})->desc('Restart Apache');


/**
 * GIT tasks
 */
task('git:config:user', static function () {
    $name = ask('Enter git name:', stage());
    gitConfig('user.name', $name);

    $email = ask('Enter git email:');
    gitConfig('user.email', $email);
})->desc('set global git properties like user.email');

task('git:status', static function () {
    gitStatus();
})->desc('git status');

task('git:check-no-changes', static function () {
    if (gitHasLocalChanges()) {
        throw new \RuntimeException('Git repo is not clean; commit changes or stash them');
    }
})->desc('git status --porcelain');

task('git:add', static function () {
    gitAdd();
})->desc('git add');

task('git:commit', static function () {
    gitCommit();
})->desc('git commit -m "Commit message"');

task('git:commit:all', static function () {
    gitCommitAll();
})->desc('git add . && git commit -m "Commit message"');

task('git:push', static function () {
    gitPush();
})->desc('git push');

task('git:pull', static function () {
    gitPull();
})->desc('git pull');

/**
 * Makes git:check inside {current} directory
 */
task('git:check', static function () {
    $out = gitStatus();

    if (stripos($out, 'nothing to commit (working directory clean)') !== false) {
        return;
    }

    if (askConfirmation('Commit changes?')) {
        gitCommitAll();
        gitPush();
    } else {
        writeln('Exiting...');
    }
})->desc('Checks for new files that not in GIT and commits them');

task('git:checkout', static function () {
    gitCheckout();
})->desc('git checkout _branch_ (use --branch option)');

/**
 * Custom Minion tasks
 */
//task('minion', static function () {
//    if (!input()->hasOption('task')) {
//        throw new Exception('Specify task name via --task option');
//    }
//
//    $name = input()->getOption('task');
//
//    runMinionTask($name, false, true);
//})->desc('Run Minion task by its name');


/**
 * Create table with migrations
 */
task('migrations:install', static function () {
    runMinionTask('migrations:install', false, true);
})->desc('Install migrations table');

/**
 * Create new migration
 */
task('migrations:create', static function () {
    $scope = ask('Enter scope (app, core, app:module:moduleName or core:module:moduleName)', 'app');

    if (!$scope) {
        throw new Exception('Migration scope is required');
    }

    $name = ask('Enter migration short name (3-128 characters, [A-Za-z0-9-_]+)');

    if (!$name) {
        throw new Exception('Migration name is required');
    }

//    $desc = ask('Enter migration description', '');
    $desc = ''; // Prevent CLI interaction from Minion task

    $output = runMinionTask("migrations:create --name=$name --description=$desc --scope=$scope", false, true);

    $outArr = explode('Done! Check ', $output);

    if ($outArr && \count($outArr) === 2) {
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
})->onStage(DEPLOYER_STAGE_DEV)->desc('Create migration');

/**
 * Apply migrations
 */
task('migrations:up', static function () {
    runMinionTask('migrations:up');
})->desc('Apply migrations');

/**
 * Rollback migrations
 */
task('migrations:down', static function () {
    $to = input()->getOption('to');

    runMinionTask("migrations:down --to=$to");
})->desc('Rollback migrations; use "--to" option to set new migration base');

/**
 * Show migrations history
 */
task('migrations:history', static function () {
    runMinionTask('migrations:history');
})->desc('Show migrations list');

task('sitemap:generate', static function () {
    runMinionTask('sitemap', true);
})->desc('Generate sitemap.xml');

task('cache:warmup', static function () {
    runMinionTask('cache:warmup', true);
})->desc('Warm up cache by making internal HTTP request to every IFace');

task('assets:deploy', static function () {
    runMinionTask('assets:deploy', true);
})->desc('Collect assets from all static-files directories');

task('bk:deploy:dotenv:migrate', static function () {
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

task('bk:deploy:dotenv:revision', static function () {
    $revision = gitRevision();
    runMinionTask('storeAppRevision --revision='.$revision);
})->desc('Set APP_REVISION env variable from current git revision');

/**
 * Maintenance mode
 */
task('maintenance:on', static function () {
    runMinionTask('maintenance:on --for=deploy');
})->desc('Enable maintenance mode');

task('maintenance:prolong', static function () {
    runMinionTask('maintenance:prolong --for=600');
})->desc('Prolong maintenance mode for 10 minutes');

task('maintenance:off', static function () {
    runMinionTask('maintenance:off');
})->desc('Disable maintenance mode');

/**
 * Daemons maintenance
 */
task('daemon:supervisor:kill', [
    'daemon:supervisor:stop',
    'daemon:supervisor:start',
])->desc('Kill and restart supervisor');

task('daemon:supervisor:start', static function () {
    runMinionTask('daemon:start --name="supervisor"', true);
})->desc('Start supervisor');

task('daemon:supervisor:stop', static function () {
    runMinionTask('daemon:stop --name="supervisor"', true);
})->desc('Stop supervisor');

task('daemon:supervisor:reload', static function () {
    runMinionTask('daemon:supervisor:reload', true);
})->desc('Reload daemons in supervisor');

task('daemon:supervisor:restart', static function () {
    runMinionTask('daemon:supervisor:restart', true);
})->desc('Restart daemons in supervisor');

/**
 * Import data
 */
task('import:roles', static function () {
    runMinionTask('import:roles');
})->desc('Import ACL roles data');

task('import:zones', static function () {
    runMinionTask('import:zones');
})->desc('Import UrlElement zones');

task('import:i18n', static function () {
    runMinionTask('import:i18n');
})->desc('Import localization data')->onStage(
    \DEPLOYER_STAGE_STAGING,
    \DEPLOYER_STAGE_PRODUCTION
);

task('import:notification', static function () {
    runMinionTask('notification:importGroups');
})->desc('Import notifications data');

/**
 * Started message
 */
task('deploy:started', static function () {
    writeln('<info>Deploying to <comment>https://{{hostname}}</comment></info>');
});

/**
 * Success message
 */
task('deploy:done', static function () use ($tz) {
    $dateTime = (new \DateTimeImmutable())->setTimezone($tz)->format('H:i:s T');
    writeln('<info>Successfully deployed to <comment>https://{{hostname}}</comment> at '.$dateTime.'!</info>');
});

/**
 * Keep maintenance mode in case ofo failure
 */
//after('deploy:failed', 'maintenance:prolong');

task('migrate', [
    // Migrate DB
    'migrations:up',

    // Import data
    'import:zones',
    'import:roles',
    'import:notification', // Depends on roles
    'import:i18n', // Depends on roles and notification
])->setPrivate();

task('bk:deploy:prepare', [
    // Check app configuration
    'check',

    'deploy:started',
    'deploy:lock',

    // Prepare directories
    'deploy:prepare',

    // Check for new untracked files and commit them
    //'git:check',

    'deploy:release',
]);

task('bk:deploy:code', [
    'deploy:app',
    'deploy:betakiller',
]);

task('bk:deploy:shared', [
    // app shared and writable dirs
    'deploy:shared',
    'deploy:writable',

    // BetaKiller shared and writable dirs
    'deploy:betakiller:shared',
    'deploy:betakiller:writable',
]);

task('bk:deploy:dotenv', [
    // Copy .env file from previous revision to the new one
    'bk:deploy:dotenv:migrate',

    // Store APP_REVISION hash (leads to cache reset)
    'bk:deploy:dotenv:revision',
]);

task('bk:deploy:assets', [
    // Deploy assets (locally or on remote host)
    'assets:build',
    'assets:deploy',
]);

task('bk:deploy:migrate', [
    // Enable maintenance mode before any DB processing
    'maintenance:on',

    // Migrate and import data
    'migrate',

    // Prepare
    'sitemap:generate',
    'cache:warmup',

    // Restart daemons and workers from new directory
    'daemon:supervisor:kill',

    // Switch to new version
    'deploy:symlink',

    // Disable maintenance mode
    'maintenance:off',
]);

task('bk:deploy:finalize', [
    'cleanup',
    'deploy:unlock',
    'deploy:done',
]);

/**
 * Composite deployment scenario
 */
task('bk:deploy', [
    'git:check-no-changes',

    'bk:deploy:prepare',
    'bk:deploy:code',
    'bk:deploy:shared',
    'bk:deploy:dotenv',
    'bk:deploy:assets',
    'bk:deploy:migrate',
    'bk:deploy:finalize',
])->desc('Deploy app bundle')->onStage(
    DEPLOYER_STAGE_STAGING,
    DEPLOYER_STAGE_PRODUCTION,
    DEPLOYER_STAGE_TESTING
);

task('update', [
    'migrate',
])->desc('Update local workspace')->onHosts('dev')->onStage(\DEPLOYER_STAGE_DEV);

task('load:errors', static function () {
    download('{{release_path}}/{{core_path}}/application/logs/errors.sqlite', __DIR__.'/application/logs/');
})->desc('Load errors database from remote host');

/**
 * Run minion-task and echo result to console
 *
 * @param string    $name
 * @param bool|null $asHttpUser
 * @param bool|null $tty
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 * @throws \Deployer\Exception\RuntimeException
 */
function runMinionTask(string $name, bool $asHttpUser = null, bool $tty = null)
{
    $currentPath = getcwd();
    $path        = getRepoPath();
    $corePath    = get('core_path');

    if (!str_contains($currentPath, $corePath)) {
        $path .= '/public';
    }

    $stage = stage();

    $cmd = "{{bin/php}} $path/index.php --task=$name --stage=$stage";

    if (isVeryVerbose()) {
        $cmd .= ' --debug';
    }

    if ($asHttpUser) {
        $cmd = 'sudo -u {{http_user}} '.$cmd;
    }

    $text = run($cmd, [
        'tty' => (bool)$tty,
    ]);

    if ($text) {
        write($text);
    }

    return $text;
}

/**
 * Run git command and echo result to console
 *
 * @param string      $gitCmd
 * @param string|null $path
 * @param bool|null   $silent
 *
 * @return string
 * @throws \Deployer\Exception\Exception
 */
function runGitCommand(string $gitCmd, string $path = null, ?bool $silent = null): string
{
    $path   = $path ?: getRepoPath();
    $silent = $silent ?? false;

    $exec = sprintf(get('git_exec'), "cd $path && git $gitCmd");
    $result = run($exec);

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

function gitHasLocalChanges(): bool
{
    $result = runLocally('git status --porcelain --untracked-files=no');

    return (bool)$result;
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
    $branch = input()->getOption('branch') ?: 'master';

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
function gitRevision(): string
{
    return trim(runGitCommand('rev-parse --short HEAD', getRepoPath('app'), true));
}

/**
 * Get current stage
 *
 * @return string
 */
function stage()
{
    $context = Context::get();

    if ($context) {
        return $context->getHost()->get('stage');
    }

    return input()->getArgument('stage') ?: get('default_stage');
}

function getLatestReleasePath()
{
    /** @var string[] $list */
    $list    = get('releases_list');
    $release = $list[0] ?? null;

    if (!$release) {
        return get('release_path') ?: get('current');
    }

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
function getRepoPath(?string $repo = null): string
{
    if (stage() === DEPLOYER_STAGE_DEV) {
        return getcwd();
    }

    $repoPaths = [
        'core' => 'core_path',
        'app'  => 'app_path',
    ];

    if (!$repo) {
        $repo = (string)input()->getOption('repo') ?: 'app'; // TODO Maybe revert to 'core'
    }

    if (!\in_array($repo, array_keys($repoPaths), true)) {
        throw new Exception('Unknown repo '.$repo);
    }

    $basePath = get('repo_base_path');

    return $basePath.'/'.get($repoPaths[$repo]);
}
