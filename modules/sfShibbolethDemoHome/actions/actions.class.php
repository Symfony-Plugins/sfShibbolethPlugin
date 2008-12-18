<?php

class sfShibbolethDemoHomeActions extends sfActions
{
  // Demo of how to use the shibboleth module's services and also
  // do application-specific stuff before and after. It doesn't really
  // make sense to enable this module as-is, but copying and
  // pasting it to your own application's modules folder and using it
  // as a starting point could be a fine plan.

  public function executeIndex()
  {
    $this->user = $this->getUser();
  }

  public function executeLogin()
  {
    $sfUser = $this->getUser();
    // Explicit login by user's choice. If we see an 'after' parameter
    // submitted, redirect there after login, otherwise the referring
    // URL, otherwise the home page. (Added referring URL support
    // to this sample code in 0.3)
    $after = $this->getRequestParameter('after', 
      $this->getRequest()->getReferer());
    if (!strlen($after))
    {
      $after = '@homepage';
    }
    $sfUser->setAttribute('sfShibboleth_after', $after);
    return $this->redirect('sfShibbolethAuth/login');
  }

  public function executeLogout()
  {
    // We could do extra signout stuff here, if we wanted
    return $this->redirect('sfShibbolethAuth/logout');
  }

  public function executeRegister()
  {
    if ($this->getRequest()->getMethod() === sfRequest::POST) {
      $sfUser = $this->getUser();
      $profile = $sfUser->getProfile();
      $profile->setEmail($this->getRequestParameter('email'));
      $profile->save();

      // sfShibbolethTools, not shibbolethTools. I was getting a stale
      // version from an earlier incarnation in my test app, so I missed it.
      return sfShibbolethTools::redirectAfterRegister();
      // Redundant 'return' line here removed
    }

    // Accepting an 'after' QUERY_STRING parameter is smart when coding a 
    // 'settings' button that should return to the page of origin 
    // (see indexSuccess.php). Just set the sfShibboleth_register_after
    // attribute to the Symfony path the user should wind up at when they 
    // have completed registration. In cases where the user is forced
    // to this action (because they have logged in but are not yet
    // registered locally with this application), this will already be
    // set to the URL they were trying to access before they logged in.
    // So override it only if 'after' is actually present.

    // See the indexSuccess.php template for an example of how to
    // generate a 'settings' link that sets 'after'.

    if ($this->hasRequestParameter('after')) {
      $this->getUser()->setAttribute('sfShibboleth_register_after',
        $this->getRequestParameter('after'));
    }

    // For consistency we set all of the stuff the template needs
    // in handleErrorRegister and use it in both the initial
    // case (this one) and the fillin helper / form_error case.
    return $this->handleErrorRegister();
  }

  public function handleErrorRegister()
  {
    // Used to redisplay the current email address in the template.
    $this->profile = $this->getUser()->getProfile();
    return sfView::SUCCESS;
  }

  public function executeSecure()
  {
  }
}
