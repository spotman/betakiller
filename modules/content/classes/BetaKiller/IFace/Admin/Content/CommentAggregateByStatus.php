<?php
namespace BetaKiller\IFace\Admin\Content;

class CommentAggregateByStatus extends AdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData()
    {
        // TODO: Implement getData() method.

        // TODO get aggregated statuses list
        $statuses = $this->model_factory_content_comment();


        return [];
    }
}
