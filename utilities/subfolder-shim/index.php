<?php

# Run the regular back end controller after convincing
# Symfony that it should run the sfShibbolethAuth login
# action for us. Note that we're making an assumption
# that you haven't changed the routing rule. 

# Clean the ever-loving daylights out of the environment so that
# the front end controller will issue correct redirects

foreach ($_SERVER as $key => &$val)
{
  $val = preg_replace("/\/shibboleth(\/index.php|\/|)$/", "/", $val); 
}

# Run the correct action (note that your routing rules probably
# vary from ours so you will probably need to change this)

$_SERVER['REQUEST_URI'] = "/cms/sfShibbolethAuth/login";
$_SERVER['PHP_SELF'] = "/cms/sfShibbolethAuth/login";

require '../index.php';

