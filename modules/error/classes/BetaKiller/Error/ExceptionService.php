<?php
declare(strict_types=1);

namespace BetaKiller\Error;

use BetaKiller\Exception;
use BetaKiller\Exception\HttpExceptionInterface;
use BetaKiller\ExceptionInterface;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\LanguageInterface;

class ExceptionService
{
    public const DEFAULT_HTTP_CODE = 500;

    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18n;

    /**
     * ExceptionService constructor.
     *
     * @param \BetaKiller\I18n\I18nFacade $i18n
     */
    public function __construct(I18nFacade $i18n)
    {
        $this->i18n = $i18n;
    }

    /**
     * Returns text which would be shown to user on uncaught exception
     * For most of exception classes it returns default label (we do not want to inform user about our problems)
     *
     * @param \Throwable                          $e
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    public function getExceptionMessage(\Throwable $e, ?LanguageInterface $lang = null): string
    {
        $showOriginalMessage = ($e instanceof ExceptionInterface) && $e->showOriginalMessageToUser();

        $lang = $lang ?? $this->i18n->getPrimaryLanguage();

        return $showOriginalMessage
            ? $this->getOriginalMessage($e, $lang)
            : $this->getMaskedMessage($e, $lang);
    }

    public function getHttpCode(\Throwable $e): int
    {
        $code = $e->getCode();

        return (($e instanceof HttpExceptionInterface) && $code)
            ? $code
            : self::DEFAULT_HTTP_CODE;
    }

    /**
     * @param \Throwable                          $e
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    private function getOriginalMessage(\Throwable $e, LanguageInterface $lang): string
    {
        $message   = $e->getMessage();
        $variables = $e instanceof ExceptionInterface ? $e->getMessageVariables() : [];

        // Return message if exists
        if ($message) {
            // Translate if i18n key is used
            if (I18nFacade::isI18nKey($message)) {
                $message = $this->i18n->translate($lang, $message, $variables);
            }

            return $message;
        }

        // Use default message if defined
        $i18nKey = ($e instanceof ExceptionInterface) ? $e->getDefaultMessageI18nKey() : null;

        // Http exceptions may omit message and will use default label instead
        if (!$i18nKey && $e instanceof HttpExceptionInterface) {
            $i18nKey = $this->getLabelI18nKey($e);
        }

        if (!$i18nKey) {
            throw new Exception('Exception :class must provide message in constructor or define default message', [
                ':class' => \get_class($e),
            ]);
        }

        return $this->i18n->translate($lang, $i18nKey, $variables);
    }

    private function getMaskedMessage(\Throwable $e, LanguageInterface $lang): string
    {
        $key = $this->getLabelI18nKey($e);

        return $this->i18n->translate($lang, $key);
    }

    private function getLabelI18nKey(\Throwable $e): string
    {
        $code = $this->getHttpCode($e);

        return $this->getLabelI18nKeyForHttpCode($code);
    }

    private function getLabelI18nKeyForHttpCode(int $code): string
    {
        return 'error.http.'.$code.'.label';
    }
}
