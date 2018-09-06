<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Exception;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Helper\PhpExceptionUrlContainerHelper;
use BetaKiller\Model\PhpExceptionHistoryModelInterface;
use BetaKiller\Repository\UserRepository;

class PhpExceptionItem extends ErrorAdminBase
{
    /**
     * @var \BetaKiller\Helper\PhpExceptionUrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * PhpExceptionItem constructor.
     *
     * @param \BetaKiller\Helper\PhpExceptionUrlContainerHelper $urlParametersHelper
     * @param \BetaKiller\Helper\IFaceHelper                    $ifaceHelper
     * @param \BetaKiller\Repository\UserRepository             $userRepo
     */
    public function __construct(
        PhpExceptionUrlContainerHelper $urlParametersHelper,
        IFaceHelper $ifaceHelper,
        UserRepository $userRepo
    ) {
        $this->urlParametersHelper = $urlParametersHelper;
        $this->ifaceHelper         = $ifaceHelper;
        $this->userRepo            = $userRepo;
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        $model = $this->urlParametersHelper->getPhpException();

        if (!$model) {
            throw new Exception('Incorrect php exception hash');
        }

        /** @var UnresolvedPhpExceptionIndex $unresolvedIFace */
        $unresolvedIFace = $this->ifaceHelper->createIFaceFromCodename('Admin_Error_UnresolvedPhpExceptionIndex');

        /** @var ResolvedPhpExceptionIndex $resolvedIFace */
        $resolvedIFace = $this->ifaceHelper->createIFaceFromCodename('Admin_Error_ResolvedPhpExceptionIndex');

        $backIFace = $model->isResolved() ? $resolvedIFace : $unresolvedIFace;

        $history = [];

        foreach ($model->getHistoricalRecords() as $record) {
            $history[] = $this->getHistoricalRecordData($record);
        }

        $paths = array_map(function ($path) {
            return \Debug::path($path);
        }, $model->getPaths());

        /** @var \BetaKiller\IFace\Admin\Error\PhpExceptionStackTrace $traceIFace */
        $traceIFace = $this->ifaceHelper->createIFaceFromCodename('Admin_Error_PhpExceptionStackTrace');

        return [
            'backUrl'    => $this->ifaceHelper->makeIFaceUrl($backIFace),
            'hash'       => $model->getHash(),
            'urls'       => $model->getUrls(),
            'paths'      => $paths,
            'modules'    => $model->getModules(),
            'message'    => $model->getMessage(),
            'lastSeenAt' => $model->getLastSeenAt()->format('d.m.Y H:i:s'),
            'isResolved' => $model->isResolved(),
            'isIgnored'  => $model->isIgnored(),
            'counter'    => $model->getCounter(),
            'trace_url'  => $this->ifaceHelper->makeIFaceUrl($traceIFace),
            'history'    => $history,
        ];
    }

    private function getHistoricalRecordData(PhpExceptionHistoryModelInterface $record): array
    {
        $userID = $record->getUserID();
        $user   = $userID ? $this->userRepo->findById($userID) : null;

        return [
            'status' => $record->getStatus(),
            'user'   => $user ? $user->getUsername() : null,
            'time'   => $record->getTimestamp()->format('d.m.Y H:i:s'),
        ];
    }
}
