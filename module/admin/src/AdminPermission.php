<?php

namespace Hunter\admin;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Provides admin module permission auth.
 */
class AdminPermission {

  /**
   * Returns bool value of admin permission.
   *
   * @return bool
   */
  public function handle(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    if (!$session = session()->get('admin')) {
      return redirect('/admin/login');
    }else{
      return $next($request, $response);
    }
  }

}
