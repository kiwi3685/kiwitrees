<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

class fancy_imagebar_KT_Module extends KT_Module implements KT_Module_Config, KT_Module_Menu {

	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of the module */ KT_I18N::translate('Fancy Imagebar');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the module */ KT_I18N::translate('An image bar with small images on your home page between header and content.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 999;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'other';
	}

	/**
	 * Get the current tree id
	 *
	 * @global type $KT_TREE
	 * @return type
	 */
	protected function getTreeId() {
		global $KT_TREE;

		$tree = KT_TREE::getIdFromName(KT_Filter::get('ged'));
		if ($tree) {
			return $tree->getTreeId();
		} else {
			return $KT_TREE->getTreeId();
		}
	}

	/**
	 * Set the default module options
	 *
	 * @param type $key
	 * @return string
	 */
	private function setDefault($key) {
		$FIB_DEFAULT = [
			'IMAGES'	=> [], // All images
			'RANDOM'	=> '1',
			'TONE'		=> '2', // Colors
			'SEPIA'		=> '30', // Example
			'SIZE'		=> '60'
		];
		return $FIB_DEFAULT[$key];
	}

	/**
	 * Get module options
	 *
	 * @param type $k
	 * @return type
	 */
	protected function options($k) {
		$FIB_OPTIONS = unserialize(get_module_setting($this->getName(), 'FIB_OPTIONS'));
		$key		 = strtoupper($k);

		if (empty($FIB_OPTIONS[$this->getTreeId()]) || (is_array($FIB_OPTIONS[$this->getTreeId()]) && !array_key_exists($key, $FIB_OPTIONS[$this->getTreeId()]))) {
			return $this->setDefault($key);
		} else {
			return($FIB_OPTIONS[$this->getTreeId()][$key]);
		}
	}

	private function load_json() {
		Zend_Session::writeClose();
		$gedcom_id = KT_TREE::getIdFromName(KT_Filter::get('ged'));
		if(!$gedcom_id) $gedcom_id = KT_GED_ID;
		$iDisplayStart  = KT_Filter::getInteger('iDisplayStart');
		$iDisplayLength = KT_Filter::getInteger('iDisplayLength');

		if ($iDisplayLength>0) {
			$LIMIT = " LIMIT " . $iDisplayStart . ',' . $iDisplayLength;
		} else {
			$LIMIT = "";
		}

		$sql = "SELECT SQL_CACHE SQL_CALC_FOUND_ROWS m_id AS xref, m_file AS gedcom_id FROM `##media` WHERE m_file=? AND m_type=?" . $LIMIT;
		$args = array($gedcom_id, 'photo');

		$rows = KT_DB::prepare($sql)->execute($args)->fetchAll();

		// Total filtered/unfiltered rows
		$iTotalRecords = $iTotalDisplayRecords = KT_DB::prepare("SELECT FOUND_ROWS()")->fetchColumn();

		$aaData = array();
		foreach ($rows as $row) {
			$media = KT_Media::getInstance($row->xref, $row->gedcom_id);
			if(file_exists($media->getServerFilename()) && ($media->mimeType() == 'image/jpeg' || $media->mimeType() == 'image/png')){
				$aaData[] = array(
					$this->displayImage($media)
				);
			}
		}
		header('Content-type: application/json');
		echo json_encode(array( // See http://www.datatables.net/usage/server-side
			'sEcho'                	=> KT_Filter::getInteger('sEcho'), // String, but always an integer
			'iTotalRecords'        	=> $iTotalRecords,
			'iTotalDisplayRecords'	=> $iTotalDisplayRecords,
			'aaData'              	=> $aaData
		));
		exit;
	}

