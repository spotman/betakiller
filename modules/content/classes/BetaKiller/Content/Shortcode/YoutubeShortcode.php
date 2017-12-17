<?php
namespace BetaKiller\Content\Shortcode;

use BetaKiller\Model\ContentYoutubeRecord;
use BetaKiller\Repository\ContentYoutubeRecordRepository;

class YoutubeShortcode extends AbstractEditableShortcode
{
    /**
     * @var \BetaKiller\Repository\ContentYoutubeRecordRepository
     */
    private $repository;

    /**
     * YoutubeShortcode constructor.
     *
     * @param \BetaKiller\Repository\ContentYoutubeRecordRepository $repository
     */
    public function __construct(ContentYoutubeRecordRepository $repository)
    {
        $this->repository = $repository;

        parent::__construct('youtube');
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

    public function getWidgetData(): array
    {
        $videoID = (int)$this->getAttribute('id');

        if (!$videoID) {
            throw new ShortcodeException('No YouTube ID provided');
        }

        /** @var \BetaKiller\Model\ContentYoutubeRecord $model */
        $model = $this->repository->findById($videoID);

//        $title  = Arr::get($context, 'title');
//        $align  = Arr::get($context, 'align', 'alignnone');
//        $alt    = Arr::get($context, 'alt');
//        $class  = Arr::get($context, 'class');
        $width  = (int)$this->getAttribute('width');
        $height = (int)$this->getAttribute('height');

//        $classes = array_filter(explode(' ', $class));
//        $classes['align'] = $align;
//
//        $attributes = [
//            'id'        =>  'admin-image-'.$model->getID(),
//            'title' =>  $title ?: $model->get_title(),
//            'alt'   =>  $alt ?: $model->getAlt(),
//            'class' =>  implode(' ', array_unique($classes)),
//        ];
//
//        if ($width) {
//            $attributes['style'] = 'width: '.$width.'px';
//        }

        return [
            'video' => [
                // TODO
                'src'    => $model->getYoutubeEmbedUrl(),
                'width'  => $width,
                'height' => $height,
            ],
        ];
    }

    public function getWysiwygPluginPreviewSrc(): string
    {
        $id = (int)$this->getAttribute('id');
        $model = $this->getRecordById($id);

        return $model->getPreviewUrl();
    }

    private function getRecordById(int $id): ContentYoutubeRecord
    {
        return $this->repository->findById($id);
    }
}
