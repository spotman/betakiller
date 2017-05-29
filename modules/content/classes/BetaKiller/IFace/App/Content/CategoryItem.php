<?php
namespace BetaKiller\IFace\App\Content;

class CategoryItem extends AppBase
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentUrlParametersHelper
     */
    private $urlParametersHelper;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData()
    {
        $category = $this->urlParametersHelper->getContentCategory();

        return [
            'category'  =>  [
               'label'  =>  $category->get_label(),
            ],
        ];
    }
}
