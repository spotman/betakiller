<?php
namespace BetaKiller\IFace\Admin\Error;

abstract class AbstractPhpExceptionIndex extends ErrorAdminBase
{
    /**
     * @Inject
     * @var \BetaKiller\Error\PhpExceptionStorageInterface
     */
    protected $phpExceptionStorage;

    /**
     * @return \BetaKiller\Error\PhpExceptionModelInterface[]
     */
    abstract protected function getPhpExceptions();

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData()
    {
        $exceptionsData = [];

        foreach ($this->getPhpExceptions() as $model) {
            $paths = array_map(function($path) {
                return \Debug::path($path);
            }, $model->getPaths());

            $exceptionsData[] = [
                'hash'          =>  $model->getHash(),
                'showUrl'       =>  $model->get_admin_url(),
                'urls'          =>  $model->getUrls(),
                'paths'         =>  $paths,
                'modules'       =>  $model->getModules(),
                'message'       =>  \Text::limit_chars($model->getMessage(), 120, '...', FALSE),
                'lastSeenAt'    =>  $model->getLastSeenAt()->format('d.m.Y H:i:s'),
                'isResolved'    =>  $model->isResolved(),
                'isRepeated'    =>  $model->isRepeated(),
                'isIgnored'     =>  $model->isIgnored(),
            ];
        }

        return [
            'exceptions'    =>  $exceptionsData,
        ];
    }
}
