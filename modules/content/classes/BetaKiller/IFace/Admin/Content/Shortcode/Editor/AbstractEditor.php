<?php

namespace BetaKiller\IFace\Admin\Content\Shortcode\Editor;

use BetaKiller\Content\Shortcode\Editor\ShortcodeEditorFactory;
use BetaKiller\Content\Shortcode\Editor\ShortcodeEditorInterface;
use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\Content\AbstractContentAdminIFace;
use Psr\Http\Message\ServerRequestInterface;

abstract readonly class AbstractEditor extends AbstractContentAdminIFace
{
    public function __construct(private ShortcodeEditorFactory $editorFactory)
    {
    }

    /**
     * @return \BetaKiller\Content\Shortcode\Editor\ShortcodeEditorInterface
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function getShortcodeEditor(ServerRequestInterface $request): ShortcodeEditorInterface
    {
        return $this->editorFactory->createFromEntity($this->getShortcodeEntity($request));
    }

    private function getShortcodeEntity(ServerRequestInterface $request): ShortcodeEntityInterface
    {
        return ServerRequestHelper::getEntity($request, ShortcodeEntityInterface::MODEL_NAME);
    }
}
