<?php

namespace Cleantalk\Custom\Http;

use Cleantalk\Common\Http\Response;

class Request extends \Cleantalk\Common\Http\Request
{
  public function request()
  {
    if ( \Drupal::config('cleantalk.settings')->get('cleantalk_use_drupal_http_api') ) {
      if ( is_string($this->data) ) {
        $this->data = json_decode($this->data, true);
      }

      $this->appendOptionsObligatory();
      $this->processPresets();

      $this->response = is_array($this->url)
        ? $this->requestMany($this->url)
        : $this->requestSingle();

      return $this->runCallbacks();
    }
    return parent::request();
  }
  public function requestSingle()
  {
    if ( ! \Drupal::config('cleantalk.settings')->get('cleantalk_use_drupal_http_api') ) {
      return parent::requestSingle();
    }
    if ( strpos($this->url, 'moderate') !== false ) {
      $request_options = ['json' => $this->data];
    } else {
      $request_options = ['form_params' => $this->data];
    }

    try {
      if ( in_array('get', $this->presets) ) {
        $response = \Drupal::httpClient()->get($this->url, $request_options);
      } else {
        $response = \Drupal::httpClient()->post($this->url, $request_options);
      }
    }  catch ( \Throwable $e ) {
      return new Response(['error' => $e->getMessage()], []);
    }
    return new Response($response->getBody()->getContents(), ['http_code' => $response->getStatusCode()]);
  }
  public function requestMany($urls)
  {
    $responses = [];
    foreach ( $urls as $url ) {
      $this->url = $url;
      $responses[$url] = $this->requestSingle();
    }
    return $responses;
  }
}
