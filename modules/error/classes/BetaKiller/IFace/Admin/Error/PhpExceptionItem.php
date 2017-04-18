<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Exception;
use BetaKiller\Helper\IFaceTrait;

class PhpExceptionItem extends ErrorAdminBase
{
    use IFaceTrait;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData()
    {
        $model = $this->phpExceptionUrlParameter();

        if (!$model) {
            throw new Exception('Incorrect php exception hash');
        }

        $trace = $this->phpExceptionStorageFactory()->getTraceFor($model);

        /** @var UnresolvedPhpExceptionIndex $unresolvedIFace */
        $unresolvedIFace = $this->iface_from_codename('Admin_Error_UnresolvedPhpExceptionIndex');

        /** @var ResolvedPhpExceptionIndex $resolvedIFace */
        $resolvedIFace = $this->iface_from_codename('Admin_Error_ResolvedPhpExceptionIndex');

        $backIFace = $model->isResolved() ? $resolvedIFace : $unresolvedIFace;

        $history = [];

        foreach ($model->getHistoricalRecords() as $record) {
            $user = $record->getUser();

            $history[] = [
                'status'    =>  $record->getStatus(),
                'user'      =>  $user ? $user->get_username() : null,
                'time'      =>  $record->getTimestamp()->format('d.m.Y H:i:s'),
            ];
        }

        $paths = array_map(function($path) {
            return \Debug::path($path);
        }, $model->getPaths());

        return [
            'backUrl'       =>  $backIFace->url(),
            'hash'          =>  $model->getHash(),
            'urls'          =>  $model->getUrls(),
            'paths'         =>  $paths,
            'modules'       =>  $model->getModules(),
            'message'       =>  $model->getMessage(),
            'lastSeenAt'    =>  $model->getLastSeenAt()->format('d.m.Y H:i:s'),
            'isResolved'    =>  $model->isResolved(),
            'isIgnored'     =>  $model->isIgnored(),
            'counter'       =>  $model->getCounter(),
            'trace'         =>  $trace,
            'history'       =>  $history,
        ];
    }
}
