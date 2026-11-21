<?php
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$specUrl = ($basePath !== '' ? $basePath : '/api/docs') . '/openapi.yaml';
?>
<!doctype html>
<html lang="en">
  <head>
    <title>Cloud Music Player API Reference</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/svg+xml" href="https://scalar.com/favicon.svg" />
    <style>
      body {
        margin: 0;
        padding: 0;
      }
    </style>
  </head>
  <body>
    <script
      id="api-reference"
      data-url="<?= htmlspecialchars($specUrl, ENT_QUOTES, 'UTF-8') ?>"
      data-configuration='{
        "theme": "purple",
        "darkMode": true,
        "showSidebar": true,
        "layout": "modern"
      }'
    ></script>
    <script src="https://cdn.jsdelivr.net/npm/@scalar/api-reference"></script>
  </body>
</html>
