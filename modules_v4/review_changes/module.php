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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class review_changes_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Pending changes');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Pending changes” module */ KT_I18N::translate('A list of changes that need moderator approval, and email notifications.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $ctype, $KIWITREES_EMAIL;

		$changes=KT_DB::prepare(
			"SELECT 1".
			" FROM `##change`".
			" WHERE status='pending'".
			" LIMIT 1"
		)->fetchOne();

		$days    =get_block_setting($block_id, 'days',     1);
		$sendmail=get_block_setting($block_id, 'sendmail', true);
		$block   =get_block_setting($block_id, 'block',    true);
		if ($cfg) {
			foreach (array('days', 'sendmail', 'block') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name=$cfg[$name];
				}
			}
		}

		if ($changes) {
			//-- if the time difference from the last email is greater than 24 hours then send out another email
			$LAST_CHANGE_EMAIL=KT_Site::preference('LAST_CHANGE_EMAIL');
			if (KT_TIMESTAMP - $LAST_CHANGE_EMAIL > (60*60*24*$days)) {
				$LAST_CHANGE_EMAIL = KT_TIMESTAMP;
				KT_Site::preference('LAST_CHANGE_EMAIL', $LAST_CHANGE_EMAIL);
				if ($sendmail == true) {
					// Which users have pending changes?
					$users_with_changes = array();
					foreach (get_all_users() as $user_id=>$user_name) {
						foreach (KT_Tree::getAll() as $tree) {
							if (exists_pending_change($user_id, $tree->tree_id)) {
								$users_with_changes[$user_id]=$user_name;
								break;
							}
						}
					}
					foreach ($users_with_changes as $user_id=>$user_name) {
						//-- send message
						$message			= array();
						$message["to"]		= $user_name;
						$message["from"]	= $KIWITREES_EMAIL;
						$message["subject"]	= KT_I18N::translate('kiwitrees - Review changes');
						$message["body"]	= KT_I18N::translate('Online changes have been made to a genealogical database. These changes need to be reviewed and accepted before they will appear to all users. Please use the URL below to enter that kiwitrees site and login to review the changes.');
						$message["method"]	= get_user_setting($user_id, 'contactmethod');
						$message["url"]		= KT_SERVER_NAME . KT_SCRIPT_PATH;
						$message["no_from"] = true;
						addMessage($message);
					}
				}
			}
			if (KT_USER_CAN_EDIT) {
				$id		=$this->getName() . $block_id;
				$class	= $this->getName() . '_block';
				if (KT_USER_GEDCOM_ADMIN) {
					$title = '<i class="icon-admin" title="'.KT_I18N::translate('Configure').'" onclick="modalDialog(\'block_edit.php?block_id='.$block_id.'\', \''.$this->getTitle().'\');"></i>';
				} else {
					$title = '';
				}
				$title .= $this->getTitle().help_link('review_changes', $this->getName());

				$content = '';
				if (KT_USER_CAN_ACCEPT) {
					$content .= '<a href="edit_changes.php" target="_blank" rel="noopener noreferrer">' . KT_I18N::translate('There are pending changes for you to moderate.') . '</a><br>';
				}
				if ($sendmail == "yes") {
					$content .= KT_I18N::translate('Last email reminder was sent ').format_timestamp($LAST_CHANGE_EMAIL) . "<br>";
					$content .= KT_I18N::translate('Next email reminder will be sent after ') . format_timestamp($LAST_CHANGE_EMAIL+(60*60*24*$days))."<br><br>";
				}
				$changes=KT_DB::prepare(
					"SELECT xref".
					" FROM  `##change`".
					" WHERE status='pending'".
					" AND   gedcom_id=?".
					" GROUP BY xref"
				)->execute(array(KT_GED_ID))->fetchAll();
				foreach ($changes as $change) {
					$record=KT_GedcomRecord::getInstance($change->xref);
					if ($record->canDisplayDetails()) {
						$content.='<b>'.$record->getFullName().'</b>';
						switch ($record->getType()) {
						case 'INDI':
						case 'FAM':
						case 'SOUR':
						case 'OBJE':
							$content.=$block ? '<br>' : ' ';
							$content.='<a href="'.$record->getHtmlUrl().'">'.KT_I18N::translate('View the changes').'</a>';
							break;
						}
						$content.='<br>';
					}
				}

				if ($template) {
					if ($block) {
						require KT_THEME_DIR . 'templates/block_small_temp.php';
					} else {
						require KT_THEME_DIR . 'templates/block_main_temp.php';
					}
				} else {
					return $content;
				}
			}
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
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'days',     KT_Filter::postInteger('num', 1, 180, 7));
			set_block_setting($block_id, 'sendmail', KT_Filter::postBool('sendmail'));
			set_block_setting($block_id, 'block',    KT_Filter::postBool('block'));
			exit;
		}

		require_once KT_ROOT.'includes/functions/functions_edit.php';

		$sendmail=get_block_setting($block_id, 'sendmail', true);
		$days=get_block_setting($block_id, 'days', 7);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Send out reminder emails?');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('sendmail', $sendmail);
		echo '<br>';
		echo KT_I18N::translate('Reminder email frequency (days)')."&nbsp;<input type='text' name='days' value='".$days."' size='2'>";
		echo '</td></tr>';

		$block=get_block_setting($block_id, 'block', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo /* I18N: label for a yes/no option */ KT_I18N::translate('Add a scrollbar when block contents grow');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('block', $block);
		echo '</td></tr>';
	}
}
