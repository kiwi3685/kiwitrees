<?php
/*
 * Plugin for calendar_utilities module
 *
 * Copyright (C) 2013 Nigel Osborne and kiwitrees.net. All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Plugin name - this needs double quotes, as file is scanned/parsed by script
$plugin_name = "Old & New Style Dates"; /* I18N: Name of a plugin. */ KT_I18N::translate('Old & New Style Dates');

// HELP //
$help1 = htmlspecialchars(addslashes(KT_I18N::translate('<div id="popup"><p>There are several different areas. They operate as follows.</p><p><b>1. Calculation of major moveable feasts.</b></p><p>Since the ecclesiastical calendar is based on lunar rather than solar cycles, certain key holidays (feasts) occur on different days each year. The method of calculating these feasts has also changed since the council of Nicea (325 A.D.). The button dived "Calculate Holidays" calculates the dates of seven major feasts for the year entered in the field dived "Year", and displays the results below this field. The only restrictions are that the number entered in the field "Year" must be an integer (no fractions) greater than zero. However, the holidays generated are only valid for dates since 325 A.D. (Early Christian and Roman dating is another story). Also, for purposes of calculation, I have assumed that the ecclesiastical year begins on January first, even though this standard was only gradually accepted. If you are working with early monastic documents you might want to consider that dates from December 25th through March may be "off" by one year. To a Benedictine, for instance (to whom the year began on December 25th), the feast of the Innocents in 1450 would be December 28th, 1450, while to others it might be December 28th, 1449. In fact, before 1582, most calendars did not have the year begin on January 1st, even though the calculation of the moveable feasts acted as if it did. In England, the year "began" either on December 25th, or, more frequently on March 25th (Lady Day), until 1752. These vagaries are not something I wanted to include in the calculations, since they often varied quite a bit. The calculations for the holidays will take into account the days dropped from the calendar when the "New style" was adopted, since these affect the month and day of Easter. Conventions about the beginning of the year are easily corrected for.</p><p><b>2. Old and New style dating and Day of the week</b></p><p>When the Pope Gregory revised the calendar in 1582, a certain number of days were omitted from the calendar at a particular time, resulting in two separate styles of dating. England persisted in using the "Old Style" until 1752, because of religious differences. The Old Style also often dated the beginning of the year from March 25 rather than January 1, and this area of the site takes this difference into account. Thus March 8, 1735, Old Style is really March 19, 1736 in the New Style! Occasionally, particularly in dating material between 1582 and 1755 or so, it becomes necessary to convert back and forth.</p><p>Here is a more detailed description of the transition year (in England): 1752.</p><p>The area called "Convert Old Style to New Style" converts the old style date to a new style date and returns an answer. The area called "Convert New Style to Old Style" converts the new style date to an old style date and enters it in the "Old style" fields. You must enter an integer greater than zero for the year.</p><p>You can also calculate the day of the week for both old and new style dates.</p><p>Remember that these forms assume that the year begins on March 25th. If you want to plug in dates derived from the "Ecclesiastical Holidays" area, be aware that you will have to subtract 1 from the year for all dates between January 1st and March 24th.</p><p><b>NOTE FOR STUDENTS OF CONTINENTAL HISTORY</b></p><p>Don\'t try to use this site to calculate dates of documents between 1588 and 1752 because England is a special case during these years.</p></p>')), ENT_QUOTES);

$help2 = 'http://en.wikisource.org/wiki/1911_Encyclop%C3%A6dia_Britannica/Calendar/Ecclesiastical_Calendar_-_Easter';

// DISPLAY
$month_name= array();
for ($i=0; $i<12; ++$i) {
	$month_name[$i] = KT_Date_Gregorian::NUM_TO_MONTH_NOMINATIVE($i+1, false);
}

