<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Model\IFaceZone;

class PostItemPreview extends AbstractAdminBase
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
//        $post = $this->urlParametersHelper->getContentPost();

//        $previewUrl = $this->ifaceHelper->getReadEntityUrl($post, IFaceZone::PUBLIC_ZONE).'?preview=true';

//        $this->redirect($previewUrl);

        return [];
    }

    /**
     * @return string
     */
    public function render(): string
    {
        /** @var \BetaKiller\IFace\App\Content\PostItem $iface */
        $iface = $this->ifaceHelper->createIFaceFromCodename('App_Content_PostItem');

        // TODO extend public PostItem template

        return $iface->render();
    }
}
