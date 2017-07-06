<?php
namespace BetaKiller\Api\Method\ContentComment;

trait ContentCommentMethodTrait
{
    protected function sanitize_string($value): string
    {
        return \HTML::chars(trim(strip_tags($value)));
    }
}
