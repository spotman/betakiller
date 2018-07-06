<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Url\UrlElementInterface;

class UrlElementRepository extends AbstractOrmBasedSingleParentTreeRepository
{
    /**
     * @return string
     */
    protected function getParentIdColumnName(): string
    {
        return 'parent_id';
    }

    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return UrlElementInterface::URL_KEY;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     *
     * @return void
     */
    protected function customFilterForTreeTraversing(ExtendedOrmInterface $orm): void
    {
        // Load with dependent IFace record and WebHook record (if exists)
        $orm->load_with([
            'iface',
            'iface:element',
            'webhook',
            'webhook:element',
        ]);

        // No special filtering is required here
    }
}
