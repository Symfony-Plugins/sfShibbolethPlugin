<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Alex Gilbert <alex@punkave.com>
 * @version    SVN: $Id: sfShibbolethRouting.class.php 7636 2008-02-27 18:50:43Z fabien $
 */
class sfShibbolethRouting
{
  /**
   * Listens to the routing.load_configuration event.
   *
   * @param sfEvent An sfEvent instance
   */
  static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    $r = $event->getSubject();

    // preprend our routes
    $r->prependRoute('sf_shibboleth_signin', new sfRoute('/login', array('module' => 'sfShibbolethAuth', 'action' => 'login'))); 
   	$r->prependRoute('sf_shibboleth_signout', new sfRoute('/logout', array('module' => 'sfShibbolethAuth', 'action' => 'logout'))); 
  }

  static public function addRouteForAdminUser(sfEvent $event)
  {
    $event->getSubject()->prependRoute('sf_shibboleth_user', new sfDoctrineRouteCollection(array(
      'name'                => 'sf_shibboleth_user',
      'model'               => 'sfGuardUser',
      'module'              => 'sfShibbolethUser',
      'prefix_path'         => 'sf_shibboleth_user',
      'with_wildcard_routes' => true,
      'collection_actions'  => array('filter' => 'post', 'batch' => 'post'),
      'requirements'        => array(),
    )));
  }
}