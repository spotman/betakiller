<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Helper\ContentUrlContainerHelper;
use BetaKiller\Repository\ContentPostRevisionRepository;
use BetaKiller\Url\UrlDispatcherException;
use Caxy\HtmlDiff\HtmlDiff;
use Psr\Http\Message\ServerRequestInterface;

class PostItemRevision extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\Helper\ContentUrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * @var \BetaKiller\Repository\ContentPostRevisionRepository
     */
    private $revisionRepository;

    /**
     * PostItemRevision constructor.
     *
     * @param \BetaKiller\Helper\ContentUrlContainerHelper         $urlParametersHelper
     * @param \BetaKiller\Repository\ContentPostRevisionRepository $revisionRepository
     */
    public function __construct(
        ContentUrlContainerHelper $urlParametersHelper,
        ContentPostRevisionRepository $revisionRepository
    ) {
        $this->urlParametersHelper = $urlParametersHelper;
        $this->revisionRepository  = $revisionRepository;
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
        $htmlDiff = new HtmlDiff($oldHtml, $newHtml);
        $htmlDiff->getConfig()
            // Pass an instance of \Doctrine\Common\Cache\Cache to cache the calculated diffs.
            ->setCacheProvider()

            // Set the cache directory that HTMLPurifier should use.
            ->setPurifierCacheLocation(sys_get_temp_dir());

        return $htmlDiff->build();
    }
}
