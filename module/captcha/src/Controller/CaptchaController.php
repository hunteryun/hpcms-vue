<?php

namespace Hunter\captcha\Controller;

use Zend\Diactoros\ServerRequest;
use Gregwar\Captcha\PhraseBuilder;
use Gregwar\Captcha\CaptchaBuilder;

/**
 * Class Captcha.
 *
 * @package Hunter\captcha\Controller
 */
class CaptchaController {
  /**
   * make_captcha.
   *
   * @return string
   *   Return make_captcha string.
   */
  public function make_captcha(ServerRequest $request) {
    $phraseBuilder = new PhraseBuilder(4, 'abcdefghikmnpqrstuvwxyz2345678');
    $builder = new CaptchaBuilder(null, $phraseBuilder);
    $builder->build($width = 100, $height = 38);
    session()->set('_captcha', $builder->getPhrase());
    return $builder->output();
  }

}
