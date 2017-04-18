<?php
namespace BetaKiller\IFace\App\Content;

class PostSearch extends AppBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData()
    {
        return [
            'term'  =>  \HTML::chars(strip_tags($this->getUrlQuery('term'))),
        ];
    }
}
