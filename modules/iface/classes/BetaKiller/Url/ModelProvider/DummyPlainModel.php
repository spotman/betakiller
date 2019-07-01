<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Model\DummyModelTrait;
use BetaKiller\Url\DummyModelInterface;

class DummyPlainModel extends AbstractPlainUrlElementModel implements DummyModelInterface
{
    use DummyModelTrait;

    public const OPTION_LABEL    = 'label';
    public const OPTION_MENU     = 'menu';
    public const OPTION_REDIRECT = 'redirect';

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

    public function fromArray(array $data): void
    {
        $this->label = $data[self::OPTION_LABEL] ?? null;

        if (isset($data[self::OPTION_MENU])) {
            $this->menu = mb_strtolower($data[self::OPTION_MENU]);
        }

        if (isset($data[self::OPTION_REDIRECT])) {
            $this->redirect = $data[self::OPTION_REDIRECT];
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
        ]);
    }
}
