<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Admin\MissingUrl;

use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Repository\MissingUrlRepository;
use Psr\Http\Message\ServerRequestInterface;

readonly class MissingIndexIFace extends AbstractAdminIFace
{
    /**
     * MissingIndex constructor.
     *
     * @param \BetaKiller\Repository\MissingUrlRepository $missingUrlRepository
     */
    public function __construct(private MissingUrlRepository $missingUrlRepository)
    {
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $data = [];

        foreach ($this->missingUrlRepository->getOrderedByTarget() as $missingUrlModel) {
            $target = $missingUrlModel->getRedirectTarget();

            $referrersData = [];

            foreach ($missingUrlModel->getReferrerList() as $referrerModel) {
                $referrersData[] = [
                    'url'          => $referrerModel->getHttpReferer(),
                    'ip'           => $referrerModel->getIpAddress(),
                    'last_seen_at' => $referrerModel->getLastSeenAt()->format('d.m.Y H:i:s'),
                ];
            }

            $data[] = [
                'id'         => $missingUrlModel->getID(),
                'source_url' => $missingUrlModel->getMissedUrl(),
                'target_url' => $target ? $target->getUrl() : null,
                'has_target' => (bool)$target,
                'referrers'  => $referrersData,
            ];
        }

        return [
            'missing_urls' => $data,
        ];
    }
}
