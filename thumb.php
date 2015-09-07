<?php
  require('includes/config.php');

  define('HTTP_ERROR_BAD_REQUEST', 400);
  define('HTTP_ERROR_NOT_FOUND', 404);
  define('HTTP_ERROR_INTERNAL_SERVER_ERROR', 500);

  define('THUMB_WIDTH', 150);
  define('THUMB_HEIGHT', 150);
  define('MOSAIC_PADDING', 2);

  define('FILE_MODE', 1);
  define('FOLDER_MODE', 2);

  /* ===========================================================================
      Helper
   */

  function http_error_exit($httpCode)
  {
    http_response_code($httpCode);
    exit;
  }

  function sanitize_path($imgPath)
  {
    $imgPath = str_replace('../', '', $imgPath);
    return $imgPath;
  }

  /* ===========================================================================
      Thumbnail creation
   */

  function output_image($img)
  {
    header('Content-Type: image'.$img->getImageFormat());
    echo $img;
  }

  function create_image_thumb($imgPath)
  {
    $img = new Imagick($imgPath);
    $img->cropThumbnailImage(THUMB_WIDTH, THUMB_HEIGHT);
    return $img;
  }

  function create_mosaic_thumb($folderPath)
  {
    /*
       ---------------      Padding = 2px
      |       |       |     Cellwidth = THUMB_WIDTH/2 - padding/2
      |       |       |     Cellheight = THUMB_WIDTH/2 - padding/2
      |-------|-------|
      |       |       |
      |       |       |
       ---------------
     */

    if (($imgDirHandle = opendir($folderPath)) === FALSE) {
      return false;
    } else {
      $images = array();
      $thumbWH = THUMB_WIDTH/2 - MOSAIC_PADDING/2;
      while (($file = readdir($imgDirHandle)) !== FALSE
             && count($images) <= 4) {
        if ($file[0] != '.') {
          if (is_dir($folderPath.$file)) {
            $dirHash   = sha1($folderPath.$file);
            $cacheDir  = substr($dirHash, 0, 2);
            $cacheFile = substr($dirHash, 2, strlen($dirHash)).THUMB_EXT;
            $dirThumb = THUMB_DIR.$cacheDir.'/'.$cacheFile;
            if (file_exists($dirThumb)) {
              $img = new Imagick($dirThumb);
              $img->cropThumbnailImage($thumbWH, $thumbWH);
              $images[] = $img;
            }
          } else {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext  == 'jpg' || $ext == 'png' || $ext == 'gif') {
              $img = new Imagick($folderPath.$file);
              $img->cropThumbnailImage($thumbWH, $thumbWH);
              $images[] = $img;
            }
          }
        }
      }
      closedir($imgDirHandle);
      if (empty($images)) {
        return false;
      } else {
        $thumbImg = new Imagick();
        $thumbImg->newImage(THUMB_WIDTH, THUMB_HEIGHT, 'white');
        $thumbImg->setImageFormat('jpg');
        for ($i = 0; $i < count($images); ++$i) {
          $thumbImg->compositeImage($images[$i],
                                    Imagick::COMPOSITE_OVER,
                                    ($i % 2)  ? (THUMB_WIDTH/2 + MOSAIC_PADDING / 2): 0,
                                    ($i >= 2) ? (THUMB_HEIGHT/2 + MOSAIC_PADDING / 2) : 0);
        }
        return $thumbImg;
      }
    }
  }

  /* ===========================================================================
   */
  $objPath = null;
  $mode = 0;
  if (isset($_GET['i'])) {
    $objPath = sanitize_path($_GET['i']);
    $mode = FILE_MODE;
  } else if (isset($_GET['f'])) {
    $objPath = sanitize_path($_GET['f']);
    $mode = FOLDER_MODE;
  } else {
    http_error_exit(HTTP_ERROR_BAD_REQUEST);
  }

  if (!file_exists($objPath)) {
    http_error_exit(HTTP_ERROR_NOT_FOUND);
  }

  $objHash   = sha1($objPath);
  $cacheDir  = substr($objHash, 0, 2);
  $cacheFile = substr($objHash, 2, strlen($objHash)).THUMB_EXT;
  $thumbPath = (USE_THUMB_CACHE) ? THUMB_DIR.$cacheDir.'/'.$cacheFile : '';

  if (USE_THUMB_CACHE && file_exists($thumbPath)) {
    $img = new Imagick($thumbPath);
    output_image($img);
  } else {
    $img = null;
    if ($mode == FILE_MODE) {
      $img = create_image_thumb($objPath);
    } else { /* mode == FOLDER_MODE */
      $img = create_mosaic_thumb($objPath.'/');
    }
    if ($img === FALSE) {
      http_error_exit(HTTP_ERROR_INTERNAL_SERVER_ERROR);
    } else {
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
