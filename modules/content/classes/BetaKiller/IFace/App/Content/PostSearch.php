<?php
namespace BetaKiller\IFace\App\Content;

use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ServerRequestInterface;

class PostSearch extends AbstractAppBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $term = ServerRequestHelper::getQueryPart($request, 'term');

        return [
            'term' => \HTML::chars(strip_tags($term)),
        ];
    }
}
