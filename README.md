# Slim Picture Gallery (SPG)

## Introduction

SPG is yet another image gallery that simply displays your pictures: Just create folders for albums and drop your pictures into them, SPG will take care of the rest.
SPG requires no database, supports JPEG, GIF and PNG, takes care of thumbnail creation and has a very simple, HTML5 compliant responsive design.

## Features

* Simple installation
* Simple, responsive design
* Auto generates thumbnails
* Displays picture files in folders
* English and German language translations
* 6 translatable strings
* No database required
* No administrator backend
* No user management

## Requirements

SPG requires PHP 5.2+ and the ImageMagick image library to work properly. For more information on PHP and the ImageMagick library, please visit http://php.net.

## Installation

1. Extract or clone this repository into the desired folder on your webserver
2. Copy ```includes/config.sample.php``` to ```includes/config.php```
2. Make the ```gallery-images/.thumb_cache/``` folder writable by the webserver (either by changing the directory owner to your PHP user or ```chmod 777``` it, if your webhost doesn't allow changing the owner of directories).
3. If you don't want to make the directory writeable or just don't want to use thumbnail caching, set ```USE_THUMB_CACHE``` to ```false``` in ```includes/config.php```.
4. Create folders within the gallery-images folder and upload pictures.
5. That's it!

### Configuration (optional)

Open ```includes/config.php``` to change the directory of your picture (```IMAGES_DIR```) and thumbnail (```THUMB_DIR```) folder, as well as some display settings.
Keep in mind that the picture folder has to be publicly accessible, whereas the thumbnail folder can be placed anywhere outside of your accessible web root.

## Directory structure

SPG looks for folders and image files within the data folder and displays them just like you'd browse your computer, with the exception of a few special cases,
for starters, SPG will only display jpg, png, gif files and subfolders.

If SPG finds a .zip-File within the current folder, however, it'll display a download link for that zip file above the image grid.

If SPG finds folder named "_hires", it'll look for images with the same name as in the current folder to generate a "display in full resolution link" for each picture that is found within that subfolder. The "_hires" folder will also not be shown in the image grid.

### Example directory structure
```
   data\
      .thumb_cache\
      friends\
         bowling\
            _hires\
               DSC123.jpg
               DSC124.jpg
               DSC126.jpg
            DSC123.jpg
            DSC124.jpg
            DSC125.jpg
            DSC126.jpg
            DSC127.jpg
            ...
         hiking\
            1.png
            2.jpg
            ...
            hiking_pictures.zip
```

## Clearing the cache

Just delete the contents of ```gallery-images/.thumb-cache```.

For automation via web cron, you can also use the provided script ```clear_cache.php``` to purge all images from the cache directory.

It is recommended that you set a ```CLEAR_THUMB_SECRET``` in ```includes/config.php``` so that only ```clear_cache.php?s=your CLEAR_THUMB_SECRET``` will actually purge the cache directory.

