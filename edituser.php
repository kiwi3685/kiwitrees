<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2017 kiwitrees.net
 * 
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 * 
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 * 
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees.  If not, see <http://www.gnu.org/licenses/>.
 */

define('KT_SCRIPT_NAME', 'edituser.php');
require './includes/session.php';
require_once KT_ROOT.'includes/functions/functions_print_lists.php';
require KT_ROOT.'includes/functions/functions_edit.php';

// Extract form variables
$form_action         = safe_POST('form_action');
$form_username       = safe_POST('form_username', KT_REGEX_USERNAME);
$form_realname       = safe_POST('form_realname' );
$form_pass1          = safe_POST('form_pass1', KT_REGEX_PASSWORD);
$form_pass2          = safe_POST('form_pass2', KT_REGEX_PASSWORD);
$form_email          = safe_POST('form_email', KT_REGEX_EMAIL, 'email@example.com');
$form_rootid         = safe_POST('form_rootid', KT_REGEX_XREF, KT_USER_ROOT_ID   );
$form_language       = safe_POST('form_language', array_keys(KT_I18N::used_languages()), KT_LOCALE );
$form_contact_method = safe_POST('form_contact_method');
$form_visible_online = safe_POST_bool('form_visible_online');

// Respond to form action
if ($form_action && KT_Filter::checkCsrf()) {
	switch ($form_action) {
		case 'update':
			if ($form_username != KT_USER_NAME && get_user_id($form_username)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate user name.  A user with that user name already exists.  Please choose another user name.'));
			} elseif ($form_email!=getUserEmail(KT_USER_ID) && findByEmail($form_email)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate email address.  A user with that email already exists.'));
			} else {
				// Change username
				if ($form_username != KT_USER_NAME) {
					AddToLog('User renamed to ->'  .$form_username . '<-', 'auth');
					rename_user(KT_USER_ID, $form_username);
				}

				// Change password
				if ($form_pass1 && $form_pass1 == $form_pass2) {
					set_user_password(KT_USER_ID, $form_pass1);
				}

				// Change other settings
				setUserFullName(KT_USER_ID, $form_realname);
				setUserEmail   (KT_USER_ID, $form_email);
				set_user_setting(KT_USER_ID, 'language',       $form_language);
				set_user_setting(KT_USER_ID, 'contactmethod',  $form_contact_method);
				set_user_setting(KT_USER_ID, 'visibleonline',  $form_visible_online);
				$KT_TREE->userPreference(KT_USER_ID, 'rootid', $form_rootid);
			}
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME);
		break;

		case 'delete':
			// An administrator can only be deleted by another administrator
			if (!KT_USER_IS_ADMIN) {
				userLogout(KT_USER_ID);
				delete_user(KT_USER_ID);
			}
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH);
		break;
	}

	return;
}

