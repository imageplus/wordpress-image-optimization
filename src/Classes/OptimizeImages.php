<?php

namespace IPIO\Classes;

use Spatie\ImageOptimizer\OptimizerChainFactory;
use S3_Uploads\Plugin;

class OptimizeImages
{
    /**
     * @var OptimizeImages
     */
    protected static $instance = null;

    /**
     * @var OptimizerChainFactory
     */
    public $optimizer = null;

    public function __construct()
    {
        $this->optimizer = \IPIO\Optimizers\OptimizerChainFactory::create();
    }

    /**
     * Generate or return the current instance of self
     *
     * @return OptimizeImages|null
     */
    public static function getInstance(){
        if(self::$instance === null){
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Adds the filters required for the plugin to function
     */
    public static function addFilters(){
        self::getInstance();

        add_action('before_s3_upload', [self::$instance, 'optimizeImageSizeBeforeMoved']);

        add_action('pre_move_uploaded_file', [self::$instance, 'optimizeUploadedImageBeforeMove'], 10, 2);

        //editor actions
        //removes the default one for the s3 handler and enable our own
        if(class_exists(Plugin::class)){
            remove_action('wp_image_editors', [Plugin::get_instance(), 'filter_editors'], 9);
        }
        add_filter( 'wp_image_editors', [self::$instance, 'filterEditors']);
    }

    /**
     * Generates the editors required for the plugin to function
     *
     * @param  array $editors
     * @return array
     */
    public function filterEditors( array $editors ) : array {
        $position = array_search( 'WP_Image_Editor_Imagick', $editors );
        if ( $position !== false ) {
            unset( $editors[ $position ] );
        }


        //adds the new editor required for the current Wordpress instance
        array_unshift(
            $editors,
            class_exists(Plugin::class)
                ? 'IPIO\\ImageEditors\\S3UploadsImageEditor'
                : 'IPIO\\ImageEditors\\DefaultImageEditor'
        );

        return $editors;
    }

    /**
     * Optimize the image in place for an individual size
     *
     * @param array $image
     */
    public function optimizeImageSizeBeforeMoved(array $image){
        $this->optimizer->optimize($image['path']);

        return null;
    }

    /**
     * Optimize the original uploaded image
     *
     * As WordPress doesn't call the image editors for the original we need to optimize it separately
     *
     * @param null  $move
     * @param array $image
     */
    public function optimizeUploadedImageBeforeMove($move, array $image){
        //at this point the image is in the tmp directory and image is the tmp file object
        $this->optimizer->optimize($image['tmp_name']);

        return null; //we aren't moving the image so return null
    }
}