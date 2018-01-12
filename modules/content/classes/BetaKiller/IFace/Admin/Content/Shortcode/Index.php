<?php
namespace BetaKiller\IFace\Admin\Content\Shortcode;

use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\IFace\Admin\AbstractAdminBase;
use BetaKiller\Repository\ShortcodeRepository;

class Index extends AbstractAdminBase
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
        parent::__construct();

        $this->repo = $repo;
    }

    /**
     * Returns data for View
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getData(): array
    {
        $data = [];

        foreach ($this->repo->getAll() as $entity) {
            $data[] = $this->makeShortcodeData($entity);
        }

        return [
            'shortcodes' => $data,
        ];
    }

    /**
     * @param \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $shortcode
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
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
