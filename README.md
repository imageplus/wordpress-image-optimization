# Imageplus Image Optimization

## Installation
For this plugin to work you'll need certain packages installed on your system. The command to install these can be found below
```shell
    sudo npm install -g sharp-cli
    sudo apt-get install jpegoptim
    sudo apt-get install optipng
    sudo apt-get install pngquant
    sudo npm install -g svgo@1.3.2
    sudo apt-get install gifsicle
    sudo apt-get install webp
```

## Usage
When activated this plugin will optimize all images uploaded into Wordpress through multiple stages. It does not leave the original file intact and will overwrite it with an optimized version. This will allow all versions of the images to load faster.

This plugin can be used with the `S3Uploads` plugin as well but will need to be activated before it as bulk optimization is currently not possible when assets are stored on S3 to reduce running costs

## Additional Information
This package makes use of ``https://github.com/spatie/image-optimizer`` to optimize images and additional information can be found there

