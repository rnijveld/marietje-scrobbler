<?php

namespace Marietje\Scrobbler;

use Silex\Application;
use Silex\Provider;

class App extends Application
{
    use Application\TwigTrait;
    use Application\FormTrait;
    use Application\MonologTrait;
    use Application\TranslationTrait;

    public function __construct()
    {
        parent::__construct();
        $this->register(new Provider\TwigServiceProvider(), array(
            'twig.path' => ROOT_LOCATION . '/views'
        ));
        $this->register(new Provider\UrlGeneratorServiceProvider());
        $this->register(new Provider\TranslationServiceProvider());
        $this->register(new Provider\FormServiceProvider());
        $this->register(new Provider\SessionServiceProvider());
        $this->register(new Provider\ValidatorServiceProvider());

        $this->register(new Provider\MonologServiceProvider(), array(
            'monolog.logfile' => ROOT_LOCATION . '/marietje_scrobbler.log',
        ));
    }
}
