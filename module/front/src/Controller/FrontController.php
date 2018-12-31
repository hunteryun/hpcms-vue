<?php

namespace Hunter\front\Controller;

use Zend\Diactoros\ServerRequest;

/**
 * Class Front.
 *
 * @package Hunter\front\Controller
 */
class FrontController {
  /**
   * home.
   *
   * @return string
   *   Return home string.
   */
  public function home(ServerRequest $request) {
    $users = db_select('user', 'u')
      ->fields('u')
      ->execute()
      ->fetchAll();

    return view('/front/index.html', array('users' => $users));
  }

}
