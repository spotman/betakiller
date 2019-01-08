<?php
declare(strict_types=1);

namespace BetaKiller\Action\Admin\I18n;

use BetaKiller\Action\AbstractAction;
use BetaKiller\EntityManager;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\I18n\PluralBagFactoryInterface;
use BetaKiller\I18n\PluralBagFormatterInterface;
use BetaKiller\Model\I18nKeyModelInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class AbstractUpdateItemAction extends AbstractAction
{
    private const ARG_I18N_VALUES      = 'values';
    private const ARG_LANG_NAME        = 'name';
    private const ARG_TRANSLATED_VALUE = 'value';

    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18nFacade;

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $langRepo;

    /**
     * @var \BetaKiller\I18n\PluralBagFormatterInterface
     */
    private $formatter;

    /**
     * @var \BetaKiller\I18n\PluralBagFactoryInterface
     */
    private $pluralFactory;

    /**
     * @var \BetaKiller\EntityManager
     */
    private $entityManager;

    /**
     * AbstractUpdateItemAction constructor.
     *
     * @param \BetaKiller\EntityManager                          $entityManager
     * @param \BetaKiller\I18n\I18nFacade                        $i18nFacade
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     * @param \BetaKiller\I18n\PluralBagFormatterInterface       $formatter
     * @param \BetaKiller\I18n\PluralBagFactoryInterface         $pluralFactory
     */
    public function __construct(
        EntityManager $entityManager,
        I18nFacade $i18nFacade,
        LanguageRepositoryInterface $langRepo,
        PluralBagFormatterInterface $formatter,
        PluralBagFactoryInterface $pluralFactory
    ) {
        $this->entityManager = $entityManager;
        $this->i18nFacade    = $i18nFacade;
        $this->langRepo      = $langRepo;
        $this->formatter     = $formatter;
        $this->pluralFactory = $pluralFactory;
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition();
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function postArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition()
            ->compositeArray(self::ARG_I18N_VALUES)
            ->string(self::ARG_LANG_NAME)->lowercase()
            ->string(self::ARG_TRANSLATED_VALUE);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        /** @var I18nKeyModelInterface $key */
        $key = ServerRequestHelper::getEntity($request, I18nKeyModelInterface::class);

        $post = ActionRequestHelper::postArguments($request);

        foreach ($post->getArray(self::ARG_I18N_VALUES) as $i18nData) {
            $langName = $i18nData[self::ARG_LANG_NAME];
            $value    = $i18nData[self::ARG_TRANSLATED_VALUE];

            $lang = $this->langRepo->findByIsoCode($langName);

            if (!$lang) {
                throw new BadRequestHttpException('Missing data for lang :name', [
                    ':name' => $langName,
                ]);
            }

            if ($key->isPlural()) {
                if (!\is_array($value)) {
                    throw new BadRequestHttpException;
                }

                $bag = $this->pluralFactory->create($value);
                $this->i18nFacade->validatePluralBag($bag, $lang);
                $value = $this->formatter->compile($bag);
            }

            $key->setI18nValue($lang, $value);
        }

        if (!$key->getAnyI18nValue()) {
            throw new BadRequestHttpException('Key must have translation for one lang at least');
        }

        $this->entityManager->persist($key);

        $url = $urlHelper->getReadEntityUrl($key, ZoneInterface::ADMIN);

        return ResponseHelper::redirect($url);
    }
}
