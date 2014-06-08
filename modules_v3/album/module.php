<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
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
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

if (!defined('WT_WEBTREES')) {
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
	public function defaultTabOrder() {
		return 60;
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
				// Add a new media object
				if (get_gedcom_setting(WT_GED_ID, 'MEDIA_UPLOAD') >= WT_USER_ACCESS_LEVEL) {
					$html.='<span><a href="#" onclick="window.open(\'addmedia.php?action=showmediaform&linktoid='.$controller->record->getXref().'\', \'_blank\', edit_window_specs);return false;"><i style="margin: 0 3px 0 10px;" class="icon-image_add">&nbsp;</i>' .WT_I18N::translate('Add a new media object'). '</a></span>';;
					// Link to an existing item
					$html.='<span><a href="#" onclick="window.open(\'inverselink.php?linktoid='.$controller->record->getXref().'&linkto=person\', \'_blank\', \'resizable=1,scrollbars=1,top=50,height=300,width=450\');"><i style="margin: 0 3px 0 10px;" class="icon-image_link">&nbsp;</i>' .WT_I18N::translate('Link to an existing media object'). '</a></span>';;
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
				album_print_media($controller->record->getXref(), 0, true, $i);
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
		$sqlmm =
			"SELECT DISTINCT m_id, m_titl" .
			" FROM `##media`" .
			" JOIN `##link` ON (m_id=l_to AND m_file=l_file AND l_type='OBJE')" .
			" WHERE m_gedcom NOT LIKE '%TYPE %'";

		$rows = WT_DB::prepare($sqlmm)->fetchAll(PDO::FETCH_ASSOC);

		if ($rows) {
			$html = '
				<p>' .WT_I18N::translate('%s media objects', count($rows)). '</p>
				<table>
					<tr>
						<th>' .WT_I18N::translate('Media object'). '</th>
						<th>' .WT_I18N::translate('Media title'). '</th>
					</tr>';
					foreach ($rows as $key => $value) {
						$media=WT_Media::getInstance($value['m_id']);
						//  Get the title of the media
						if ($media) {
							$mediaTitle = $media->getFullName();
						} else {
							$mediaTitle = $rowm['m_id'];
						}
						
						$html .= '<tr>
							<td>' .$media->displayImage(). '</td>
							<td><a href="#" onclick="return window.open(\'addmedia.php?action=editmedia&amp;pid=' .$value['m_id']. '\', \'_blank\', edit_window_specs);")>' .$mediaTitle. '</a></td>
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
		$controller = new WT_Controller_Page;
		$controller
			->requireAdminLogin()
			->setPageTitle($this->getTitle())
			->pageHeader();

		if (WT_Filter::postBool('save')) {
			$ALBUM_GROUPS = WT_Filter::post('NEW_ALBUM_GROUPS');
			$ALBUM_TITLES = WT_Filter::postArray('NEW_ALBUM_TITLES');
			$ALBUM_OPTIONS = WT_Filter::postArray('NEW_ALBUM_OPTIONS');
			if (isset($ALBUM_GROUPS)) set_module_setting($this->getName(), 'ALBUM_GROUPS', $ALBUM_GROUPS);				
			if (!empty($ALBUM_TITLES)) set_module_setting($this->getName(), 'ALBUM_TITLES', serialize($ALBUM_TITLES));				
			if (!empty($ALBUM_OPTIONS)) set_module_setting($this->getName(), 'ALBUM_OPTIONS', serialize($ALBUM_OPTIONS));				
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
				WT_I18N::translate('Census'),
				WT_I18N::translate('Census'),
				WT_I18N::translate('Documents'),
				WT_I18N::translate('Documents'),
				WT_I18N::translate('Documents'),
				WT_I18N::translate('Documents'),
				WT_I18N::translate('Other'),
				WT_I18N::translate('Photos'),
				WT_I18N::translate('Photos'),
				WT_I18N::translate('Photos'),
				WT_I18N::translate('Other')
		);

		if (empty($ALBUM_OPTIONS))	{
			$ALBUM_OPTIONS = array_combine(WT_Gedcom_Tag::getFileFormTypes(), $default_groups);
		}

		$html = '<div id="album_config">
			<h2>' .$controller->getPageTitle(). '</h2>
			<h3>' . WT_I18N::translate('Configure display of grouped media items using GEDCOM media tag TYPE.').  '</h3>

			<form method="post" name="album_form" action="'.$this->getConfigLink().'">
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
				<div id="album_buttons">
					<input type="submit" value="'.WT_I18N::translate('Save').'">
					<input type="reset" value="'.WT_I18N::translate('Reset').'" onclick="if (confirm(\''.WT_I18N::translate('The settings will be reset to default (for all trees). Are you sure you want to do this?').'\')) window.location.href=\'module.php?mod='.$this->getName().'&amp;mod_action=admin_reset\';">
				</div>
				<div id="album_options">
					<label for="NEW_ALBUM_OPTIONS" class="label">'.WT_I18N::translate('Match groups to types').'</label>
					<table>';
						$html .= '<tr>';
							$html .= '<th>&nbsp;</th>';
							for ($i = 0; $i < $ALBUM_GROUPS; $i++) {
								$html .= '<th style="min-width:130px;"><input type="input" name="NEW_ALBUM_TITLES[]" value="' .(isset($ALBUM_TITLES[$i]) ? WT_I18N::translate($ALBUM_TITLES[$i]) : ""). '"></th>';
							}
						$html .= '</tr>';
							foreach ($ALBUM_OPTIONS as $key=>$value) {
								$html .= '
									<tr>
										<td>' .$key. '</td>';
										for ($i = 0; $i < $ALBUM_GROUPS; $i++) {
											if (isset($ALBUM_TITLES[$i]) && $value == WT_I18N::translate($ALBUM_TITLES[$i])) {
												$html .= '<td><input type="radio" name="NEW_ALBUM_OPTIONS[' .$key. ']" value="' .(isset($ALBUM_TITLES[$i]) ? WT_I18N::translate($ALBUM_TITLES[$i]) : ""). '" checked="checked"></td>';
											} else {
												$html .= '<td><input type="radio" name="NEW_ALBUM_OPTIONS[' .$key. ']" value="' .(isset($ALBUM_TITLES[$i]) ? WT_I18N::translate($ALBUM_TITLES[$i]) : ""). '"></td>';
											}
										}
									$html .= '</tr>
								';
							}
					$html .= '</table>
				</div>
			</form>
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
		$html .= ob_get_clean();
		echo $html;
	}
}
