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

class random_media_WT_Module extends WT_Module implements WT_Module_Block {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */WT_I18N::translate('Slide show');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Slide show” module */ WT_I18N::translate('Random images from the current family tree.');
	}

	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $ctype, $foundlist;

		$filter			=get_block_setting($block_id, 'filter',   'all');
		$controls		=get_block_setting($block_id, 'controls', true);
		$start			=get_block_setting($block_id, 'start',    false) || WT_Filter::getBool('start');
		if ($block_id == '') {
			$start = 0;
			$controls = 0;
			$content = '';
		} else {
			$content = '
				<script>
					var play = false;
					function togglePlay() {
						if (play) {
							play = false;
							jQuery("#play_stop").removeClass("icon-media-stop").addClass("icon-media-play");
						} else {
							play = true;
							playSlideShow();
							jQuery("#play_stop").removeClass("icon-media-play").addClass("icon-media-stop");
						}
					};
					function playSlideShow() {
						if (play) {
							window.setTimeout("reload_image()", 5000);
						}
					};
					function reload_image() {
						if (play) {
							jQuery("#block_'. $block_id .'").load("index.php?ctype='. $ctype .'&action=ajax&block_id='. $block_id .'&start=1");
						}
					};
				</script>
			';
		}
		// We can apply the filters using SQL
		// Do not use "ORDER BY RAND()" - it is very slow on large tables.  Use PHP::array_rand() instead.
		$all_media=WT_DB::prepare(
			"SELECT m_id FROM `##media`" .
			" WHERE m_file = ?" .
			" AND m_ext  IN (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '')" .
			" AND m_type IN (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '')"
		)->execute(array(
			WT_GED_ID,
			get_block_setting($block_id, 'filter_avi',         false) ? 'avi'         : NULL,
			get_block_setting($block_id, 'filter_bmp',         true ) ? 'bmp'         : NULL,
			get_block_setting($block_id, 'filter_gif',         true ) ? 'gif'         : NULL,
			get_block_setting($block_id, 'filter_jpeg',        true ) ? 'jpg'         : NULL,
			get_block_setting($block_id, 'filter_jpeg',        true ) ? 'jpeg'        : NULL,
			get_block_setting($block_id, 'filter_mp3',         false) ? 'mp3'         : NULL,
			get_block_setting($block_id, 'filter_ole',         true ) ? 'ole'         : NULL,
			get_block_setting($block_id, 'filter_pcx',         true ) ? 'pcx'         : NULL,
			get_block_setting($block_id, 'filter_pdf',         false) ? 'pdf'         : NULL,
			get_block_setting($block_id, 'filter_png',         true ) ? 'png'         : NULL,
			get_block_setting($block_id, 'filter_tiff',        true ) ? 'tiff'        : NULL,
			get_block_setting($block_id, 'filter_wav',         false) ? 'wav'         : NULL,
			get_block_setting($block_id, 'filter_audio',       false) ? 'audio'       : NULL,
			get_block_setting($block_id, 'filter_book',        false) ? 'book'        : NULL,
			get_block_setting($block_id, 'filter_card',        false) ? 'card'        : NULL,
			get_block_setting($block_id, 'filter_certificate', false) ? 'certificate' : NULL,
			get_block_setting($block_id, 'filter_coat',        false) ? 'coat'        : NULL,
			get_block_setting($block_id, 'filter_document',    false) ? 'document'    : NULL,
			get_block_setting($block_id, 'filter_electronic',  false) ? 'electronic'  : NULL,
			get_block_setting($block_id, 'filter_fiche',       false) ? 'fiche'       : NULL,
			get_block_setting($block_id, 'filter_film',        false) ? 'film'        : NULL,
			get_block_setting($block_id, 'filter_magazine',    false) ? 'magazine'    : NULL,
			get_block_setting($block_id, 'filter_manuscript',  false) ? 'manuscript'  : NULL,
			get_block_setting($block_id, 'filter_map',         false) ? 'map'         : NULL,
			get_block_setting($block_id, 'filter_newspaper',   false) ? 'newspaper'   : NULL,
			get_block_setting($block_id, 'filter_other',       false) ? 'other'       : NULL,
			get_block_setting($block_id, 'filter_painting',    false) ? 'painting'    : NULL,
			get_block_setting($block_id, 'filter_photo',       true ) ? 'photo'       : NULL,
			get_block_setting($block_id, 'filter_tombstone',   true ) ? 'tombstone'   : NULL,
			get_block_setting($block_id, 'filter_video',       false) ? 'video'       : NULL,
		))->fetchOneColumn();

		// Keep looking through the media until a suitable one is found.
		$random_media = null;
		while ($all_media) {
			$n = array_rand($all_media);
			$media = WT_Media::getInstance($all_media[$n]);
			if ($media->canDisplayDetails() && !$media->isExternal()) {
				// Check if it is linked to a suitable individual
				foreach ($media->fetchLinkedIndividuals() as $indi) {
					if (
						$filter=='all' ||
						$filter=='indi'  && strpos($indi->getGedcomRecord(), "\n1 OBJE @" . $media->getXref() . '@') !==false ||
						$filter=='event' && strpos($indi->getGedcomRecord(), "\n2 OBJE @" . $media->getXref() . '@') !==false
					) {
						// Found one :-)
						$random_media = $media;
						break 2;
					}
				}
			}
			unset($all_media[$n]);
		};

		if ($ctype == 'gedcom' && WT_USER_GEDCOM_ADMIN || $ctype=='user' && WT_USER_ID) {
			$title = '<i class="icon-admin" title="'.WT_I18N::translate('Configure').'" onclick="modalDialog(\'block_edit.php?block_id='.$block_id.'\', \''.$this->getTitle().'\');"></i>';
		} else {
			$title='';
		}

		if ($start) {
			$title .= WT_I18N::translate('Slide Show');
			$content .= '<script>togglePlay();</script>';
		} else {
			$title .= WT_I18N::translate('Random image');
		}

		if ($random_media) {
			$content .= '<div id="random_media_container' .$block_id. '">';
				if ($controls) {
					if ($start) {
						$icon_class = 'icon-media-stop';
					} else {
						$icon_class = 'icon-media-play';
					}
					$content .= '
						<div dir="ltr" id="random_media_controls' . $block_id .'">
							<a href="#" onclick="togglePlay(); return false;" id="play_stop" class="'.$icon_class.'" title="' . WT_I18N::translate('Play') . '/' . WT_I18N::translate('Stop') . '"></a>
							<a href="#" onclick="jQuery(\'#block_'. $block_id.'\').load(\'index.php?ctype='.$ctype.'&amp;action=ajax&amp;block_id='.$block_id.'\');return false;" title="'.WT_I18N::translate('Next image').'" class="icon-media-next"></a>
						</div>';
				}

				$content .= '
					<div class="random_media_content">
						<div class="random_media_title"><a href="'.$random_media->getHtmlUrl().'" title="'. strip_tags($random_media->getFullName()) .'">'. $random_media->getFullName() .'</a></div>
						' .$random_media->displayImage(). '
					</div>
			</div>';
		} else {
			$content = WT_I18N::translate('This family tree has no images to display.');
		}
		$id = $this->getName().$block_id;
		$class = $this->getName().'_block';
		if ($template) {
			require WT_THEME_DIR.'templates/block_main_temp.php';
		} else {
			return $content;
		}
	}

	// Implement class WT_Module_Block
	public function loadAjax() {
		return true;
	}

	// Implement class WT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class WT_Module_Block
	public function configureBlock($block_id) {

		if (WT_Filter::postBool('save') && WT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'filter',					WT_Filter::post('filter', 'indi|event|all', 'all'));
			set_block_setting($block_id, 'controls',				WT_Filter::postBool('controls'));
			set_block_setting($block_id, 'start',					WT_Filter::postBool('start'));
			set_block_setting($block_id, 'filter_avi',				WT_Filter::postBool('filter_avi'));
			set_block_setting($block_id, 'filter_bmp',				WT_Filter::postBool('filter_bmp'));
			set_block_setting($block_id, 'filter_gif',				WT_Filter::postBool('filter_gif'));
			set_block_setting($block_id, 'filter_jpeg',				WT_Filter::postBool('filter_jpeg'));
			set_block_setting($block_id, 'filter_mp3',				WT_Filter::postBool('filter_mp3'));
			set_block_setting($block_id, 'filter_ole',				WT_Filter::postBool('filter_ole'));
			set_block_setting($block_id, 'filter_pcx',				WT_Filter::postBool('filter_pcx'));
			set_block_setting($block_id, 'filter_pdf',				WT_Filter::postBool('filter_pdf'));
			set_block_setting($block_id, 'filter_png',				WT_Filter::postBool('filter_png'));
			set_block_setting($block_id, 'filter_tiff',				WT_Filter::postBool('filter_tiff'));
			set_block_setting($block_id, 'filter_wav',				WT_Filter::postBool('filter_wav'));
			set_block_setting($block_id, 'filter_audio',			WT_Filter::postBool('filter_audio'));
			set_block_setting($block_id, 'filter_book',				WT_Filter::postBool('filter_book'));
			set_block_setting($block_id, 'filter_card',				WT_Filter::postBool('filter_card'));
			set_block_setting($block_id, 'filter_certificate',		WT_Filter::postBool('filter_certificate'));
			set_block_setting($block_id, 'filter_coat',				WT_Filter::postBool('filter_coat'));
			set_block_setting($block_id, 'filter_document',			WT_Filter::postBool('filter_document'));
			set_block_setting($block_id, 'filter_electronic',		WT_Filter::postBool('filter_electronic'));
			set_block_setting($block_id, 'filter_fiche',			WT_Filter::postBool('filter_fiche'));
			set_block_setting($block_id, 'filter_film',				WT_Filter::postBool('filter_film'));
			set_block_setting($block_id, 'filter_magazine',			WT_Filter::postBool('filter_magazine'));
			set_block_setting($block_id, 'filter_manuscript',		WT_Filter::postBool('filter_manuscript'));
			set_block_setting($block_id, 'filter_map',				WT_Filter::postBool('filter_map'));
			set_block_setting($block_id, 'filter_newspaper',		WT_Filter::postBool('filter_newspaper'));
			set_block_setting($block_id, 'filter_other',			WT_Filter::postBool('filter_other'));
			set_block_setting($block_id, 'filter_painting',			WT_Filter::postBool('filter_painting'));
			set_block_setting($block_id, 'filter_photo',			WT_Filter::postBool('filter_photo'));
			set_block_setting($block_id, 'filter_tombstone',		WT_Filter::postBool('filter_tombstone'));
			set_block_setting($block_id, 'filter_video',			WT_Filter::postBool('filter_video'));
			exit;
		}

		require_once WT_ROOT.'includes/functions/functions_edit.php';

		$filter		= get_block_setting($block_id, 'filter', 'all');
		$controls	= get_block_setting($block_id, 'controls', true);
		$start		= get_block_setting($block_id, 'start', false);
		$filters=array(
			'avi'        =>get_block_setting($block_id, 'filter_avi', false),
			'bmp'        =>get_block_setting($block_id, 'filter_bmp', true),
			'gif'        =>get_block_setting($block_id, 'filter_gif', true),
			'jpeg'       =>get_block_setting($block_id, 'filter_jpeg', true),
			'mp3'        =>get_block_setting($block_id, 'filter_mp3', false),
			'ole'        =>get_block_setting($block_id, 'filter_ole', true),
			'pcx'        =>get_block_setting($block_id, 'filter_pcx', true),
			'pdf'        =>get_block_setting($block_id, 'filter_pdf', false),
			'png'        =>get_block_setting($block_id, 'filter_png', true),
			'tiff'       =>get_block_setting($block_id, 'filter_tiff', true),
			'wav'        =>get_block_setting($block_id, 'filter_wav', false),
			'audio'      =>get_block_setting($block_id, 'filter_audio', false),
			'book'       =>get_block_setting($block_id, 'filter_book', true),
			'card'       =>get_block_setting($block_id, 'filter_card', true),
			'certificate'=>get_block_setting($block_id, 'filter_certificate', true),
			'coat'       =>get_block_setting($block_id, 'filter_coat', true),
			'document'   =>get_block_setting($block_id, 'filter_document', true),
			'electronic' =>get_block_setting($block_id, 'filter_electronic', true),
			'fiche'      =>get_block_setting($block_id, 'filter_fiche', true),
			'film'       =>get_block_setting($block_id, 'filter_film', true),
			'magazine'   =>get_block_setting($block_id, 'filter_magazine', true),
			'manuscript' =>get_block_setting($block_id, 'filter_manuscript', true),
			'map'        =>get_block_setting($block_id, 'filter_map', true),
			'newspaper'  =>get_block_setting($block_id, 'filter_newspaper', true),
			'other'      =>get_block_setting($block_id, 'filter_other', true),
			'painting'   =>get_block_setting($block_id, 'filter_painting', true),
			'photo'      =>get_block_setting($block_id, 'filter_photo', true),
			'tombstone'  =>get_block_setting($block_id, 'filter_tombstone', true),
			'video'      =>get_block_setting($block_id, 'filter_video', false),
		);

		$html = '
			<script>
				function toggle(source) {
					checkboxes = document.getElementsByClassName("check");
					for(var i=0, n=checkboxes.length;i<n;i++) {
				    	checkboxes[i].checked = source.checked;
				 	}
				}
				function toggle2(source) {
					checkboxes = document.getElementsByClassName("check2");
					for(var i=0, n=checkboxes.length;i<n;i++) {
				    	checkboxes[i].checked = source.checked;
				 	}
				}
			</script>
		';

		$html .= '
			<tr>
				<td class="descriptionbox wrap width33">'.
					WT_I18N::translate('Show only individuals, events, or all?').
				'</td>
				<td class="optionbox">'.
					select_edit_control('filter', array('indi'=>WT_I18N::translate('Individuals'), 'event'=>WT_I18N::translate('Facts and events'), 'all'=>WT_I18N::translate('All')), null, $filter, '').
				'</td>
			</tr>
			<tr>
				<td class="descriptionbox wrap width33">'. WT_I18N::translate('Filter'). '</td>
				<td class="optionbox">
					<h4 style="margin-bottom:0;">'. WT_Gedcom_Tag::getLabel('FORM'). '</h4>
					<table class="width100">
						<tr>
							<td colspan="3" class="center">
								<input id="toggle1" type="checkbox" onClick="toggle(this)" >&nbsp;&nbsp;' .WT_I18N::translate('Select all').
							'</td>
						</tr>
						<tr>
							<td class="width33"><input class="check" type="checkbox" value="yes" name="filter_avi"';
								if ($filters['avi']) $html .= ' checked="checked" '; $html .= '>&nbsp;&nbsp;avi&nbsp;&nbsp;
							</td>
							<td class="width33"><input class="check" type="checkbox" value="yes" name="filter_bmp"';
								if ($filters['bmp']) $html .= ' checked="checked" '; $html .= '>&nbsp;&nbsp;bmp&nbsp;&nbsp;
							</td>
							<td class="width33"><input class="check" type="checkbox" value="yes" name="filter_gif"';
								if ($filters['gif']) $html .= ' checked="checked" '; $html .= '>&nbsp;&nbsp;gif&nbsp;&nbsp;
							</td>
						</tr>
						<tr>
							<td class="width33"><input class="check" type="checkbox" value="yes" name="filter_jpeg"';
								if ($filters['jpeg']) $html .= ' checked="checked" '; $html .= '>&nbsp;&nbsp;jpeg&nbsp;&nbsp;
							</td>
							<td class="width33"><input class="check" type="checkbox" value="yes" name="filter_mp3"';
								if ($filters['mp3']) $html .= ' checked="checked" '; $html .= '>&nbsp;&nbsp;mp3&nbsp;&nbsp;
							</td>
							<td class="width33"><input class="check" type="checkbox" value="yes" name="filter_ole"';
								if ($filters['ole']) $html .= ' checked="checked" '; $html .= '>&nbsp;&nbsp;ole&nbsp;&nbsp;
							</td>
						</tr>
						<tr>
							<td class="width33"><input class="check" type="checkbox" value="yes" name="filter_pcx"';
								if ($filters['pcx']) $html .= ' checked="checked" '; $html .= '>&nbsp;&nbsp;pcx&nbsp;&nbsp;
							</td>
							<td class="width33"><input class="check" type="checkbox" value="yes" name="filter_pdf"';
								if ($filters['pdf']) $html .= ' checked="checked" '; $html .= '>&nbsp;&nbsp;pdf&nbsp;&nbsp;
							</td>
							<td class="width33"><input class="check" type="checkbox" value="yes" name="filter_png"';
								if ($filters['png']) $html .= ' checked="checked" '; $html .= '>&nbsp;&nbsp;png&nbsp;&nbsp;
							</td>
						</tr>
						<tr>
							<td class="width33"><input class="check" type="checkbox" value="yes" name="filter_tiff"';
								if ($filters['tiff']) $html .= ' checked="checked" ';$html .= '>&nbsp;&nbsp;tiff&nbsp;&nbsp;
							</td>
							<td class="width33"><input class="check" type="checkbox" value="yes" name="filter_wav"';
								if ($filters['wav']) $html .= ' checked="checked" '; $html .= '>&nbsp;&nbsp;wav&nbsp;&nbsp;
							</td>
							<td class="width33">&nbsp;</td>
						</tr>
					</table>
					<h4 style="margin-bottom:0;">'. WT_Gedcom_Tag::getLabel('TYPE'). '</h4>
					<table class="width100" id="type_list">
						<tr>
							<td colspan="3" class="center">
								<input type="checkbox" onClick="toggle2(this)" >&nbsp;&nbsp;' .WT_I18N::translate('Select all').
							'</td>
						</tr>
						<tr>';
						//-- Build the list of checkboxes
						$i = 0;
						foreach (WT_Gedcom_Tag::getFileFormTypes() as $typeName => $typeValue) {
							$i++;
							if ($i > 3) {
								$i = 1;
								$html .= '</tr><tr>';
							}
							$html .= '<td class="width33"><input class="check2" type="checkbox" value="yes" name="filter_'. $typeName .'"';
							if ($filters[$typeName]) $html .= ' checked="checked" ';
							$html .= '>&nbsp;&nbsp;'. $typeValue .'&nbsp;&nbsp;</td>';
						}
						$html .= '</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="descriptionbox wrap width33">'.
					WT_I18N::translate('Show slide show controls?').
				'</td>
				<td class="optionbox">'.
					edit_field_yes_no('controls', $controls).
				'</td>
			</tr>
			<tr>
				<td class="descriptionbox wrap width33">'.
					WT_I18N::translate('Start slide show on page load?').
				'</td><td class="optionbox">'.
					edit_field_yes_no('start', $start).
				'</td>
			</tr>';
		// output
		ob_start();
		$html .= ob_get_clean();
		echo $html;
	}
}
