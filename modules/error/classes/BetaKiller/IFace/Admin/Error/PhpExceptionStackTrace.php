<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Exception;

class PhpExceptionStackTrace extends ErrorAdminBase
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\PhpExceptionUrlContainerHelper
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
        $model = $this->urlParametersHelper->getPhpException();

        if (!$model) {
            throw new Exception('Incorrect php exception hash');
        }

        return [
            'trace' => $model->getTrace(),
        ];
    }
}
