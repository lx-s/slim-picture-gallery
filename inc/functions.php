<?php
  define('HTTP_ERROR_BAD_REQUEST', 400);
  define('HTTP_ERROR_NOT_FOUND', 404);
  define('HTTP_ERROR_UNSUPPORTED_MEDIA_TYPE', 415);
  define('HTTP_ERROR_INTERNAL_SERVER_ERROR', 500);

  function http_error_exit($httpCode)
  {
    http_response_code($httpCode);
    exit;
  }