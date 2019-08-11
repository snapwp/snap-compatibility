<?php

namespace Snap\Compatibility;

use Snap\Services\Config;
use Snap\Services\Container;
use Snap\Services\Service_Provider;

/**
 * Load various plugin compatibility fixes.
 */
class Compatibility_Service_Provider extends Service_Provider
{
    /**
     * Load any fixes.
     *
     * @since 1.0.0
     */
    public function boot()
    {
        if ($this->is_offload_media_present() === true && Config::get('images.dynamic_image_sizes') !== false) {
            Container::resolve('\\Snap\\Compatibility\\Fixes\\Offload_Media')->run();
        }
    }

    /**
     * Check if WP Offload S3/Offload Media plugin active.
     *
     * @since  1.0.0
     *
     * @return boolean
     */
    private function is_offload_media_present()
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
