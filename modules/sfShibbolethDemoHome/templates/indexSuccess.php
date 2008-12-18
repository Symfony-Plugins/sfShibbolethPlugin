<?php if ($user->isAuthenticated()): ?>
<p>You are authenticated as <?php echo $user->getUsername() ?></p>
<p>Your display name is <?php echo $user->getProfile()->getDisplayName() ?></p>
<p>Your email address is <?php echo $user->getProfile()->getEmail() ?></p>
<?php echo link_to("Log Out", "sfShibbolethDemoHome/logout") ?>
&nbsp;
<?php echo link_to("Settings", "sfShibbolethDemoHome/register", 
  array("query_string" => "after=sfShibbolethDemoHome/index")) ?>
<?php else: ?>
<p>You are not logged in.</p>
<p><?php echo link_to("Log In", "sfShibbolethDemoHome/login") ?></p>
<?php endif ?>
