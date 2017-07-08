<?php

use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;

class Task_Backup extends AbstractTask
{
    /**
     * @Inject
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $configProvider;

    protected function _execute(array $params): void
    {
        $service = $this->config('backup.service');

        $instance = null;

        switch ($service) {
            case 'YandexDisk':
                $instance = new YandexBackup($this->config('backup.login'), $this->config('backup.password'));
                break;

            case 'GoogleDisk':
                $instance = new GoogleBackup($this->config('backup.login'), $this->config('backup.password'));
                break;

            default:
                throw new TaskException('Unknown backup service :name', [':name' => $service]);
        }

        $this->debug('Service '.$service.' selected');

        $instance->setType($this->config('backup.type'));

        $dbKey    = 'database.'.$this->config('backup.database').'.';
        $dbDriver = $this->getDBDriver(mb_strtolower($this->config($dbKey.'type')));

        $dbHost = $this->config($dbKey.'connection.hostname');
        $dbPort = $this->config($dbKey.'connection.port');
        $dbName = $this->config($dbKey.'connection.database');
        $dbUser = $this->config($dbKey.'connection.username');
        $dbPass = $this->config($dbKey.'connection.password');

        $this->debug('dbDriver is '.$dbDriver);
        $this->debug('dbHost is '.$dbHost);

        $this->info('Backing up database '.$dbName);

        /**
         * Force utf8 charset for mysql
         * @url http://stackoverflow.com/questions/4475548/pdo-mysql-and-broken-utf-8-encoding
         */
        if ($dbDriver === 'mysql') {
            $dbName .= ';charset=utf8';
        }

        $instance->setConnection(
            $dbUser, // user
            $dbPass, // pass
            $dbName, // db name
            $dbHost.($dbPort ? ':'.$dbPort : ''), // host:port
            $dbDriver // driver
        );

        $folder = realpath($this->config('backup.folder'));

        $this->info('Backing up folder '.$folder);

        $prefix            = $this->config('backup.prefix');
        $timestampedPrefix = $this->config('backup.useTimestampedPrefix');

        $instance
            ->setPath($folder)
            ->setPrefix($prefix, $timestampedPrefix);

        if ($instance->execute()) {
            $this->info('Backup done, see file '.$instance->getRealName());
        } else {
            $this->warning('Backup was not created!');
        }
    }

    /**
     * @param string $group
     * @param null   $default
     *
     * @return \Config_Group|string|int|bool|null
     * @throws \Kohana_Exception
     */
    private function config($group, $default = null)
    {
        $path = explode('.', $group);

        return $this->configProvider->load($path) ?: $default;
    }

    protected function getDBDriver($driver): string
    {
        if (in_array($driver, ['mysqli', 'mysql'], true)) {
            return 'mysql';
        }

        throw new TaskException('Unknown database driver :name', [':name' => $driver]);
    }
}
