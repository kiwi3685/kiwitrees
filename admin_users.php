<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

define('KT_SCRIPT_NAME', 'admin_users.php');
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('User administration'));

require_once KT_ROOT.'includes/functions/functions_edit.php';

// Valid values for form variables
$ALL_EDIT_OPTIONS = array(
	'none'  => /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Visitor'),
	'access'=> /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Member'),
	'edit'  => /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Editor'),
	'accept'=> /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Moderator'),
	'admin' => /* I18N: Listbox entry; name of a role */ KT_I18N::translate('Manager')
);

// convert days to seconds
$days = (KT_Site::preference('VERIFY_DAYS') ? KT_Site::preference('VERIFY_DAYS') : 7);
$time = $days * 60 * 60 * 24;

// Form actions
switch (KT_Filter::post('action')) {

	case 'save':
		if (KT_Filter::checkCsrf()) {
			$user_id			= KT_Filter::postInteger('user_id');
			$username			= KT_Filter::post('username');
			$realname			= KT_Filter::post('real_name');
			$email				= KT_Filter::postEmail('email');
			$pass1				= KT_Filter::post('pass1', KT_REGEX_PASSWORD);
			$pass2				= KT_Filter::post('pass2', KT_REGEX_PASSWORD);
			$language			= KT_Filter::post('language');
			$contact_method		= KT_Filter::post('contact_method');
			$comment			= KT_Filter::post('comment');
			$auto_accept		= KT_Filter::postBool('auto_accept');
			$canadmin			= KT_Filter::postBool('canadmin');
			$visible_online		= KT_Filter::postBool('visible_online');
			$verified			= KT_Filter::postBool('verified');
			$verified_by_admin	= KT_Filter::postBool('verified_by_admin');
			$notify_clipping	= KT_Filter::postBool('notify_clipping');

			if ($user_id === 0) {
				// Create a new user
				if (get_user_id($username)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate username. A user with that username already exists. Please choose another username.'));
				} elseif (findByEmail($email)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate email address. A user with that email already exists.'));
				} elseif ($pass1 !== $pass2) {
					KT_FlashMessages::addMessage(KT_I18N::translate('The passwords do not match.'));
				} else {
					$user_id = create_user($username, $realname, $email, $pass1);
					set_user_setting($user_id, 'reg_timestamp', date('U'));
					set_user_setting($user_id, 'sessiontime', '0');
					AddToLog('User ->' . $username . '<- created', 'auth');
				}
			} else {
				if ($user_id && $username && $realname) {
					setUserFullName ($user_id, $realname);
					setUserName	($user_id, $username);
                    if (findByEmail($email) && findByEmail($email) != $user_id) {
    					KT_FlashMessages::addMessage(KT_I18N::translate('Duplicate email address. A user with that email already exists.'));
    				} else {
					    setUserEmail ($user_id, $email);
                    }

                    if ($pass1 !== null && $pass1 !== $pass2) {
                        KT_FlashMessages::addMessage(KT_I18N::translate('The passwords do not match.'));
                    }
					if ($pass1 !== null && $pass1 === $pass2) {
						set_user_password($user_id, $pass1);
					}

				}
			}

			if ($user_id > 0) {
				$tree_link = '<a href="' . KT_SERVER_NAME . KT_SCRIPT_PATH . '?ged=' . KT_GEDCOM . '"><strong>' . strip_tags(KT_TREE_TITLE) . '</strong></a>';
				// Approving for the first time? Send a confirmation email
				if ($verified_by_admin && !get_user_setting($user_id, 'verified_by_admin') && get_user_setting($user_id, 'sessiontime') == 0) {
					KT_I18N::init(get_user_setting($user_id, 'language'));
					KT_Mail::systemMessage(
						$KT_TREE,
						$user_id,
						KT_I18N::translate('Approval of account at %s', strip_tags(KT_TREE_TITLE)),
						KT_I18N::translate('The administrator at %1s has approved your application for an account.<br><br>You may now login by accessing the following link: %2s', strip_tags(KT_TREE_TITLE), $tree_link)
					);
				}

				set_user_setting($user_id, 'language', $language);
				set_user_setting($user_id, 'contactmethod', $contact_method);
				set_user_setting($user_id, 'comment', $comment);
				set_user_setting($user_id, 'auto_accept', $auto_accept);
				set_user_setting($user_id, 'visibleonline', $visible_online);
				set_user_setting($user_id, 'verified', $verified);
				set_user_setting($user_id, 'verified_by_admin',	$verified_by_admin);
				set_user_setting($user_id, 'notify_clipping', $notify_clipping);

				// We cannot change our own admin status. Another admin will need to do it.
				if ($user_id !== KT_USER_ID) {
					set_user_setting($user_id, 'canadmin', $canadmin);
				}

				foreach (KT_Tree::getAll() as $tree) {
					$tree->userPreference($user_id, 'rootid', KT_Filter::post('rootid' . $tree->tree_id, KT_REGEX_XREF));
					$tree->userPreference($user_id, 'gedcomid', KT_Filter::post('gedcomid' . $tree->tree_id, KT_REGEX_XREF));
					$tree->userPreference($user_id, 'canedit', KT_Filter::post('canedit' . $tree->tree_id, implode('|', array_keys($ALL_EDIT_OPTIONS))));
					if (KT_Filter::post('gedcomid' . $tree->tree_id, KT_REGEX_XREF)) {
						$tree->userPreference($user_id, 'RELATIONSHIP_PATH_LENGTH', KT_Filter::postInteger('RELATIONSHIP_PATH_LENGTH' . $tree->tree_id, 0, 10, 0));
					} else {
						// Do not allow a path length to be set if the individual ID is not
						$tree->userPreference($user_id, 'RELATIONSHIP_PATH_LENGTH', null);
					}
				}
			}
		}
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH .  KT_SCRIPT_NAME);

		return;
}

