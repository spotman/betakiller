<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Url\IFaceModelInterface;

class IFaceXmlConfigModel extends AbstractXmlConfigModel implements IFaceModelInterface
{
    public const OPTION_TITLE  = 'title';
    public const OPTION_LAYOUT = 'layout';

    /**
     * @var string
     */
    private $title;

    /**
     * Admin IFaces have "admin" layout by default
     *
     * @var string|null
     */
    private $layoutCodename;

    /**
     * @var bool
     */
    private $hideInSiteMap = false;

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
        throw new NotImplementedHttpException('Admin model can not change title');
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
        throw new NotImplementedHttpException('Admin model can not change description');
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
            self::OPTION_TITLE  => $this->getTitle(),
            self::OPTION_LAYOUT => $this->getLayoutCodename(),
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
        $this->title = $data[self::OPTION_TITLE] ?? null;

        if (isset($data[self::OPTION_HIDE_IN_SITEMAP])) {
            $this->hideInSiteMap = true;
        }

        if (isset($data[self::OPTION_LAYOUT])) {
            $this->layoutCodename = (string)$data[self::OPTION_LAYOUT];
        }

        parent::fromArray($data);
    }

    /**
     * @return bool
     */
    public function hideInSiteMap(): bool
    {
        return $this->hideInSiteMap;
    }
}
