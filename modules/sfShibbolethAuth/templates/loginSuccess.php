<?php
  // Never seen in staging/production, which use
  // real Shibboleth logins. TBB

  use_helper('Form');
?>
<h2>
Please Select a Test User
</h2>
<p>
In a production controller, you won't see this form. Note that
if you are seeing this on a production server you must immediately
set <tt>app_shibboleth_fake</tt> to <tt>false</tt> in app.yml to
preserve the security of your site!
</p>
<?php echo form_tag('sfShibbolethAuth/login') ?> 
<?php echo select_tag('fake_user',
  options_for_select($options, false)) ?>
<?php echo submit_tag('Go') ?>
</form>
