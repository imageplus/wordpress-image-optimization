<?php

namespace IPIO\ImageEditors;

use Imagick;
use WP_Error;
use WP_Image_Editor_Imagick;

class DefaultImageEditor extends WP_Image_Editor_Imagick
{
    /**
     * @param  Imagick $image
     * @param  string  $filename
     * @param  string  $mime_type
     * @return array|WP_Error
     */
    protected function _save( $image, $filename = null, $mime_type = null ) {
        $saved = parent::_save($image, $filename, $mime_type);

        do_action('before_s3_upload', $saved);

        return $saved;
    }
}