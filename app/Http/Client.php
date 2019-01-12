<?php

namespace TwitterWidget\Http;

use TwitterWidget\Http\Auth\OAuth;

class Client
{
  /**
   * The default curl options
   * 
   * @var array
   */
  const OPTIONS = [
    CURLOPT_HEADER => false,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_RETURNTRANSFER => true,
    CURLINFO_HEADER_OUT => true,
    CURLOPT_SSL_VERIFYPEER => false
  ];

  /**
   * The curl resource descriptor
   * 
   * @var OAuth The OAuth authenticator
   */
  private $oauth;

  /**
   * The curl resource descriptor
   * 
   * @var Resource
   */
  private $curl = null;

  /**
   * @param OAuth $oauth OAuth Authorization header builder.
   */
  public function __construct(OAuth $oauth)
  {
    $this->oauth = $oauth;
    $this->curl = curl_init();
  }

  /**
   * Perform GET request.
   *
   * @param string $url The URL
   * @param array $params The request query parameters
   * @param array $options The request options
   * @return array|null
   */
  public function get(string $url, array $params, array $options): ?array
  {
    $this->setOptions($options, $url, $params, __FUNCTION__);

    $data = $this->execute();

    return json_decode($data);
  }

  /**
   * Set request options.
   *
   * @param array $options The request options
   * @param string $url The request url
   * @param string $method The request method
   * @return bool
   */
  private function setOptions(array $options, string $url, array $params, string $method): bool
  {
    $options = array_replace(self::OPTIONS, $options, [
      CURLOPT_URL => $url . '?' . http_build_query($params),
      CURLOPT_HTTPHEADER => $this->getHeaders($method, $url, $params)
    ]);

    return curl_setopt_array($this->curl, $options);
  }

  /**
   * Get request headers.
   *
   * @param string $method The request method
   * @param string $url The request url
   * @param array $params The request query parameters
   * @return array
   */
  private function getHeaders(string $method, string $url, array $params): array
  {
    $default_headers = [
      'Content-Type: application/x-www-form-urlencoded',
      'Expect:'
    ];

    return array_merge(
      $this->oauth->authorizationHeader($method, $url, $params),
      $default_headers
    );
  }

  /**
   * Execute the curl handler.
   *
   * @return string
   * @throws \Exception
   */
  private function execute(): string
  {
    $result = curl_exec($this->curl);

    $http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

    curl_close($this->curl);

    if($http_code > 299) {
      throw new \Exception($result, $http_code);
    }

    return $result;
  }
}
