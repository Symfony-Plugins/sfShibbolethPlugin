<?php

/**
 * Shibboleth support module.
 *
 * This module's job is simply to (a) simulate Shibboleth well enough 
 * to exercise the Shibboleth filter, and (b) in real production
 * with real Shibboleth, act as a landing point that redirects to
 * the more interesting URL of your choice after login.
 *
 * The principle here: in production Shibboleth should be configured to protect
 * this module's URL (via a <Location> block).
 * 
 * If login_on_secure is true (which is typical in production environments,
 * because Shibboleth usually isn't set up to protect non-secure pages), 
 * attempts to reach executeLogin or executeLogout via an http URL get 
 * kicked over to the https version of the same URL.
 *
 * This is NOT the place to call sfGuard's signIn. That logic belongs in the
 * Shibboleth filter.
 *
 */
class sfShibbolethAuthActions extends sfActions
{
  /**
   * Executes login action
   *
   */
  public function executeLogin()
  {
    if ($this->enforceLoginOnSecure() !== false)
    {
      return;
    }

    $sfUser = $this->getUser();
    // In production the only job of this action is to get shibbolized and
    // then redirect somewhere else. If you wish, use the 'shibboleth_after'
    // attribute to specify a 'somewhere' other than the home page.

    // In development, with shibboleth_fake set to true,
    // this action lets the developer pick one of a number of
    // test users in a manner that exercises the code in 
    // the shibboleth filter just as much as real Shibboleth would.

    if (sfConfig::get('app_sfShibboleth_fake', false)) {
      // Let them pick a fake user
      $fakeUsers = sfConfig::get('app_sfShibboleth_fake_users', false);
      if (!$fakeUsers) {
        return $this->forward404();
      }
      $this->options = array();
      foreach ($fakeUsers as $id => $data) 
      {
        $this->options[$id] = $data['display_name'];
      }
      if ($this->hasRequestParameter('fake_user')) {
        $fakeUser = $this->getRequestParameter('fake_user'); 
        if (!isset($fakeUsers[$fakeUser])) {
          return $this->forward404();
        }
        $fakeDisplayName = $fakeUsers[$fakeUser]['display_name'];
        $sfUser->setAttribute('sfShibboleth_fake_user', $fakeUser);
        $sfUser->setAttribute('sfShibboleth_fake_display_name', $fakeDisplayName);
      } else {
        // Display the fake user picker
        return sfView::SUCCESS;
      }
    } else {
      if (!isset($_SERVER['REMOTE_USER'])) 
      {
        $shim = sfConfig::get('app_sfShibboleth_shim', false);
        if ($shim)
        {
          $shim = str_replace("_HTTPHOST_", $_SERVER['HTTP_HOST'], $shim);
          return $this->redirect($shim);
        }
        else
        {
          return 'Misconfigured';
        }
      }
    }
    // DON'T try to set this to the referrer here. In a true
    // Shibboleth environment, a redirect to Shibboleth will
    // already have ruined that option. See sfShibbolethDemoHome for an
    // example of how to correctly set this attribute in 
    // YOUR OWN login action which then redirects to this action.
    $after = $sfUser->getAttribute('sfShibboleth_after', '@homepage');
    $sfUser->setAttribute('sfShibboleth_after', null);
    return $this->redirect($after);
  }
  // This action signs the user out of Symfony, and then out of
  // Shibboleth as well. In production the latter is done by redirecting
  // to the Shibboleth logout URL. If your Apache configuration uses
  // a different logout URL, you'll need to make the appropriate change
  // in app.yml. 

  // In development this action purges the attributes we use for fake 
  // shibboleth auth first, then goes to the home page. Keep in mind
  // that typical shibboleth webauth systems unfortunately do NOT send you
  // home, they just dump you on a useless external "goodbye" page somewhere.
  // But sending users home in dev is a good test of whether the Symfony-layer
  // signout worked properly.

  public function executeLogout()
  {
    if ($this->enforceLoginOnSecure() !== false)
    {
      return;
    }
    $sfUser = $this->getUser();
    if ($sfUser) {
      $sfUser->signOut();
    }
    if (!sfConfig::get('app_sfShibboleth_fake', false)) {
      $returnTo = $this->getController()->genUrl("@homepage", true);
      $to = sfConfig::get('app_sfShibboleth_logout', 
        $this->getRequest()->getUriPrefix() . '/Shibboleth.sso/Logout?returnto=_RETURNTO_');
      $to = str_replace(array("_RETURNTO_", "_HTTPHOST_"), array(urlencode($returnTo), $_SERVER['HTTP_HOST']), $to);
      return $this->redirect($to);
    }
    $sfUser = $this->getUser();
    $sfUser->setAttribute('sf_shibboleth_fake_user', null);
    $sfUser->setAttribute('sf_shibboleth_fake_display_name', null);
    // @homepage works, / doesn't (at least not in all routing setups) 
    return $this->redirect('@homepage');
  }

  private function enforceLoginOnSecure()
  {
    if (sfConfig::get('app_sfShibboleth_login_on_secure', false))
    {
      $request = $this->getRequest();
      if (!$request->isSecure())
      {
				$controller = sfContext::getInstance()->getController();
				$url = $controller->genUrl("sfShibbolethAuth/login", true);
				$url = preg_replace("/^http:/", "https:", $url);
        return $this->redirect($url);
      }
      return false;
    }
    else
    {
      return false;
    }
  }
}
