<?php
namespace BetaKiller\Model;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Url\IFaceModelInterface;

/**
 * Class IFace
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller
 */
class IFace extends AbstractOrmModelContainsUrlElement implements IFaceModelInterface
{
    protected function configure(): void
    {
        $this->belongs_to([
            'layout' => [
                'model'       => 'IFaceLayout',
                'foreign_key' => 'layout_id',
            ],
        ]);

        $this->load_with([
            'layout',
        ]);

        parent::configure();
    }

    /**
     * Returns title for using in page <title> tag
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Returns description for using in <meta> tag
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Returns layout model
     *
     * @return IFaceLayout
     */
    private function getLayoutRelation(): IFaceLayout
    {
        return $this->layout;
    }

    /**
     * Returns layout codename
     * Allow null layout so it will be detected via climbing up the IFaces tree
     *
     * @return string
     */
    public function getLayoutCodename(): ?string
    {
        $layout = $this->getLayoutRelation();

        return $layout->loaded() ? $layout->getCodename() : null;
    }

    /**
     * @return bool
     */
    public function hideInSiteMap(): bool
    {
        return (bool)$this->hide_in_site_map;
    }

    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     */
    public function setTitle(string $value): SeoMetaInterface
    {
        $this->title = $value;

        return $this;
    }

    /**
     * Sets description for using in <meta> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     */
    public function setDescription(string $value): SeoMetaInterface
    {
        $this->description = $value;

        return $this;
    }
}
