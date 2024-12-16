<?php

declare(strict_types=1);

namespace BetaKiller\Action\Admin\I18n;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Action\PostRequestActionInterface;
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
use BetaKiller\Url\Zone;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

readonly class AbstractUpdateItemAction extends AbstractAction implements PostRequestActionInterface
{
    private const ARG_I18N_VALUES      = 'values';
    private const ARG_LANG_NAME        = 'name';
    private const ARG_TRANSLATED_VALUE = 'value';

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
        private EntityManager $entityManager,
        private I18nFacade $i18nFacade,
        private LanguageRepositoryInterface $langRepo,
        private PluralBagFormatterInterface $formatter,
        private PluralBagFactoryInterface $pluralFactory
    ) {
    }

    public function definePostArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->compositeArrayStart(self::ARG_I18N_VALUES)
            ->string(self::ARG_LANG_NAME)->lowercase()
            ->string(self::ARG_TRANSLATED_VALUE);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\Url\UrlElementException
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

        $url = $urlHelper->getReadEntityUrl($key, Zone::admin());

        return ResponseHelper::redirect($url);
    }
}
