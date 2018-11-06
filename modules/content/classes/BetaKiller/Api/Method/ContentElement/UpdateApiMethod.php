<?php
namespace BetaKiller\Api\Method\ContentElement;

use BetaKiller\Content\Shortcode\ContentElementShortcodeInterface;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;

class UpdateApiMethod extends AbstractApiMethod
{
    /**
     * @var \BetaKiller\Content\Shortcode\ContentElementShortcodeInterface
     */
    private $shortcode;

    /**
     * @var array
     */
    private $data;

    /**
     * ReadApiMethod constructor.
     *
     * @param string                                        $name
     * @param int                                           $modelID
     * @param array                                         $data
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade $facade
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    public function __construct(string $name, int $modelID, $data, ShortcodeFacade $facade)
    {
        $this->shortcode = $facade->createFromCodename($name);

        if (!$this->shortcode instanceof ContentElementShortcodeInterface) {
            throw new ApiMethodException('Content element [:name] must implement :must', [
                ':name' => $name,
                ':must' => ContentElementShortcodeInterface::class,
            ]);
        }

        $this->shortcode->setID($modelID);
        $this->data = (array)$data;
    }

    /**
     * @param \BetaKiller\Api\Method\ContentElement\ArgumentsInterface $arguments
     * @param \BetaKiller\Api\Method\ContentElement\UserInterface      $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $this->shortcode->updateEditorItemData($this->data);

        // Return true
        return $this->response(true);
    }
}
