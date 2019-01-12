<?php

namespace TwitterWidget\Http\Auth;

class OAuth
{
    /**
     * The Twitter access token
     * 
     * @var string
     */
    private $oauth_access_token;

    /**
     * The Twitter token secret
     * 
     * @var string
     */
    private $oauth_access_token_secret;

    /**
     * The Twitter consumer key
     * 
     * @var string
     */
    private $consumer_key;

    /**
     * The Twitter consumer secret
     * 
     * @var string
     */
    private $consumer_secret;

    /**
     * The Twitter access token
     * 
     * @var string
     */
    private $nonce = null;

    /**
     * The timestamp
     * 
     * @var string
     */
    private $timestamp = null;

    public function __construct(
        $oauth_access_token,
        $oauth_access_token_secret,
        $consumer_key,
        $consumer_secret)
    {
        $this->oauth_access_token = $oauth_access_token;
        $this->oauth_access_token_secret = $oauth_access_token_secret;
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->nonce = $this->nonce();
        $this->timestamp = time();
    }

    /**
     * Calculate the signature.
     *
     * @var string $method The request method.
     * @var string $url The request url.
     * @var array $params The request query parameters.
     * @return string
     */
    public function sign(string $method, string $url, array $params): array
    {
        $params = $this->buildParams([]) + [
          'oauth_signature' => $this->signature($method, $url, $params)
        ];

        ksort($params);

        return $params;
    }

    /**
     * Generate authorization header.
     *
     * @var string $method The request method.
     * @var string $url The request url.
     * @var array $params The request query parameters.
     * @return array
     */
    public function authorizationHeader(string $method, string $url, array $params): array
    {
        $oauth = $this->sign($method, $url, $params);

        return [
            'Authorization: OAuth ' .
            implode(', ', array_map(function($value, $key) {
                return "$key=\"" . rawurlencode($value) . "\""; 
            }, $oauth, array_keys($oauth)))
        ];
    }

    /**
     * Calculate the signature.
     *
     * @var string $method The request method.
     * @var string $url The request url.
     * @var array $params The request query parameters.
     * @return string
     */
    private function signature(string $method, string $url, array $params): string
    {
        return base64_encode(hash_hmac(
            'sha1',
            $this->baseString($method, $url, $params),
            $this->signingKey(),
            true
        ));
    }

    /**
     * Generate a base string from url method and parameters.
     *
     * @var string $method The request method.
     * @var string $url The request url.
     * @var array $params The request query parameters.
     * @return string
     */
    private function baseString(string $method, string $url, array $params): string
    {
      return strtoupper($method) . '&' . 
             rawurlencode($url) . '&' .
             rawurlencode($this->parameters($params));
    }

    /**
     * Generate parameters string.
     *
     * @var array $params The request query parameters.
     * @return string
     */
    private function parameters(array $params): string
    {
        $params = $this->buildParams($params);

        $encoded_params = array_map(function($value, $key) {
          return rawurlencode($key) . "=" . rawurlencode($value);
        }, $params, array_keys($params));

        return implode('&', $encoded_params);
    }

    /**
     * Generate oauth parameters.
     *
     * @var array $params The request query parameters.
     * @return array
     */
    private function buildParams(array $params): array
    {
        $all_params = array_merge([
            'oauth_consumer_key' =>  $this->consumer_key,
            'oauth_nonce' => $this->nonce,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => $this->timestamp,
            'oauth_token' => $this->oauth_access_token,
            'oauth_version' => '1.0'
        ], $params);

        ksort($all_params);

        return $all_params;
    }

    /**
     * Generate a nonce.
     *
     * @return string
     */
    private function nonce(): string
    {
        return preg_replace(
            '/[^\w]+/', 
            '', 
            base64_encode(random_bytes(32))
        );
    }

    /**
     * Generate a signing key.
     *
     * @return string
     */
    private function signingKey(): string
    {
      return rawurlencode($this->consumer_secret) .
             '&' . 
             rawurlencode($this->oauth_access_token_secret);
    }
}
