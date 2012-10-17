<?php

namespace Marietje\Scrobbler;

use Silex\Application;
use Silex\Provider;
use Symfony\Component\Translation\Loader\YamlFileLoader;

/**
 * Base application
 */
class App extends Application
{
    use Application\TwigTrait;
    use Application\FormTrait;
    use Application\MonologTrait;
    use Application\TranslationTrait;
    use Application\UrlGeneratorTrait;

    /**
     * Name of the variable where session data is stored
     * @var string
     */
    const LASTFM_SESSION = 'lfm_session';

    /**
     * Enables or disables debugging mode.
     * @var boolean
     */
    private $debug = true;

    /**
     * Last.fm application key
     * @var string
     */
    private $key = '';

    /**
     * Last.fm secret
     * @var string
     */
    private $secret = '';

    /**
     * Locations available
     * @var array
     */
    private $locations = [
        'noord' => [
            'http://noordslet.science.ru.nl:8080/playing',
            'nk'
        ],
        'zuid' => [
            'http://zuidslet.science.ru.nl:8080/playing',
            'zk'
        ],
    ];

    /**
     * Interval to check for new song.
     * @var float
     */
    private $checkInterval = 10.0;

    /**
     * Hours to keep scrobbles in local database.
     */
    private $keep_scrobbles = 24;

    /**
     * Hours to keep retrieved tracks in local database.
     */
    private $keep_retrieved = 48;

    /**
     * Construct a new application and setup all components required.
     */
    public function __construct()
    {
        parent::__construct();
        $this['debug'] = $this->debug;

        // logging using monolog
        $this->register(new Provider\MonologServiceProvider(), array(
            'monolog.logfile' => ROOT_LOCATION . '/marietje_scrobbler.log',
        ));

        // twig templates
        $this->register(new Provider\TwigServiceProvider(), array(
            'twig.path' => ROOT_LOCATION . '/views'
        ));

        // helpers for urls
        $this->register(new Provider\UrlGeneratorServiceProvider());

        // translation services
        $this->register(new Provider\TranslationServiceProvider(), array(
            'locale_fallback' => 'en'
        ));
        $this['translator'] = $this->share($this->extend('translator', function ($translator, $app) {
            $translator->addLoader('yaml', new YamlFileLoader());

            // add yaml translation sources
            $locales = ROOT_LOCATION . '/locales/';
            $translator->addResource('yaml', $locales . 'en.yml', 'en');
            $translator->addResource('yaml', $locales . 'nl.yml', 'nl');

            return $translator;
        }));

        // forms and validation
        $this->register(new Provider\FormServiceProvider());
        $this->register(new Provider\ValidatorServiceProvider());

        // sessions
        $this->register(new Provider\SessionServiceProvider());

        // database connection
        $this->register(new Provider\DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver' => 'pdo_sqlite',
                'path'   => ROOT_LOCATION . '/data/app.db'
            )
        ));

        // easy database access
        $this['retrieved'] = new Model\Retrieved($this['db']);
        $this['listeners'] = new Model\Listeners($this['db']);
        $this['scrobbles'] = new Model\Scrobbles($this['db']);
        $this['ignores']   = new Model\Ignores($this['db']);
        $this['locations'] = $this->locations;
        $this['interval']  = $this->checkInterval;
        $this['keep_retrieved'] = $this->keep_retrieved;
        $this['keep_scrobbles'] = $this->keep_scrobbles;

        // last.fm and session data
        $this['lastfm'] = new Lastfm($this->key, $this->secret);
        $sess = $this['session']->get(self::LASTFM_SESSION);
        $this['user'] = null;
        $this['sess'] = null;
        if ($sess !== null) {
            $this['user'] = $sess['name'];
            $this['sess'] = $sess['key'];
            $this['lastfm']->setSession($sess['key']);
        }
    }

    public function controller ($c)
    {
        $app = $this;
        return require 'Controllers/' . ucfirst($c) . '.php';
    }
}
