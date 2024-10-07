<?php

declare(strict_types=1);

namespace BetaKiller\Task\Import;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Model\UrlElementZone;
use BetaKiller\Repository\UrlElementZoneRepository;
use BetaKiller\Task\AbstractTask;

class Zones extends AbstractTask
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
     * Zones constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface      $config
     * @param \BetaKiller\Repository\UrlElementZoneRepository $repo
     */
    public function __construct(ConfigProviderInterface $config, UrlElementZoneRepository $repo)
    {
        $this->config = $config;
        $this->repo   = $repo;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @param \BetaKiller\Console\ConsoleOptionBuilderInterface $builder *
     *
     * @return array
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        foreach ((array)$this->config->load('zones', []) as $zoneName) {
            $model = $this->repo->findByName($zoneName);

            if (!$model) {
                $model = new UrlElementZone;
                $model->setName($zoneName);
                $this->repo->save($model);
            }
        }
    }
}
