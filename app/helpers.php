<?php

function getUrl($url) {
  if (env('APP_ENV') == 'prod') {
    return secure_url($url);
  }
  return url($url);
}