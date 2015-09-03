<?php
  require('inc/config.php');
  require('inc/functions.php');

  function sanitize_image_path($imgPath)
  {
    $imgPath = str_replace('../', '', $imgPath);
    return $imgPath;
  }

  function output_image($img)
  {
    header('Content-Type: image'.$img->getImageFormat());
    echo $img;
  }

  $imgPath = isset($_GET['i']) ? sanitize_image_path($_GET['i'])
                               : http_error_exit(HTTP_ERROR_BAD_REQUEST);
  $imgHash  = sha1($imgPath);
  $cacheDir   = substr($imgHash, 0, 2);
  $cacheFile  = substr($imgHash, 2, strlen($imgHash)).THUMB_EXT;

  $thumbPath = (USE_THUMB_CACHE) ? THUMB_DIR.$cacheDir.'/'.$cacheFile : '';

  if (USE_THUMB_CACHE && file_exists($thumbPath)) {
    $img = new imagick($thumbPath);
    output_image($img);
  } else {
    if (($size = getimagesize($imgPath)) === FALSE) {
      http_error_exit(HTTP_ERROR_NOT_FOUND);
    } else {
      $img = new imagick($imgPath);
      $img->cropThumbnailImage( 120, 120 );
      if (USE_THUMB_CACHE) {
        if (!file_exists(THUMB_DIR.$cacheDir)) {
          if (mkdir(THUMB_DIR.$cacheDir) === FALSE) {
            http_error_exit(HTTP_ERROR_INTERNAL_SERVER_ERROR);
          }
        }
        $img->writeImage($thumbPath);
      }
      output_image($img);
    }
  }
