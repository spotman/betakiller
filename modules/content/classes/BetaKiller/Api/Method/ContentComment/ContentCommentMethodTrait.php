<?php
namespace BetaKiller\Api\Method\ContentComment;

trait ContentCommentMethodTrait
{
    protected function sanitizeString($value): string
    {
        return \HTML::chars(trim(strip_tags($value)));
    }
}
