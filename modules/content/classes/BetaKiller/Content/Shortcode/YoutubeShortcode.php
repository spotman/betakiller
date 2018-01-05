<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Model\ContentYoutubeRecord;
use BetaKiller\Repository\ContentYoutubeRecordRepository;

class YoutubeShortcode extends AbstractContentElementShortcode
{
    /**
     * @var \BetaKiller\Repository\ContentYoutubeRecordRepository
     */
    private $repository;

    /**
     * YoutubeShortcode constructor.
     *
     * @param string                                                $tagName
     * @param \BetaKiller\Repository\ContentYoutubeRecordRepository $repository
     */
    public function __construct(string $tagName, ContentYoutubeRecordRepository $repository)
    {
        $this->repository = $repository;

        parent::__construct($tagName);
    }

    /**
     * Returns true if current tag may have text content between open and closing markers
     *
     * @return bool
     */
    public function mayHaveContent(): bool
    {
        return false;
    }

    /**
     * @return array
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWidgetData(): array
    {
        $videoID = (int)$this->getID();

        if (!$videoID) {
            throw new ShortcodeException('No YouTube ID provided');
        }

        $model = $this->repository->findById($videoID);

        $width  = (int)$this->getAttribute('width');
        $height = (int)$this->getAttribute('height');

        return [
            'video' => [
                'src'    => $model->getYoutubeEmbedUrl(),
                'width'  => $width,
                'height' => $height,
            ],
        ];
    }

    /**
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     */
    public function getWysiwygPluginPreviewSrc(): string
    {
        $id = (int)$this->getID();
        $model = $this->getRecordById($id);

        return $model->getPreviewUrl();
    }

    private function getRecordById(int $id): ContentYoutubeRecord
    {
        return $this->repository->findById($id);
    }
}
