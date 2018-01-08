<?php
namespace BetaKiller\Api\Method\ContentPost;

trait ContentPostMethodTrait
{
    protected function sanitizeString(string $value): string
    {
        return \HTML::chars(trim(strip_tags($value)));
    }
}
