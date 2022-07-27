<?php

$multiClient = (getenv('MULTI_CLIENT') === 'true') ?: false;

if ($multiClient === true) {

   if (!is_dir('/env')) {
      throw new RuntimeException('env dir does not exists');
   }
   if ((PHP_SAPI === 'cli' OR defined('STDIN'))) {
      $hostName = null;
      foreach ($_SERVER['argv'] as $arg) {
         if (preg_match('/^--client=(.*)$/', $arg, $matches)) {
            $hostName = $matches[1];
            break;
         }
      }
   } else {
      $hostName = $_SERVER['HTTP_X_HOST'] ?? $_SERVER['HTTP_HOST'];
   }

   if (empty($hostName)) {
      throw new RuntimeException('hostName is undefined. hostName can be specified by HOST, or X-Host header, or by --client=<host> when using cli');
   }

   if (!is_dir("/env/{$hostName}")) {
      http_response_code(404);
      echo 'Not Found.';
      exit(1);
   }

   $variablesDir = "/env/{$hostName}/";
   $variables = array_diff(scandir($variablesDir), array('..', '.'));
   foreach ($variables as $variable) {
      $path = $variablesDir . $variable;
      if (!is_file($path)) {
         continue;
      }
      $envName = $variable;
      $envValue = file_get_contents($variablesDir . $variable);
      $_ENV[$envName] = $envValue;
   }
}
