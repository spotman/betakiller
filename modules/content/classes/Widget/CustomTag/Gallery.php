<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\IFace\Widget\WidgetException;

class Widget_CustomTag_Gallery extends AbstractBaseWidget
{
    use BetaKiller\Helper\LogTrait;

    /**
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentImageRepository
     */
    private $repository;

    /**
     * Returns data for View rendering
     *
     * @throws \BetaKiller\IFace\Widget\WidgetException
     * @return array
     */
    public function getData(): array
    {
        $context = $this->getContext();

        $image_ids = explode(',', $context['ids']);

        if (!$image_ids) {
            throw new WidgetException('No image IDs provided');
        }

        // TODO move to tag-related class CustomTag_Gallery
        $allowed_types = [
            'masonry',
            'slider',
        ];

        $type = Arr::get($context, 'type', $allowed_types[0]);

        if (!in_array($type, $allowed_types, true)) {
            throw new WidgetException('Unknown gallery type :value', [':value' => $type]);
        }

        $columns = (int)Arr::get($context, 'columns', 3);

        $images = [];

        foreach ($image_ids as $id) {
            /** @var \BetaKiller\Model\ContentImage $model */
            $model = $this->repository->findById($id);

            $images[] = $this->assetsHelper->getAttributesForImgTag($model, $model::SIZE_PREVIEW);
        }

        if (!$images) {
            $this->warning('Gallery has no images for ids [:ids]', [':ids' => implode(', ', $image_ids)]);
        }

        return [
            'images'  => $images,
            'type'    => $type,
            'columns' => $columns,
        ];
    }
}
