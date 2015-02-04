<?php
// A form to edit site configuration.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

define('WT_SCRIPT_NAME', 'admin_site_config.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$controller=new WT_Controller_Page();
$controller
	->requireAdminLogin()
	->addExternalJavascript(WT_JQUERY_JEDITABLE_URL)
	->addInlineJavascript('jQuery("#tabs").tabs();')
	->setPageTitle(WT_I18N::translate('Site configuration'))
	->pageHeader();

// Lists of options for <select> controls.
$SMTP_SSL_OPTIONS = array(
	'none'=>WT_I18N::translate('none'),
	/* I18N: Secure Sockets Layer - a secure communications protocol*/ 'ssl'=>WT_I18N::translate('ssl'),
	/* I18N: Transport Layer Security - a secure communications protocol */ 'tls'=>WT_I18N::translate('tls'),
);
$SMTP_ACTIVE_OPTIONS = array(
	'internal'=>WT_I18N::translate('Use PHP mail to send messages'),
	'external'=>WT_I18N::translate('Use SMTP to send messages'),
);
$WELCOME_TEXT_AUTH_MODE_OPTIONS = array(
	0 => WT_I18N::translate('No predefined text'),
	1 => WT_I18N::translate('Predefined text that states all users can request a user account'),
	2 => WT_I18N::translate('Predefined text that states admin will decide on each request for a user account'),
	3 => WT_I18N::translate('Predefined text that states only family members can request a user account'),
	4 => WT_I18N::translate('Choose user defined welcome text typed below'),
);

?>
<div id="site-config">
	<div id="tabs">
		<ul>
			<li>
				<a href="#site"><span><?php echo WT_I18N::translate('Site configuration'); ?></span></a>
			</li>
			<li>
				<a href="#mail"><span><?php echo WT_I18N::translate('Mail configuration'); ?></span></a>
			</li>
			<li>
				<a href="#login"><span><?php echo WT_I18N::translate('Login'); ?></span></a>
			</li>
		</ul>
		<div id="site">
			<table>
				<tr>
					<td><?php echo WT_I18N::translate('Data folder'), help_link('INDEX_DIRECTORY'); ?></td>
					<td><?php echo edit_field_inline('site_setting-INDEX_DIRECTORY', WT_Site::preference('INDEX_DIRECTORY'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Memory limit'), help_link('MEMORY_LIMIT'); ?></td>
					<td><?php echo edit_field_inline('site_setting-MEMORY_LIMIT', WT_Site::preference('MEMORY_LIMIT'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('PHP time limit'), help_link('MAX_EXECUTION_TIME'); ?></td>
					<td><?php echo edit_field_inline('site_setting-MAX_EXECUTION_TIME', WT_Site::preference('MAX_EXECUTION_TIME'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Allow visitors to request account registration'), help_link('USE_REGISTRATION_MODULE'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-USE_REGISTRATION_MODULE', WT_Site::preference('USE_REGISTRATION_MODULE'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Require an administrator to approve new user registrations'), help_link('REQUIRE_ADMIN_AUTH_REGISTRATION'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-REQUIRE_ADMIN_AUTH_REGISTRATION', WT_Site::preference('REQUIRE_ADMIN_AUTH_REGISTRATION'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Show list of family trees'), help_link('ALLOW_CHANGE_GEDCOM'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-ALLOW_CHANGE_GEDCOM', WT_Site::preference('ALLOW_CHANGE_GEDCOM'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Session timeout'), help_link('SESSION_TIME'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SESSION_TIME', WT_Site::preference('SESSION_TIME'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Website URL'), help_link('SERVER_URL'); ?></td>
					<td><?php echo select_edit_control_inline('site_setting-SERVER_URL', array(WT_SERVER_NAME.WT_SCRIPT_PATH=>WT_SERVER_NAME.WT_SCRIPT_PATH), '', WT_Site::preference('SERVER_URL'), $controller); ?></td>
				</tr>
			</table>
		</div>
		<div id="mail">
			<table>
				<tr>					
					<td><?php echo WT_I18N::translate('Messages'), help_link('SMTP_ACTIVE'); ?></td>
					<td><?php echo select_edit_control_inline('site_setting-SMTP_ACTIVE', $SMTP_ACTIVE_OPTIONS, null, WT_Site::preference('SMTP_ACTIVE'), $controller); ?></td>
				</tr>
				<tr>					
					<td><?php echo WT_I18N::translate('Send mail in HTML format'), help_link('MAIL_FORMAT'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-MAIL_FORMAT', WT_Site::preference('MAIL_FORMAT'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Sender email'), help_link('SMTP_FROM_NAME'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_FROM_NAME', WT_Site::preference('SMTP_FROM_NAME'), $controller); ?></td>						
				</tr>
				<tr></tr>
				<tr>
					<th colspan="2" class="smtp">
						<?php echo WT_I18N::translate('SMTP mail server'); ?>
					</th>
				</tr>
				<tr>					
					<td><?php echo WT_I18N::translate('Server name'), help_link('SMTP_HOST'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_HOST', WT_Site::preference('SMTP_HOST'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Port number'), help_link('SMTP_PORT'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_PORT', WT_Site::preference('SMTP_PORT'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Use password'), help_link('SMTP_AUTH'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-SMTP_AUTH', WT_Site::preference('SMTP_AUTH'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Username'), help_link('SMTP_AUTH_USER'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_AUTH_USER', WT_Site::preference('SMTP_AUTH_USER'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Password'), help_link('SMTP_AUTH_PASS'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_AUTH_PASS', '' /* Don't show password.  save.php has special code for this. */, $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Secure connection'), help_link('SMTP_SSL'); ?></td>
					<td><?php echo select_edit_control_inline('site_setting-SMTP_SSL', $SMTP_SSL_OPTIONS, null, WT_Site::preference('SMTP_SSL'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Sending server name'), help_link('SMTP_HELO'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_HELO', WT_Site::preference('SMTP_HELO'), $controller); ?></td>
				</tr>
			</table>
			<p>
				<?php echo WT_I18N::translate('To use a Google mail account, use the following settings: server=smtp.gmail.com, port=587, security=tls, username=xxxxx@gmail.com, password=[your gmail password]'); ?>
			</p>
		</div>
		<div id="login">
			<table>
				<tr>
					<td><?php echo WT_I18N::translate('Login URL'), help_link('LOGIN_URL'); ?></td>
					<td><?php echo edit_field_inline('site_setting-LOGIN_URL', WT_Site::preference('LOGIN_URL'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Standard header for custom welcome text'), help_link('WELCOME_TEXT_CUST_HEAD'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-WELCOME_TEXT_CUST_HEAD', WT_Site::preference('WELCOME_TEXT_CUST_HEAD'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Welcome text on login page'), help_link('WELCOME_TEXT_AUTH_MODE'); ?></td>
					<td><?php echo select_edit_control_inline('site_setting-WELCOME_TEXT_AUTH_MODE', $WELCOME_TEXT_AUTH_MODE_OPTIONS, null, WT_Site::preference('WELCOME_TEXT_AUTH_MODE'), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Custom welcome text'), help_link('WELCOME_TEXT_AUTH_MODE_CUST'); ?></td>
					<td><?php echo edit_text_inline('site_setting-WELCOME_TEXT_AUTH_MODE_4', WT_Site::preference('WELCOME_TEXT_AUTH_MODE_'.WT_LOCALE), $controller); ?></td>
				</tr>
				<tr>
					<td><?php echo WT_I18N::translate('Show acceptable use agreement on «Request new user account» page'), help_link('SHOW_REGISTER_CAUTION'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-SHOW_REGISTER_CAUTION', WT_Site::preference('SHOW_REGISTER_CAUTION'), $controller); ?></td>
				</tr>
			</table>
		</div>
	</div>
</div>
