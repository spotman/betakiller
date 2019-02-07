<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Backup\DavBackup;
use BetaKiller\Config\ConfigProviderInterface;
use Psr\Log\LoggerInterface;

class Backup extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $configProvider;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Task_Backup constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $configProvider
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(ConfigProviderInterface $configProvider, LoggerInterface $logger)
    {
        $this->configProvider = $configProvider;
        $this->logger         = $logger;

        parent::__construct();
    }

    public function defineOptions(): array
    {
        // No cli arguments
        return [];
    }

    public function run(): void
    {
        $service = (string)$this->config('backup.service');

        $instance = null;

        switch ($service) {
            case 'YandexDisk':
                $instance = new \YandexBackup($this->config('backup.login'), $this->config('backup.password'));
                break;

            case 'GoogleDisk':
                $instance = new \GoogleBackup($this->config('backup.login'), $this->config('backup.password'));
                break;

            case 'Dav':
                $instance = new DavBackup($this->config('backup.url'), $this->config('backup.login'), $this->config('backup.password'));
                break;

            default:
                throw new TaskException('Unknown backup service :name', [':name' => $service]);
        }

        $this->logger->debug('Service '.$service.' selected');

        $instance->setType($this->config('backup.type'));

        $dbKey    = 'database.'.$this->config('backup.database').'.';
        $dbDriver = $this->getDBDriver(mb_strtolower($this->config($dbKey.'type')));

        $dbHost = $this->config($dbKey.'connection.hostname');
        $dbPort = $this->config($dbKey.'connection.port');
        $dbName = $this->config($dbKey.'connection.database');
        $dbUser = $this->config($dbKey.'connection.username');
        $dbPass = $this->config($dbKey.'connection.password');

        $this->logger->debug('dbDriver is '.$dbDriver);
        $this->logger->debug('dbHost is '.$dbHost);

        $this->logger->info('Backing up database '.$dbName);

        /**
         * Force utf8 charset for mysql
         * @url http://stackoverflow.com/questions/4475548/pdo-mysql-and-broken-utf-8-encoding
         */
        if ($dbDriver === 'mysql') {
            $dbName .= ';charset=utf8';
        }

        $instance->setDbConnection(
            $dbUser, // user
            $dbPass, // pass
            $dbName, // db name
            $dbHost.($dbPort ? ':'.$dbPort : ''), // host:port
            $dbDriver // driver
        );

        $folder = realpath($this->config('backup.folder'));

        $this->logger->info('Backing up folder '.$folder);

        $prefix            = $this->config('backup.prefix');
        $timestampedPrefix = $this->config('backup.useTimestampedPrefix');

        $instance
            ->setPath($folder)
            ->setPrefix($prefix, $timestampedPrefix);

        if ($instance->execute()) {
            $this->logger->info('Backup done, see file '.$instance->getRealName());
        } else {
            $this->logger->warning('Backup was not created!');
        }
    }

    /**
     * @param string $group
     * @param null   $default
     *
     * @return \Config_Group|string|int|bool|null
     */
    private function config($group, $default = null)
    {
        $path = explode('.', $group);

        return $this->configProvider->load($path) ?: $default;
    }

    protected function getDBDriver($driver): string
    {
        if (\in_array($driver, ['mysqli', 'mysql'], true)) {
            return 'mysql';
        }

        throw new TaskException('Unknown database driver :name', [':name' => $driver]);
    }
}
