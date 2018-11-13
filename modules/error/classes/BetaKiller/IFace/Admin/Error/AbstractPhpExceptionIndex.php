<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Repository\PhpExceptionRepository;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractPhpExceptionIndex extends AbstractErrorAdminIFace
{
    /**
     * @var \BetaKiller\Repository\PhpExceptionRepository
     */
    private $repo;

    /**
     * AbstractPhpExceptionIndex constructor.
     *
     * @param \BetaKiller\Repository\PhpExceptionRepository $repo
     */
    public function __construct(PhpExceptionRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param \BetaKiller\Repository\PhpExceptionRepository $repo
     *
     * @return \BetaKiller\Model\PhpExceptionModelInterface[]
     */
    abstract protected function getPhpExceptions(PhpExceptionRepository $repo): array;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $exceptionsData = [];

        foreach ($this->getPhpExceptions($this->repo) as $model) {
            $paths = array_map(function ($path) {
                return \Debug::path($path);
            }, $model->getPaths());

            $exceptionsData[] = [
                'hash'       => $model->getHash(),
                'showUrl'    => $urlHelper->getReadEntityUrl($model, ZoneInterface::ADMIN),
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
