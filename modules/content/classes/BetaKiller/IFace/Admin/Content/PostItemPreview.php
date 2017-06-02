<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Model\IFaceZone;

class PostItemPreview extends AdminBase
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentUrlParametersHelper
     */
    private $urlParametersHelper;

    /**
     * Returns data for View
     * Override this method in child classes
     */
    public function getData(): array
    {
        $post = $this->urlParametersHelper->getContentPost();

        $previewUrl = $this->ifaceHelper->getReadEntityUrl($post, IFaceZone::PUBLIC_ZONE).'?preview=true';

        return [
            'previewUrl' => $previewUrl,
        ];
    }
}
