<!doctype html>
<html lang="de" dir="ltr">
<head>
  <meta charset="utf-8">
  <title>Clear Cache</title>
</head>
<body>
<?php
  require('includes/config.php');

  if (CLEAR_THUMB_SECRET == '' || (isset($_GET['s']) && CLEAR_THUMB_SECRET == $_GET['s'])) {
    function recursiveDelete($str) {
        if (is_file($str) && basename($str) != 'index.html') {
          echo '<li>File: '.$str.'</li>';
          return @unlink($str);
        } else if (is_dir($str)) {
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index=>$path) {
                recursiveDelete($path);
            }
            return @rmdir($str);
        }
    }

    $files = glob(THUMB_DIR.'*');
    echo '<p>Deleting:</p><ol>';
    foreach($files as $file) {
      recursiveDelete($file);
    }
    echo '</ol></p>...Done</p>';
  }
?>
</body>
</html>