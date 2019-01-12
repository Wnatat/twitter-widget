<?php
  require __DIR__ . '/app/Container/App.php';

  use TwitterWidget\Container\App;

  $app = new App();

  $output = $app->run();

  echo $output;
  
  exit(0);
?>