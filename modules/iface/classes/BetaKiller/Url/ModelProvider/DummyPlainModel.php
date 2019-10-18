<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Model\DummyModelTrait;
use BetaKiller\Url\DummyModelInterface;

class DummyPlainModel extends AbstractPlainEntityLinkedUrlElement implements DummyModelInterface
{
    use DummyModelTrait;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string|null
     */
    private $menu;

    /**
     * @var string|null
     */
    private $redirect;

    /**
     * @var string|null
     */
    private $forward;

    /**
     * @var string|null
     */
    private $layoutCodename;

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
     * Returns menu codename to which URL is assigned
     *
     * @return null|string
     */
    public function getMenuName(): ?string
    {
        return $this->menu;
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

    public function fromArray(array $data): void
    {
        $this->label = $data[self::OPTION_LABEL] ?? null;

        if (isset($data[self::OPTION_MENU])) {
            $this->menu = mb_strtolower($data[self::OPTION_MENU]);
        }

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
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray(): array
    {
        return array_merge(parent::asArray(), [
            self::OPTION_LABEL    => $this->getLabel(),
            self::OPTION_MENU     => $this->getMenuName(),
            self::OPTION_REDIRECT => $this->getRedirectTarget(),
            self::OPTION_FORWARD  => $this->getForwardTarget(),
            self::OPTION_LAYOUT   => $this->getLayoutCodename(),
        ]);
    }
}
