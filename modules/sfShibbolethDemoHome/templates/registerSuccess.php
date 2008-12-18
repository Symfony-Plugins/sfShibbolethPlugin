<?php use_helper('Validation') ?>
<?php echo form_tag('sfShibbolethDemoHome/register') ?> 
<h2>Profile Settings</h2>
<p>
Thanks for logging in. In addition to the information you have already
provided, this site requires an email address in order to contact you.
</p>
<p>
<?php echo form_error('email') ?>
</p>
<p>
<b>Email Address:</b> 
<?php echo input_tag('email', $profile->getEmail()) ?>
<?php echo submit_tag('OK') ?>
</form>
<p>
<?php echo link_to("Log Out", "sfShibbolethDemoHome/logout") ?>
</p>
