<?php
declare(strict_types=1);

namespace BetaKiller\Action\Admin\I18n;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\I18n\PluralBagFactoryInterface;
use BetaKiller\I18n\PluralBagFormatterInterface;
use BetaKiller\Model\I18nKeyModelInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\Translation;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\TranslationRepository;
use BetaKiller\Url\ZoneInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UpdateCommonItemAction extends AbstractAction
{
    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18nFacade;

    /**
     * @var \BetaKiller\Repository\TranslationRepository
     */
    private $i18nRepo;

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
     * UpdateCommonItemAction constructor.
     *
     * @param \BetaKiller\I18n\I18nFacade                        $i18nFacade
     * @param \BetaKiller\Repository\TranslationRepository       $i18nRepo
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     * @param \BetaKiller\I18n\PluralBagFormatterInterface       $formatter
     * @param \BetaKiller\I18n\PluralBagFactoryInterface         $pluralFactory
     */
    public function __construct(
        I18nFacade $i18nFacade,
        TranslationRepository $i18nRepo,
        LanguageRepositoryInterface $langRepo,
        PluralBagFormatterInterface $formatter,
        PluralBagFactoryInterface $pluralFactory
    ) {
        $this->i18nFacade    = $i18nFacade;
        $this->i18nRepo      = $i18nRepo;
        $this->langRepo      = $langRepo;
        $this->formatter     = $formatter;
        $this->pluralFactory = $pluralFactory;
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        /** @var I18nKeyModelInterface $key */
        $key = ServerRequestHelper::getEntity($request, I18nKeyModelInterface::class);

        $post = ServerRequestHelper::getPost($request);

        foreach ($this->langRepo->getAll() as $lang) {
            $langName = $lang->getName();

            $value = $post[$langName];

            if ($key->isPlural()) {
                if (!\is_array($value)) {
                    throw new BadRequestHttpException;
                }

                $bag = $this->pluralFactory->create($value);
                $this->i18nFacade->validatePluralBag($bag, $lang);
                $value = $this->formatter->compile($bag);
            }

            $this->updateValue($key, $lang, $value);
        }

        $url = $urlHelper->getReadEntityUrl($key, ZoneInterface::ADMIN);

        return ResponseHelper::redirect($url);
    }

    private function updateValue(I18nKeyModelInterface $key, LanguageInterface $lang, string $value): void
    {
        $model = $this->i18nRepo->findItem($key, $lang);

        if (!$model) {
            $model = (new Translation())
                ->setKey($key)
                ->setLanguage($lang);
        }

        $model->setValue($value);

        $this->i18nRepo->save($model);
    }
}
