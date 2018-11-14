<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\I18n;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\I18n\PluralBagFormatterInterface;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\I18nKeyModelInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\TranslationRepository;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ServerRequestInterface;

class CommonItemIFace extends AbstractAdminIFace
{
    /**
     * @var \BetaKiller\Repository\TranslationRepository
     */
    private $i18nRepo;

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
     * @param \BetaKiller\Repository\TranslationRepository       $i18nRepo
     * @param \BetaKiller\I18n\I18nFacade                        $facade
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     * @param \BetaKiller\I18n\PluralBagFormatterInterface       $plural
     */
    public function __construct(
        TranslationRepository $i18nRepo,
        I18nFacade $facade,
        LanguageRepositoryInterface $langRepo,
        PluralBagFormatterInterface $plural
    ) {
        $this->i18nRepo = $i18nRepo;
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
            'key'       => $key->getI18nKey(),
            'is_plural' => $isPlural,
            'values'    => $this->getValues($key, $languages),
            'action'    => $urlHelper->getUpdateEntityUrl($key, ZoneInterface::ADMIN),
        ];
    }

    /**
     * @param \BetaKiller\Model\I18nKeyModelInterface $key
     * @param \BetaKiller\Model\LanguageInterface[]   $languages
     *
     * @return array
     */
    private function getValues(I18nKeyModelInterface $key, array $languages): array
    {
        $data = [];

        foreach ($languages as $lang) {
            $item = $this->i18nRepo->findItem($key, $lang);

            $value = $item ? $item->getValue() : '';

            $data[] = [
                'lang' => [
                    'name' => $lang->getName(),
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
