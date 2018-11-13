<?php
declare(strict_types=1);

namespace BetaKiller\Action\Admin\I18n;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\I18nKeyModelInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\Translation;
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
        /** @var I18nKeyModelInterface $key */
        $key = ServerRequestHelper::getEntity($request, I18nKeyModelInterface::class);

        $post = ServerRequestHelper::getPost($request);

        foreach ($this->langRepo->getAll() as $lang) {
            $langName = $lang->getName();

            $value = $post[$langName];

            $this->updateValue($key, $lang, $value);
        }

        // TODO: Implement handle() method.
    }

    /**
     * @param \BetaKiller\Model\I18nKeyModelInterface $key
     * @param \BetaKiller\Model\LanguageInterface[]   $languages
     * @param array                                   $values
     */
    private function processRegular(I18nKeyModelInterface $key, array $languages, array $values): void
    {
        foreach ($languages as $lang) {
            $langName = $lang->getName();

            $value = $values[$langName];

            $this->updateValue($key, $lang, $value);
        }
    }

    /**
     * @param \BetaKiller\Model\I18nKeyModelInterface $key
     * @param \BetaKiller\Model\LanguageInterface[]   $languages
     * @param array                                   $values
     *
     * @throws \Punic\Exception
     */
    private function processPlural(I18nKeyModelInterface $key, array $languages, array $values): void
    {
        foreach ($languages as $lang) {
            $langName = $lang->getName();

            $value = $values[$langName];

            $forms = $this->i18nFacade->getPluralFormsForLocale($lang->getLocale());

            // TODO
        }
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
