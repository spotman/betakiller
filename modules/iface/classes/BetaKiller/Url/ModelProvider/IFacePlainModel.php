<?php

namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementForMenuPlainModelTrait;
use Carbon\CarbonInterval;
use DateInterval;

final class IFacePlainModel extends AbstractPlainEntityLinkedUrlElement implements IFaceModelInterface
{
    use UrlElementForMenuPlainModelTrait;

    public const OPTION_TITLE = 'title';

    private ?string $label = null;

    private ?string $title = null;

    private ?string $layoutCodename = null;

    private bool $cache = false;

    private ?DateInterval $expiresIn = null;

    /**
     * @inheritDoc
     */
    public static function getXmlTagName(): string
    {
        return 'iface';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return $this->label ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setLabel(string $value): void
    {
        throw new NotImplementedHttpException('Config-based URL element model can not change label');
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $value): SeoMetaInterface
    {
        throw new NotImplementedHttpException('Config-based model can not change title');
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $value): SeoMetaInterface
    {
        throw new NotImplementedHttpException('Config-based model can not change description');
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): ?string
    {
        // Admin IFace does not need description
        return null;
    }

    /**
     * @inheritDoc
     */
    public function isCacheEnabled(): bool
    {
        return $this->cache;
    }

    /**
     * @inheritDoc
     */
    public function getExpiresInterval(): ?DateInterval
    {
        return $this->expiresIn;
    }

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray(): array
    {
        return array_merge(parent::asArray(), $this->menuToArray(), [
            self::OPTION_LABEL  => $this->getLabel(),
            self::OPTION_TITLE  => $this->getTitle(),
            self::OPTION_LAYOUT => $this->getLayoutCodename(),

            self::OPTION_CACHE => $this->cache
                ? CarbonInterval::instance($this->expiresIn)->spec()
                : null,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getLayoutCodename(): ?string
    {
        return $this->layoutCodename;
    }

    public function fromArray(array $data): void
    {
        $this->label = $data[self::OPTION_LABEL] ?? null;
        $this->title = $data[self::OPTION_TITLE] ?? null;

        $layout = $data[self::OPTION_LAYOUT] ?? null;

        if ($layout) {
            $this->layoutCodename = (string)$layout;
        }

        $cache = $data[self::OPTION_CACHE] ?? null;

        if ($cache && $cache !== 'false') {
            $this->cache = true;

            $this->expiresIn = $cache !== 'true'
                ? new DateInterval($cache)
                : null;
        }

        $this->menuFromArray($data);

        parent::fromArray($data);
    }
}
