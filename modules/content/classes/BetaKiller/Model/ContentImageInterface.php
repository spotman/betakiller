<?php
namespace BetaKiller\Model;

use BetaKiller\Assets\Model\AssetsModelImageInterface;

interface ContentImageInterface extends WordpressAttachmentInterface, ContentElementInterface, AssetsModelImageInterface {}
