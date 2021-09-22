<?php

namespace IPIO\Classes;

use S3_Uploads\Plugin;
use WP_Query;

class BulkOptimizer
{
    /**
     * @var BulkOptimizer
     */
    protected static $instance = null;

    /**
     * Gets or creates an instance of self
     */
    public static function initialise(){
        if(self::$instance === null){
            self::$instance = new self();
        }
    }

    public function __construct(){
        //add the form handler
        add_action('admin_post_ipio_optimize_all', [$this, 'handleBulkOptimization']);

        //add the custom page to the sidebar
        add_action('admin_menu', [$this, 'addSettingsPage']);
    }

    /**
     * Handles form submission and optimizes all attachments and their respective sizes
     */
    public function handleBulkOptimization(){
        //make sure the request is valid
        if(isset($_POST['ipio_optimize_all_nonce']) && wp_verify_nonce($_POST['ipio_optimize_all_nonce'],'ipio_optimize_all')){

            //if the plugin class exists we can't optimize currently as images are stored in s3 not locally
            if(class_exists(Plugin::class)){
                wp_die(
                    __('S3 Uploads Already Active Bulk Optimization Not Supported (Please optimize images before uploading to S3)'),
                    __('Error'),
                    [
                        'response' 	=> 403,
                        'back_link' => 'wp-admin/options-general.php?page=ipio-settings'
                    ]
                );
            }

            //so we can always set session variables to flash on the next page start a session if one hasn't started
            if (!session_id()) {
                session_start();
            }

            $attachments = get_posts([
                'post_type'      => 'attachment',
                'posts_per_page' => -1,
                'post_status'    => null
            ]);

            $basePath = wp_get_upload_dir()['basedir'] . '/';

            $_SESSION['ipio_messages'] = [];

            foreach ($attachments as $attachment) {

                $attachmentData = wp_get_attachment_metadata($attachment->ID);

                //we have a scaled image as the large so optimize the full size too
                if(strpos($attachment->guid, $attachmentData['file']) === false){
                    $this->optimizeByPath(
                        $basePath . dirname($attachmentData['file']),
                        basename($attachment->guid)
                    );
                }

                $this->optimizeByPath($basePath . $attachmentData['file']);

                //optimize all sub sizes of the image
                foreach ($attachmentData['sizes'] as $sizeData){
                    $this->optimizeByPath(
                        $basePath . dirname($attachmentData['file']),
                        $sizeData['file']
                    );
                }
            }

            $_SESSION['ipio_messages'][] = [
                'message' => 'Image Optimization Complete',
                'type'    => 'success'
            ];
            wp_redirect($_SERVER["HTTP_REFERER"]);
            return;
        }

        //the nonce given was invalid so the form cannot be submitted
        wp_die(
            __('Invalid nonce specified'),
            __('Error'),
            [
                'response' 	=> 403,
                'back_link' => 'wp-admin/options-general.php?page=ipio-settings'
            ]
        );
    }

    /**
     * Optimize an image after generating its path
     *
     * @param  $basePath
     * @param  null $file
     * @return false|null
     */
    protected function optimizeByPath($basePath, $file = null){
        $path = $file !== null
            ? path_join(
                $basePath,
                $file
            ) : $basePath;

        try {
            return (OptimizeImages::getInstance())->optimizeImageSizeBeforeMoved([
                'path' => $path
            ]);
        } catch (\Throwable $t){
            $_SESSION['ipio_messages'][] = [
                'message' => 'Image Optimization Failed For Image: ' . $path ,
                'type'    => 'warning'
            ];

            return false;
        }
    }

    /**
     * Adds the plugins page to the submenu inside of settings
     */
    public function addSettingsPage(){
        add_submenu_page(
            'options-general.php',
            'Image Optimization',
            'Image Optimization',
            'administrator',
            'ipio-settings',
            [$this, 'addFrontendPage']
        );
    }

    /**
     * gets the site details and renders the frontend page
     */
    public function addFrontendPage(){
        //add the frontend page
        require IMAGE_OPTIMIZATION_ENTRY . '/src/views/settings.php';
    }
}