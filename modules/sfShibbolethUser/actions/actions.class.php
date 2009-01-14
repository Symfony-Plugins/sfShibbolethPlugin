<?php

require_once dirname(__FILE__).'/../lib/BasesfShibbolethUserActions.class.php';
require_once dirname(__FILE__).'/../lib/sfShibbolethUserGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sfShibbolethUserGeneratorHelper.class.php';

/**
 * sfShibbolethUser actions.
 *
 * @package    sfShibbolethPlugin
 * @subpackage sfShibbolethUser
 * @author     Fabien Potencier
 * @version    SVN: $Id: actions.class.php 12896 2008-11-10 19:02:34Z fabien $
 */
class sfShibbolethUserActions extends BasesfShibbolethUserActions
{
  protected function processForm(sfWebRequest $request, sfForm $form)
  {
		$fields = $request->getParameter($form->getName());
		$fields['password'] = sfShibbolethTools::generatePassword();
		$fields['password_again'] = $fields['password'];
		$request->setParameter($form->getName(), $fields);

		parent::processForm($request, $form);
	}
}
