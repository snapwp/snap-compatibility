<?php

namespace Snap\Package;

use Snap\Services\Config;
use Snap\Services\Container;
use Snap\Services\Service_Provider;


/**
 * Example Service Provider.
 *
 * This class is auto-wired. So dependencies can be injected automatically via the constructor.
 */
class Package_Service_Provider extends Service_Provider
{
    /**
     * Register any services into the container.
     *
     * This method is run after snap-core has been initialized, but does not guarantee all other packages have been.
     */
    public function register()
    {
        // Add package config to the Snap Config service.
        $this->add_config_location(\realpath(__DIR__ . '/../config'));

        // If your package adds functions to the global namespace, include them.
        require_once __DIR__ . '/functions.php';

        // As the package config has now been added to the Config service, all config is available to use.
        if (Config::get('package.example_config_item') === true) {
            // Do something.
        }

        /*
         * Indicates that if this package is published, the contents of the config directory should be published
         * to the active theme.
         */
        $this->publishes_config(\realpath(__DIR__ . '/../config'));

        /*
         * Packages can publish anything into a theme - not just config.
         *
         * All published directories can be 'tagged' allowing theme developers to publish exactly what they need.
         */
        $this->publishes_directory('directory to publish', 'target directory in theme', 'tag');
    }

    /**
     * This method is run after all other packages have been registered - directly before any routing takes place.
     *
     * Typically this method is used to attach hooks and interface with other packages.
     *
     * This method is auto-wired.
     */
    public function boot()
    {
        /*
         * All service providers use the \Snap\Core\Concerns\Manages_Hooks trait.
         *
         * This provides a simple way to add methods from this class as WordPress hooks.
         */
        $this->add_action('after_setup_theme', 'example_hook');
        $this->remove_action('after_setup_theme', 'example_hook');
    }

    /**
     * Example hook callback.
     */
    public function example_hook()
    {

    }
}
