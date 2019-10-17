<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use DebugBar\Bridge\Twig\TimeableTwigExtensionProfiler;
use DebugBar\Bridge\TwigProfileCollector;
use DebugBar\DebugBar;
use Twig\Environment;
use Twig\Profiler\Dumper\TextDumper;

final class DebugBarTwigDataCollector extends TwigProfileCollector
{
    private $prof;

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

        $this->prof = $profile;

        parent::__construct($profile, $twigEnv);

        // Force AJAX
        $this->setXdebugLinkTemplate('idea', true);
    }

    public function getHtmlCallGraph()
    {
        return '<pre>'.(new TextDumper())->dump($this->prof).'<br /></pre>';
    }
}