	private function displayImage($media) {
		$image = $this->FancyThumb($media, 60, 60);
		if($this->options('images') == 1) $img_checked = ' checked="checked"';
		elseif(is_array($this->options('images')) && in_array($media->getXref(), $this->options('images'))) $img_checked = ' checked="checked"';
		else $img_checked = "";

		// ouput all thumbs as jpg thumbs (transparent png files are not possible in the Fancy Imagebar, so there is no need to keep the mimeType png).
		ob_start();imagejpeg($image,null,100);$image = ob_get_clean();
		return '<img src="data:image/jpeg;base64,' . base64_encode($image) . '" alt="' . $media->getXref() . '" title="' . strip_tags($media->getFullName()) . '"/><br/>
				<span><input type="checkbox" value="' . $media->getXref() . '"' . $img_checked . '></span>';
	}

	private function getXrefs() {
		$gedcom_id = KT_TREE::getIdFromName(KT_Filter::get('ged'));
		if(!$gedcom_id) $gedcom_id = KT_GED_ID;
		$sql = "SELECT m_id AS xref, m_file AS gedcom_id FROM `##media` WHERE m_file=? AND m_type=?";
		$args = array($gedcom_id, 'photo');

		$rows = KT_DB::prepare($sql)->execute($args)->fetchAll();
		$list = array();
		foreach ($rows as $row) {
			$list[] = $row->xref;
		}
		return $list;
	}

	// Extend KT_Module_Config
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_config':
			$this->config();
			break;
		case 'load_json':
			$this->load_json();
			break;
		case 'admin_reset':
			$this->fib_reset();
			$this->config();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Reset all settings to default
	private function fib_reset() {
		KT_DB::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'FIB%'")->execute();
		AddToLog($this->getTitle() . ' reset to default values', 'config');
	}

