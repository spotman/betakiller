<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Model\UrlElementZone;
use BetaKiller\Repository\UrlElementZoneRepository;

class ImportZones extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Repository\UrlElementZoneRepository
     */
    private $repo;

    /**
     * ImportZones constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface      $config
     * @param \BetaKiller\Repository\UrlElementZoneRepository $repo
     */
    public function __construct(ConfigProviderInterface $config, UrlElementZoneRepository $repo)
    {
        parent::__construct();

        $this->config = $config;
        $this->repo   = $repo;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        foreach ((array)$this->config->load(['zones']) as $zoneName) {
            $model = $this->repo->findByName($zoneName);

            if (!$model) {
                $model = new UrlElementZone;
                $model->setName($zoneName);
                $this->repo->save($model);
            }
        }
    }
}
