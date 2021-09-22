<?php

namespace IPIO\Optimizers;

use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\ImageOptimizer\Optimizers\Cwebp;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Svgo;

/**
 * Making use of a custom chain factory so we can include sharpjs as the first optimizer as I've had
 * better luck making use of that for compression
 */
class OptimizerChainFactory
{
    public static function create(array $config = []): OptimizerChain
    {
        $jpegQuality = '--max=85';
        $pngQuality = '--quality=85';
        if (isset($config['quality'])) {
            $jpegQuality = '--max='.$config['quality'];
            $pngQuality = '--quality='.$config['quality'];
        }

        //only difference between this and spaties is sharpjs beind the first option
        return (new OptimizerChain())
            ->addOptimizer(new SharpJS([

            ]))

            ->addOptimizer(new Jpegoptim([
                $jpegQuality,
                '--strip-all',
                '--all-progressive',
            ]))

            ->addOptimizer(new Pngquant([
                $pngQuality,
                '--force',
                '--skip-if-larger',
            ]))

            ->addOptimizer(new Optipng([
                '-i0',
                '-o2',
                '-quiet',
            ]))

            ->addOptimizer(new Svgo([
                '--disable={cleanupIDs,removeViewBox}',
            ]))

            ->addOptimizer(new Gifsicle([
                '-b',
                '-O3',
            ]))

            ->addOptimizer(new Cwebp([
                '-m 6',
                '-pass 10',
                '-mt',
                '-q 80',
            ]));
    }
}