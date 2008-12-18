<h2>Misconfigured</h2>
<p>
URLs prefixed with <tt>/sfShibbolethAuth</tt> must be protected by Shibboleth in order
to take advantage of this plugin correctly in a production environment.
</p>
<p>
In a development environment that is NOT on a production server, you can
set the <tt>app_sfShibboleth_fake</tt> option to <tt>true</tt> via
<tt>settings.yml</tt>, allowing this plugin to present you with a choice of
test accounts. Never do so on a production server.
</p>
