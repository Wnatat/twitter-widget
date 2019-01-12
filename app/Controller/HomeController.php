<?php

namespace TwitterWidget\Controller;

require __DIR__ . '/../../app/Http/Response.php';

use TwitterWidget\Http\Client;
use TwitterWidget\Cache\Cache;
use TwitterWidget\Http\Response;

class HomeController
{

  /**
   * The http client to perform requests.
   *
   * @var Client
   */
  private $client;

  /**
   * HomeController constructor.
   *
   * @var Client The http client to perform requests.
   */
  public function __construct(Client $client)
  {
    $this->client = $client;
  }

  /**
   * Index action.
   *
   * @params array $params Request parameters.
   */
  public function index(array $params): string
  {
    $data = $this->client->get(
      'https://api.twitter.com/1.1/statuses/home_timeline.json', $params, [
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CAINFO => '/usr/local/etc/openssl/cert.pem',
        // CURLOPT_HEADER => true
    ]);

    return $this->render($data);
  }

  /**
   * Render api response.
   *
   * @params array $data The data to render.
   */
  private function render(array $data): string
  {
    $response = new Response($data);

    return $response->render();
  }
}

?>