$html.='
	<style type="text/css">
		#oldnew h1 {font-size:15px; font-weight:bold;}
		#oldnew {overflow:hidden;}
		#oldnew p {padding:0;}
		#oldnew form{margin-top:50px; border-top:1px solid #555; width:680px;}
		#oldnew div.main {text-align:left; width:auto; font-size: 14px; font-weight:bold; display:inline;}
		#oldnew div.secondary {display:inline; text-align:left; width:auto; font-size: 13px; margin:10px; line-height:40px; white-space:nowrap;}
		#acknowledgement{background:#fff; border:1px solid #c0c0c0; border-radius:8px; float:right; font-size:12px; width:250px; margin:0 30px; padding:10px;}
		#acknowledgement p{padding:5px;}
		#acknowledgement i{cursor:pointer; color:blue; margin:0; vertical-align:baseline;}
		#oldnew	.note{font-size:11px; font-style:italic; padding:5px; white-space:normal; font-weight:normal;}
	</style>
	<div id="oldnew">
		<p>' .
			KT_I18N::translate('There are two parts to this page. The first converts from Old Style dates, typically found in English documents before 1752, to New Style dates.  The second converts New Style into Old Style.  To avoid confusion, the year is assumed to begin on January 1st. If you are dealing with a old style document dated before March 25th, be aware that the legal year did not change until then.') . '
		</p>
		<div id="acknowledgement"> ' .
			/* I18N: Acknowledgement of origin for a calendar utility */
			KT_I18N::translate('
				<p>
					This page is based on work done by Ian MacInnes <span class="note">(imacinnes@albion.edu)</span> at his website <a href="http://people.albion.edu/imacinnes/calendar/Welcome.html" target="blank"><b>Ian\'s English Calendar</b></a></p>
				<p>
					His site is intended to replace quick reference handbooks of dates for those interested in English history, literature, and genealogy. It is also accurate for European history outside of England, with the exception of the period 1582-1752. Students of Continental documents who wish to date documents from this period will need to follow this link
				</p>
				<p>
					<i title="Help with English calendar" onclick="modalNotes(\'%1s\', \'Help with English calendar\')">Help with English calendar</i>
				</p>
				<p>
					<a title="Easter Formulae" href="%2s" target="_blank" rel="noopener noreferrer">
						<i>The formulae for calculating Easter are derived from the 11th edition Encyclopedia Brittanica</i>
					</a>
				</p>
			', $help1, $help2) . '
		</div>
';
// Part one - Old to New
$html.='
	<form name="Olddayofweek">
		<h1>' . KT_I18N::translate('Convert Old Style Dates to New Style Dates') . '</h1>
		<div class="main">' . KT_I18N::translate('Enter the Old Style date in question') . ':
			<div class="secondary">' . KT_I18N::translate('Month') . ':
				<select name="Month" size="1">';
					for ($i=1; $i<13; ++$i) {
						$html.='<option value="'. $i. '"';
						if ($i==1) $html.=' selected ';
						$html.='>'. $month_name[$i-1]. '</option>';
					}
				$html.='</select>
			</div>
			<div class="secondary">' . KT_I18N::translate('Day') . ':
				<select name="Day" size="1">';
					for ($i=1; $i<32; $i++) {
						$html.='<option value="' .$i. '"';
						if ($i==1) $html.=' selected ';
						$html.='>' .$i. '</option>';
					}
				$html.='</select>
			</div>
			<div class="secondary">' . KT_I18N::translate('Year') . ':
				<input type="text" name="year" size="7" maxlength="4">
			</div>
		</div>
		<p>
			<input class="button_ec" type="button" name="Caculatedayofweek" value="' . KT_I18N::translate('Convert Old style to New Style') . '" onclick="OldDayOfWeek()">
			<br><br>
			<input type="text" name="Weekdayoldstyle" readonly size="56">
		</p>
	</form>
';
// Part 2 - New to old
$html.='
	<form name="Newdayofweek">
		<h1>' . KT_I18N::translate('Convert New Style Dates to Old Style Dates') . '</h1>
		<div class="main">' . KT_I18N::translate('Enter the New Style date in question') . ':
			<div class="secondary">' . KT_I18N::translate('Month') . ':
				<select name="Month" size="1">';
					for ($i=1; $i<13; ++$i) {
						$html.='<option value="'. $i. '"';
						if ($i==1) $html.=' selected ';
						$html.='>'. $month_name[$i-1]. '</option>';
					}
				$html.='</select>
			</div>
			<div class="secondary">' . KT_I18N::translate('Day') . ':
				<select name="Day" size="1">';
					for ($i=1; $i<32; $i++) {
						$html.='<option value="' .$i. '"';
						if ($i==1) $html.=' selected ';
						$html.='>' .$i. '</option>';
					}
				$html.='</select>
			</div>
			<div class="secondary">' . KT_I18N::translate('Year') . ':
				<input type="text" name="year" size="7" maxlength="4">
			</div>
		</div>
		<p>
			<input class="button_ec" type="button" name="Caculatedayofweek" value="' . KT_I18N::translate('Convert New style to Old Style') . '" onclick="NewDayOfWeek()">
			<br><br>
		<input type="text" name="Weekdayoldstyle" readonly size="56">
		</p>
	</form>
';
$html.='</div>';
?>
<!-- SCRIPTS -->
 <script><!--

	function OldDayOfWeek() {
		var Month	= parseInt(document . Olddayofweek.Month.value)
		var Day		= parseInt(document . Olddayofweek.Day.value)
		var Year	= parseInt(document . Olddayofweek.year.value)

		if (isNaN(Year)) {
				alert("<?php echo KT_I18N::translate('You must enter a year.'); ?>")
				return
		} else {
			if (Year < 325) {alert("<?php echo KT_I18N::translate('This site is valid only for dates later than 325 A.D.'); ?>"); return}
			   if (Year > 1752) {alert("<?php echo KT_I18N::translate('Nobody used Old Style dates after 1752'); ?>"); return;}
		}

		if (Month == 4)  {if (Day > 30) {alert("<?php echo KT_I18N::translate('April has only 30 days. Proceed assuming April 31st is the same as May 1st?'); ?>");}}
		if (Month == 6)  {if (Day > 30) {alert("<?php echo KT_I18N::translate('June has only 30 days. Proceed assuming June 31st is the same as July 1st?'); ?>");}}
		if (Month == 9)  {if (Day > 30) {alert("<?php echo KT_I18N::translate('September has only 30 days. Proceed assuming September 31st is the same as August 1st?'); ?>");}}
		if (Month == 11) {if (Day > 30) {alert("<?php echo KT_I18N::translate('November has only 30 days. Proceed assuming November 31st is the same as December 1st?'); ?>");}}
		if (Month == 2)  {
			if (Day > 28) {
				if (Day == 29) {
					if (Year%4 != 0) {
						alert(Year + "<?php echo KT_I18N::translate(' was not a leap year. Proceed assuming February 29th to mean March 1st?'); ?>");
					}
				}
				else {alert("<?php echo KT_I18N::translate('February never has more than 29 days. Proceed, assuming you mean early March?'); ?>");}
			}
		}

		var OldYear = Year
		var OldMonth = Month
		var OldDay = Day

		Day = Day + 10
		var COR = parseInt((OldYear/100) - 16)
		if (COR > 0) {COR = COR - parseInt((OldYear/400) -4)}
		if (COR > 0) {Day = Day + COR}

		if (Day > 31) {
			if (Month == 2) {
				if ( (OldYear/4) - parseInt(OldYear/4) < 0.1 ) {
					if ((OldYear/100 - parseInt(OldYear/100)) == 0) {Day = Day - 28}
					else {Day = Day - 29}
				}
			else {Day = Day - 28}
			}
			if (Month == 1){Day = Day - 31}
			if (Month == 3){Day = Day - 31}
			if (Month == 5){Day = Day - 31}
			if (Month == 7){Day = Day - 31}
			if (Month == 8){Day = Day - 31}
			if (Month == 10){Day = Day - 31}
			if (Month == 12){Day = Day - 31}
			if (Month == 4){Day = Day - 30}
			if (Month == 6){Day = Day - 30}
			if (Month == 9){Day = Day - 30}
			if (Month == 11){Day = Day - 30}
			Month = Month + 1

		}

		if (Day > 30) {
			if (Month == 2) {
				if ( (OldYear/4) - parseInt(OldYear/4) < 0.1 ) {
					if ((OldYear/100 - parseInt(OldYear/100)) == 0) {
						Day = Day - 28
						Month = Month + 1
					}
					else {Day = Day - 29}
				}
			else {Day = Day - 28
				Month = Month + 1
				}
			}
			if (Month == 4){Day = Day - 30
				Month = Month + 1
			}
			if (Month == 6){Day = Day - 30
				Month = Month + 1
			}
			if (Month == 9){Day = Day - 30
				Month = Month + 1
			}
			if (Month == 11){Day = Day - 30
				Month = Month + 1
			}

		}

		if (Day > 28) {
			if (Month == 2) {
				if ( (OldYear/4) - parseInt(OldYear/4) < 0.1 ) {
					if ((OldYear/100 - parseInt(OldYear/100)) == 0) {Day = Day - 28}
					else {Day = Day - 29}
				}
			else {Day = Day - 28}
			Month = (Month + 1)
		if (Day == 0) {
				Day = 29
				Month = (Month - 1)
				}
			}
		}

		if (Month == 13) {
			Month = 1
			Year = (Year + 1)
		}

		document . Olddayofweek . Weekdayoldstyle . value = OldMonth + "/" + OldDay + "/" + OldYear + " <?php echo KT_I18N::translate('Old Style corresponds to '); ?>" + Month + "/" + Day + "/" + Year + " <?php echo KT_I18N::translate('New Style'); ?>"

	}

	function NewDayOfWeek() {
		var Month	=parseInt(document . Newdayofweek . Month.value)
		var Day		=parseInt(document . Newdayofweek . Day.value)
		var Year	=parseInt(document . Newdayofweek . year.value)

		if (isNaN(Year)) {
				alert("<?php echo KT_I18N::translate('You must enter a year.'); ?>")
				document . Olddayofweek . Weekdayoldstyle . value = " "
				return
		}
		else {
			if (Year < 325) {alert("<?php echo KT_I18N::translate('This site is valid only for dates later than 325 A.D.'); ?>");
			document . Olddayofweek . Weekdayoldstyle . value = " "
			return}
			   if (Year < 1582 ) {alert("<?php echo KT_I18N::translate('Nobody used New Style dates before 1582.'); ?>");
			document . Olddayofweek . Weekdayoldstyle . value = " "
			return;}

		}

		if (Month == 4)  {if (Day > 30) {alert("<?php echo KT_I18N::translate('April has only 30 days. Proceed assuming April 31st is the same as May 1st?'); ?>");}}
		if (Month == 6)  {if (Day > 30) {alert("<?php echo KT_I18N::translate('June has only 30 days. Proceed assuming June 31st is the same as July 1st?'); ?>");}}
		if (Month == 9)  {if (Day > 30) {alert("<?php echo KT_I18N::translate('September has only 30 days. Proceed assuming September 31st is the same as August 1st?'); ?>");}}
		if (Month == 11) {if (Day > 30) {alert("<?php echo KT_I18N::translate('November has only 30 days. Proceed assuming November 31st is the same as December 1st?'); ?>");}}
		if (Month == 2)  {
			if (Day > 28) {
				if (Day == 29) {
					if (Year%4 != 0) {
						alert(Year + "<?php echo KT_I18N::translate(' was not a leap year. Proceed assuming February 29th to mean March 1st?'); ?>");
					}
				}
				else {alert("<?php echo KT_I18N::translate('February never has more than 29 days. Proceed, assuming you mean early March?'); ?>");}
			}
		}

		var OldYear = Year
		var OldMonth = Month
		var OldDay = Day

		Day = Day - 10
		var COR = parseInt((OldYear/100) - 16)
		if (COR > 0) {COR = COR - parseInt((OldYear/400) -4)}
		if (COR > 0) {Day = Day - COR}

		if (Day < 1) {
		Month = Month - 1
				if(Month < 1){Month = 12
				Year = (Year - 1)}
			if (Month == 2) {
				if ( (OldYear/4) - parseInt(OldYear/4) < 0.1 ) {
					if ((OldYear/100 - parseInt(OldYear/100)) == 0) {Day = Day + 28}
					else {Day = Day + 29}
				}
			else {Day = Day + 28}
			}
			if (Month == 1){Day = Day + 31}
			if (Month == 3){Day = Day + 31}
			if (Month == 5){Day = Day + 31}
			if (Month == 7){Day = Day + 31}
			if (Month == 8){Day = Day + 31}
			if (Month == 10){Day = Day + 31}
			if (Month == 12){Day = Day + 31}
			if (Month == 4){Day = Day + 30}
			if (Month == 6){Day = Day + 30}
			if (Month == 9){Day = Day + 30}
			if (Month == 11){Day = Day + 30}


		}

		document.Newdayofweek . Weekdayoldstyle . value = OldMonth + "/" + OldDay + "/" + OldYear + " <?php echo KT_I18N::translate('New Style corresponds to '); ?>" + Month + "/" + Day + "/" + Year + " <?php echo KT_I18N::translate('Old Style'); ?>"
	}
// --></script>
