<?php
namespace BetaKiller\IFace\Admin\Content\Shortcode;

use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Repository\ShortcodeRepository;
use Psr\Http\Message\ServerRequestInterface;

class IndexIFace extends AbstractAdminIFace
{
    /**
     * @var \BetaKiller\Repository\ShortcodeRepository
     */
    private $repo;

    /**
     * Index constructor.
     *
     * @param \BetaKiller\Repository\ShortcodeRepository $repo
     */
    public function __construct(ShortcodeRepository $repo)
    {
//        parent::__construct();

        $this->repo = $repo;
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $otherData = [];

        /** @var ShortcodeEntityInterface[] $otherShortcodes */
        $otherShortcodes = array_merge($this->repo->getStaticShortcodes(), $this->repo->getDynamicShortcodes());

        foreach ($otherShortcodes as $entity) {
            $tagName             = $entity->getTagName();
            $otherData[$tagName] = $this->makeShortcodeData($entity);
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
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function makeShortcodeData(ShortcodeEntityInterface $shortcode): array
    {
        return [
            'codename' => $shortcode->getCodename(),
            'tag_name' => $shortcode->getTagName(),
            'url'      => $this->ifaceHelper->getReadEntityUrl($shortcode),
        ];
    }
}