switch (KT_Filter::get('action')) {

	case 'deleteuser':
		// Delete a user - but don't delete ourselves!
		$username	= KT_Filter::get('username');
		$user_id	= get_user_id($username);
		if ($user_id && $user_id != KT_USER_ID) {
			delete_user($user_id);
			AddToLog("deleted user ->{$username}<-", 'auth');
		}

		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH .  KT_SCRIPT_NAME);

		return;

	case 'masquerade_user':
		// Masquerade as a user.
		$username	= KT_Filter::get('username');
		$user_id	= get_user_id($username);
		$KT_SESSION->kt_user = $user_id;
		Zend_Session::regenerateId();
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'index.php');
		break;

	case 'loadrows':
		// Generate an AJAX/JSON response for datatables to load a block of rows
		$sSearch=safe_GET('sSearch');
		$WHERE=" WHERE u.user_id>0";
		$ARGS=array();
		if ($sSearch) {
			$WHERE.=
				" AND (".
				" user_name LIKE CONCAT('%', ?, '%') OR " .
				" real_name LIKE CONCAT('%', ?, '%') OR " .
				" email     LIKE CONCAT('%', ?, '%'))";
			$ARGS = array($sSearch, $sSearch, $sSearch);
		} else {
		}
		$iDisplayStart	= (int)safe_GET('iDisplayStart');
		$iDisplayLength	= (int)safe_GET('iDisplayLength');
		set_user_setting(KT_USER_ID, 'admin_users_page_size', $iDisplayLength);
		if ($iDisplayLength>0) {
			$LIMIT = " LIMIT " . $iDisplayStart . ',' . $iDisplayLength;
		} else {
			$LIMIT = "";
		}
		$iSortingCols = (int)safe_GET('iSortingCols');
		if ($iSortingCols) {
			$ORDER_BY = ' ORDER BY ';
			for ($i=0; $i<$iSortingCols; ++$i) {
				// Datatables numbers columns 0, 1, 2, ...
				// MySQL numbers columns 1, 2, 3, ...
				switch (safe_GET('sSortDir_'.$i)) {
				case 'asc':
					$ORDER_BY .= (1+(int)safe_GET('iSortCol_'.$i)) . ' ASC ';
					break;
				case 'desc':
					$ORDER_BY .= (1+(int)safe_GET('iSortCol_'.$i)) . ' DESC ';
					break;
				}
				if ($i < $iSortingCols - 1) {
					$ORDER_BY .= ',';
				}
			}
		} else {
			$ORDER_BY='';
		}

		$sql=
			"SELECT SQL_CALC_FOUND_ROWS '', u.user_id, user_name, real_name, email, us1.setting_value, us2.setting_value, us2.setting_value, us3.setting_value, us3.setting_value, us4.setting_value, us5.setting_value".
			" FROM `##user` u".
			" LEFT JOIN `##user_setting` us1 ON (u.user_id=us1.user_id AND us1.setting_name='language')".
			" LEFT JOIN `##user_setting` us2 ON (u.user_id=us2.user_id AND us2.setting_name='reg_timestamp')".
			" LEFT JOIN `##user_setting` us3 ON (u.user_id=us3.user_id AND us3.setting_name='sessiontime')".
			" LEFT JOIN `##user_setting` us4 ON (u.user_id=us4.user_id AND us4.setting_name='verified')".
			" LEFT JOIN `##user_setting` us5 ON (u.user_id=us5.user_id AND us5.setting_name='verified_by_admin')".
			$WHERE .
			$ORDER_BY .
			$LIMIT;

		// This becomes a JSON list, not array, so need to fetch with numeric keys.
		$aaData = KT_DB::prepare($sql)->execute($ARGS)->fetchAll(PDO::FETCH_NUM);
		$installed_languages = array();
		foreach (KT_I18N::used_languages() as $code=>$name) {
			$installed_languages[$code] = KT_I18N::translate($name);
		}

		// Reformat various columns for display
		foreach ($aaData as &$aData) {
			$user_id	= $aData[1];
			$username	= $aData[2];

			$aData[0] = '<a href="?action=edit&amp;user_id=' . $user_id . '" title="'. KT_I18N::translate('Edit user').'"><i class="icon-edit"></i></a>';
			// $aData[1] is the user ID (not displayed)
			// $aData[2] is the username
			$aData[2] = '<a href="?action=edit&amp;user_id=' . $user_id . '" title="'. KT_I18N::translate('Edit user').'"><span dir="auto">' . KT_Filter::escapeHtml($aData[2]) . '</span></a>';
			// $aData[3] is the real name
			$aData[3] = '<a href="?action=edit&amp;user_id=' . $user_id . '" title="'. KT_I18N::translate('Edit user').'"><span dir="auto">' . KT_Filter::escapeHtml($aData[3]) . '</span></a>';
			// $aData[4] is the email address
			if ($user_id != KT_USER_ID) {
				$url = KT_SERVER_NAME . KT_SCRIPT_PATH . 'admin_users.php';
				$aData[4] = '<a href="message.php?to=' . $username . '&amp;url=' . $url . '"  title="' . KT_I18N::translate('Send Message') . '">' . KT_Filter::escapeHtml($aData[4]) . '&nbsp;<i class="fa-envelope-o"></i></a>';
			}
			// $aData[5] is the langauge
			if (array_key_exists($aData[5], $installed_languages)) {
				$aData[5] = $installed_languages[$aData[5]];
			}
			// $aData[6] is the sortable registration timestamp
			$aData[7] = $aData[7] ? format_timestamp($aData[7]) : '';
			if (date("U") - $aData[6] > $time && !$aData[10]) {
				$aData[7] = '<span class="red">' . $aData[7] . '</span>'; // display in red if user does not verify within number of days set at login & registration settings
			}
			// $aData[8] is the sortable last-login timestamp
			if ($aData[8]) {
				$aData[9] = format_timestamp($aData[8]) . '<br>' . KT_I18N::time_ago(KT_TIMESTAMP - $aData[8]);
			} else {
				$aData[9] = KT_I18N::translate('Never');
			}
			$aData[10] = $aData[10] ? KT_I18N::translate('yes') : KT_I18N::translate('no');
			$aData[11] = $aData[11] ? KT_I18N::translate('yes') : KT_I18N::translate('no');
			// Add extra column for "delete" action
			if ($user_id != KT_USER_ID) {
				$aData[12]='<div class="icon-delete" onclick="if (confirm(\''.htmlspecialchars(KT_I18N::translate('Are you sure you want to delete “%s”?', $username)).'\')) { document.location=\''.KT_SCRIPT_NAME.'?action=deleteuser&username='.htmlspecialchars((string) $username).'\'; }"></div>';
			} else {
				// Do not delete ourself!
				$aData[12]='';
			}
			// Add extra column for "masquerade" action
			if ($user_id != KT_USER_ID) {
				$aData[13]='<div class="icon-mask" onclick="if (confirm(\''.htmlspecialchars(KT_I18N::translate('Are you sure you want to masquerade as “%s”?', $username)).'\')) { document.location=\''.KT_SCRIPT_NAME.'?action=masquerade_user&username='.htmlspecialchars((string) $username).'\'; }"></div>';
			} else {
				// Do not masquerade as ourself!
				$aData[13]='';
			}
		}

		// Total filtered/unfiltered rows
		$iTotalDisplayRecords	= KT_DB::prepare("SELECT FOUND_ROWS()")->fetchOne();
		$iTotalRecords			= KT_DB::prepare("SELECT COUNT(*) FROM `##user` WHERE user_id>0")->fetchOne();

		Zend_Session::writeClose();
		header('Content-type: application/json');
		echo json_encode(array( // See http://www.datatables.net/usage/server-side
			'sEcho'               => (int)safe_GET('sEcho'),
			'iTotalRecords'       => $iTotalRecords,
			'iTotalDisplayRecords'=> $iTotalDisplayRecords,
			'aaData'              => $aaData
		));
		exit;

	case 'edit':
		$user_id	= KT_Filter::getInteger('user_id');
		$username	= get_user_name($user_id);
		$realname	= getUserFullName($user_id);
		$email		= getUserEmail($user_id);

		if ($user_id === 0) {
			$controller->setPageTitle(KT_I18N::translate('Add a new user'));
			$user_id	= '';
			$username	= '';
			$realname	= '';
			$email		= '';
		} else {
			$controller->setPageTitle(KT_I18N::translate('Edit user') . ' - ' . $realname);
		}

		$controller
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addExternalJavascript(KT_PASSWORDSCHECK)
			->addInlineJavascript('
				autocomplete();
				jQuery(".relpath").change(function() {
					var fieldIDx = jQuery(this).attr("id");
					var idNum = fieldIDx.replace("RELATIONSHIP_PATH_LENGTH","");
					var newIDx = "gedcomid"+idNum;
					if (jQuery("#"+newIDx).val() === "" && jQuery("#".fieldIDx).val() !== "0") {
						alert("' . KT_I18N::translate('You must specify an individual record before you can restrict the user to their immediate family.') . '");
						jQuery(this).val("0");
					}
				});
				function regex_quote(str) {
					return str.replace(/[\\\\.?+*()[\](){}|]/g, "\\\\$&");
				};
				jQuery(function() {
					jQuery("div.config_options:odd").addClass("odd");
					jQuery("div.config_options:even").addClass("even");
				});

			');

		?>
		<div id="user_details">
			<h2><?php echo $controller->getPageTitle(); ?></h2>

			<form name="newform" method="post" role="form" action="admin_users.php?action=edit" autocomplete="off">
				<?php echo KT_Filter::getCsrf(); ?>
				<input type="hidden" name="action" value="save">
				<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
				<!-- REAL NAME -->
				<div class="config_options">
					<label for="real_name">
						<?php echo KT_I18N::translate('Real name'); ?>
					</label>
					<div class="input_group">
						<input type="text" id="real_name" name="real_name" required maxlength="64" value="<?php echo KT_Filter::escapeHtml($realname); ?>" dir="auto">
						<div class="helpcontent">
							<?php echo KT_I18N::translate('This is your real name, as you would like it displayed on screen.'); ?>
						</div>
					</div>
				</div>
				<!-- USER NAME -->
				<div class="config_options">
					<label for="username">
						<?php echo KT_I18N::translate('Username'); ?>
					</label>
					<div class="input_group">
						<input type="text" id="username" name="username" required maxlength="32" value="<?php echo KT_Filter::escapeHtml($username); ?>" dir="auto">
						<div class="helpcontent">
							<?php echo KT_I18N::translate('Usernames are case-insensitive and ignore accented letters, so that “chloe”, “chloë”, and “Chloe” are considered to be the same.'); ?>
						</div>
					</div>
				</div>
				<!-- PASSWORD -->
				<div class="config_options">
					<label for="pass1">
						<?php echo KT_I18N::translate('Password'); ?>
					</label>
					<div class="input_group">
						<input type="password" id="pass1" name="pass1" pattern = "<?php echo KT_REGEX_PASSWORD; ?>" placeholder="<?php echo KT_I18N::plural('Use at least %s character.', 'Use at least %s characters.', KT_MINIMUM_PASSWORD_LENGTH, KT_I18N::number(KT_MINIMUM_PASSWORD_LENGTH)); ?>" <?php echo $user_id ? '' : 'required'; ?> onchange="form.pass2.pattern = regex_quote(this.value);">
						<span id="result" class="input_label right">&nbsp;</span>
						<div class="helpcontent">
							<?php if ($user_id > 0) { ?>
								<?php echo KT_I18N::translate('Leave password blank if you want to keep the current password.'); ?>
								<br>
							<?php } ?>
							<?php echo KT_I18N::translate('
								Passwords must be at least 6 characters long and are case-sensitive, so that “secret” is different from “SECRET”.
								<br>
								Anything with 6 characters or more is acceptable, but mixed lower and uppercase characters, numbers, and special characters will increase the security of the password.
							'); ?>
						</div>
					</div>
					<!-- CONFIRM PASSWORD -->
					<label for="pass2">
						<?php echo KT_I18N::translate('Confirm password'); ?>
					</label>
					<div class="input_group">
						<input type="password" id="pass2" name="pass2" pattern = "<?php echo KT_REGEX_PASSWORD; ?>" placeholder="<?php echo KT_I18N::translate('Type the password again.'); ?>" <?php echo $user_id ? '' : 'required'; ?>>
					</div>
				</div>
				<!-- EMAIL ADDRESS -->
				<div class="config_options">
					<label for="email">
						<?php echo KT_I18N::translate('Email address'); ?>
					</label>
					<div class="input_group">
						<input type="email" id="email" name="email" required maxlength="64" value="<?php echo KT_Filter::escapeHtml($email); ?>">
						<div class="helpcontent">
							<?php echo KT_I18N::translate('This email address will be used to send password reminders, website notifications, and messages from other family members who are registered on the website.'); ?>
						</div>
					</div>
				</div>
				<!-- EMAIL VERIFIED and ACCOUNT APPROVED -->
				<div class="config_options">
					<label for="verified">
						<?php echo KT_I18N::translate('Account approval and email verification'); ?>
					</label>
					<div class="input_group">
						<div class="checkbox">
							<label>
								<input type="checkbox" name="verified" value="1" <?php echo get_user_setting($user_id, 'verified') ? 'checked' : ''; ?>>
								<?php echo KT_I18N::translate('Email verified'); ?>
							</label>
							<label>
								<input type="checkbox" name="verified_by_admin" value="1" <?php echo get_user_setting($user_id, 'verified_by_admin') ? 'checked' : ''; ?>>
								<?php echo KT_I18N::translate('Approved by administrator'); ?>
							</label>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('When a user registers for an account, an email is sent to their email address with a verification link. When they follow this link, we know the email address is correct, and the “email verified” option is selected automatically.
								<br>
								If an administrator creates a user account, the verification email is not sent, and the email must be verified manually.
								<br>
								You should not approve an account unless you know that the email address is correct.
								<br>
								A user will not be able to sign in until both “email verified” and “approved by administrator” are selected.'); ?>
							</div>
						</div>
					</div>
				</div>
				<!-- LANGUAGE -->
				<div class="config_options">
					<label for="language">
						<?php echo /* I18N: A configuration setting */ KT_I18N::translate('Language'); ?>
					</label>
					<div class="input_group">
						<select id="language" name="language">
							<?php foreach (KT_I18N::used_languages() as $code=>$name) { ?>
								<option value="<?php echo $code; ?>" dir="auto" <?php echo get_user_setting($user_id, 'language') === $code ? 'selected' : ''; ?>>
									<?php echo KT_I18N::translate($name); ?>
								</option>
							<?php } ?>
						</select>
					</div>
				</div>
				<!-- AUTO ACCEPT -->
				<div class="config_options">
					<label for="auto_accept">
						<?php echo KT_I18N::translate('Changes'); ?>
					</label>
					<div class="input_group">
						<div class="checkbox">
							<label>
								<input type="checkbox" name="auto_accept" value="1" <?php echo get_user_setting($user_id, 'auto_accept') ? 'checked' : ''; ?>>
								<?php echo KT_I18N::translate('Automatically accept changes made by this user'); ?>
							</label>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Normally, any changes made to a family tree need to be reviewed by a moderator. This option allows a user to make changes without needing a moderator.'); ?>
							</div>
						</div>
					</div>
				</div>
				<!-- VISIBLE ONLINE -->
				<div class="config_options">
					<label for="visible_online">
						<?php echo /* I18N: A configuration setting */ KT_I18N::translate('Visible online'); ?>
					</label>
					<div class="input_group">
						<div class="checkbox">
							<label>
								<input type="checkbox" id="visible_online" name="visible_online" value="1" <?php echo get_user_setting($user_id, 'visibleonline') ? 'checked' : ''; ?>>
								<?php echo /* I18N: A configuration setting */ KT_I18N::translate('Visible to other users when online'); ?>
							</label>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('You can choose whether to appear in the list of users who are currently signed-in.'); ?>
							</div>
						</div>
					</div>
				</div>
				<!-- CONTACT METHOD -->
				<div class="config_options">
					<label for="contactmethod">
						<?php echo /* I18N: A configuration setting */ KT_I18N::translate('Preferred contact method'); ?>
					</label>
					<div class="input_group">
						<?php echo edit_field_contact('contact_method', get_user_setting($user_id, 'contactmethod')); ?>
						<div class="helpcontent">
							<?php echo /* I18N: Help text for the “Preferred contact method” configuration setting */
							KT_I18N::translate('Site members can send each other messages. You can choose to how these messages are sent to you, or choose not receive them at all.'); ?>
						</div>
					</div>
				</div>
				<!-- COMMENTS -->
				<div class="config_options">
					<label for="comment">
						<?php echo KT_I18N::translate('Administrator comments on user'); ?>
					</label>
					<div class="input_group">
						<textarea id="comment" name="comment" rows="5" maxlength="255"><?php echo KT_Filter::escapeHtml(get_user_setting($user_id, 'comment')); ?></textarea>
					</div>
				</div>
				<!-- ADMIN NOTIFICATION OPTIONS -->
				<?php if (KT_USER_IS_ADMIN) { ?>
					<div class="config_options">
						<label for="verified">
							<?php echo KT_I18N::translate('Notification options'); ?>
						</label>
						<div class="input_group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="notify_clipping" value="1" <?php echo get_user_setting($user_id, 'notify_clipping', 1) ? 'checked' : ''; ?>>
									<?php echo KT_I18N::translate('Clippings cart downloads'); ?>
								</label>
								<div class="helpcontent">
									<?php echo KT_I18N::translate('When a user downloads a GEDCOM file created in the Clippings cart the site administrator will be notified by mail if this option is selected.'); ?>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
				<!-- FAMILY TREEs - ACCESS and SETTINGS -->
				<div id="access">
					<h3><?php echo KT_I18N::translate('Family tree access and settings'); ?></h3>
					<div class="helpcontent">
						<h4><?php echo KT_I18N::translate('Help for family tree access settings'); ?></h4>
						<div class="help_label">
							<label><?php echo KT_I18N::translate('Default individual'); ?></label>
							<span>
								<?php echo KT_I18N::translate('This individual will be selected by default when viewing charts and reports.'); ?>
							</span>
						</div>
						<div class="help_label">
							<label><?php echo KT_I18N::translate('Individual record'); ?></label>
							<span>
								<?php echo KT_I18N::translate('Link this user to an individual in the family tree.'); ?>
							</span>
						</div>
						<div class="help_label">
							<label><?php echo KT_I18N::translate('Roles'); ?></label>
							<span>
								<?php echo KT_I18N::translate('A role is a set of access rights, which give permission to view data, change preferences, etc. Access rights are assigned to roles, and roles are granted to users. Each family tree can assign different access to each role, and users can have a different role in each family tree.'); ?>
							</span>
						</div>
						<div class="indent">
							<div class="help_label">
								<label><?php echo KT_I18N::translate('Visitor'); ?></label>
								<span>
									<?php echo KT_I18N::translate('Everybody has this role, including visitors to the website and search engines.'); ?>
								</span>
							</div>
							<div class="help_label">
								<label><?php echo KT_I18N::translate('Member'); ?></label>
								<span>
									<?php echo KT_I18N::translate('This role has all the permissions of the visitor role, plus any additional access granted by the family tree configuration.'); ?>
								</span>
							</div>
							<div class="help_label">
								<label><?php echo KT_I18N::translate('Editor'); ?></label>
								<span>
									<?php echo KT_I18N::translate('This role has all the permissions of the member role, plus permission to add/change/delete data. Any changes will need to be reviewed by a moderator, unless the user has the “automatically accept changes” option enabled.'); ?>
								</span>
							</div>
							<div class="help_label">
								<label><?php echo KT_I18N::translate('Moderator'); ?></label>
								<span>
									<?php echo KT_I18N::translate('This role has all the permissions of the editor role, plus permission to accept/reject changes made by other users.'); ?>
								</span>
							</div>
							<div class="help_label">
								<label><?php echo KT_I18N::translate('Manager'); ?></label>
								<span>
									<?php echo KT_I18N::translate('This role has all the permissions of the moderator role, plus any additional access granted by the family tree configuration, plus permission to change the settings/configuration of a family tree.'); ?>
								</span>
							</div>
							<div class="help_label">
								<label><?php echo KT_I18N::translate('Administrator'); ?></label>
								<span>
									<?php echo KT_I18N::translate('This role has all the permissions of the manager role in all family trees, plus permission to change the settings/configuration of the website, users, and modules.'); ?>
								</span>
							</div>
						</div>
						<div class="help_label">
							<label><?php echo KT_I18N::translate('Restrict to immediate family'); ?></label>
							<span>
								<?php echo KT_I18N::translate('Where a user is associated with an individual record in a family tree and has a role of member, editor, or moderator, you can prevent them from accessing the details of distant, living relations. You specify the number of relationship steps that the user is allowed to see.'); ?>
								<?php echo KT_I18N::translate('For example, if you specify a path length of 2, the individual will be able to see their grandson (child, child), their aunt (parent, sibling), their step-daughter (spouse, child), but not their first cousin (parent, sibling, child).'); ?>
								<?php echo KT_I18N::translate('Note: longer path lengths require a lot of calculation, which can make your website run slowly for these users.'); ?>
							</span>
						</div>
					</div>
					<!-- ADMINISTRATOR -->
					<div class="config_options">
						<label for="admin">
							<?php echo KT_I18N::translate('Administration role'); ?>
						</label>
						<div class="input_group">
							<div class="checkbox">
								<label>
									<input
										type="checkbox" id="admin" name="canadmin" value="1"
										<?php echo get_user_setting($user_id, 'canadmin') ? 'checked' : ''; ?>
										<?php echo $user_id === KT_USER_ID ? 'disabled' : ''; ?>
									>
									<?php echo KT_I18N::translate('Administrator'); ?>
								</label>
							</div>
						</div>
					</div>
					<!-- FAMILY TREE SETTINGS -->
					<div class="config_options access_table">
						<table>
							<thead>
								<tr>
									<th><?php echo KT_I18N::translate('Family tree'); ?></th>
									<th><?php echo KT_I18N::translate('Default individual'); ?></th>
									<th><?php echo KT_I18N::translate('Individual record'); ?></th>
									<th><?php echo KT_I18N::translate('Role'); ?></th>
									<th><?php echo KT_I18N::translate('Restrict to immediate family'); ?></th>
							</thead>
							<tbody>
								<?php foreach (KT_Tree::getAll() as $tree): ?>
									<tr>
										<td><?php echo $tree->tree_title_html; ?></td>
										<!-- PEDIGREE ROOT PERSON -->
										<td>
											<?php $varname = 'rootid' . $tree->tree_id; ?>
											<input data-autocomplete-type="INDI" data-autocomplete-ged="<?php echo $tree->tree_name_html; ?>" type="text" size="12" name="<?php echo $varname; ?>" id="<?php echo $varname; ?>" value="<?php echo KT_Filter::escapeHtml($tree->userPreference($user_id, 'rootid')); ?>">
										<!-- GEDCOM INDI Record ID -->
										<td>
											<?php $varname = 'gedcomid' . $tree->tree_id; ?>
											<input data-autocomplete-type="INDI" data-autocomplete-ged="<?php echo $tree->tree_name_html; ?>" type="text" size="12" name="<?php echo $varname; ?>" id="<?php echo $varname; ?>" value="<?php echo KT_Filter::escapeHtml($tree->userPreference($user_id, 'gedcomid')); ?>">
										</td>
										<!-- ROLE -->
										<td>
											<?php $varname = 'canedit' . $tree->tree_id; ?>
											<select name="<?php echo $varname; ?>" style="width: 200px;">
												<?php foreach ($ALL_EDIT_OPTIONS as $EDIT_OPTION => $desc) { ?>
													<option value="<?php echo $EDIT_OPTION; ?>"
														<?php echo $EDIT_OPTION === $tree->userPreference($user_id, 'canedit') ? 'selected' : ''; ?>
													><?php echo $desc; ?></option>
												<?php } ?>
											</select>
										</td>
										<!-- RELATIONSHIP PATH -->
										<td>
											<?php $varname = 'RELATIONSHIP_PATH_LENGTH' . $tree->tree_id; ?>
											<select name="<?php echo $varname; ?>" id="<?php echo $varname; ?>" class="relpath" style="width: 200px;">
												<?php for ($n = 0; $n <= 10; ++$n): ?>
													<option value="<?php echo $n; ?>" <?php echo $tree->userPreference($user_id, 'RELATIONSHIP_PATH_LENGTH') == $n ? 'selected' : ''; ?>>
													<?php echo $n ? $n : KT_I18N::translate('No'); ?>
												</option>
												<?php endfor; ?>
											</select>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
				<p class ="clearfloat">
					<button class="btn btn-primary" type="submit">
					<i class="fa fa-floppy-o"></i>
						<?php echo KT_I18N::translate('Save'); ?>
					</button>
				</p>

			</form>
		</div>
		<?php
		return;

	case 'cleanup':
		$controller
			->pageHeader()
			->addInlineJavascript('
				jQuery("#selectMonths").change(function() {
				    window.location = "admin_users.php?action=cleanup&month=" + jQuery(this).val();
				});
			'); ?>

		<h2><?php echo KT_I18N::translate('Delete inactive users'); ?></h2>
		<form name="cleanupform" method="post" action="admin_users.php?action=cleanup2">
			<table id="clean">
				<!-- Check for idle users -->
				<?php
				$month		= KT_Filter::getInteger('month', 1, 60, 6);
				$range		= "1,2,3,4,5,6,7,8,9,10,11,12,18,24,36,48,60";
				$monthRange	= explode( ',', $range );
				?>
				<tr>
					<th>
						<?php echo KT_I18N::translate('Number of months since the last login for a user\'s account to be considered inactive: '); ?>
					</th>
					<td>
						<select id="selectMonths">
							<?php foreach( $monthRange as $i ) { ?>
								<option value="<?php echo $i; ?>"
									<?php if ($i == $month) { ?>
										selected="selected"
									<?php } ?>
									> <?php echo $i; ?>
								</option>
							<?php } ?>
						</select>
					</td>
				</tr>

				<!-- Check users inactive too long -->
				<tr><th colspan="2">&nbsp;&nbsp;</th></tr>
				<tr>
					<th colspan="2">
						<?php echo KT_I18N::plural(
							'These users have not logged in for at least %s month',
							'These users have not logged in for at least %s months',
							$month, $month
						); ?>
					</th>
				</tr>
				<?php
				$ucnt = 0;
				foreach (get_all_users() as $user_id=>$username) {
					$userName = getUserFullName($user_id);
					if ((int)get_user_setting($user_id, 'sessiontime') == "0")
						$datelogin = (int)get_user_setting($user_id, 'reg_timestamp');
					else
						$datelogin = (int)get_user_setting($user_id, 'sessiontime');
					if ((mktime(0, 0, 0, (int)date("m")-$month, (int)date("d"), (int)date("Y")) > $datelogin) && get_user_setting($user_id, 'verified') && get_user_setting($user_id, 'verified_by_admin')) { ?>
						<tr>
							<td>
								<?php echo $username; ?>
								 - <span>
									 <?php echo $userName; ?>
								 </span>
								  - <span>
									<?php echo KT_I18N::translate('Not logged in since: %s', timestamp_to_gedcom_date($datelogin)->Display(false));
									$ucnt++; ?>
								</span>
							</td>
							<td>
								<input type="checkbox" name="<?php echo "del_", str_replace(array(".", "-", " "), array("_", "_", "_"), $username); ?>" value="1">
							</td>
						</tr>
					<?php }
				}
				if ($ucnt == 0) { ?>
					<tr>
						<td class="accepted" colspan="2">
							<?php echo KT_I18N::translate('Nothing found to cleanup'); ?>
						</td>
					</tr>
				<?php } ?>

				<!-- Check unverified users -->
				<tr><th colspan="2">&nbsp;&nbsp;</th></tr>
				<tr>
					<th colspan="2">
						<?php echo KT_I18N::plural(
							'These users have not verified their email address for %s day or more',
							'These users have not verified their email address for %s days or more',
							$days, $days
						); ?>
					</th>
				</tr>
				<?php
				$vcnt = 0;
				foreach (get_all_users() as $user_id => $username) {
					if (((date("U") - (int)get_user_setting($user_id, 'reg_timestamp')) > $time) && !get_user_setting($user_id, 'verified')) {
						$userName = getUserFullName($user_id); ?>
						<tr>
							<td>
								<?php echo $username, " - ", $userName;
								$vcnt++; ?>
							</td>
							<td>
								<input type="checkbox" checked="checked" name="<?php echo "del_", str_replace(array(".", "-", " "), array("_",  "_", "_"), $username); ?>" value="1">
							</td>
						</tr>
					<?php }
				}
				if ($vcnt == 0) { ?>
					<tr>
						<td class="accepted" colspan="2">
							<?php echo KT_I18N::translate('Nothing found to cleanup'); ?>
						</td>
					</tr>
				<?php } ?>

				<!-- Check users not verified by admin -->
				<tr><th colspan="2">&nbsp;&nbsp;</th></tr>
				<tr>
					<th colspan="2">
						<?php echo KT_I18N::translate('These users have not been verified by admin'); ?>
					</th>
				</tr>
				<?php
				$acnt = 0;
				foreach (get_all_users() as $user_id => $username) {
					if (!get_user_setting($user_id, 'verified_by_admin') && get_user_setting($user_id, 'verified')) {
						$userName = getUserFullName($user_id); ?>
						<hr>
						<tr>
							<td>
								<?php echo $username, " - ", $userName, ":&nbsp;&nbsp;", KT_I18N::translate('User not verified by administrator.'); ?>
							</td>
							<td>
								<input type="checkbox" name="<?php echo "del_", str_replace(array(".", "-", " "), array("_", "_", "_"), $username); ?>" value="1">
							</td>
						</tr>
						<?php
						$acnt++;
					}
				}
				if ($acnt == 0) { ?>
					<tr>
						<td class="accepted" colspan="2">
							<?php echo KT_I18N::translate('Nothing found to cleanup'); ?>
						</td>
					</tr>
				<?php } ?>
			</table>
			<p>
				<?php if ($ucnt >0) {
					?><input type="submit" value="<?php echo KT_I18N::translate('continue'); ?>">&nbsp;&nbsp;<?php
				} ?>
			</p>
		</form>
		<?php
		break;

	case 'cleanup2':
		foreach (get_all_users() as $user_id => $username) {
			$var = "del_" . str_replace(array(".", "-", " "), array("_", "_", "_"), $username);
			if (safe_POST($var) == '1') {
				delete_user($user_id);
				AddToLog("deleted user ->{$username}<-", 'auth');
				echo KT_I18N::translate('Deleted user: ');
				echo $username, "<br>";
			}
		}
		break;

	default:
		$controller
			->addExternalJavascript(KT_JQUERY_DATATABLES_URL)
			->addExternalJavascript(KT_JQUERY_DT_HTML5)
			->addExternalJavascript(KT_JQUERY_DT_BUTTONS)
			->addInlineJavascript('
				jQuery("#list").dataTable({
					dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
					' . KT_I18N::datatablesI18N() . ',
					buttons: [{extend: "csv", exportOptions: {columns: [1,2,3,5,7] }}],
					jQueryUI: true,
					stateSave: true,
					stateDuration: -1,
					processing: true,
					serverSide: true,
					"sAjaxSource" : " '. KT_SCRIPT_NAME . '?action=loadrows",
					autoWidth: false,
					pagingType: "full_numbers",
					sorting: [[2,"asc"]],
					columns: [
						/*  0 edit          	*/ { sortable:false, class:"center" },
						/*  1 user-id           */ { bVisible:false },
						/*  2 user_name         */ null,
						/*  3 real_name         */ null,
						/*  4 email             */ null,
						/*  5 language          */ null,
						/*  6 registered (sort) */ { bVisible:false },
						/*  7 registered        */ { dataSort:7 },
						/*  8 last_login (sort) */ { bVisible:false },
						/*  9 last_login        */ { dataSort:9 },
						/* 10 verified          */ { class:"center" },
						/* 11 verified_by_admin */ { class:"center" },
						/* 12 delete            */ { sortable:false, class:"center" },
						/* 13 masquerade        */ { sortable:false, class:"center" }
					],
				})
				.fnFilter("' . KT_Filter::get('filter') . '"); // View the details of a newly created user
			')
			->pageHeader();
			?>
		<table id="list">
			<thead>
				<tr>
					<th><?php echo KT_I18N::translate('Edit'); ?></th>
					<th>user-id </th>
					<th><?php echo KT_I18N::translate('Username'); ?></th>
					<th><?php echo KT_I18N::translate('Real name'); ?></th>
					<th><?php echo KT_I18N::translate('Email'); ?></th>
					<th><?php echo KT_I18N::translate('Language'); ?></th>
					<th>date_registered </th>
					<th><?php echo KT_I18N::translate('Date registered'); ?></th>
					<th>last_login </th>
					<th><?php echo KT_I18N::translate('Last logged in'); ?></th>
					<th><?php echo KT_I18N::translate('Verified'); ?></th>
					<th><?php echo KT_I18N::translate('Approved'); ?></th>
					<th colspan="2"><?php echo KT_I18N::translate('Options'); ?></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<?php
		break;

}
