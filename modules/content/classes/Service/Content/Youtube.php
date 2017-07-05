<?php

use BetaKiller\Model\ContentYoutubeRecord;
use BetaKiller\Service\ServiceException;

class Service_Content_Youtube extends \BetaKiller\Service
{
    /**
     * @var \BetaKiller\Repository\ContentYoutubeRecordRepository
     * @Inject
     */
    private $youtubeRepository;

    public function getYoutubeIdFromEmbedUrl($url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if ( strpos($path, 'embed') === null)
            throw new ServiceException('No embed in URL :url', [':url' => $url]);

        return basename($path);
    }

    /**
     * @param string $youtubeID
     *
     * @return ContentYoutubeRecord
     */
    public function findRecordByYoutubeId($youtubeID): ContentYoutubeRecord
    {
        $model = $this->youtubeRepository->find_by_youtube_id($youtubeID);

        if (!$model) {
            $model = $this->youtubeRepository->create();
            $model->setYoutubeId($youtubeID);
        }

        return $model;
    }
}
