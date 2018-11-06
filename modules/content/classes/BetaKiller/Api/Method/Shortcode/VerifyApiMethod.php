<?php
namespace BetaKiller\Api\Method\Shortcode;

use BetaKiller\Content\Shortcode\ShortcodeFacade;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;

class VerifyApiMethod extends AbstractApiMethod
{
    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeInterface
     */
    private $shortcode;

    /**
     * @var array
     */
    private $attributesData;

    /**
     * ApproveApiMethod constructor.
     *
     * @param string                                        $name
     * @param                                               $data
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade $facade
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(string $name, $data, ShortcodeFacade $facade)
    {
        $this->shortcode      = $facade->createFromCodename(ucfirst($name));
        $this->attributesData = (array)$data;
    }

    /**
     * @param \BetaKiller\Api\Method\Shortcode\ArgumentsInterface $arguments
     * @param \BetaKiller\Api\Method\Shortcode\UserInterface      $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        // Set attributes
        $this->shortcode->setAttributes($this->attributesData);

        // Check attributes
        $this->shortcode->validateAttributes();

        // Return sanitized attributes
        return $this->response($this->shortcode->getAttributes());
    }
}
