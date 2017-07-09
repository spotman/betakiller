<?php
namespace BetaKiller\IFace\Admin\Error;

abstract class AbstractPhpExceptionIndex extends ErrorAdminBase
{
    /**
     * @Inject
     * @var \BetaKiller\Repository\PhpExceptionRepository
     */
    protected $repository;

    /**
     * @return \BetaKiller\Model\PhpExceptionModelInterface[]
     */
    abstract protected function getPhpExceptions(): array;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        $exceptionsData = [];

        foreach ($this->getPhpExceptions() as $model) {
            $paths = array_map(function ($path) {
                return \Debug::path($path);
            }, $model->getPaths());

            $exceptionsData[] = [
                'hash'       => $model->getHash(),
                'showUrl'    => $this->ifaceHelper->getReadEntityUrl($model),
                'urls'       => $model->getUrls(),
                'paths'      => $paths,
                'modules'    => $model->getModules(),
                'message'    => \Text::limit_chars($model->getMessage(), 120, '...', false),
                'lastSeenAt' => $model->getLastSeenAt()->format('d.m.Y H:i:s'),
                'isResolved' => $model->isResolved(),
                'isRepeated' => $model->isRepeated(),
                'isIgnored'  => $model->isIgnored(),
            ];
        }

        return [
            'exceptions' => $exceptionsData,
        ];
    }
}
