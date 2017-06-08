<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Exception;

class PhpExceptionItem extends ErrorAdminBase
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\PhpExceptionUrlParametersHelper
     */
    private $urlParametersHelper;

    /**
     * @Inject
     * @var \BetaKiller\Error\PhpExceptionStorageInterface
     */
    private $storage;

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

        $trace = $this->storage->getTraceFor($model);

        /** @var UnresolvedPhpExceptionIndex $unresolvedIFace */
        $unresolvedIFace = $this->ifaceHelper->createIFaceFromCodename('Admin_Error_UnresolvedPhpExceptionIndex');

        /** @var ResolvedPhpExceptionIndex $resolvedIFace */
        $resolvedIFace = $this->ifaceHelper->createIFaceFromCodename('Admin_Error_ResolvedPhpExceptionIndex');

        $backIFace = $model->isResolved() ? $resolvedIFace : $unresolvedIFace;

        $history = [];

        foreach ($model->getHistoricalRecords() as $record) {
            $user = $record->getUser();

            $history[] = [
                'status' => $record->getStatus(),
                'user'   => $user ? $user->get_username() : null,
                'time'   => $record->getTimestamp()->format('d.m.Y H:i:s'),
            ];
        }

        $paths = array_map(function ($path) {
            return \Debug::path($path);
        }, $model->getPaths());

        return [
            'backUrl'    => $backIFace->url(),
            'hash'       => $model->getHash(),
            'urls'       => $model->getUrls(),
            'paths'      => $paths,
            'modules'    => $model->getModules(),
            'message'    => $model->getMessage(),
            'lastSeenAt' => $model->getLastSeenAt()->format('d.m.Y H:i:s'),
            'isResolved' => $model->isResolved(),
            'isIgnored'  => $model->isIgnored(),
            'counter'    => $model->getCounter(),
            'trace'      => $trace,
            'history'    => $history,
        ];
    }
}
