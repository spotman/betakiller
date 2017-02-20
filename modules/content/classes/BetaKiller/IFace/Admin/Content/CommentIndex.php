<?php
namespace BetaKiller\IFace\Admin\Content;

class CommentIndex extends AdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        // TODO
        $articles = $this->model_factory_content_comment()->get_all_articles();

        $data = [];

        foreach ($articles as $article)
        {
            $data[] = [
                'id'          => $article->get_id(),
                'url'         => $article->get_admin_url(),
                'label'       => $article->get_label(),
            ];
        }

        return [
            'posts' => $data,
        ];
    }
}
