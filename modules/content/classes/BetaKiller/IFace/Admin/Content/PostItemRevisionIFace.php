<?php

namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\Repository\ContentPostRevisionRepository;
use BetaKiller\Url\UrlDispatcherException;
use Psr\Http\Message\ServerRequestInterface;

readonly class PostItemRevisionIFace extends AbstractContentAdminIFace
{
    /**
     * PostItemRevision constructor.
     *
     * @param \BetaKiller\Helper\ContentUrlContainerHelper         $urlParametersHelper
     * @param \BetaKiller\Repository\ContentPostRevisionRepository $revisionRepository
     */
    public function __construct(
        private ContentUrlContainerHelper $urlParametersHelper,
        private ContentPostRevisionRepository $revisionRepository
    ) {
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Url\UrlDispatcherException
     * @throws \Kohana_Exception
     */
    public function getData(ServerRequestInterface $request): array
    {
        $post     = $this->urlParametersHelper->getContentPost($request);
        $revision = $this->urlParametersHelper->getContentPostRevision($request);

        if (!$post) {
            throw new UrlDispatcherException('Missing ContentPost model');
        }

        if (!$revision) {
            throw new UrlDispatcherException('Missing ContentPostRevision model');
        }

        $post->useRevision($revision);
        $currentLabel       = $post->getLabel();
        $currentContent     = $post->getContent();
        $currentTitle       = $post->getTitle();
        $currentDescription = $post->getDescription();

        $previousRevision = $this->revisionRepository->getPreviousRevision($revision);

        if ($previousRevision) {
            $post->useRevision($previousRevision);
        }

        $previousLabel       = $previousRevision ? $post->getLabel() : null;
        $previousContent     = $previousRevision ? $post->getContent() : null;
        $previousTitle       = $previousRevision ? $post->getTitle() : null;
        $previousDescription = $previousRevision ? $post->getDescription() : null;

        return [
            'revision' => [
                'label'       => $this->getDiff($previousLabel, $currentLabel),
                'content'     => $this->getDiff($previousContent, $currentContent),
                'title'       => $this->getDiff($previousTitle, $currentTitle),
                'description' => $this->getDiff($previousDescription, $currentDescription),
            ],
        ];
    }

    private function getDiff(?string $oldHtml, ?string $newHtml): string
    {
        throw new NotImplementedHttpException();
    }
}
