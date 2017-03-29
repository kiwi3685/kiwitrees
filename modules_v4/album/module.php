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

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class album_WT_Module extends WT_Module implements WT_Module_Tab, WT_Module_Config {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Album');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Album” module */ WT_I18N::translate('An alternative to the “media” tab, and an enhanced image viewer.');
	}

	// Implement WT_Module_Tab
	public function defaultAccessLevel() {
		return false;
	}

	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 90;
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_config':
			$this->config();
			break;
		case 'admin_reset':
			$this->album_reset();
			$this->config();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}

	// Implement WT_Module_Tab
	public function hasTabContent() {
		return WT_USER_CAN_EDIT || $this->get_media_count()>0;
	}

	// Implement WT_Module_Tab
	public function isGrayedOut() {
		return $this->get_media_count()==0;
	}

	// Implement WT_Module_Tab
	public function getTabContent() {
		global $controller;

		$ALBUM_GROUPS = get_module_setting($this->getName(), 'ALBUM_GROUPS');
		if (!isset($ALBUM_GROUPS)) {
			$ALBUM_GROUPS = 4;
		}

		require_once WT_ROOT.WT_MODULES_DIR.'album/album_print_media.php';
		$html='<div id="'.$this->getName().'_content">';
			//Show Album header Links
			if (WT_USER_CAN_EDIT) {
				$html.='<div class="descriptionbox rela">';
				// Add a media object
				if (get_gedcom_setting(WT_GED_ID, 'MEDIA_UPLOAD') >= WT_USER_ACCESS_LEVEL) {
					$html.='<span><a href="addmedia.php?action=showmediaform&amp;linktoid=' . $controller->record->getXref() . '" target="_blank" rel="noopener noreferrer"><i style="margin: 0 3px 0 10px;" class="icon-image_add">&nbsp;</i>' .WT_I18N::translate('Add a media object'). '</a></span>';
					// Link to an existing item
					$html.='<span><a href="inverselink.php?linktoid=' . $controller->record->getXref() . '&amp;linkto=person" target="_blank"><i style="margin: 0 3px 0 10px;" class="icon-image_link">&nbsp;</i>' .WT_I18N::translate('Link to an existing media object'). '</a></span>';
				}
				if (WT_USER_GEDCOM_ADMIN && $this->get_media_count()>1) {
					// Popup Reorder Media
					$html.='<span><a href="#" onclick="reorder_media(\''.$controller->record->getXref().'\')"><i style="margin: 0 3px 0 10px;" class="icon-image_sort">&nbsp;</i>' .WT_I18N::translate('Re-order media'). '</a></span>';
				}
				$html.='</div>';
			}
		$media_found = false;

		$html .= '<div style="width:100%; vertical-align:top;">';
		ob_start();
		if ($ALBUM_GROUPS == 0) {
			album_print_media($controller->record->getXref(), 0, true);
		} else {
			for ($i = 0; $i < $ALBUM_GROUPS; $i++) {
				ob_start();
				album_print_media($controller->record->getXref(), 0, true, $i);
				$print_row = ob_get_contents();
				$check = strrpos($print_row, "class=\"pic\"");
				if(!$check) {
					ob_end_clean();
				} else {
					ob_end_flush();
				}
			}
		}
		return
			$html.
			ob_get_clean().
			'</div>';
	}


	// Implement WT_Module_Tab
	public function canLoadAjax() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER; // Search engines cannot use AJAX
	}

	// Implement WT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

	// Reset all settings to default
	private function album_reset() {
		WT_DB::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'ALBUM%'")->execute();
		AddToLog($this->getTitle().' reset to default values', 'config');
	}

	protected $mediaCount = null;

	private function get_media_count() {
		global $controller;

		if ($this->mediaCount===null) {
			$this->mediaCount = 0;
			preg_match_all('/\d OBJE @(' . WT_REGEX_XREF . ')@/', $controller->record->getGedcomRecord(), $matches);
			foreach ($matches[1] as $match) {
				$obje = WT_Media::getInstance($match);
				if ($obje && $obje->canDisplayDetails()) {
					$this->mediaCount++;
				}
			}
			foreach ($controller->record->getSpouseFamilies() as $sfam) {
				preg_match_all('/\d OBJE @(' . WT_REGEX_XREF . ')@/', $sfam->getGedcomRecord(), $matches);
				foreach ($matches[1] as $match) {
					$obje = WT_Media::getInstance($match);
					if ($obje && $obje->canDisplayDetails()) {
						$this->mediaCount++;
					}
				}
			}
		}
		return $this->mediaCount;
	}

	private function find_no_type() {
		$medialist = WT_Query_Media::medialist('', 'include', 'title', '', 'blank');
		$ct = count($medialist);
		if ($medialist) {
			$html = '
				<p>' .WT_I18N::translate('%s media objects', $ct). '</p>
				<table>
					<tr>
						<th>' . WT_I18N::translate('Media object') . '</th>
						<th>' . WT_I18N::translate('Media title') . '</th>
					</tr>';
					for ($i=0; $i<$ct; ++$i) {
						$mediaobject = $medialist[$i];
						$html .= '<tr>
							<td>' . $mediaobject->displayImage() . '</td>
							<td>
								<a href="addmedia.php?action=editmedia&pid=' . $mediaobject->getXref() . '" target="_blank">' . $mediaobject->getFullName() . '</a>
							</td>
						</tr>';
					}
				$html .= '</table>';
		} else {
			$html = '<p>' .WT_I18N::translate('No media objects found'). '</p>';
		}
		return $html;
	}

	private function getJS() {
		return '';
	}

	private function config() {
		require WT_ROOT.'includes/functions/functions_edit.php';
		$controller = new WT_Controller_Page();
		$controller
			->restrictAccess(WT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader();

		if (WT_Filter::postBool('save')) {
			$ALBUM_GROUPS = WT_Filter::post('NEW_ALBUM_GROUPS');
			$ALBUM_TITLES = WT_Filter::postArray('NEW_ALBUM_TITLES');
			$ALBUM_OPTIONS = WT_Filter::postArray('NEW_ALBUM_OPTIONS');
			if (isset($ALBUM_GROUPS)) set_module_setting($this->getName(), 'ALBUM_GROUPS', $ALBUM_GROUPS);
			if (!empty($ALBUM_TITLES)) set_module_setting($this->getName(), 'ALBUM_TITLES', serialize($ALBUM_TITLES));
			if (!empty($ALBUM_OPTIONS)) set_module_setting($this->getName(), 'ALBUM_OPTIONS', serialize($ALBUM_OPTIONS));

			AddToLog($this->getTitle().' set to new values', 'config');
		}
		$SHOW_FIND = WT_Filter::post('show');
		$HIDE_FIND = WT_Filter::post('hide');
		$ALBUM_GROUPS = get_module_setting($this->getName(), 'ALBUM_GROUPS');
		$ALBUM_TITLES = unserialize(get_module_setting($this->getName(), 'ALBUM_TITLES'));
		$ALBUM_OPTIONS = unserialize(get_module_setting($this->getName(), 'ALBUM_OPTIONS'));

		if (!isset($ALBUM_GROUPS)) {
			$ALBUM_GROUPS = 4;
		}

		if (empty($ALBUM_TITLES)) {
			$ALBUM_TITLES = array(
				WT_I18N::translate('Photos'),
				WT_I18N::translate('Documents'),
				WT_I18N::translate('Census'),
				WT_I18N::translate('Other')
			);
		}

		$default_groups = array(
				WT_I18N::translate('Other'),
				WT_I18N::translate('Other'),
				WT_I18N::translate('Documents'),
				WT_I18N::translate('Documents'),
				WT_I18N::translate('Other'),
				WT_I18N::translate('Documents'),
				WT_I18N::translate('Census'),
				WT_I18N::translate('Documents'),
				WT_I18N::translate('Documents'),
				WT_I18N::translate('Documents'),
				WT_I18N::translate('Census'),
				WT_I18N::translate('Census'),
				WT_I18N::translate('Documents'),
				WT_I18N::translate('Other'),
				WT_I18N::translate('Photos'),
				WT_I18N::translate('Photos'),
				WT_I18N::translate('Photos'),
				WT_I18N::translate('Other')
		);

		if (empty($ALBUM_OPTIONS))	{
			$ALBUM_OPTIONS = array_combine(array_keys(WT_Gedcom_Tag::getFileFormTypes()), $default_groups);
		}

		$html = '<div id="album_config">';
			$html .= '<a class="current faq_link" href="http://kiwitrees.net/faqs/modules-faqs/album/" target="_blank" rel="noopener noreferrer" title="'. WT_I18N::translate('View FAQ for this page.'). '">'. WT_I18N::translate('View FAQ for this page.'). '<i class="fa fa-comments-o"></i></a>
			<h2>' .$controller->getPageTitle(). '</h2>
			<h3>' . WT_I18N::translate('Configure display of grouped media items using GEDCOM media tag TYPE.').  '</h3>';

			// check for emty groups
			foreach ($ALBUM_TITLES as $value) {
				if(!in_array($value, $ALBUM_OPTIONS)) echo '<script>alert(\''.WT_I18N::translate('You can not have any empty group.').'\')</script>';
			}

			$html .= '<form method="post" name="album_form" action="'.$this->getConfigLink().'">
				<input type="hidden" name="save" value="1">
				<div id="album_groups">
					<label for="NEW_ALBUM_GROUPS" class="label">'.WT_I18N::translate('Number of groups').'</label>'.
					select_edit_control('NEW_ALBUM_GROUPS',
						array(
							0=>WT_I18N::number(0),
							1=>WT_I18N::number(1),
							2=>WT_I18N::number(2),
							3=>WT_I18N::number(3),
							4=>WT_I18N::number(4),
							5=>WT_I18N::number(5),
							6=>WT_I18N::number(6),
							7=>WT_I18N::number(7)
						), null, $ALBUM_GROUPS);

				$html .= '</div>
				<div id="album_options">
					<label for="NEW_ALBUM_OPTIONS" class="label">'.WT_I18N::translate('Match groups to types').'</label>
					<table>';
						$html .= '<tr><th colspan="2" rowspan="2"></th><th colspan="4">'.WT_I18N::translate('Groups (These must always be English titles)').'</th></tr>';
							for ($i = 0; $i < $ALBUM_GROUPS; $i++) {
								$html .= '<th style="min-width:130px;"><input type="input" name="NEW_ALBUM_TITLES[]" value="' .(isset($ALBUM_TITLES[$i]) ? $ALBUM_TITLES[$i] : ""). '"></th>';
							}
						$html .= '</tr>';
							$html .= '<tr><th rowspan="19" style="max-width:25px;"><span class="rotate">'.WT_I18N::translate('Types').'</span></th></tr>';
							foreach ($ALBUM_OPTIONS as $key=>$value) {
								$translated_type = WT_Gedcom_Tag::getFileFormTypeValue($key);
								$html .= '
									<tr>
										<td>' .$translated_type. '</td>';
										for ($i = 0; $i < $ALBUM_GROUPS; $i++) {
											if (isset($ALBUM_TITLES[$i]) && $value == $ALBUM_TITLES[$i]) {
												$html .= '<td><input type="radio" name="NEW_ALBUM_OPTIONS[' .$key. ']" value="' .(isset($ALBUM_TITLES[$i]) ? $ALBUM_TITLES[$i] : ""). '" checked="checked"></td>';
											} else {
												$html .= '<td><input type="radio" name="NEW_ALBUM_OPTIONS[' .$key. ']" value="' .(isset($ALBUM_TITLES[$i]) ? $ALBUM_TITLES[$i] : ""). '"></td>';
											}
										}
									$html .= '</tr>
								';
							}
					$html .= '</table>
				</div>
				<button class="btn btn-primary save" type="submit">
					<i class="fa fa-floppy-o"></i>'.
					WT_I18N::translate('save').'
				</button>
			</form>
			<button class="btn btn-primary reset" type="submit" onclick="if (confirm(\''.WT_I18N::translate('The settings will be reset to default (for all trees). Are you sure you want to do this?').'\')) window.location.href=\'module.php?mod='.$this->getName().'&amp;mod_action=admin_reset\';">
				<i class="fa fa-refresh"></i>'.
				WT_I18N::translate('Reset').'
			</button>
			<form method="post" name="find_show" action="'.$this->getConfigLink().'">
				<div id="album_find">
				    <input type="hidden" name="show">
				    <a class="current" href="javascript:document.find_show.submit()">'.WT_I18N::translate('Show media objects with no TYPE'). '</a>';
					if (isset($SHOW_FIND) && !isset($HIDE_FIND)) {
						$html .= '<div id="show_list">' .$this->find_no_type(). '</div>
						<input type="submit" name="hide" value="'.WT_I18N::translate('close'). '">';
					}
				$html .= '</div>
			</form>
		</div>';
		// output
		ob_start();
		$html .= ob_get_contents();
		ob_end_clean();
		echo $html;
	}
}
