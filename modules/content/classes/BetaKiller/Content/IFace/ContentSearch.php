<?php

namespace BetaKiller\Content\IFace;

class ContentSearch extends Base
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        return [
            'term'  =>  \HTML::chars(strip_tags($this->getUrlQuery('term'))),
        ];
    }
}
