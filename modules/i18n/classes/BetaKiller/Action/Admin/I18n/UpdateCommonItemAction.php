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
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class UpdateCommonItemAction extends AbstractAction
{
    private const ARG_LOGIN = 'user-login';

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
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition()
            ->identity()
            ->compositeArray('values')
            ->string('name')->lowercase()
            ->string('value');
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Spotman\Defence\ArgumentsInterface      $arguments
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function handle(ServerRequestInterface $request, ArgumentsInterface $arguments): ResponseInterface
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        /** @var I18nKeyModelInterface $key */
        $key = ServerRequestHelper::getEntity($request, I18nKeyModelInterface::class);

        $post = ServerRequestHelper::getPost($request);

        foreach ($this->langRepo->getAll() as $lang) {
            $langName = $lang->getName();

            if (!isset($post[$langName])) {
                throw new BadRequestHttpException('Missing data for lang :name', [
                    ':name' => $langName,
                ]);
            }

            $value = $post[$langName];

            // Skip empty lines
            if (!$value) {
                continue;
            }

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
