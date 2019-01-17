<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\IFace\App\Content\PostItemIFace;
use Psr\Http\Message\ServerRequestInterface;

class PostItemPreviewIFace extends PostItemIFace
{
    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \Kohana_Exception
     */
    public function beforeProcessing(ServerRequestInterface $request): void
    {
        $this->disableHttpCache();

        parent::beforeProcessing($request);
    }
}