	private function config() {
		require KT_ROOT . 'includes/functions/functions_edit.php';

		$controller = new KT_Controller_Page;
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(KT_JQUERY_DATATABLES_URL);

		if (KT_Filter::postBool('save')) {
			$key = KT_Filter::postInteger('NEW_FIB_TREE');
			$NEW_FIB_OPTIONS[$key] = KT_Filter::postArray('NEW_FIB_OPTIONS');
			$NEW_FIB_OPTIONS[$key]['IMAGES'] = explode("|", KT_Filter::post('NEW_FIB_IMAGES'));
			set_module_setting($this->getName(), 'FIB_OPTIONS',  serialize($NEW_FIB_OPTIONS));
			AddToLog($this->getTitle().' config updated', 'config');
		}

		$controller->addInlineJavascript('
			var oTable=jQuery("#image_block").dataTable( {
				sDom: \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
				bProcessing: true,
				bServerSide: true,
				sAjaxSource: "module.php?mod=' . $this->getName() . '&mod_action=load_json",
				'.KT_I18N::datatablesI18N(array(5,10,15,25,50,100,500,1000,-1)).',
				bJQueryUI: true,
				bAutoWidth: false,
				bFilter: false,
				iDisplayLength: 15,
				sPaginationType: "full_numbers",
				bStateSave: true,
				"sScrollY": "310px",
				"bScrollCollapse": true,
				iCookieDuration: 300,
				aoColumns: [
					{bSortable: false}
				],
				fnDrawCallback: function() {
					var images = jQuery("#imagelist").val().split("|");
					jQuery("input[type=checkbox]", this).each(function(){
						if(jQuery.inArray(jQuery(this).val(), images) > -1){
							jQuery(this).prop("checked", true);
						} else {
							jQuery(this).prop("checked", false);
						}
					});
				}
			});

			var formChanged = false;
			jQuery(oTable).on("change", "input[type=checkbox]",function() {
				var images = jQuery("#imagelist").val().split("|")
				if(this.checked){
					images.push(jQuery(this).val());
				 } else {
					var index = images.indexOf(jQuery(this).val());
					images.splice(index, 1 );
				 }

				 // remove empty values from array
				 images = images.filter(function(e){return e});

				 // turn array into a string
				 jQuery("#imagelist").val(images.join("|"));

				 formChanged = true;
			});

			jQuery("input[name=select-all]").click(function(){
				if (jQuery(this).is(":checked") == true) {
					jQuery("#imagelist").val("' . implode("|", $this->getXrefs()) . '");
					oTable.find(":checkbox").prop("checked", true);
				} else {
					jQuery("#imagelist").val("");
					oTable.find(":checkbox").prop("checked", false);
				}
				formChanged = true;
			});

			// detect changes on other form elements
			jQuery("#block_right").on("change", "input, select", function(){
				formChanged = true;
			});

			var current = jQuery(".tree option:selected");
			jQuery(".tree").change(function() {
				if (formChanged == false || (formChanged == true && confirm("' . KT_I18N::translate('The settings are changed. You will loose your changes if you switch trees.') . '"))) {
					var ged = jQuery("option:selected", this).data("ged");
					jQuery.get("module.php?mod=' . $this->getName() . '&mod_action=admin_config&ged=" + ged, function(data) {
						 jQuery("#imagelist").replaceWith(jQuery(data).find("#imagelist"));
						 jQuery("#options").replaceWith(jQuery(data).find("#options"));
						 oTable.fnDraw();
					});
					formChanged = false;
					current = jQuery("option:selected", this);
				}
				else {
					jQuery(current).prop("selected", true);
				}
			});
		');

		$html =
			'<div id="fib_config"><h2>' . $this->getTitle() . '</h2>
			<form method="post" name="configform" action="' . $this->getConfigLink() . '">
				<input type="hidden" name="save" value="1">
				<div id="selectbar">
					<div class="left">
						<label for="NEW_FIB_TREE" class="label">' . KT_I18N::translate('Family tree') . '</label>
						<select name="NEW_FIB_TREE" id="NEW_FIB_TREE" class="tree">';
							foreach (KT_Tree::getAll() as $tree):
								if($tree->tree_id == KT_GED_ID) {
									$html .= '<option value="' . $tree->tree_id . '" data-ged="' . $tree->tree_name . '" selected="selected">' . $tree->tree_title . '</option>';
								} else {
									$html .= '<option value="' . $tree->tree_id . '" data-ged="' . $tree->tree_name . '">' . $tree->tree_title . '</option>';
								}
							endforeach;
			$html .= '	</select>
				</div>
			</div>
			<div class="clearfloat"></div>
			<div id="block_left" class="left">
				<div class="left">' . KT_I18N::translate('Choose which images you want to show in the Fancy Imagebar') . ':' . help_link('choose_images', $this->getName()) . '</div>
				<div class="selectbox">' .
					checkbox('select-all') . KT_I18N::translate('select all');
					// The datatable will be dynamically filled with images from the database.
					// IMAGE LIST -->
					if (empty($this->options('images'))) {
						// we have not used the configuration page yet so use the default (list all images)
						$imagelist = implode("|", $this->getXrefs());
					} else {
						$imagelist = implode("|", $this->options('images'));
					}
		// The datatable will be dynamically filled with images from the database.
		$html .= '</div>
					<div class="clearfloat"></div>
					<h3 class="no_images">' . KT_I18N::translate('No images to display for this tree') . '</h3>';
		$html .= '	<input id="imagelist" type="hidden" name="NEW_FIB_IMAGES" value = "' . $imagelist .'">
					<table id="image_block" class="table">
						<thead></thead>
						<tbody></tbody>
					</table>
				</div>
				<div id="block_right" class="right">
					<h3>' . KT_I18N::translate('Options') . ':</h3>
					<div id="options">
						<div class="field">
							<label class="label">'.KT_I18N::translate('Random images').':</label>'.
							 edit_field_yes_no('NEW_FIB_OPTIONS[RANDOM]', $this->options('random')).'
						</div>
						<div class="field tone">
							<label class="label">'.KT_I18N::translate('Images Tone').':</label>'.
							select_edit_control('NEW_FIB_OPTIONS[TONE]', array('Sepia', 'Black and White', 'Colors'), null, $this->options('tone')).'
						</div>
						<div class="field">
							<label class="label">'.KT_I18N::translate('Cropped image size').':</label>
							<input type="text" name="NEW_FIB_OPTIONS[SIZE]" size="3" value="' . $this->options('size') . '"/>&nbsp;px
						</div>
					</div>
				</div>
				<p class="buttons">
					<button class="btn btn-primary save" type="submit">
						<i class="fa fa-floppy-o"></i>' .
						KT_I18N::translate('Save') . '
					</button>
					<button class="btn btn-primary cancel" type="reset" onclick="if (confirm(\'' . KT_I18N::translate('The settings will be reset to default (for all trees). Are you sure you want to do this?') . '\')) window.location.href=\'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_reset\';">
						<i class="fa fa-refresh"></i>' .
						KT_I18N::translate('Reset') . '
					</button>
				</p>
			</form>
			</div>';

		// output
		ob_start();
		$html .= ob_get_clean();
		echo $html;
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	// Get the medialist from the database
	private function FancyImageBarMedia() {
		$images_sql = array();
		$sql =	"SELECT SQL_CACHE m_id AS xref, m_file AS gedcom_id FROM `##media` WHERE m_file='" . KT_GED_ID . "'";
				if($this->options('images') == 1) {
					$sql .= " AND m_type='photo'";
				} else {
					// single quotes needed around id's for sql statement.
					foreach ($this->options('images') as $image) {
						$images_sql[] = '\'' . $image . '\'';
					}
					$sql .= " AND m_id IN (" . implode(',', $images_sql) . ")";
				}
		$sql .=	$this->options('random') == 1 ? " ORDER BY RAND()" : " ORDER BY m_id DESC";
		$sql .= " LIMIT " . ceil(2400 / $this->options('size'));

		$rows = KT_DB::prepare($sql)->execute()->fetchAll();
		$list = array();
		foreach ($rows as $row) {
			$media = KT_Media::getInstance($row->xref, $row->gedcom_id);
			if ($media->canDisplayDetails() && ($media->mimeType() == 'image/jpeg' || $media->mimeType() == 'image/png')) {
				$list[] = $media;
			}
		}
		return $list;
	}

	private function FancyThumb($mediaobject, $thumbwidth, $thumbheight) {
		$imgSrc	= $mediaobject->getServerFilename();
		$type	= $mediaobject->mimeType();

		//getting the image dimensions
		list($width_orig, $height_orig) = @getimagesize($imgSrc);
		switch ($type) {
			case 'image/jpeg':
				$image = @imagecreatefromjpeg($imgSrc);
				break;
			case 'image/png':
				$image = @imagecreatefrompng($imgSrc);
				break;
		}

		$ratio_orig = $width_orig/$height_orig;

		if ($thumbwidth/$thumbheight > $ratio_orig) {
		   $new_height	= $thumbwidth/$ratio_orig;
		   $new_width	= $thumbwidth;
		} else {
		   $new_width	= $thumbheight*$ratio_orig;
		   $new_height	= $thumbheight;
		}

		// transparent png files are not possible in the Fancy Imagebar, so no extra code needed.
		$new_image = @imagecreatetruecolor(round($new_width), round($new_height));
		@imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);

		$thumb = @imagecreatetruecolor($thumbwidth, $thumbheight);
		@imagecopyresampled($thumb, $new_image, 0, 0, 0, 0, $thumbwidth, $thumbheight, $thumbwidth, $thumbheight);

		@imagedestroy($new_image);
		@imagedestroy($image);
		return $thumb;
	}

	private function CreateFancyImageBar($srcImages, $thumbWidth, $thumbHeight, $numberOfThumbs) {
		// defaults
		$pxBetweenThumbs = 0;
		$leftOffSet		= $topOffSet = 0;
		$canvasWidth	= ($thumbWidth + $pxBetweenThumbs) * $numberOfThumbs;
		$canvasHeight	= $thumbHeight;

		// create the FancyImagebar canvas to put the thumbs on
		$FancyImageBar = @imagecreatetruecolor($canvasWidth, $canvasHeight);

		foreach ($srcImages as $index => $thumb)
		{
			$x = ($index % $numberOfThumbs) * ($thumbWidth + $pxBetweenThumbs) + $leftOffSet;
		 	$y = floor($index / $numberOfThumbs) * ($thumbWidth + $pxBetweenThumbs) + $topOffSet;

		 	@imagecopy($FancyImageBar, $thumb, $x, $y, 0, 0, $thumbWidth, $thumbHeight);
		}
		return $FancyImageBar;
	}

	private function FancyImageBarSepia($FancyImageBar, $depth) {
		@imagetruecolortopalette($FancyImageBar,1,256);

		for ($c = 0; $c < 256; $c++) {
			$col = @imagecolorsforindex($FancyImageBar,$c);
			$new_col = floor($col['red'] * 0.2125 + $col['green'] * 0.7154 + $col['blue'] * 0.0721);
		if ($depth > 0) {
				$r = $new_col+$depth;
				$g = floor($new_col+$depth/1.86);
				$b = floor($new_col+$depth/-3.48);
			} else {
				$r = $new_col;
				$g = $new_col;
				$b = $new_col;
			}
			@imagecolorset($FancyImageBar,$c,max(0,min(255,$r)),max(0,min(255,$g)),max(0,min(255,$b)));

		}
		return $FancyImageBar;
	}

	// Extend KT_Module_Menu
	private function GetFancyImageBar(){
		global $controller;

		if($medialist = $this->FancyImageBarMedia()) {
			$width = $height = $this->options('size');

			// begin looping through the media and write the imagebar
			$srcImages = array();
			foreach ($medialist as $media) {
				if (file_exists($media->getServerFilename())) {
					$srcImages[] = $this->FancyThumb($media, $width, $height);
				}
			}

			if(!empty($srcImages)) {
				// be sure the imagebar will be big enough for wider screens
				$newArray = array();

				// determine how many thumbs we need (based on a users screen of 2400px);
				$fib_length = ceil(2400 / $this->options('size'));
				while(count($newArray) <= $fib_length){
					$newArray = array_merge($newArray, $srcImages);
				}
				// reduce the new array to the desired length (as there might be too many elements in the new array
				$srcImages		= array_slice($newArray, 0, $fib_length);
				$FancyImageBar	= $this->CreateFancyImageBar($srcImages, $width, $height, $fib_length);
				if($this->options('tone') == 0) {
					$FancyImageBar = $this->FancyImageBarSepia($FancyImageBar, 50);
				}
				if($this->options('tone') == 1) {
					$FancyImageBar = $this->FancyImageBarSepia($FancyImageBar, 0);
				}
				ob_start();imagejpeg($FancyImageBar,null,100);$FancyImageBar = ob_get_clean();
				$html = '<div id="fancy_imagebar" style="clear:both; overflow:hidden;">
							<img alt="fancy_imagebar" src="data:image/jpeg;base64,' . base64_encode($FancyImageBar).'">
						</div>';
						$theme = explode('/', KT_THEME_DIR);
						switch ($theme[1]) {
							case 'kiwitrees':
							case 'levy':
							$height = $this->options('size');
							$controller->addInlineJavaScript("jQuery('#content').css({'margin-top':'" . $height . "px'});");
							break;
							case 'xenea':
							$height = $this->options('size') + 48;
							$controller->addInlineJavaScript("jQuery('#topMenu').css({'height':'" . $height . "px'});");
							break;
							default:
							break;
						}
				// output
				return $html;
			}
		}
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		// We don't actually have a menu - this is just a convenient "hook" to execute code at the right time during page execution
		global $controller, $ctype, $SEARCH_SPIDER;

		if (!empty($this->options('IMAGES')) && KT_SCRIPT_NAME === 'index.php') {
			if ($SEARCH_SPIDER) return null;
				// put the fancy imagebar in the right position
				$controller->addInlineJavaScript("jQuery('#topMenu').append(jQuery('#fancy_imagebar'));");
				$html = $this->GetFancyImageBar();

				// output
				ob_start();
				$html .= ob_get_clean();
				echo $html;
		}

		return null;
	}
}
