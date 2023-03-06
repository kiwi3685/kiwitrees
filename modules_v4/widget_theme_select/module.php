<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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

class widget_theme_select_KT_Module extends KT_Module implements KT_Module_Widget {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Theme change');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Theme change” module */ KT_I18N::translate('An alternative way to select a new theme.');
	}

	// Implement class KT_Module_Block
	public function getWidget($widget_id, $template = true, $cfg = null) {
		$id					= $this->getName() . $widget_id;
		$class				= $this->getName();
		$title				= $this->getTitle();
		$current_themedir	= str_replace(array('themes','/'), '', KT_THEME_DIR);

		if (KT_Filter::post('action') == 'update') {
			set_gedcom_setting(KT_GED_ID, 'THEME_DIR', KT_Filter::post('NEW_THEME_DIR'));
		}

		if(strstr(get_query_url(), 'php?')) {
			$separator = '&amp;';
		} else {
			$separator = '?';
		}

		$content = '
			<div class="center theme_form">
				<form method="post" id="themeForm" name="themeForm" action="update">';
					foreach (get_theme_names() as $themename=>$themedir) {
						$themedir == $current_themedir ? $current = 'class="current"' : $current = '';
						$content .= '
							<div ' . $current . '>
								<a href="#" onclick="jQuery.post(\'action.php\',{action:\'theme\',theme:\'' . $themedir . '\'},function(){location.reload();})">
									<img src="themes/' . $themedir . '/images/screenshot_' . $themedir . '.png" alt="' . $themename . ' title="' . $themename . '">
									<p>
										<input type="hidden" id="hidden_' . $themedir . '" name="NEW_THEME_DIR" value="' . $themedir . '" ' . ($current_themedir == $themedir ? ' checked="checked"' : '') . '>' .
										$themename . '
									</p>
								</a>
							</div>
						';
					}
		$content .= '
				</form>
			</div>
		<br>';

		if ($template) {
			require KT_THEME_DIR . 'templates/widget_template.php';
		} else {
			return $content;
		}
	}

	// Implement class KT_Module_Widget
	public function loadAjax() {
		return false;
	}

	// Implement KT_Module_Widget
	public function defaultWidgetOrder() {
		return 130;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_NONE; // Access to GEDCOM manager only
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
	}
}
