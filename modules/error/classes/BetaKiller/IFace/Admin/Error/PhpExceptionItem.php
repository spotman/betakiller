<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Exception;
use BetaKiller\Model\PhpExceptionHistoryModelInterface;

class PhpExceptionItem extends ErrorAdminBase
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\PhpExceptionUrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     * @throws \BetaKiller\Exception
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
        $user = $record->getUser();

        return [
            'status' => $record->getStatus(),
            'user'   => $user ? $user->getUsername() : null,
            'time'   => $record->getTimestamp()->format('d.m.Y H:i:s'),
        ];
    }
}
