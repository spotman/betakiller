<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Url\IFaceModelInterface;

class IFacePlainModel extends AbstractPlainEntityLinkedUrlElement implements IFaceModelInterface
{
    public const OPTION_LABEL  = 'label';
    public const OPTION_TITLE  = 'title';
    public const OPTION_LAYOUT = 'layout';
    public const OPTION_MENU   = 'menu';

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string|null
     */
    private $layoutCodename;

    /**
     * @var bool
     */
    private $hideInSiteMap = false;

    /**
     * @var string|null
     */
    private $menu;

    /**
     * Returns label for using in breadcrumbs and etc
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label ?: '';
    }

    /**
     * @param string $value
     *
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function setLabel(string $value): void
    {
        throw new NotImplementedHttpException('Config-based URL element model can not change label');
    }

    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function setTitle(string $value): SeoMetaInterface
    {
        throw new NotImplementedHttpException('Config-based model can not change title');
    }

    /**
     * Sets description for using in <meta> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function setDescription(string $value): SeoMetaInterface
    {
        throw new NotImplementedHttpException('Config-based model can not change description');
    }

    /**
     * Returns title for using in page <title> tag
     *
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Returns description for using in <meta> tag
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        // Admin IFace does not need description
        return null;
    }

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray(): array
    {
        return array_merge(parent::asArray(), [
            self::OPTION_LABEL  => $this->getLabel(),
            self::OPTION_TITLE  => $this->getTitle(),
            self::OPTION_LAYOUT => $this->getLayoutCodename(),
            self::OPTION_MENU   => $this->getMenuName(),
        ]);
    }

    /**
     * Returns layout codename
     *
     * @return string
     */
    public function getLayoutCodename(): ?string
    {
        return $this->layoutCodename;
    }

    public function fromArray(array $data): void
    {
        $this->label = $data[self::OPTION_LABEL] ?? null;
        $this->title = $data[self::OPTION_TITLE] ?? null;

        if (isset($data[self::OPTION_HIDE_IN_SITEMAP])) {
            $this->hideInSiteMap = true;
        }

        if (isset($data[self::OPTION_LAYOUT])) {
            $this->layoutCodename = (string)$data[self::OPTION_LAYOUT];
        }

        if (isset($data[self::OPTION_MENU])) {
            $this->menu = mb_strtolower($data[self::OPTION_MENU]);
        }

        parent::fromArray($data);
    }

    /**
     * @return bool
     */
    public function isHiddenInSiteMap(): bool
    {
        return $this->hideInSiteMap;
    }

    /**
     * Returns menu codename to which URL is assigned
     *
     * @return null|string
     */
    public function getMenuName(): ?string
    {
        return $this->menu;
    }
}
