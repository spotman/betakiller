<?php
namespace BetaKiller\Helper;

use BetaKiller\Exception;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\HasI18nKeyNameInterface;
use BetaKiller\Model\I18nKeyInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlPrototypeService;

class StringPatternHelper
{
    // [N[...]] tags
    public const PLACEHOLDER_PRIORITY_PCRE = '/\[([\d]{1,2})\[([^\]]+)\]\]/';

    // {~...~} tags
    public const PLACEHOLDER_I18N_PCRE = '/\{\~([A-Za-z]+)\~\}/';

    /**
     * @var \BetaKiller\Url\UrlPrototypeService
     */
    private $prototypeHelper;

    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18n;

    /**
     * StringPatternHelper constructor.
     *
     * @param \BetaKiller\Url\UrlPrototypeService $prototypeHelper
     * @param \BetaKiller\I18n\I18nFacade         $i18n
     */
    public function __construct(UrlPrototypeService $prototypeHelper, I18nFacade $i18n)
    {
        $this->prototypeHelper = $prototypeHelper;
        $this->i18n            = $i18n;
    }

    /**
     * Pattern consists of tags like [N[Text]] where N is tag priority
     *
     * @param string                     $source
     * @param UrlContainerInterface|null $params
     *
     * @param LanguageInterface          $lang
     * @param int|null                   $limit
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function process(
        string $source,
        UrlContainerInterface $params,
        LanguageInterface $lang,
        ?int $limit = null
    ): string {
        if (I18nFacade::isI18nKey($source)) {
            return $this->i18n->translateKeyName($lang, $source);
        }

        // Replace i18n keys
        $source = $this->replaceI18nKeys($source, $params, $lang);

        // Replace url parameters
        $source = $this->prototypeHelper->replaceUrlParametersParts($source, $params);

        /** @var array[] $matches */
        preg_match_all(self::PLACEHOLDER_PRIORITY_PCRE, $source, $matches, PREG_SET_ORDER);

        $tags = [];

        foreach ($matches as [$key, $priority, $value]) {
            $tags[$priority] = [
                'key'   => $key,
                'value' => $value,
            ];
        }

        $output = $source;

        if ($tags) {
            // Sort tags via keys in straight order
            ksort($tags);

            // Iteration counter
            $i        = 0;
            $maxLoops = \count($tags);

            while ($i < $maxLoops && $output !== '') {
                $output = $source;

                // Replace tags
                foreach ($tags as $tag) {
                    $output = str_replace($tag['key'], $tag['value'], $output);
                }

                if ($limit && mb_strlen($output) > $limit) {
                    $drop   = array_pop($tags);
                    $source = trim(str_replace($drop['key'], '', $source));
                    $i++;
                } else {
                    break;
                }
            }
        }

        if ($limit && mb_strlen($output) > $limit) {
            // Dirty limit
            \Text::limit_chars($output, $limit, null, true);
        }

        return $output;
    }

    private function replaceI18nKeys(string $source, UrlContainerInterface $parameters, LanguageInterface $lang): string
    {
        return preg_replace_callback(
            self::PLACEHOLDER_I18N_PCRE,
            function ($matches) use ($parameters, $lang) {
                $key = $matches[1];

                $param = $parameters->getParameter($key);

                if ($param instanceof I18nKeyInterface) {
                    return $this->i18n->translateKey($lang, $param);
                }

                if ($param instanceof HasI18nKeyNameInterface) {
                    return $this->i18n->translateHasKeyName($lang, $param);
                }

                throw new Exception('Can not translate i18n param ":name"', [
                    ':name' => $key,
                ]);
            },
            $source
        );
    }
}
