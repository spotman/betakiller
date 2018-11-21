<?php
namespace BetaKiller\Model;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Url\IFaceModelInterface;

/**
 * Class IFace
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller\Url
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
        return $this->get('title');
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->get('label');
    }

    /**
     * Returns description for using in <meta> tag
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->get('description');
    }

    /**
     * Returns layout model
     *
     * @return IFaceLayout
     */
    private function getLayoutRelation(): IFaceLayout
    {
        return $this->get('layout');
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
    public function isHiddenInSiteMap(): bool
    {
        return (bool)$this->get('hide_in_site_map');
    }

    /**
     * @param string $value
     */
    public function setLabel(string $value): void
    {
        $this->set('label', $value);
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
        $this->set('title', $value);

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
        $this->set('description', $value);

        return $this;
    }

    /**
     * Returns menu codename to which URL is assigned
     *
     * @return null|string
     */
    public function getMenuName(): ?string
    {
        // realization temporarily is absent.
        // at this moment there is no need to store IFace data in database.
        return null;
    }
}
