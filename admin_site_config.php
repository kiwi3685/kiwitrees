<?php
// A form to edit site configuration.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
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

$controller = new WT_Controller_Page();
$controller
	->requireAdminLogin()
	->addExternalJavascript(WT_JQUERY_JEDITABLE_URL)
	->setPageTitle(WT_I18N::translate('Site configuration'))
	->pageHeader()
	->addInlineJavascript('jQuery("#tabs").tabs();');

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

if (WT_Filter::post('action') == 'languages') {
	WT_Site::preference('LANGUAGES', implode(',', WT_Filter::postArray('LANGUAGES')));
}

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
			<li>
				<a href="#lang"><span><?php echo WT_I18N::translate('Languages'); ?></span></a>
			</li>
		</ul>
		<!-- SITE CONFIG TAB -->
		<div id="site">
			<table>
				<!-- INDEX_DIRECTORY -->
				<tr>
					<td><?php echo WT_I18N::translate('Data folder'); ?></td>
					<td><?php echo edit_field_inline('site_setting-INDEX_DIRECTORY', WT_Site::preference('INDEX_DIRECTORY'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the "Data folder" site configuration setting */ WT_I18N::translate('This folder will be used by kiwitrees to store media files, GEDCOM files, temporary files, etc.  These files may contain private data, and should not be made available over the internet.'); ?>
								<a href="#" class="more accepted" style="display: block; font-weight: 600;"><?php echo /* I18N: Click this text to read or hide more information */ WT_I18N::translate('More / Less ....'); ?></a>
								<span class="hidden" style="display: none";>
									<?php echo /* I18N: “Apache” is a software program. */ WT_I18N::translate('To protect this private data, kiwitrees uses an Apache configuration file (.htaccess) which blocks all access to this folder.  If your web-server does not support .htaccess files, and you cannot restrict access to this folder, then you can select another folder, away from your web documents.'); ?>
									<br>
									<?php echo WT_I18N::translate('If you select a different folder, you must also move all files (except config.ini.php, index.php and .htaccess) from the existing folder to the new folder.'); ?>
									<br>
									<?php echo WT_I18N::translate('The folder can be specified in full (e.g. /home/user_name/kiwitrees_data/) or relative to the installation folder (e.g. ../../kiwitrees_data/).'); ?>
								</span>
							</span>
						</div>
					</td>
				</tr>
				<!-- MEMORY_LIMIT -->
				<tr>
					<td><?php echo WT_I18N::translate('Memory limit'); ?></td>
					<td><?php echo edit_field_inline('site_setting-MEMORY_LIMIT', WT_Site::preference('MEMORY_LIMIT'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php
								ini_restore('memory_limit');
								$dflt_mem = ini_get('memory_limit');
								echo /* I18N: %s is an amount of memory, such as 32MB */ WT_I18N::translate('By default, your server allows scripts to use %s of memory.', $dflt_mem); ?>
								<?php echo WT_I18N::translate('You can request a higher or lower limit, although the server may ignore this request.'); ?>
								<?php echo WT_I18N::translate('If you leave this setting empty, the default value will be used.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- MAX_EXECUTION_TIME -->
				<tr>
					<td><?php echo WT_I18N::translate('PHP time limit'); ?></td>
					<td> <?php echo edit_field_inline('site_setting-MAX_EXECUTION_TIME', WT_Site::preference('MAX_EXECUTION_TIME'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php
								ini_restore('max_execution_time');
								$dflt_cpu 	= ini_get('max_execution_time');
								echo WT_I18N::plural(
									'By default, your server allows scripts to run for %s second.',
									'By default, your server allows scripts to run for %s seconds.',
									$dflt_cpu, $dflt_cpu
								); ?>
								<?php echo WT_I18N::translate('You can request a higher or lower limit, although the server may ignore this request.'); ?>
								<?php echo WT_I18N::translate('If you leave this setting empty, the default value will be used.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- USE_REGISTRATION_MODULE -->
				<tr>
					<td><?php echo WT_I18N::translate('Allow visitors to request account registration'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-USE_REGISTRATION_MODULE', WT_Site::preference('USE_REGISTRATION_MODULE'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo WT_I18N::translate('Gives visitors the option of registering themselves for an account on the site. The visitor will receive an email message with a code to verify his application for an account. After verification, the Administrator will have to approve the registration before it becomes active.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- REQUIRE_ADMIN_AUTH_REGISTRATION -->
				<tr>
					<td><?php echo WT_I18N::translate('Require an administrator to approve new user registrations'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-REQUIRE_ADMIN_AUTH_REGISTRATION', WT_Site::preference('REQUIRE_ADMIN_AUTH_REGISTRATION'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo WT_I18N::translate('If the option <b>Allow visitors to request account registration</b> is enabled this setting controls whether the admin must approve the registration. Setting this to <b>Yes</b> will require that all new users first verify themselves and then be approved by an admin before they can login. With this setting on <b>No</b>, the <b>User approved by Admin</b> checkbox will be checked automatically when users verify their account, thus allowing an immediate login afterwards without admin intervention.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- ALLOW_CHANGE_GEDCOM -->
				<tr>
					<td><?php echo WT_I18N::translate('Show list of family trees'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-ALLOW_CHANGE_GEDCOM', WT_Site::preference('ALLOW_CHANGE_GEDCOM'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the “Show list of family trees” site configuration setting */ WT_I18N::translate('For sites with more than one family tree, this option will show the list of family trees in the main menu, the search pages, etc.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- SESSION_TIME -->
				<tr>
					<td><?php echo WT_I18N::translate('Session timeout'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SESSION_TIME', WT_Site::preference('SESSION_TIME'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the “Session timeout” site configuration setting */ WT_I18N::translate('The time in seconds that a kiwitrees session remains active before requiring a login.  The default is 7200, which is 2 hours.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- SERVER_URL -->
				<tr>
					<td><?php echo WT_I18N::translate('Website URL'); ?></td>
					<td><?php echo select_edit_control_inline('site_setting-SERVER_URL', array(WT_SERVER_NAME.WT_SCRIPT_PATH=>WT_SERVER_NAME.WT_SCRIPT_PATH), '', WT_Site::preference('SERVER_URL'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the "Website URL" site configuration setting */ WT_I18N::translate('If your site can be reached using more than one URL, such as <b>http://www.example.com/kiwitrees/</b> and <b>http://kiwitrees.example.com/</b>, you can specify the preferred URL.  Requests for the other URLs will be redirected to the preferred one.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- MAINTENANCE -->
				<tr>
					<td>
						<?php echo WT_I18N::translate('Maintenance'); ?>
					</td>
					<td><?php echo edit_field_yes_no_inline('site_setting-MAINTENANCE', WT_Site::preference('MAINTENANCE'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo WT_I18N::translate('Set this to <b>yes</b> to temporarily prevent anyone <u>except the site administrator</u> from accessing your site.'); ?>
							</span>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<!-- MAIL TAB -->
		<div id="mail">
			<table>
				<!-- SMTP_ACTIVE -->
				<tr>
					<td><?php echo WT_I18N::translate('Messages'); ?></td>
					<td><?php echo select_edit_control_inline('site_setting-SMTP_ACTIVE', $SMTP_ACTIVE_OPTIONS, null, WT_Site::preference('SMTP_ACTIVE'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the “Messages” site configuration setting */ WT_I18N::translate('Kiwitrees needs to send emails, such as password reminders and site notifications. To do this, it can use this server\'s built in PHP mail facility (which is not always available) or an external SMTP (mail-relay) service, for which you will need to provide the connection details.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- MAIL_FORMAT -->
				<tr>
					<td><?php echo WT_I18N::translate('Send mail in HTML format'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-MAIL_FORMAT', WT_Site::preference('MAIL_FORMAT'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the “Messages” site configuration setting */ WT_I18N::translate('By default kiwitrees sends emails in plain text format. Setting this option to <b>yes</b> will change that to the multipart format. This allows the use of HTML formatting, but also includes a plain text version for recipients that do not allow HTML formatted emails.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- SMTP_FROM_NAME -->
				<tr>
					<td><?php echo WT_I18N::translate('Sender email'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_FROM_NAME', WT_Site::preference('SMTP_FROM_NAME'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the “Sender name” site configuration setting */ WT_I18N::translate('This name is used in the “From” field, when sending automatic emails from this server. It must be a valid email address.'); ?>
							</span>
						</div>
					</td>
				</tr>
			</table>
			<!-- SMTP SECTION -->
			<table>
				<tr>
					<th colspan="2" class="smtp">
						<?php echo WT_I18N::translate('SMTP mail server'); ?>
					</th>
				</tr>
				<tr>
					<th colspan="2" class="error" style="font-weight:normal;">
						<?php echo WT_I18N::translate('You can ignore the options below unless you have set <b>Messages</b> above to <b>Use SMTP to send messages</b>'); ?>
					</th>
				</tr>
				<!-- SMTP_HOST -->
				<tr>
					<td><?php echo WT_I18N::translate('Server name'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_HOST', WT_Site::preference('SMTP_HOST'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the “Server name” site configuration setting */ WT_I18N::translate('This is the name of the SMTP server. \'localhost\' means that the mail service is running on the same computer as your web server.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- SMTP_PORT -->
				<tr>
					<td><?php echo WT_I18N::translate('Port number'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_PORT', WT_Site::preference('SMTP_PORT'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the "Port number" site configuration setting */ WT_I18N::translate('By default, SMTP works on port 25.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- SMTP_AUTH -->
				<tr>
					<td><?php echo WT_I18N::translate('Use password'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-SMTP_AUTH', WT_Site::preference('SMTP_AUTH'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the “Use password” site configuration setting */ WT_I18N::translate('Most SMTP servers require a password.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- SMTP_AUTH_USER -->
				<tr>
					<td><?php echo WT_I18N::translate('Username'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_AUTH_USER', WT_Site::preference('SMTP_AUTH_USER'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo WT_I18N::translate('The user name required for authentication with the SMTP server.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- SMTP_AUTH_PASS -->
				<tr>
					<td><?php echo WT_I18N::translate('Password'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_AUTH_PASS', '' /* Don't show password.  save.php has special code for this. */, $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo WT_I18N::translate('The password required for authentication with the SMTP server.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- SMTP_SSL -->
				<tr>
					<td><?php echo WT_I18N::translate('Secure connection'); ?></td>
					<td><?php echo select_edit_control_inline('site_setting-SMTP_SSL', $SMTP_SSL_OPTIONS, null, WT_Site::preference('SMTP_SSL'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the "Secure connection" site configuration setting */ WT_I18N::translate('Most servers do not use secure connections.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- SMTP_AUTH_PASS -->
				<tr>
					<td><?php echo WT_I18N::translate('Password'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_AUTH_PASS', '' /* Don't show password.  save.php has special code for this. */, $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo WT_I18N::translate('The password required for authentication with the SMTP server.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- SMTP_HELO -->
				<tr>
					<td><?php echo WT_I18N::translate('Sending server name'), help_link('SMTP_HELO'); ?></td>
					<td><?php echo edit_field_inline('site_setting-SMTP_HELO', WT_Site::preference('SMTP_HELO'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the “Sending server name” site configuration setting */ WT_I18N::translate('Many mail servers require that the sending server identifies itself correctly, using a valid domain name.'); ?>
							</span>
						</div>
					</td>
				</tr>
			</table>
			<p>
				<?php echo WT_I18N::translate('To use a Google mail account, use the following settings: server=smtp.gmail.com, port=587, security=tls, username=xxxxx@gmail.com, password=[your gmail password]'); ?>
			</p>
		</div>
		<!-- LOGIN TAB -->
		<div id="login">
			<table>
				<!-- LOGIN_URL -->
				<tr>
					<td><?php echo WT_I18N::translate('Login URL'); ?></td>
					<td><?php echo edit_field_inline('site_setting-LOGIN_URL', WT_Site::preference('LOGIN_URL'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo /* I18N: Help text for the “Login URL” site configuration setting */ WT_I18N::translate('You only need to enter a Login URL if you want to redirect to a different site or location when your users login. This is very useful if you need to switch from http to https when your users login. Include the full URL to <i>login.php</i>. For example, https://www.yourserver.com/kiwitrees/login.php .'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- WELCOME_TEXT_CUST_HEAD -->
				<tr>
					<td><?php echo WT_I18N::translate('Standard header for custom welcome text'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-WELCOME_TEXT_CUST_HEAD', WT_Site::preference('WELCOME_TEXT_CUST_HEAD'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo WT_I18N::translate('Choose to display a standard header for your custom welcome text on the login page.'); ?>
								<a href="#" class="more accepted" style="display: block; font-weight: 600;"><?php echo /* I18N: Click this text to read or hide more information */ WT_I18N::translate('More / Less ....'); ?></a>
								<span class="hidden" style="display: none";>
									<?php echo /* Explanation for custom welcome text option */ WT_I18N::translate('When your users change language, this header will appear in the new language. If set to <b>Yes</b>, the header will look like this:<div class="list_value_wrap"><center><b>Welcome to this Genealogy website</b><br>Access is permitted to users who have an account and a password for this website.</center></div>'); ?>
								</span>
							</span>
						</div>
					</td>
				</tr>
				<!-- WELCOME_TEXT_AUTH_MODE -->
				<tr>
					<td><?php echo WT_I18N::translate('Welcome text on login page'); ?></td>
					<td><?php echo select_edit_control_inline('site_setting-WELCOME_TEXT_AUTH_MODE', $WELCOME_TEXT_AUTH_MODE_OPTIONS, null, WT_Site::preference('WELCOME_TEXT_AUTH_MODE'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo WT_I18N::translate('Here you can choose text to appear on the login screen. You must determine which predefined text is most appropriate. You can also choose to enter your own custom welcome text.'); ?>
								<a href="#" class="more accepted" style="display: block; font-weight: 600;"><?php echo /* I18N: Click this text to read or hide more information */ WT_I18N::translate('More / Less ....'); ?></a>
								<span class="hidden" style="display: none";>
									<?php echo /* Explanation for custom welcome text */ WT_I18N::translate('Please refer to the Help text associated with the <b>Custom Welcome text</b> field for more information.<br>The predefined texts are:<ul><li><b>Predefined text that states all users can request a user account:</b><div class="list_value_wrap"><center><b>Welcome to this Genealogy website</b><br>Access to this site is permitted to every visitor who has a user account.<br>If you have a user account, you can login on this page.  If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.<br>After verifying your application, the site administrator will activate your account.  You will receive an email when your application has been approved.</center></div><br/></li><li><b>Predefined text that states admin will decide on each request for a user account:</b><div class="list_value_wrap"><center><b>Welcome to this Genealogy website</b><br>Access to this site is permitted to <u>authorized</u> users only.<br>If you have a user account you can login on this page.  If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.<br>After verifying your information, the administrator will either approve or decline your account application.  You will receive an email message when your application has been approved.</center></div><br/></li><li><b>Predefined text that states only family members can request a user account:</b><div class="list_value_wrap"><center><b>Welcome to this Genealogy website</b><br>Access to this site is permitted to <u>family members only</u>.<br>If you have a user account you can login on this page.  If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.<br>After verifying the information you provide, the administrator will either approve or decline your request for an account.  You will receive an email when your request is approved.</center></div></li></ul>'); ?>
								</span>
							</span>
						</div>
					</td>
				</tr>
				<!-- WELCOME_TEXT_AUTH_MODE_CUST -->
				<tr>
					<td><?php echo WT_I18N::translate('Custom welcome text'), help_link('WELCOME_TEXT_AUTH_MODE_CUST'); ?></td>
					<td><?php echo edit_text_inline('site_setting-WELCOME_TEXT_AUTH_MODE_4', WT_Site::preference('WELCOME_TEXT_AUTH_MODE_'.WT_LOCALE), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo WT_I18N::translate('If you have opted for custom welcome text, you can type that text here. To set this text for other languages, you must switch to that language, and visit this page again.'); ?>
							</span>
						</div>
					</td>
				</tr>
				<!-- SHOW_REGISTER_CAUTION -->
				<tr>
					<td><?php echo WT_I18N::translate('Show acceptable use agreement on «Request new user account» page'); ?></td>
					<td><?php echo edit_field_yes_no_inline('site_setting-SHOW_REGISTER_CAUTION', WT_Site::preference('SHOW_REGISTER_CAUTION'), $controller); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="help_text">
							<span class="help_content">
								<?php echo WT_I18N::translate('When set to <b>Yes</b>, the following message will appear above the input fields on the «Request new user account» page:<div class="list_value_wrap"><div class="largeError">Notice:</div><div class="error">By completing and submitting this form, you agree:<ul><li>to protect the privacy of living people listed on our site;</li><li>and in the text box below, to explain to whom you are related, or to provide us with information on someone who should be listed on our site.</li></ul></div></div>'); ?>
							</span>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<!-- LANGUAGE TAB -->
		<div id="lang">
			<h3>
				<?php echo WT_I18N::translate('Select the languages your site will use'); ?>
			</h3>
			<h4>
				<?php echo WT_I18N::translate('Select all'); ?>
				<input type="checkbox" onclick="toggle_select(this)" style="vertical-align:middle;">
			</h4>
			<form method="post">
				<input type="hidden" name="action" value="languages">
				<?php
					$code_list = WT_Site::preference('LANGUAGES');
					if ($code_list) {
						$languages = explode(',', $code_list);
					} else {
						$languages = array(
							'ar', 'bg', 'ca', 'cs', 'da', 'de', 'el', 'en_GB', 'en_US', 'es',
							'et', 'fi', 'fr', 'he', 'hr', 'hu', 'is', 'it', 'ka', 'lt', 'nb',
							'nl', 'nn', 'pl', 'pt', 'ru', 'sk', 'sv', 'tr', 'uk', 'vi', 'zh',
						);
					}
					foreach (WT_I18N::installed_languages() as $code=>$name) {
						echo '
							<span style="display:inline-block;width: 200px;">
								<input class="check" type="checkbox" name="LANGUAGES[]" id="lang_' . $code . '"';
									if (in_array($code, $languages)) {
										echo 'checked="checked"';
									}
								echo ' value="' . $code . '">
								<label for="lang_' . $code . '"> '.$name.'</label>
							</span>
						';
					}
				?>
				<p>
					<button type="submit" class="btn btn-primary">
						<i class="fa fa-floppy-o"></i>
						<?php echo WT_I18N::translate('save'); ?>
					</button>
				</p>
			</form>
		</div>
	</div>
</div>
