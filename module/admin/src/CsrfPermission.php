<?php

namespace Hunter\admin;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Hunter\Core\CSRF\CSRF;

/**
 * Provides admin module permission auth.
 */
class CsrfPermission {

  /**
   * Returns bool value of admin permission.
   *
   * @return bool
   */
  public function handle(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    if ($parms = $request->getParsedBody()) {
      $token_uuid = $parms['_csrf_token_uuid'];
      $token = $parms['_csrf_token'];
      $csrf = new CSRF();
      if (!$csrf->validate($token_uuid, $token)) {
        http_response_code(403);
	 	    echo '403 Access Forbidden, bad CSRF token';
	 	    exit();
      }
    }
    return $next($request, $response);
  }

}
