<?php

$basedir = dirname(__DIR__);
require("$basedir/vendor/autoload.php");

use OpenApi\Annotations as OA;
use function OpenApi\scan;

/**
 * @OA\Info(title="Elexp Conclusions API", version="1.0")
 * @OA\PathItem(path="/")
 */

$openapi = scan([
   "$basedir/public/docs.php",
   "$basedir/src/Domain",
   "$basedir/src/Http",
]);

//header('Content-Type: application/x-yaml');
header('Content-Type: application/json');
echo $openapi->toJson();
