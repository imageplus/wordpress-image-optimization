<?php

namespace IPIO\Optimizers;

use Spatie\ImageOptimizer\Image;
use Spatie\ImageOptimizer\Optimizers\BaseOptimizer;

class SharpJS extends BaseOptimizer
{
    //https://github.com/vseventer/sharp-cli
    public $binaryName = 'sharp';

    /**
     * allow sharp to compress and optimize jpegs, webps and pngs only
     *
     * @param Image $image
     *
     * @return bool
     */
    public function canHandle(Image $image): bool
    {
        return in_array($image->mime(), [
            'image/jpeg',
            'image/webp',
            'image/png'
        ]);
    }

    /**
     * Generate the command to optimize the file in place
     *
     * @return string
     */
    public function getCommand(): string
    {
        $optionString = implode(' ', $this->options);

        $imagePath = escapeshellarg($this->imagePath);

        return "\"{$this->binaryPath}{$this->binaryName}\" -i {$imagePath} -o {$imagePath} {$optionString}";
    }
}