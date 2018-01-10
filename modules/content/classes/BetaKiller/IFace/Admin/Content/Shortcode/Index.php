<?php
namespace BetaKiller\IFace\Admin\Content\Shortcode;

use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\Content\Shortcode\ShortcodeInterface;
use BetaKiller\IFace\Admin\AbstractAdminBase;
use BetaKiller\Repository\ShortcodeRepository;

class Index extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\Repository\ShortcodeRepository
     */
    private $repo;

    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeFacade
     */
    private $facade;

    /**
     * Index constructor.
     *
     * @param \BetaKiller\Repository\ShortcodeRepository $repo
     */
    public function __construct(ShortcodeRepository $repo, ShortcodeFacade $facade)
    {
        parent::__construct();

        $this->repo = $repo;
        $this->facade = $facade;
    }

    /**
     * Returns data for View
     *
     * @return array
     */
    public function getData(): array
    {
        $data = [];

        foreach ($this->repo->getAll() as $param) {
            $shortcode = $this->facade->createFromUrlParameter($param);
            $data[] = $this->makeShortcodeData($shortcode);
        }

        return [
            'shortcodes' => $data,
        ];
    }

    private function makeShortcodeData(ShortcodeInterface $shortcode): array
    {
        return [
            'codename' => $shortcode->getCodename(),
            'tag_name' => $shortcode->getTagName(),
            // TODO
            'url' => '',
        ];
    }
}
