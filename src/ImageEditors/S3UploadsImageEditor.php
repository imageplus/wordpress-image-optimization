<?php

namespace IPIO\ImageEditors;

use Imagick;
use S3_Uploads\Image_Editor_Imagick;
use WP_Error;

class S3UploadsImageEditor extends Image_Editor_Imagick
{
    /**
     * we need to overwrite the save method of the s3 plugin so we can add our own hook
     * this allows us to do something before the image is copied to s3 (in this case optimize it)
     *
     *
     * Imagick by default can't handle s3:// paths
     * for saving images. We have instead save it to a file file,
     * then copy it to the s3:// path as a workaround.
     *
     * @param Imagick $image
     * @param ?string $filename
     * @param ?string $mime_type
     * @return WP_Error|array{path: string, file: string, width: int, height: int, mime-type: string}
     */
    protected function _save( $image, $filename = null, $mime_type = null ) {
        /**
         * @var ?string $filename
         * @var string $extension
         * @var string $mime_type
         */
        list( $filename, $extension, $mime_type ) = $this->get_output_format( $filename, $mime_type );

        if ( ! $filename ) {
            $filename = $this->generate_filename( null, null, $extension );
        }

        $upload_dir = wp_upload_dir();

        if ( strpos( $filename, $upload_dir['basedir'] ) === 0 ) {
            /** @var false|string */
            $temp_filename = tempnam( get_temp_dir(), 's3-uploads' );
        } else {
            $temp_filename = false;
        }

        /**
         * @var WP_Error|array{path: string, file: string, width: int, height: int, mime-type: string}
         */
        $parent_call = get_parent_class(parent::class)::_save( $image, $temp_filename ?: $filename, $mime_type );

        if ( is_wp_error( $parent_call ) && $temp_filename ) {
            unlink( $temp_filename );
            return $parent_call;
        } else {
            /**
             * @var array{path: string, file: string, width: int, height: int, mime-type: string} $save
             */
            $save = $parent_call;
        }

        //THIS IS THE NEW FUNCTIONALITY
        do_action('before_s3_upload', $save);

        $copy_result = copy( $save['path'], $filename );

        unlink( $save['path'] );
        if ( $temp_filename ) {
            unlink( $temp_filename );
        }

        if ( ! $copy_result ) {
            return new WP_Error( 'unable-to-copy-to-s3', 'Unable to copy the temp image to S3' );
        }

        $response = [
            'path'      => $filename,
            'file'      => wp_basename( apply_filters( 'image_make_intermediate_size', $filename ) ),
            'width'     => $this->size['width'] ?? 0,
            'height'    => $this->size['height'] ?? 0,
            'mime-type' => $mime_type,
        ];

        return $response;
    }
}