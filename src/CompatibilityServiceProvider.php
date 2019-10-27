<?php

namespace Snap\Compatibility;

use Snap\Services\Config;
use Snap\Services\Container;
use Snap\Services\ServiceProvider;

/**
 * Load various plugin compatibility fixes.
 */
class CompatibilityServiceProvider extends ServiceProvider
{
    /**
     * Load any fixes.
     */
    public function boot()
    {
        if ($this->isOffloadMediaPresent() === true && Config::get('images.dynamic_image_sizes') !== false) {
            Container::resolve('\\Snap\\Compatibility\\Fixes\\Offload_Media')->run();
        }
    }

    /**
     * Check if WP Offload S3/Offload Media plugin active.
     *
     * @return boolean
     */
    private function isOffloadMediaPresent()
    {
        if (isset($GLOBALS['as3cf'])
            || isset($GLOBALS['as3cfpro_compat_check'])
            || \class_exists('AS3CF_Compatibility_Check')
        ) {
            return true;
        }

        return false;
    }
}
