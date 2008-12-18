<?php

class sfShibbolethTools
{
  public static function redirectAfterRegister()
  {
    $sfUser = sfContext::getInstance()->getUser(); 
    // @homepage, not /, is the most portable default.
    // Also: always sfShibboleth, never sf_shibboleth.
    $registerAfter = $sfUser->getAttribute('sfShibboleth_register_after', '@homepage');
    $sfUser->setAttribute('sfShibboleth_register_after', null);
    return self::redirectRouted($registerAfter);
  }

  // 0.2: oops, this was missing, imported from one of our private plugins
  private static function redirectRouted($url)
  {
    $controller = sfContext::getInstance()->getController();
    $url = $controller->genUrl($url);
    return $controller->redirect($url);
  }

  public static function generatePassword()
  {
    // Random 8-letter password for protection in the event 
    // that sfGuardAuth is somehow enabled
    $p = '';
    for ($i = 0; ($i < 8); $i++) {
      $p .= sprintf("%c", rand(ord('a'), ord('z')));
    }
    return $p;
  }
}

