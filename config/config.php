<?php

if (sfConfig::get('app_sf_shibboleth_plugin_routes_register', true) && in_array('sfShibbolethAuth', sfConfig::get('sf_enabled_modules', array())))
{
  $this->dispatcher->connect('routing.load_configuration', array('sfShibbolethRouting', 'listenToRoutingLoadConfigurationEvent'));
}

if (sfConfig::get('app_sf_shibboleth_plugin_routes_register', true) && in_array('sfShibbolethUser', sfConfig::get('sf_enabled_modules')))
{
  $this->dispatcher->connect('routing.load_configuration', array('sfShibbolethRouting', 'addRouteForAdminUser'));
}
