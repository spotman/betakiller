<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Model\DummyModelTrait;
use BetaKiller\Url\DummyModelInterface;

class DummyPlainModel extends AbstractPlainUrlElementModel implements DummyModelInterface
{
    use DummyModelTrait;

    public const OPTION_LABEL = 'label';
    public const OPTION_MENU  = 'menu';

    /**
     * @var string
     */
    private $label;

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
     * Returns menu codename to which URL is assigned
     *
     * @return null|string
     */
    public function getMenuName(): ?string
    {
        return $this->menu;
    }

    public function fromArray(array $data): void
    {
        $this->label = $data[self::OPTION_LABEL] ?? null;

        if (isset($data[self::OPTION_MENU])) {
            $this->menu = mb_strtolower($data[self::OPTION_MENU]);
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
            self::OPTION_LABEL => $this->getLabel(),
            self::OPTION_MENU  => $this->getMenuName(),
        ]);
    }
}
