<?php

require __DIR__ . '/../vendor/autoload.php';

(new MyApp\App(require('../config/config.php')))->run();
die();
