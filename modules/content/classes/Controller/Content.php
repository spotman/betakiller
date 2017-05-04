<?php


use BetaKiller\Assets\Model\AssetsModelInterface;

class Controller_Content extends Controller
{
    use \BetaKiller\Helper\ContentTrait;

    public function action_files_bc_redirect()
    {
        $file = $this->param('file');

        if (!$file)
            throw new HTTP_Exception_404();

        $path = '/'.ltrim($this->getRequest()->uri(), "/");

        $model = $this->find_content_model_by_wp_path($path);

        if (!$model)
            throw new HTTP_Exception_404();

        $url = $model->getOriginalUrl();

        $this->redirect($url, 301);
    }

    /**
     * @param string $path
     *
     * @return AssetsModelInterface|null
     */
    protected function find_content_model_by_wp_path($path)
    {
        /** @var \BetaKiller\Content\HasWordpressPathInterface[] $models */
        $models = [
            $this->model_factory_content_image_element(),
            $this->model_factory_content_post_thumbnail(),
            $this->model_factory_content_attachment_element(),
        ];

        foreach ($models as $orm)
        {
            /** @var AssetsModelInterface $model */
            $model = $orm->find_by_wp_path($path);

            if ($model)
                return $model;
        }

        return NULL;
    }
}
