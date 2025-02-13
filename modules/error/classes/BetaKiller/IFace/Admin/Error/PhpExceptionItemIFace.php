<?php

namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Model\PhpExceptionHistoryModelInterface;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class PhpExceptionItemIFace extends AbstractErrorAdminIFace
{
    /**
     * PhpExceptionItem constructor.
     *
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     */
    public function __construct(private UserRepositoryInterface $userRepo)
    {
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

        // Using unsafe-inline for the whole admin coz of DebugBar CSP issues
//        \Debug::injectStackTraceCsp($request);

        $trace = $model->getTraceSize() > 0 ? $model->getTrace() : null;

        return [
            'backUrl'    => $this->getBackIFaceUrl($model, $urlHelper),
            'hash'       => $model->getHash(),
            'urls'       => $model->getUrls(),
            'paths'      => $model->getPaths(),
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

    private function getBackIFaceUrl(PhpExceptionModelInterface $model, UrlHelperInterface $helper): string
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
