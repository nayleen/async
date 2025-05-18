<?php

declare(strict_types = 1);

use Nayleen\Finder\Dumper;
use Nayleen\Finder\Engine\ComposerEngine;

require dirname(__DIR__) . '/vendor/autoload.php';

// dump Finder results
(new Dumper(new ComposerEngine()))->dump();
