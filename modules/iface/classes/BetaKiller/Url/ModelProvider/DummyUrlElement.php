<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Model\DummyModelTrait;
use BetaKiller\Url\DummyModelInterface;
use BetaKiller\Url\UrlElementForMenuPlainModelTrait;

final class DummyUrlElement extends AbstractPlainEntityLinkedUrlElement implements DummyModelInterface
{
    use DummyModelTrait;
    use UrlElementForMenuPlainModelTrait;

    /**
     * @var string|null
     */
    private ?string $label;

    /**
     * @var string|null
     */
    private ?string $redirect = null;

    /**
     * @var string|null
     */
    private ?string $forward = null;

    /**
     * @var string|null
     */
    private ?string $layoutCodename = null;

    /**
     * @return string
     */
    public static function getXmlTagName(): string
    {
        return 'dummy';
    }

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
     * Returns UrlElement codename (if defined)
     *
     * @return string|null
     */
    public function getRedirectTarget(): ?string
    {
        return $this->redirect;
    }

    /**
     * Returns UrlElement codename to proceed instead of current Dummy (if defined)
     *
     * @return string|null
     */
    public function getForwardTarget(): ?string
    {
        return $this->forward;
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

    /**
     * @inheritDoc
     */
    public function fromArray(array $data): void
    {
        $this->label = $data[self::OPTION_LABEL] ?? null;

        $this->menuFromArray($data);

        if (isset($data[self::OPTION_LAYOUT])) {
            $this->layoutCodename = (string)$data[self::OPTION_LAYOUT];
        }

        if (isset($data[self::OPTION_REDIRECT])) {
            $this->redirect = $data[self::OPTION_REDIRECT];
        }

        if (isset($data[self::OPTION_FORWARD])) {
            $this->forward = $data[self::OPTION_FORWARD];
        }

        parent::fromArray($data);
    }

    /**
     * @inheritDoc
     */
    public function asArray(): array
    {
        return array_merge(parent::asArray(), $this->menuToArray(), [
            self::OPTION_LABEL    => $this->getLabel(),
            self::OPTION_REDIRECT => $this->getRedirectTarget(),
            self::OPTION_FORWARD  => $this->getForwardTarget(),
            self::OPTION_LAYOUT   => $this->getLayoutCodename(),
        ]);
    }
}
