<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\IFace\App\Content\PostItem;
use Psr\Http\Message\ServerRequestInterface;

class PostItemPreview extends PostItem
{
    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \Kohana_Exception
     */
    public function before(ServerRequestInterface $request): void
    {
        // Disable caching
        $this->setExpiresInPast();

        parent::before($request);
    }
}
