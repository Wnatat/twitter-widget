<?php

namespace TwitterWidget\Container;

require __DIR__ . '/../../app/Http/Client.php';
require __DIR__ . '/../../app/Http/Auth/OAuth.php';
require __DIR__ . '/../../app/Controller/HomeController.php';

use TwitterWidget\Http\Auth\OAuth;
use TwitterWidget\Http\Client;
use TwitterWidget\Controller\HomeController;

class App {

  const CONFIGURATION_FILE = __DIR__ . '/../../.env';

  /**
   * Loaded dependencies.
   *
   *  @var array
   */
  private $bindings = [];

  /**
   * Application constructor.
   */
  public function __construct()
  {
    if(file_exists(self::CONFIGURATION_FILE)) {
      $this->loadConfiguration(self::CONFIGURATION_FILE);
    }

    $this->exceptionHandler();
  }

  /**
   * Run application.
   */
  public function run()
  {
    $this->registerDependencies();

    $reflector = new \ReflectionClass('TwitterWidget\Controller\HomeController');

    $dependencies = [];

    foreach($reflector->getConstructor()->getParameters() as $parameter) {
      $dependencies[] = $this->bindings[$parameter->getClass()->name];
    }

    $controller = new HomeController(...$dependencies);

    return $controller->index($_GET);
  }

  /**
   * Register a global exception handler.
   */
  public function exceptionHandler()
  {
    set_exception_handler(function(\Throwable $exception) {
      http_response_code($exception->getCode());

      header('Content-Type: application/json');

      echo $exception->getMessage();

      exit(1);
    });
  }

  /**
   * Loaded configuration from file.
   */
  private function loadConfiguration($path)
  {
    $envs = parse_ini_file($path);

    foreach($envs as $key => $env) {
      $_ENV[$key] = $env;
    }
  }

  /**
   * Build dependencies.
   */
  private function registerDependencies()
  {
    $oauth = new OAuth(
      $_ENV['OAUTH_ACCESS_TOKEN'],
      $_ENV['OAUTH_ACCESS_TOKEN_SECRET'],
      $_ENV['OAUTH_CONSUMER_KEY'],
      $_ENV['OAUTH_CONSUMER_SECRET']
    );

    $client = new Client($oauth);

    $this->bind($client);
  }

  /**
   * Bind a dependency to application.
   */
  private function bind($object)
  {
    $this->bindings[get_class($object)] = $object;
  }
}

?>