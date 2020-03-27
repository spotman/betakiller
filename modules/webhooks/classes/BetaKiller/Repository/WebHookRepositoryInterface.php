<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\WebHookModelInterface;

/**
 * Class WebHookRepository
 *
 * @package BetaKiller\Repository
 * @method WebHookModelInterface[] getAll()
 * @method WebHookModelInterface[] findItemByUrlKeyValue()
 */
interface WebHookRepositoryInterface extends ConfigBasedDispatchableRepositoryInterface
{
    /**
     * @param string $serviceName
     *
     * @return WebHookModelInterface[]
     */
    public function getByServiceName(string $serviceName): array;
}
