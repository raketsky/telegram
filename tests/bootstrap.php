<?php
use Dotenv\Dotenv;

defined('ROOT') or define('ROOT', __DIR__.'/../');

$dotenv = new Dotenv(ROOT);
$dotenv->load();
