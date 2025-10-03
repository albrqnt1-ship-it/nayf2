<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/') {
    require 'index.html';
    return true;
}

if (file_exists(__DIR__ . $uri) && is_file(__DIR__ . $uri)) {
    return false;
}

require 'index.html';
