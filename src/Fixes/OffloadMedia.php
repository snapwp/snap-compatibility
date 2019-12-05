<?php

namespace Snap\Compatibility\Fixes;

use Snap\Core\Hookable;

/**
 * Add dynamic image compatibility fixes for Delicious Brains' Offload Media plugin.
 */
class OffloadMedia extends Hookable
{
    /**
     * Run Hookable.
     */
    public function boot()
    {
        $this->addFilter('snap_dynamic_image_source', 'downloadBucketImageToServer');
        $this->addFilter('as3cf_preserve_file_from_local_removal', 'runGarbageCollection');
        $this->addAction('snap_deleted_dynamic_image', 'handleDeletedImage');
    }

    /**
     * Ensures deleted sizes are removed from S3 buckets as well.
     *
     * Runs the same methods that ACF would run on 'delete_attachment' action.
     *
     * @param int $id
     */
    public function handleDeletedImage($id)
    {
        /** @var \Amazon_S3_And_CloudFront $as3cf */
        global $as3cf;
        $as3cf->filter_local->purge_cache_on_attachment_delete($id);
        $as3cf->delete_attachment($id);
    }

    /**
     * Ensure the parent file always exists when WP Offload Media is present.
     *
     * @param  string $src Expected parent file location on local system.
     * @param  int    $id  The attachment ID.
     * @return string
     */
    public function downloadBucketImageToServer($src, $id)
    {
        /** @var \Amazon_S3_And_CloudFront $as3cf */
        global $as3cf;

        $provider_object = \DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item::get_by_source_id($id);
        $file = \get_attached_file($id, true);

        if ($as3cf->get_setting('remove-local-file') == true) {
            // Copy original to server.
            if (isset($as3cf->plugin_compat)) {
                $file = $as3cf->plugin_compat->copy_provider_file_to_server($provider_object, $file);
            }
        } elseif (!\file_exists($file)) {
            if (isset($as3cf->plugin_compat)) {
                $file = $as3cf->plugin_compat->copy_provider_file_to_server($provider_object, $file);
            }
        }

        if ($file !== false) {
            return $file;
        }

        return $src;
    }

    /**
     * Fix issue where local files were not deleted due to amazon S3 lib having a file lock.
     *
     * @see \Amazon_S3_And_CloudFront::remove_local_files()
     *
     * @param bool $preserve Whether to reserve the local file.
     * @return bool
     */
    public function runGarbageCollection($preserve)
    {
        \gc_collect_cycles();
        return $preserve;
    }
}
