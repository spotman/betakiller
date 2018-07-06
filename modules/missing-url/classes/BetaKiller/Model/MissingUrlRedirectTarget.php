<?php
declare(strict_types=1);

namespace BetaKiller\Model;


use BetaKiller\Url\IFaceModelInterface;

class MissingUrlRedirectTarget extends \ORM implements MissingUrlRedirectTargetModelInterface
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'redirect_targets';

        $this->belongs_to([
            'iface' => [
                'model' => 'IFace',
                'foreign_key' => 'iface_id',
            ],
        ]);

        parent::_initialize();
    }

    public function getUrl(): string
    {
        return $this->get('url');
    }

    public function setUrl(string $value): MissingUrlRedirectTargetModelInterface
    {
        return $this->set('url', $value);
    }

    public function getParentIFaceModel(): ?IFaceModelInterface
    {
        return $this->get('iface');
    }

    public function setParentIFaceModel(IFaceModelInterface $parentModel): MissingUrlRedirectTargetModelInterface
    {
        return $this->set('iface', $parentModel);
    }
}
