<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

// The shared configuration, re-pointed at the theme's source tree.
$shared = require __DIR__.'/vendor/iniznet/mahout-devtools/rector.php';

return $shared->withPaths([__DIR__.'/app']);
