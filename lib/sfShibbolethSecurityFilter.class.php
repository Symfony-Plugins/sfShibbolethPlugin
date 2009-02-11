<?php

/*
 * Modified by TBB to save the path we were trying to get to before
 * we redirect away to another action. Capturing referrers later (the way
 * sfGuardAuth solves the problem) won't work if mod_shib is interposed.
 *
 * This file is derived from sfBasicSecurityFilter, which is
 * part of the symfony package. sfBasicSecurityFilter's terms:
 *
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfShibbolethSecurityFilter extends sfBasicSecurityFilter
{
  /**
   * Forwards the current request to the login action.
   *
   * @throws sfStopException
   */
  protected function forwardToLoginAction()
  {
	  // the user is not authenticated
	  // TBB: save where we wanted to go
    // ... But only if we haven't already done that s
    // (avoids redirect loop with shim)
    if (!$this->context->getUser()->hasAttribute('sfShibboleth_after'))
    {
      $this->context->getUser()->setAttribute('sfShibboleth_after', $this->context->getRequest()->getUri());
    }
	  $this->context->getController()->forward(sfConfig::get('sf_login_module'), sfConfig::get('sf_login_action'));

	  throw new sfStopException();
  }
}
