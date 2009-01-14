<?php

class sfShibbolethUserAdminForm extends BasesfGuardUserAdminForm
{
  public function configure()
  {
		parent::configure();
		
    unset(
      $this['password'],
      $this['password_again']
    );
  }
}