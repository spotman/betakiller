<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\MissingUrl;

use BetaKiller\IFace\Admin\AbstractAdminBase;

class MissingIndex extends AbstractAdminBase
{
    /**
     * @Inject
     * @var \BetaKiller\Repository\MissingUrlRepository
     */
    private $missingUrlRepository;

    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * Returns data for View
     *
     * @return array
     */
    public function getData(): array
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
                'edit_url'   => $this->ifaceHelper->getReadEntityUrl($missingUrlModel),
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
