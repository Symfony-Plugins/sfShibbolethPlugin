<?php

/*
 *
 * Automatically log users into Symfony if they have
 * a REMOTE_USER (Shibboleth). When app_sfShibboleth_fake is set,
 * accepts simulated REMOTE_USER set by the sfShibbolethAuth/login action.
 * This wrapper is done at as low a level as possible to ensure that
 * the "fake" mode exercises all of the "real" code.
 *
 * If registration is necessary, see to it that they register,
 * then bring them back to the same action with the same parameters.
 *
 * Support is provided for fetching display names (full names)
 * from Shibboleth, if Shibboleth is set up to provide them. 
 * Specifically, this filter will set the 'display_name' attribute
 * of the sfUser object, which you should utilize when creating a
 * brand-new sfGuardUserProfile object. This filter will also
 * automatically update the sfGuardUserProfile object if it already
 * exists and the user's display name has changed.
 *
 */
class sfShibbolethFilter extends sfFilter
{
  private static $testNames = array("noshibsuperadmin", "noshibadmin", "noshibeditor1", "noshibeditor2", "noshibnormal");
  public function execute ($filterChain)
  {
		$context = $this->getContext();
    $sfUser = $context->getUser();
		$request = $context->getRequest();
   
    if (sfConfig::get('app_sfShibboleth_fake', false)) 
    {
      // Accept the fake shibboleth attributes and stuff them into the
      // environment so that the rest of the Shibboleth-related code is used
      // normally. This way we don't have to debug everything twice.
      $_SERVER['REMOTE_USER'] = 
        $sfUser->getAttribute('sfShibboleth_fake_user', null);
      $_SERVER['HTTP_SHIB_INETORGPERSON_DISPLAYNAME'] = 
        $sfUser->getAttribute('sfShibboleth_fake_display_name', null);
      // If we let these linger they will screw up logout
      $sfUser->setAttribute('sfShibboleth_fake_user', null);
      $sfUser->setAttribute('sfShibboleth_fake_display_name', null);
    }

    if (!sfConfig::get('app_sfShibboleth_fake', false))
    {
      // I considered just rejecting the noshib prefix, but that is actually a somewhat common name
      if (in_array($_SERVER['REMOTE_USER'], self::$testNames))
      {
        throw new sfException("Attempt to log in with a noshib test account in a Shibbolized environment");
      } 
    }

    if (sfConfig::get('app_sfShibboleth_shim', false))
    {
      if (isset($_SESSION['sfShibboleth_shim_user']))
      {
        // user attributes from the subfolder shim script. This allows the
        // use of Shibboleth to protect a variety of sensitive activities 
        // throughout the site without the need to apply the Shibboleth 
        // login prompt to the entire site.
        $_SERVER['REMOTE_USER'] = 
          $_SESSION['sfShibboleth_shim_user'];
        $_SERVER['HTTP_SHIB_INETORGPERSON_DISPLAYNAME'] = 
          $_SESSION['sfShibboleth_shim_display_name'];
        // If we let these linger they will screw up logout
        unset($_SESSION['sfShibboleth_shim_user']);
        unset($_SESSION['sfShibboleth_shim_display_name']);
      }
    }

    if (!$sfUser->isAuthenticated())
    {
			/*
			 * We need to check for a valid username in the REMOTE_USER variable.
       * They may not have a local account yet. If not, we create one.
       *
       * Whether or not they have a local account, they still might need
       * to spend some time on the registration page providing information
       * we're not getting from Shibboleth, such as their preferred
       * email address and whatever else you consider essential. So we
       * check for that condition and force them over to the registration
       * action if need be, then allow them to continue to the action
       * they were originally trying to access.
			 */
			if (isset($_SERVER['HTTP_SHIB_INETORGPERSON_DISPLAYNAME'])) 
      {
        $sfUser->setAttribute('sfShibboleth_display_name', 
          $_SERVER['HTTP_SHIB_INETORGPERSON_DISPLAYNAME']);
      }
			if (isset($_SERVER['REMOTE_USER'])) 
      {
        $name = $_SERVER['REMOTE_USER'];

        // For backwards and forwards compatibility, we accept
        // REMOTE_USER with or without a domain on the end, and
        // check for an sfGuard username with or without a domain.
        // on the end. Of course, if REMOTE_USER actually does
        // specify a domain other than the default domain, 
        // then only a full match will do. 
        
        // The default domain is set in settings.yml.

        // The pretty case: an exact match

        $args = array();
        $where = 'username = ?';
        $args[] = $name;

        $domain = sfConfig::get("app_sfShibboleth_domain", false);
        if ($domain !== false) {
          // Forward compatibility: REMOTE_USER contains the default domain, 
          // so also check for database entries with no domain
          if (preg_match("/^(.*?)@$domain/i", $name, $matches)) {
            $where .= ' or username = ?';
            $args[] = $matches[1];
          } 
          // Backward compatibility: REMOTE_USER contains no domain, 
          // so also check for database entries with the default domain
          if (!preg_match("/@/", $name)) {
            $where .= ' or username = ?';
            $args[] = "$name@$domain";
          }
        }
        $query = new Doctrine_Query();
        $user = $query->from('sfGuardUser u')->
          where($where, $args)->fetchOne();
        if (!$user)
        {
          // Make a new user here
          $user = new sfGuardUser();
          $user->setUserName($name);
          // Set a secure, random sfGuard password. This is never used,
          // but if the user somehow misconfigures this plugin and
          // activates the sfGuardAuth module, we should have passwords
          // in there to prevent a security disaster
          $user->setPassword(sfShibbolethTools::generatePassword());
          $user->save();

					if (class_exists('sfGuardUserProfile'))
					{
	          $profile = new sfGuardUserProfile();
	          if ($sfUser->hasAttribute('sfShibboleth_display_name')) 
	          {
	            $profile->setDisplayName($sfUser->getAttribute('sfShibboleth_display_name'));
	          }
	          $profile->setUserId($user->getId());
	          $profile->save();
					}
        }
        if (sfConfig::get('app_sfShibboleth_superadmin', false) === $name)
        {
          $user->setIsSuperAdmin(true);
        }
        $sfUser->signIn($user);

				if (class_exists('sfGuardUserProfile'))
				{
        $profile = $sfUser->getProfile();
        // Dynamically update display name if it has changed
        if ($sfUser->hasAttribute('sfShibboleth_display_name') && $profile &&
          ($profile->getDisplayName() !== 
            $sfUser->getAttribute('sfShibboleth_display_name')))
        {
          $profile->setDisplayName($sfUser->getAttribute('sfShibboleth_display_name'));
          $profile->save();
        }
				}
			}
		}
    if ($sfUser->isAuthenticated())
    {
      // The user exists and is signed in. But is their profile complete 
      // enough for this site's needs? If not, force the user to the
      // registration action.

      // If you have a registration action, you must also have a
      // registrationIsComplete method in your myUser class that returns
      // false when the registration action is necessary.

      $action = sfConfig::get('app_sfShibboleth_register_action', false);
      if ($action && (!$sfUser->registrationIsComplete())) {
        $currentAction = $request->getParameter('module') . '/' . 
          $request->getParameter('action');
        $exemptActions = sfConfig::get('app_sfShibboleth_register_exempt', array());
        // Always allow them to give up
        $exemptActions[] = "sfShibbolethAuth/logout";
        $exemptActions[] = $action;
        if (!in_array($currentAction, $exemptActions))
        {
          // Remember where we parked, dear. This makes it possible to
          // bring the user back to where they were before they
          // began the registration process.
          $sfUser->setAttribute('sfShibboleth_register_after',
            $request->getUri());
          $controller = $context->getController();
          $action = $controller->genUrl($action, true);
          return $context->getController()->redirect($action);
        } else {
          // We are already in the register action or an action
          // that is exempt. Don't get stuck in an infinite loop
          // in the register action or refuse to generate a CAPTCHA etc.
          $filterChain->execute($filterChain);
          return;
        }
      }
    }
    $filterChain->execute($filterChain);
	}
}
