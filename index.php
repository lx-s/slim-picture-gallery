<?php
  require('inc/config.php');
  require('inc/functions.php');

  // ===========================================================================
  //  Prepare parameters

  $galleryId = isset($_GET['g']) ? str_replace('../', '', $_GET['g']) : '';
  $curPage   = isset($_GET['p']) ? (int)$_GET['p'] : 1;
  if ($curPage < 0) {
    $curPage = 1;
  }

  $galleryName = basename($galleryId);
  $galleryPath = IMAGES_DIR.$galleryId.'/';

  // ===========================================================================
  //  Gallery Functions

  function get_gallery_content($galleryId, &$folders, &$images, &$downloads)
  {
    $galleryPath = IMAGES_DIR.$galleryId.'/';

    // Get all files in current gallery
    if (($imgDirHandle = opendir($galleryPath)) === FALSE) {
      return false;
    } else {
      while (($file = readdir($imgDirHandle)) !== FALSE) {
        if ($file[0] != '.' && $file != '_hires') {
          $filePath = $galleryPath.$file;
          if (is_dir($filePath)) {
            $linkGalleryId = ($galleryId != '') ? $galleryId.'/'.$file : $file;
            $folders[] = array('name' => $file, 'link' => $linkGalleryId);
          } else {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if ($ext  == 'jpg' || $ext == 'png' || $ext == 'gif') {
              $hqPath = $galleryPath.'_hires/'.$file;
              if (!file_exists($hqPath)) {
                $hqPath = '';
              }
              $images[] = array('path' => $filePath, 'hqPath' => $hqPath);
            } else if ($ext == 'zip') {
              $downloads[] = array('name' => $file, 'path' => $filePath);
            }
          }
        }
      }
      closedir($imgDirHandle);
    }

    sort($folders);
    sort($images);

    // If in sub-folder, then add upward navigation
    if (strlen($galleryId) > 0) {
      $backLink = substr($galleryId, 0, strrpos($galleryId, '/'));
      $backFolder = array('name' => '↑ Zurück', 'link' => $backLink);
      array_unshift($folders, $backFolder);
    }

    return true;
  }
  function get_breadcrumb_list($galleryId)
  {
    $bcList = array();
    if ($galleryId != '') {
      $bcList[] = array('name' => basename($galleryId), 'link' => $galleryId);
    }
    while (($separatorPos = strrpos($bcList[count($bcList)-1]['link'], '/')) !== FALSE) {
      $gid = substr($galleryId, 0, $separatorPos);
      $bcList[] = array('name' => basename($gid), 'link' => $gid);
    }
    $bcList[] = array('name' => '⌂', 'link' => '');
    return array_reverse($bcList);
  }

?><!doctype html>
<html lang="de" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link  rel="stylesheet" type="text/css" href="res/css/lightbox.css" media="screen">
  <link  rel="stylesheet" type="text/css" href="res/css/default.css" media="screen">
  <title><?php echo $lang['page_title']; ?> <?php if ($galleryName) { echo '&bdquo;'.$galleryName.'&rdquo;'; } ?></title>
</head>
<body>
<div class="page-wrapper">
  <header>
    <h1 class="gallery-title"><?php echo $lang['page_title']; ?> <?php if ($galleryName) { echo ' &bdquo;'.$galleryName.'&rdquo;'; } ?></h1>
    <nav><?php
      $breadcrumbs = get_breadcrumb_list($galleryId);
      if (!empty($breadcrumbs)) {
        echo '<ul class="breadcrumbs-list">';
        foreach ($breadcrumbs as $bc) {
          echo '<li><a href="?g='.rawurlencode($bc['link']).'">'.$bc['name'].'</a></li>';
          echo '<li class="separator">/</li>';
        }
        echo '</ul>';
      }
    ?></nav>
  </header>
  <?php
    $folders   = array();
    $images    = array();
    $downloads = array();

    if (get_gallery_content($galleryId, $folders, $images, $downloads) === FALSE) {
      echo  '<div class="error"><p>'.$lang['error:display_gallery'].'</p></div>';
    } else {
      if (!empty($downloads)) {
        echo '<ul class="downloads-list"><li>'.$lang['download_zip'].': </li>';
        foreach ($downloads as $dl) {
          echo '<li>&raquo; <a href="'.$dl['path'].'">'.$dl['name'].'</a></li>';
        }
        echo '</ul>';
      }
      echo '<div class="gallery">';

      foreach ($folders as $folder) {
        echo '<a class="gallery-tile folder-tile" href="?g='.rawurlencode($folder['link']).'">'.$folder['name'].'</a>';
      }

      $imageCount = count($images);
      for ($curImgIdx = ($curPage - 1) * MAX_IMAGES_PER_PAGE, $curImgNum = 0;
           $curImgNum < MAX_IMAGES_PER_PAGE && $curImgIdx < $imageCount;
           ++$curImgIdx, ++$curImgNum) {
        $img = $images[$curImgIdx];
        $thumbNail = 'thumb.php?i='.rawurlencode($img['path']);
        echo '<a class="gallery-tile img-tile" style="background-image:url('.$thumbNail.')" href="'.$img['path'].'" data-lightbox="gallery"';
        if ($img['hqPath'] != '') {
          echo ' data-title="<a href=\''.$img['hqPath'].'\'>'.$lang['show_pic_full_res'].'</a>"';
        } else {
          echo ' data-title="<a href=\''.$img['path'].'\'>'.$lang['show_pic'].'</a>"';
        }
        echo '></a>';
      }

      echo '</div>';

      if ($imageCount > MAX_IMAGES_PER_PAGE) {
        echo '<ul class="page-nav">';
        $maxPages = $imageCount / MAX_IMAGES_PER_PAGE + 1;
        for ($i = 1; $i <= $maxPages; ++$i) {
          echo '<li><a href="?g='.rawurlencode($galleryId).'&amp;p='.$i.'"';
          if ($i == $curPage) {
            echo ' class="current-page"';
          }
          echo '>'.$i.'</a>';
        }
        echo '</ul>';
      }
    }
?>
<script src="res/js/jquery-2.1.4.min.js"></script>
<script src="res/js/lightbox.min.js"></script>
</div>
</body>
</html>