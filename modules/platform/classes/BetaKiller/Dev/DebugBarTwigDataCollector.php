<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use DebugBar\Bridge\Twig\TimeableTwigExtensionProfiler;
use DebugBar\Bridge\TwigProfileCollector;
use DebugBar\DebugBar;
use Twig\Environment;

final class DebugBarTwigDataCollector extends TwigProfileCollector
{
    /**
     * DebugBarTwigDataCollector constructor.
     *
     * @param \DebugBar\DebugBar $debugBar
     * @param \Twig\Environment  $twigEnv
     */
    public function __construct(DebugBar $debugBar, Environment $twigEnv)
    {
        $profile = new \Twig_Profiler_Profile();
        $twigEnv->addExtension(new TimeableTwigExtensionProfiler($profile, $debugBar['time']));

        parent::__construct($profile);
    }
}
