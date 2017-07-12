<?php
namespace BetaKiller\IFace\App\Content;

class PostSearch extends AbstractAppBase
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\UrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        $term = $this->urlParametersHelper->getQueryPart('term');

        return [
            'term'  =>  \HTML::chars(strip_tags($term)),
        ];
    }
}
