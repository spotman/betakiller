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
    public function get_data()
    {
        // TODO: Implement get_data() method.

        // TODO get aggregated statuses list
        $statuses = $this->model_factory_content_comment();


        return [];
    }
}
