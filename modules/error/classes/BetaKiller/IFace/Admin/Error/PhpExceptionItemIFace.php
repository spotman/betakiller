<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\PhpExceptionHistoryModelInterface;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class PhpExceptionItemIFace extends AbstractErrorAdminIFace
{
    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepo;

    /**
     * PhpExceptionItem constructor.
     *
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     */
    public function __construct(UserRepositoryInterface $userRepo)
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
     * @throws \BetaKiller\Url\UrlElementException
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
            'backUrl'    => $this->getBackIFaceUrl($model, $urlHelper),
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

    private function getBackIFaceUrl(PhpExceptionModelInterface $model, UrlHelper $helper): string
    {
        if ($model->isIgnored()) {
            return $helper->makeCodenameUrl(IgnoredPhpExceptionIndexIFace::codename());
        }

        return $model->isResolved()
            ? $helper->makeCodenameUrl(ResolvedPhpExceptionIndexIFace::codename())
            : $helper->makeCodenameUrl(UnresolvedPhpExceptionIndexIFace::codename());
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
