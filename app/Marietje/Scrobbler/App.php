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
        $this->register(new Provider\MonologServiceProvider(), array(
            'monolog.logfile' => ROOT_LOCATION . '/marietje_scrobbler.log',
        ));

        $this->register(new Provider\TwigServiceProvider(), array(
            'twig.path' => ROOT_LOCATION . '/views'
        ));
        $this->register(new Provider\UrlGeneratorServiceProvider());

        $this->register(new Provider\TranslationServiceProvider(), array(
            'locale_fallback' => 'en'
        ));
        $this['translator'] = $this->share($this->extend('translator', function ($translator, $app) {
            $translator->addLoader('yaml', new YamlFileLoader());

            $translator->addResource('yaml', ROOT_LOCATION . 'locales/en.yml', 'en');
            $translator->addResource('yaml', ROOT_LOCATION . 'locales/nl.yml', 'nl');
        }));

        $this->register(new Provider\FormServiceProvider());
        $this->register(new Provider\SessionServiceProvider());
        $this->register(new Provider\ValidatorServiceProvider());
        $this->register(new Provider\DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver' => 'pdo_sqlite',
                'path'   => ROOT_LOCATION . '/data/app.db'
            )
        ));
    }
}
