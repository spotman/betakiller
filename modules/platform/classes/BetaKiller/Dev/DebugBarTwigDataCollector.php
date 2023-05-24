<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use DebugBar\Bridge\NamespacedTwigProfileCollector;
use Twig\Environment;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile;

final class DebugBarTwigDataCollector extends NamespacedTwigProfileCollector
{
    /**
     * DebugBarTwigDataCollector constructor.
     *
     * @param \Twig\Environment $twigEnv
     */
    public function __construct(Environment $twigEnv)
    {
        $profile = new Profile();

        $twigEnv->addExtension(new ProfilerExtension($profile));

        parent::__construct($profile, $twigEnv);

        // Force AJAX
        $this->setXdebugLinkTemplate('idea', true);
    }
}
