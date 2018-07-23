<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\IFace\App\Content\PostItem;

class PostItemPreview extends PostItem
{
    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     */
    public function before(): void
    {
        // Disable caching
        $this->setExpiresInPast();

        parent::before();
    }
}
