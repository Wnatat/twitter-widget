<?php

namespace TwitterWidget\Http;

class Response
{
  public function __construct(
    array $data = [], 
    int $http_code = 200, 
    array $headers = [])
  {
    $this->http_code = $http_code;
    $this->data = $data;
    $this->headers = array_merge([ 'Content-Type: application/json' ], $headers);
  }

  public function render(): string
  {
    foreach($this->headers as $header) {
      header($header);
    }

    http_response_code($this->http_code);

    $response = $this->getResponse();

    return json_encode($response, JSON_PRETTY_PRINT);
  }

  private function getResponse(): array
  {
    return array_map(function($value) {
      return [
          'id' => $value->id,
          'text' => $value->text,
          'user' => [
            'name' => $value->user->name,
            'screen_name' => $value->user->screen_name,
            'url' => $value->user->url
          ],
          'medias' => [
            'videos' => $this->extractVideo($value),
            'images' => $this->extractImage($value)
          ]
        ];
      }, $this->data);
  }

  private function extractVideo($value)
  {
    if(isset($value->extended_entities)) {
      $media = $value->extended_entities->media;

      return array_map(function($media) { 
        return $media->video_info->variants[0];
      }, $media);
    }
    
    return [];
  }

  private function extractImage($value)
  {
    if(isset($value->entities->media)) {
      $media = $value->extended_entities->media;

      return array_map(function($media) { 
        return $media->media_url_https;
      }, $media);
    }
    
    return [];
  }

}

?>