<?php

  // File system
  define('IMAGES_DIR', './gallery-images/'); // path to your pictures folder

  define('THUMB_DIR', IMAGES_DIR.'.thumb_cache/'); // this path is used by thumbs.php to generate thumbnails

  define('THUMB_EXT', '.cacheImage'); // file extension of generated thumbnails

  define('USE_THUMB_CACHE', true);    // Set to true if you want to enable caching of thumbnails

  define('CLEAR_THUMB_SECRET', '');   // This string is used when opening clear_thumb.php?s=<secret> in your browser.
                                      // It prevents automated "unauthorized" purging of your thumbnail cache

  // Display
  define('THEME', 'default');          // Which theme in /themes/default to use
  define('LANGUAGE', 'de_de');         // see language files in inc/lang
  define('MAX_IMAGES_PER_PAGE', 100);  // how many pictures per page?

  // Do not edit after this line
  require_once dirname(__FILE__).'/lang/'.LANGUAGE.'.php';
