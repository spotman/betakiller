<?php
namespace BetaKiller\Helper;


use BetaKiller\IFace\Url\UrlContainerInterface;

class StringPatternHelper
{
    /**
     * @Inject
     * @var \BetaKiller\IFace\Url\UrlPrototypeHelper
     */
    private $prototypeHelper;

    /**
     * Pattern consists of tags like [N[Text]] where N is tag priority
     *
     * @param string                     $source
     * @param int|NULL                   $limit
     * @param UrlContainerInterface|null $params
     *
     * @return string
     */
    public function processPattern(
        ?string $source,
        ?int $limit = null,
        UrlContainerInterface $params = null
    ): ?string
    {
        if (!$source) {
            return null;
        }

        // Replace url parameters
        $source = $this->prototypeHelper->replaceUrlParametersParts($source, $params);

        // Parse [N[...]] tags
        $pcre_pattern = '/\[([\d]{1,2})\[([^\]]+)\]\]/';

        /** @var array[] $matches */
        preg_match_all($pcre_pattern, $source, $matches, PREG_SET_ORDER);

        $tags = [];

        foreach ($matches as list($key, $priority, $value)) {
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
            $i         = 0;
            $max_loops = \count($tags);

            while ($i < $max_loops && mb_strlen($output) > 0) {
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
}
