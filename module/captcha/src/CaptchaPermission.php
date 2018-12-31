<?php

namespace Hunter\captcha;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Provides captcha module permission auth.
 */
class CaptchaPermission {

  /**
   * Returns bool value of captcha permission.
   *
   * @return bool
   */
  public function handle(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    if ($parms = $request->getParsedBody()) {
      if(session()->get('_captcha') != $parms['_captcha']){
        if(isset($parms['reformat']) && $parms['reformat'] == 'notjson'){
          hunter_set_message(t('验证码错误'), 'error');
          if(isset($parms['redirect']) && !empty($parms['redirect'])){
            return redirect('/login?redirect='.$parms['redirect']);
          }
          return redirect('/login');
        }else {
          $response->getBody()->write(json_encode(array('code' => 1, 'msg' => '验证码错误')));
          return $response->withAddedHeader('content-type', 'application/json');
        }
      }
    }

    return $next($request, $response);
  }

}
