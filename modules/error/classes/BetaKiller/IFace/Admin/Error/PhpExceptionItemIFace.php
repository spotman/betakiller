<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\PhpExceptionHistoryModelInterface;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Url\UrlElementInterface;
use Psr\Http\Message\ServerRequestInterface;

class PhpExceptionItemIFace extends AbstractErrorAdminIFace
{
    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * PhpExceptionItem constructor.
     *
     * @param \BetaKiller\Repository\UserRepository $userRepo
     */
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Repository\RepositoryException
     * @uses \BetaKiller\IFace\Admin\Error\UnresolvedPhpExceptionIndexIFace
     * @uses \BetaKiller\IFace\Admin\Error\ResolvedPhpExceptionIndexIFace
     * @uses \BetaKiller\IFace\Admin\Error\PhpExceptionStackTraceIFace
     */
    public function getData(ServerRequestInterface $request): array
    {
        /** @var PhpExceptionModelInterface $model */
        $model = ServerRequestHelper::getEntity($request, PhpExceptionModelInterface::class);

        $urlHelper = ServerRequestHelper::getUrlHelper($request);

//        $traceIFace      = $urlHelper->getUrlElementByCodename('Admin_Error_PhpExceptionStackTrace');

        $backIFace = $this->getBackIFace($model, $urlHelper);

        $history = [];

        foreach ($model->getHistoricalRecords() as $record) {
            $history[] = $this->getHistoricalRecordData($record);
        }

        $paths = array_map(function ($path) {
            return \Debug::path($path);
        }, $model->getPaths());

        \Debug::injectStackTraceCsp($request);

        $trace = $model->getTraceSize() > 0 ? $model->getTrace() : null;

        return [
            'backUrl'    => $urlHelper->makeUrl($backIFace),
            'hash'       => $model->getHash(),
            'urls'       => $model->getUrls(),
            'paths'      => $paths,
            'modules'    => $model->getModules(),
            'message'    => $model->getMessage(),
            'lastSeenAt' => $model->getLastSeenAt()->format('d.m.Y H:i:s'),
            'isResolved' => $model->isResolved(),
            'isIgnored'  => $model->isIgnored(),
            'counter'    => $model->getCounter(),
//            'trace_url'  => $urlHelper->makeUrl($traceIFace),
            'trace'      => $trace,
            'history'    => $history,
        ];
    }

    private function getBackIFace(PhpExceptionModelInterface $model, UrlHelper $helper): UrlElementInterface
    {
        if ($model->isIgnored()) {
            return $helper->getUrlElementByCodename(IgnoredPhpExceptionIndexIFace::codename());
        }

        return $model->isResolved()
            ? $helper->getUrlElementByCodename(ResolvedPhpExceptionIndexIFace::codename())
            : $helper->getUrlElementByCodename(UnresolvedPhpExceptionIndexIFace::codename());
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
