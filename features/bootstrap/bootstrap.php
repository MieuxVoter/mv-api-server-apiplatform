<?php

print("Validating the Algorithmic Constitution of a Liquid Majority Judgment Application…\n");

require 'functions.php';

// Careful, here.  Perhaps we're leaking?
// See https://jolicode.com/blog/you-may-have-memory-leaking-from-php-7-and-symfony-tests
ini_set('memory_limit', '1G');

putenv('APP_ENV='.$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test');
require dirname(__DIR__, 2).'/config/bootstrap.php';
