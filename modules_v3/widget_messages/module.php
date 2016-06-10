<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class widget_messages_WT_Module extends WT_Module implements WT_Module_Widget {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Messages');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Messages” module */ WT_I18N::translate('Communicate directly with other users, using private messages.');
	}

	// Implement class WT_Module_Block
	public function getWidget($widget_id, $template=true, $cfg=null) {
		global $ctype;

		require_once WT_ROOT.'includes/functions/functions_print_facts.php';

		// Block actions
		$action     = WT_Filter::post('action');
		$message_id = WT_Filter::postArray('message_id');
		if ($action == 'deletemessage') {
			if (is_array($message_id)) {
				foreach ($message_id as $msg_id) {
					deleteMessage($msg_id);
				}
			} else {
				deleteMessage($message_id);
			}
		}
		$messages = getUserMessages(WT_USER_ID);

		$id=$this->getName();
		$class=$this->getName();
		$title=WT_I18N::plural('%s message', '%s messages',count($messages), WT_I18N::number(count($messages)));
		$content='<form name="messageform" method="post" onsubmit="return confirm(\''.WT_I18N::translate('Are you sure you want to delete this message?  It cannot be retrieved later.').'\');">';
		if (get_user_count()>1) {
			$content.='<br>'.WT_I18N::translate('Send Message')." <select name=\"touser\">";
			$content.='<option value="">' . WT_I18N::translate('Select') . '</option>';
			foreach (get_all_users() as $user_id=>$user_name) {
				if ($user_id!=WT_USER_ID && get_user_setting($user_id, 'verified_by_admin') && get_user_setting($user_id, 'contactmethod')!='none') {
					$content.='<option value="'.$user_name.'">';
					$content.='<span dir="auto">'.htmlspecialchars(getUserFullName($user_id)).'</span> - <span dir="auto">'.$user_name.'</span>';
					$content.='</option>';
				}
			}
			$content.='</select> <input type="button" value="'.WT_I18N::translate('Send').'" onclick="message(document.messageform.touser.options[document.messageform.touser.selectedIndex].value, \'messaging2\', \'\', \'\'); return false;"><br><br>';
		}
		if (count($messages)==0) {
			$content.=WT_I18N::translate('You have no pending messages.')."<br>";
		} else {
			$content.='<input type="hidden" name="action" value="deletemessage">';
			$content.='<table class="list_table ' . $this->getName() . '_widget"><tr>';
			$content.='<td class="list_label">'.WT_I18N::translate('Delete').'<br><a href="#" onclick="jQuery(\'.'.$this->getName().'_widget :checkbox\').attr(\'checked\',\'checked\'); return false;">'.WT_I18N::translate('All').'</a></td>';
			$content.='<td class="list_label">'.WT_I18N::translate('Subject:').'</td>';
			$content.='<td class="list_label">'.WT_I18N::translate('Date Sent:').'</td>';
			$content.='<td class="list_label">'.WT_I18N::translate('Email Address:').'</td>';
			$content.='</tr>';
			foreach ($messages as $message) {
				$content.='<tr>';
				$content.='<td class="list_value_wrap"><input type="checkbox" id="cb_message'.$message->message_id.'" name="message_id[]" value="'.$message->message_id.'"></td>';
				$content.='<td class="list_value_wrap"><a href="#" onclick="return expand_layer(\'message'.$message->message_id.'\');"><i id="message'.$message->message_id.'_img" class="icon-plus"></i> <b>'.htmlspecialchars($message->subject).'</b></a></td>';
				$content.='<td class="list_value_wrap">'.format_timestamp($message->created).'</td>';
				$content.='<td class="list_value_wrap">';
				$user_id=get_user_id($message->sender);
				if ($user_id) {
					$content.='<span dir="auto">'.getUserFullName($user_id).'</span>';
					$content.='  - <span dir="auto">'.getUserEmail($user_id).'</span>';
				} else {
					$content.='<a href="mailto:'.htmlspecialchars($message->sender).'">'.htmlspecialchars($message->sender).'</a>';
				}
				$content.='</td>';
				$content.='</tr>';
				$content.='<tr><td class="list_value_wrap" colspan="5"><div id="message'.$message->message_id.'" style="display:none;">';
				// PHP5.3 $content.=expand_urls(nl2br(htmlspecialchars($message->body), false)).'<br><br>';
				$content.=expand_urls(nl2br(htmlspecialchars($message->body))).'<br><br>';
				if (strpos($message->subject, /* I18N: When replying to an email, the subject becomes “RE: <subject>” */ WT_I18N::translate('RE: '))!==0) {
					$message->subject= WT_I18N::translate('RE: ').$message->subject;
				}
				if ($user_id) {
					$content.='<a href="#" onclick="reply(\''.htmlspecialchars($message->sender, ENT_QUOTES).'\', \''.htmlspecialchars($message->subject, ENT_QUOTES).'\'); return false;">'.WT_I18N::translate('Reply').'</a> | ';
				}
				$content.='<a href="index.php?action=deletemessage&amp;message_id='.$message->message_id.'" onclick="return confirm(\''.WT_I18N::translate('Are you sure you want to delete this message?  It cannot be retrieved later.').'\');">'.WT_I18N::translate('Delete').'</a></div></td></tr>';
			}
			$content.='</table>';
			$content.='<input type="submit" value="'.WT_I18N::translate('Delete Selected Messages').'"><br>';
		}
		$content.='</form>';

		if ($template) {
			require WT_THEME_DIR.'templates/widget_template.php';
		} else {
			return $content;
		}
	}

	// Implement class WT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement WT_Module_Sidebar
	public function defaultWidgetOrder() {
		return 100;
	}

	// Implement WT_Module_Menu
	public function defaultAccessLevel() {
		return false;
	}

	// Implement class WT_Module_Block
	public function configureBlock($widget_id) {
		return false;
	}
}
