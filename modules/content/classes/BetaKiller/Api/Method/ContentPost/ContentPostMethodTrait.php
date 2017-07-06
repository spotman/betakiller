<?php
namespace BetaKiller\Api\Method\ContentPost;

trait ContentPostMethodTrait
{
    protected function sanitizeString($value)
    {
        return \HTML::chars(trim(strip_tags($value)));
    }
}