$controller = new KT_Controller_Page();
$controller
	->setPageTitle(KT_I18N::translate('User administration'))
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('
		autocomplete();
		display_help();
	');

// Form validation
?>
<script>
function checkform(frm) {
	if (frm.form_username.value=="") {
		alert("<?php echo KT_I18N::translate('You must enter a user name.'); ?>");
		frm.form_username.focus();
		return false;
	}
	if (frm.form_realname.value=="") {
		alert("<?php echo KT_I18N::translate('You must enter a real name.'); ?>");
		frm.form_realname.focus();
		return false;
	}
	if (frm.form_pass1.value!=frm.form_pass2.value) {
		alert("<?php echo KT_I18N::translate('Passwords do not match.'); ?>");
		frm.form_pass1.focus();
		return false;
	}
	if (frm.form_pass1.value.length > 0 && frm.form_pass1.value.length < 6) {
		alert("<?php echo KT_I18N::translate('Passwords must contain at least 6 characters.'); ?>");
		frm.form_pass1.focus();
		return false;
	}
	return true;
}
</script>

<div id="edituser-page">
	<h2><?php echo KT_I18N::translate('My account'); ?></h2>
	<div id="edituser-table">
		<form name="editform" method="post" action="" onsubmit="return checkform(this);">
			<input type="hidden" id="form_action" name="form_action" value="update">
			<?php echo KT_Filter::getCsrf(); ?>
			<div class="chart_options">
				<label><?php echo KT_I18N::translate('Username'); ?></label>
				<input type="text" name="form_username" value="<?php echo KT_USER_NAME; ?>" autofocus>
				<span id="username" class="help_text"></span>
			</div>
			<div class="chart_options">
				<label><?php echo KT_I18N::translate('Real name'); ?></label>
				<input type="text" name="form_realname" value="<?php echo getUserFullName(KT_USER_ID); ?>">
				<span id="real_name" class="help_text"></span>
			</div>
			<?php $person = KT_Person::getInstance(KT_USER_GEDCOM_ID); ?>
			<div class="chart_options">
				<label><?php echo KT_I18N::translate('Individual record'); ?></label>
				<?php if ($person) { ?>
					<div><?php echo $person->format_list('span'); ?></div>
				<?php } else { ?>
					<div class="label"><?php echo KT_I18N::translate('Unknown'); ?></div>
				<?php } ?>
				<span id="edituser_gedcomid" class="help_text"></span>
			</div>
			<?php $person = KT_Person::getInstance(KT_USER_ROOT_ID); ?>
			<div class="chart_options">
				<label><?php echo KT_I18N::translate('Default individual'); ?></label>
				<input data-autocomplete-type="INDI" type="text" name="form_rootid" id="rootid" value="<?php echo KT_USER_ROOT_ID; ?>">
					<?php echo print_findindi_link('rootid'); ?>
					<br>
					<?php if ($person) {
						echo $person->format_list('span');
					} ?>
				<span id="default_individual" class="help_text"></span>
			</div>
			<div class="chart_options">
				<label><?php echo KT_I18N::translate('Password'); ?></label>
				<input type="password" name="form_pass1">
				<span id="password" class="help_text"></span>
				<label><?php echo KT_I18N::translate('Confirm password'); ?></label>
				<input type="password" name="form_pass2">
				<span id="password_confirm" class="help_text"></span>
			</div>
			<div class="chart_options">
				<label><?php echo KT_I18N::translate('Language'); ?></label>
				<div class="label">
					<select id="form_language" name="form_language">
						<?php foreach (KT_I18N::used_languages() as $code=>$name) { ?>
							<option value="<?php echo $code; ?>" dir="auto" <?php echo get_user_setting(KT_USER_ID, 'language') === $code ? 'selected' : ''; ?>>
								<?php echo KT_I18N::translate($name); ?>
							</option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="chart_options">
				<label><?php echo KT_I18N::translate('Email address'); ?></label>
				<input type="email" name="form_email" value="<?php echo getUserEmail(KT_USER_ID); ?>" size="150">
				<span id="email" class="help_text"></span>
			</div>
			<div class="chart_options">
				<label><?php echo KT_I18N::translate('Preferred contact method'); ?></label>
				<div class="label"><?php echo edit_field_contact('form_contact_method', get_user_setting(KT_USER_ID, 'contactmethod')); ?></div>
				<span id="edituser_contact_meth_short" class="help_text"></span>
			</div>
			<div id="edituser_submit" class="btn btn-primary">
				<button type="submit" value="<?php echo KT_I18N::translate('save') ?>"><?php echo KT_I18N::translate('save'); ?></button>
			</div>
		</form>
		<?php if (!KT_USER_IS_ADMIN) { ?>
			<div id="edituser_delete" class="btn btn-primary">
				<button onclick="if (confirm('<?php echo htmlspecialchars(KT_I18N::translate('Are you sure you want to delete “%s”?', KT_USER_NAME)); ?>')) {jQuery('#form_action').val('delete'); document.editform.submit(); }"><?php echo KT_I18N::translate('Delete your account'); ?></button>
			</div>
		<?php } ?>
	</div> <!-- close edituser-table -->
</div> <!-- close edituser-page -->
