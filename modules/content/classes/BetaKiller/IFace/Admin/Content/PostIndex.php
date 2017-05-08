<?php
namespace BetaKiller\IFace\Admin\Content;

class PostIndex extends AdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData()
    {
        $articles = $this->model_factory_content_post()->getAllArticles();

        $data = [];

        foreach ($articles as $article)
        {
            $data[] = [
                'id'          => $article->get_id(),
                'url'         => $article->get_admin_url(),
                'label'       => $article->getLabel(),
            ];
        }

        return [
            'posts' => $data,
        ];
    }
}
