<?php

namespace BetaKiller\IFace\Admin\Content\Shortcode;

use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Repository\ShortcodeRepository;
use BetaKiller\Url\Zone;
use Psr\Http\Message\ServerRequestInterface;

readonly class IndexIFace extends AbstractAdminIFace
{
    /**
     * Index constructor.
     *
     * @param \BetaKiller\Repository\ShortcodeRepository $repo
     */
    public function __construct(private ShortcodeRepository $repo)
    {
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $otherData = [];

        $otherShortcodes = array_merge($this->repo->getStaticShortcodes(), $this->repo->getDynamicShortcodes());

        foreach ($otherShortcodes as $entity) {
            $tagName             = $entity->getTagName();
            $otherData[$tagName] = $this->makeShortcodeData($entity, $request);
        }

        // Sort by tag name alphabetically
        ksort($otherData);

        return [
            'other_shortcodes' => $otherData,
        ];
    }

    /**
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $shortcode
     *
     * @return array
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function makeShortcodeData(ShortcodeEntityInterface $shortcode, ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        return [
            'codename' => $shortcode->getCodename(),
            'tag_name' => $shortcode->getTagName(),
            'url'      => $urlHelper->getReadEntityUrl($shortcode, Zone::admin()),
        ];
    }
}
