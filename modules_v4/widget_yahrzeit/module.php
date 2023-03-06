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

class widget_yahrzeit_KT_Module extends KT_Module implements KT_Module_Widget {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module.  Yahrzeiten (the plural of Yahrzeit) are special anniversaries of deaths in the Hebrew faith/calendar. */ KT_I18N::translate('Yahrzeiten');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Yahrzeiten” module.  A “Hebrew death” is a death where the date is recorded in the Hebrew calendar. */ KT_I18N::translate('A list of the Hebrew death anniversaries that will occur in the near future.');
	}

	// Implement class KT_Module_Block
	public function getWidget($block_id, $template=true, $cfg=null) {
		global $ctype, $controller;

		$days      = get_block_setting($block_id, 'days',       7);
		$infoStyle = get_block_setting($block_id, 'infoStyle', 'table');
		$calendar  = get_block_setting($block_id, 'calendar',  'jewish');

		if ($cfg) {
			foreach (array('days', 'infoStyle') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name=$cfg[$name];
				}
			}
		}

		$startjd=KT_CLIENT_JD;
		$endjd  =KT_CLIENT_JD+$days-1;

		$id=$this->getName();
		$class=$this->getName();
		if (KT_USER_GEDCOM_ADMIN) {
			$title='<i class="icon-admin" title="'.KT_I18N::translate('Configure').'" onclick="modalDialog(\'block_edit.php?block_id='.$block_id.'\', \''.$this->getTitle().'\');"></i>';
		} else {
			$title='';
		}
		$title.=$this->getTitle();

		$content='';
		// The standard anniversary rules cover most of the Yahrzeit rules, we just
		// need to handle a few special cases.
		// Fetch normal anniversaries...
		$yahrzeits=array();
		for ($jd=$startjd-1; $jd<=$endjd+30;++$jd) {
			foreach (get_anniversary_events($jd, 'DEAT _YART') as $fact) {
				// Extract hebrew dates only
				if ($fact['date']->date1 instanceof KT_Date_Jewish && $fact['date']->MinJD()==$fact['date']->MaxJD()) {
					$yahrzeits[]=$fact;
				}
			}
		}

		// ...then adjust dates
		foreach ($yahrzeits as $key=>$yahrzeit) {
			if (strpos('1 DEAT', $yahrzeit['factrec'])!==false) { // Just DEAT, not _YART
				$today=new KT_Date_Jewish($yahrzeit['jd']);
				$hd=$yahrzeit['date']->MinDate();
				$hd1=new KT_Date_Jewish($hd);
				$hd1->y+=1;
				$hd1->SetJDFromYMD();
				// Special rules.  See http://www.hebcal.com/help/anniv.html
				// Everything else is taken care of by our standard anniversary rules.
				if ($hd->d==30 && $hd->m==2 && $hd->y!=0 && $hd1->DaysInMonth()<30) { // 30 CSH
					// Last day in CSH
					$yahrzeit[$key]['jd']=KT_Date_Jewish::YMDtoJD($today->y, 3, 1)-1;
				}
				if ($hd->d==30 && $hd->m==3 && $hd->y!=0 && $hd1->DaysInMonth()<30) { // 30 KSL
					// Last day in KSL
					$yahrzeit[$key]['jd']=KT_Date_Jewish::YMDtoJD($today->y, 4, 1)-1;
				}
				if ($hd->d==30 && $hd->m==6 && $hd->y!=0 && $today->DaysInMonth()<30 && !$today->IsLeapYear()) { // 30 ADR
					// Last day in SHV
					$yahrzeit[$key]['jd']=KT_Date_Jewish::YMDtoJD($today->y, 6, 1)-1;
				}
			}
		}

		switch ($infoStyle) {
		case 'list':
			foreach ($yahrzeits as $yahrzeit)
				if ($yahrzeit['jd']>=$startjd && $yahrzeit['jd']<$startjd+$days) {
					$ind=person::GetInstance($yahrzeit['id']);
					$content .= "<a href=\"".$ind->getHtmlUrl()."\" class=\"list_item name2\">".$ind->getFullName()."</a>".$ind->getSexImage();
					$content .= "<div class=\"indent\">";
					$content .= $yahrzeit['date']->Display(true);
					$content .= ', '.KT_I18N::translate('%s year anniversary', $yahrzeit['anniv']);
					$content .= "</div>";
				}
			break;
		case 'table':
		default:
			$table_id = "ID".(int)(microtime(true)*1000000); // table requires a unique ID
			$controller
				->addExternalJavascript(KT_JQUERY_DATATABLES_URL)
				->addInlineJavascript('
					jQuery("#'.$table_id.'").dataTable({
						"sDom": \'t\',
						'.KT_I18N::datatablesI18N().',
						"bAutoWidth":false,
						"bPaginate": false,
						"bLengthChange": false,
						"bFilter": false,
						"bInfo": true,
						"bJQueryUI": true,
						"aaSorting": [[5,"asc"]],
						"aoColumns": [
							/* 0-name */ { "iDataSort": 1 },
							/* 1-NAME */ { "bVisible": false },
							/* 2-date */ { "iDataSort": 3 },
							/* 3-DATE */ { "bVisible": false },
							/* 4-Aniv */ { "sClass": "center"},
							/* 5-yart */ { "iDataSort": 6 },
							/* 6-YART */ { "bVisible": false }
						]
					});
					jQuery("#'.$table_id.'").css("visibility", "visible");
					jQuery(".loading-image").css("display", "none");
				');
			$content='';
			$content .= '<div class="loading-image">&nbsp;</div>';
			$content .= '<table id="'.$table_id.'" class="width100" style="visibility:hidden;">';
			$content .= '<thead><tr>';
			$content .= '<th>'.KT_Gedcom_Tag::getLabel('NAME').'</th>';
			$content .= '<th>'.KT_Gedcom_Tag::getLabel('NAME').'</th>';
			$content .= '<th>'.KT_Gedcom_Tag::getLabel('DEAT').'</th>';
			$content .= '<th>DEAT</th>';
			$content .= '<th><i class="icon-reminder" title="'.KT_I18N::translate('Anniversary').'"></i></th>';
			$content .= '<th>'.KT_Gedcom_Tag::getLabel('_YART').'</th>';
			$content .= '<th>_YART</th>';
			$content .= '</tr></thead><tbody>';

			foreach ($yahrzeits as $yahrzeit) {
				if ($yahrzeit['jd']>=$startjd && $yahrzeit['jd']<$startjd+$days) {
					$content .= '<tr>';
					$ind=KT_person::GetInstance($yahrzeit['id']);
					// Individual name(s)
					$name=$ind->getFullName();
					$url=$ind->getHtmlUrl();
					$content .= '<td>';
					$content .= '<a href="'.$url.'">'.$name.'</a>';
					$content .= $ind->getSexImage();
					$addname=$ind->getAddName();
					if ($addname) {
						$content .= '<br><a href="'.$url.'">'.$addname.'</a>';
					}
					$content .= '</td>';
					$content .= '<td>'.$ind->getSortName().'</td>';

					// death/yahrzeit event date
					$content .= '<td>'.$yahrzeit['date']->Display().'</td>';
					$content .= '<td>'.$yahrzeit['date']->minJD().'</td>';// sortable date

					// Anniversary
					$content .= '<td>'.$yahrzeit['anniv'].'</td>';

					// upcomming yahrzeit dates
					switch ($calendar) {
					case 'gregorian':
						$today=new KT_Date_Gregorian($yahrzeit['jd']);
						break;
					case 'jewish':
					default:
						$today=new KT_Date_Jewish($yahrzeit['jd']);
						break;
					}
					$td=new KT_Date($today->Format('%@ %A %O %E'));
					$content .= '<td>'.$td->Display().'</td>';
					$content .= '<td>'.$td->minJD().'</td>';// sortable date

					$content .= '</tr>';
				}
			}
			$content .= '</tbody></table>';

			break;
		}

		if ($template) {
			require KT_THEME_DIR.'templates/widget_template.php';
		} else {
			return $content;
		}
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return true;
	}

	// Implement KT_Module_Widget
	public function defaultWidgetOrder() {
		return 110;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'days',      KT_Filter::postInteger('days', 1, 30, 7));
			set_block_setting($block_id, 'infoStyle', KT_Filter::post('infoStyle', 'list|table', 'table'));
			set_block_setting($block_id, 'calendar',  KT_Filter::post('calendar', 'jewish|gregorian', 'jewish'));
			exit;
		}

		require_once KT_ROOT.'includes/functions/functions_edit.php';

		$days=get_block_setting($block_id, 'days', 7);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Number of days to show');
		echo '</td><td class="optionbox">';
		echo '<input type="text" name="days" size="2" value="'.$days.'">';
		echo ' <em>', KT_I18N::plural('maximum %d day', 'maximum %d days', 30, 30) ,'</em>';
		echo '</td></tr>';

		$infoStyle=get_block_setting($block_id, 'infoStyle', 'table');
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Presentation style');
		echo '</td><td class="optionbox">';
		echo select_edit_control('infoStyle', array('list'=>KT_I18N::translate('list'), 'table'=>KT_I18N::translate('table')), null, $infoStyle, '');
		echo '</td></tr>';

		$calendar=get_block_setting($block_id, 'calendar');
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Calendar');
		echo '</td><td class="optionbox">';
		echo select_edit_control('calendar', array(
			'jewish'   =>KT_Date_Jewish::calendarName(),
			'gregorian'=>KT_Date_Gregorian::calendarName(),
		), null, $calendar, '');
		echo '</td></tr>';
	}
}
