<?php

# Run the regular back end controller after convincing
# Symfony that it should run the sfShibbolethAuth login
# action for us. Note that we're making an assumption
# that you haven't changed the routing rule. 

$_SERVER['REQUEST_URI'] = "/cms/sfShibbolethAuth/login";

require '../index.php';

