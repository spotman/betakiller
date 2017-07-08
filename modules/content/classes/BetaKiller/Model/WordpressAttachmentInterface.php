<?php
namespace BetaKiller\Model;

use BetaKiller\Assets\Model\AssetsModelInterface;

/**
 * Interface WordpressAttachmentInterface
 * Base interface for all attachments imported from WP
 *
 * @package BetaKiller\Model
 */
interface WordpressAttachmentInterface extends AssetsModelInterface, EntityHasWordpressIdInterface, EntityHasWordpressPathInterface {}
