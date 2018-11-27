<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\I18n;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\I18n\PluralBagFormatterInterface;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\I18nKeyInterface;
use BetaKiller\Model\I18nKeyModelInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractItemIFace extends AbstractAdminIFace
{
    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $langRepo;

    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $facade;

    /**
     * @var \BetaKiller\I18n\PluralBagFormatterInterface
     */
    private $plural;

    /**
     * CommonItemIFace constructor.
     *
     * @param \BetaKiller\I18n\I18nFacade                        $facade
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     * @param \BetaKiller\I18n\PluralBagFormatterInterface       $plural
     */
    public function __construct(
        I18nFacade $facade,
        LanguageRepositoryInterface $langRepo,
        PluralBagFormatterInterface $plural
    ) {
        $this->langRepo = $langRepo;
        $this->facade   = $facade;
        $this->plural   = $plural;
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        /** @var I18nKeyModelInterface $key */
        $key = ServerRequestHelper::getEntity($request, I18nKeyModelInterface::class);

        $languages = $this->langRepo->getAll();
        $isPlural  = $key->isPlural();

        return [
            'key'       => $key->getI18nKeyName(),
            'is_plural' => $isPlural,
            'values'    => $this->getValues($key, $languages),
            'action'    => $urlHelper->getUpdateEntityUrl($key, ZoneInterface::ADMIN),
        ];
    }

    /**
     * @param \BetaKiller\Model\I18nKeyInterface    $key
     * @param \BetaKiller\Model\LanguageInterface[] $languages
     *
     * @return array
     * @throws \Punic\Exception
     */
    private function getValues(I18nKeyInterface $key, array $languages): array
    {
        $data = [];

        foreach ($languages as $lang) {
            $value = $key->getI18nValue($lang) ?: '';

            $data[] = [
                'lang'  => [
                    'name'  => $lang->getName(),
                    'label' => $lang->getLabel(),
                ],
                'value' => $key->isPlural() ? $this->getPluralItemData($lang, $value) : $value,
            ];
        }

        return $data;
    }

    private function getPluralItemData(LanguageInterface $lang, string $value): array
    {
        $bag = $this->plural->parse($value);

        // Get all forms for current lang
        $forms = $this->facade->getPluralFormsForLocale($lang->getLocale());

        $output = [];

        foreach ($forms as $form) {
            $output[$form] = $bag->getValue($form);
        }

        return $output;
    }
}
