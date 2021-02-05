<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class login_block_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Login');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Login” module */ KT_I18N::translate('An alternative way to login and logout.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $controller;
		$id=$this->getName().$block_id;
		$class=$this->getName().'_block';
		$url = KT_LOGIN_URL.'?url='.rawurlencode(get_query_url());
		if (KT_USER_ID) {
			$title = KT_I18N::translate('Logout');
			$content='';
			$content = '<div class="center logoutform"><form method="post" action="index.php?logout=1" name="logoutform" onsubmit="return true;">';
			$content .= '<br><a href="edituser.php" class="name2">'.KT_I18N::translate('Logged in as ').' ('.KT_USER_NAME.')</a><br><br>';

			$content .= "<input type=\"submit\" value=\"".KT_I18N::translate('Logout')."\">";

			$content .= "<br><br></form></div>";
		} else {
			$title = (KT_Site::preference('USE_REGISTRATION_MODULE') ? KT_I18N::translate('Login or Register') : KT_I18N::translate('Login'));

			$content='';
			$content='<div id="login-box">
				<form id="login-form" name="login-form" method="post" action="' .$url. '" onsubmit="t = new Date(); this.usertime.value=t.getFullYear()+\'-\'+(t.getMonth()+1)+\'-\'+t.getDate()+\' \'+t.getHours()+\':\'+t.getMinutes()+\':\'+t.getSeconds();return true;">
				<input type="hidden" name="action" value="login">
				<input type="hidden" name="ged" value="'; if (isset($ged)) $content.= htmlspecialchars($ged); else $content.= htmlentities(KT_GEDCOM); $content.= '">
				<input type="hidden" name="pid" value="'; if (isset($pid)) $content.= htmlspecialchars($pid); $content.= '">
				<input type="hidden" name="usertime" value="">';
			$content.= '<div>
				<label for="moduleUsername">'. KT_I18N::translate('Username').
					'<input type="text" id="moduleUsername" name="username" class="formField">
				</label>
				</div>
				<div>
					<label for="modulePassword">'. KT_I18N::translate('Password').
						'<input type="password" id="modulePassword" name="password" class="formField">
					</label>
				</div>
				<div>
					<input type="submit" value="'. KT_I18N::translate('Login'). '">
				</div>
				<div>
					<a href="#" class="passwd_click">'. KT_I18N::translate('Request new password').'</a>
				</div>';
			if (KT_Site::preference('USE_REGISTRATION_MODULE')) {
				$content.= '<div><a href="'.KT_LOGIN_URL.'?action=register">'. KT_I18N::translate('Request new user account').'</a></div>';
			}
		$content.= '</form>'; // close "login-form"

		// hidden New Password block
		$content.= '<div class="new_passwd">
			<form class="new_passwd_form" name="new_passwd_form" action="'.KT_LOGIN_URL.'" method="post">
			<input type="hidden" name="time" value="">
			<input type="hidden" name="action" value="requestpw">
			<h4>'. KT_I18N::translate('Lost password request').'</h4>
			<div>
				<label>'. KT_I18N::translate('Username or email address').
					'<input type="text" class="new_passwd_username" name="new_passwd_username" value="">
				</label>
			</div>
			<div><input type="submit" value="'. KT_I18N::translate('continue'). '"></div>
			</form>
		</div>'; //"new_passwd"
		$content.= '</div>';//"login-box"
		}

		if ($template) {
			require KT_THEME_DIR . 'templates/block_main_temp.php';
		} else {
			return $content;
		}
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
	}
}
