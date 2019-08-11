<?php

namespace Snap\Compatibility\Fixes;

use Snap\Core\Hookable;

/**
 * Add dynamic image compatibility fixes for Delicious Brains' Offload Media plugin.
 */
class Offload_Media extends Hookable
{
    /**
     * The filters to run when booted.
     *
     * @since  1.0.0
     * @var array
     */
    public $filters = [
        'snap_dynamic_image_source' => 'wp_offload_media_creation_fix',
        'as3cf_preserve_file_from_local_removal' => 'preserve_file_from_local_removal',
        'as3cf_remove_attachment_paths' => 'ensure_correct_file_deletion',
    ];

    /**
     * The actions to run when booted.
     *
     * @since  1.0.0
     * @var array
     */
    public $actions = [
        'snap_dynamic_image_before_delete' => 'pre_delete',
        'snap_dynamic_image_after_delete' => 'post_delete',
    ];

    /**
     * Holds the amazonS3_info meta data for an image being deleted.
     *
     * @since  1.0.0
     * @var array
     */
    protected $meta;

    /**
     * Holds the dynamic image sizes to be deleted.
     *
     * @since  1.0.0
     * @var array
     */
    protected $sizes;

    /**
     * Ensure the parent file always exists when WP Offload Media is present.
     *
     * @since  1.0.0
     *
     * @param  string $src Expected parent file location on local system.
     * @param  int    $id  The attachment ID.
     * @return string
     */
    public function wp_offload_media_creation_fix($src, $id)
    {
        if (isset($GLOBALS['as3cf'])) {
            global $as3cf;

            $provider_object = $as3cf->get_attachment_provider_info($id);
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
        }

        return $src;
    }

    /**
     * This filter allows you to stop files from being removed from the local server
     * even when using WP Offload Media's "Remove all files from server" tool.
     *
     * @see \Amazon_S3_And_CloudFront::remove_local_files()
     * @since 1.0.0
     *
     * @param bool $preserve Whether to reserve the local file.
     * @return bool
     */
    public function preserve_file_from_local_removal($preserve)
    {
        \gc_collect_cycles();

        return $preserve;
    }

    /**
     * Fires just before 'delete_attachment' is fired when an intermediate image size is deleted via the
     * dynamic sizes admin UI.
     *
     * @since 1.0.0
     *
     * @param array $sizes List of sizes to be deleted.
     * @param int   $attachment_id The ID of the current attachment.
     */
    public function pre_delete($sizes, $attachment_id)
    {
        $this->sizes = $sizes;
        $this->meta = \get_post_meta($attachment_id, 'amazonS3_info', true);
    }

    /**
     * Fires just after 'delete_attachment' is fired when an intermediate image size is deleted via the
     * dynamic sizes admin UI.
     *
     * @since 1.0.0
     *
     * @param array $sizes List of sizes to be deleted.
     * @param int   $attachment_id The ID of the current attachment.
     */
    public function post_delete($sizes, $attachment_id)
    {
        $this->sizes = [];
        \update_post_meta($attachment_id, 'amazonS3_info', $this->meta);
    }

    /**
     * Ensures only the dynamic sizes deleted from the server are deleted from S3.
     *
     * @see \Amazon_S3_And_CloudFront::remove_attachment_files_from_provider()
     * @since 1.0.0
     *
     * @param array $sizes Sizes to be deleted.
     * @return array
     */
    public function ensure_correct_file_deletion($sizes)
    {
        if (isset($GLOBALS['as3cf'])) {
            global $as3cf;

            if ($as3cf->get_setting('remove-local-file') == true && !empty($this->sizes)) {
                return \array_intersect_key($sizes, \array_flip($this->sizes));
            }
        }

        return $sizes;
    }
}
