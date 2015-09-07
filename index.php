<?php
  require('includes/config.php');

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
    global $lang;

    $galleryPath = IMAGES_DIR;
    if ($galleryId != '') {
      $galleryPath .= $galleryId.'/';
    }

    // Get all files in current gallery
    if (($imgDirHandle = opendir($galleryPath)) === FALSE) {
      return false;
    } else {
      while (($file = readdir($imgDirHandle)) !== FALSE) {
        if ($file[0] != '.' && $file != '_hires') {
          $filePath = $galleryPath.$file;
          if (is_dir($filePath)) {
            $linkGalleryId = ($galleryId != '') ? $galleryId.'/'.$file : $file;
            $folders[] = array('name' => $file, 'path' => $filePath, 'link' => $linkGalleryId);
          } else {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if ($ext  == 'jpg' || $ext == 'png' || $ext == 'gif') {
              $hqPath = $galleryPath.'_hires/'.$file;
              if (!file_exists($hqPath)) {
                $hqPath = '';
              }
              $images[] = array('path' => $filePath, 'hqPath' => $hqPath);
            } else if ($ext == 'zip') {
              $fileSize = @filesize($filePath);
              if ($fileSize === FALSE) {
                $fileSize = 0;
              } else {
                $fileSize = round($fileSize / (1024 * 1024), 2);
              }
              $downloads[] = array('name' => $file, 'path' => $filePath, 'size' => $fileSize);
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
      $backFolder = array('name' => $lang['navigate_back'], 'link' => $backLink);
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
<html lang="<?php echo HTML_LANG; ?>" dir="<?php echo HTML_DIR; ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link  rel="stylesheet" type="text/css" href="assets/css/lightbox.css" media="screen">
  <link  rel="stylesheet" type="text/css" href="themes/<?php echo THEME; ?>/style.css" media="screen">
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
          echo '<li class="separator"></li>';
        }
        echo '</ul>';
      }
    ?></nav>
  </header>
  <div class="content" role="main">
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
          echo '<li>&raquo; <a href="'.$dl['path'].'">'.$dl['name'].'</a>';
          if ($dl['size'] > 0) {
            echo ' <small>('.strval($dl['size']).' MB)</small>';
          }
          echo '</li>';
        }
        echo '</ul>';
      }
      echo '<div class="gallery">';

      foreach ($folders as $folder) {
        $thumbNail = (!isset($folder['path'])) ? '' : ' style="background-image:url(thumb.php?f='.rawurlencode($folder['path']).')"';
        echo '<a class="gallery-tile folder-tile"'.$thumbNail.' href="?g='.rawurlencode($folder['link']).'"><span>'.$folder['name'].'</span></a>';
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
?>
  </div>
  <footer>
  <?php
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
  </footer>
</div>
<script src="assets/js/jquery-2.1.4.min.js"></script>
<script src="assets/js/lightbox.min.js"></script>
<?php
  $themeScript = 'themes/'.THEME.'/main.js';
  if (file_exists($themeScript)) {
    echo '<script src="'.$themeScript.'"></script>';
  }
?>
</body>
</html>