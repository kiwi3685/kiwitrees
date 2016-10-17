<?php
// Plugin for calendar_utilities module
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010 PGV Development Team
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

// Plugin name - this needs double quotes, as file is scanned/parsed by script
$plugin_name = "Yearly Calendar"; /* I18N: Name of a plugin. */ WT_I18N::translate('Yearly Calendar');

// DATA & FUNCTIONS
if ( !isset($_REQUEST['year']) ) {
    $year = date("Y",time());
} else {
    $year = (int)$_REQUEST['year'];
}

define("A_DAY", 86400);

if ( !function_exists('easter_date') ) {
    function easter_date($Year) {
        $G = $Year % 19;
        $C = (int)($Year / 100);
        $H = (int)($C - ($C / 4) - ((8*$C+13) / 25) + 19*$G + 15) % 30;
        $I = (int)$H - (int)($H / 28)*(1 - (int)($H / 28)*(int)(29 / ($H + 1))*((int)(21 - $G) / 11));
        $J = ($Year + (int)($Year/4) + $I + 2 - $C + (int)($C/4)) % 7;
        $L = $I - $J;
        $m = 3 + (int)(($L + 40) / 44);
        $d = $L + 28 - 31 * ((int)($m / 4));
        $y = $Year;
        $E = time(0,0,0, $m, $d, $y);
        return $E;
    }
}

function day_exists($year, $mth, $dy) {
    return ( checkdate($mth, $dy, $year) ) ? true : false;
}

function print_month($start,$end,$year) {
    $leap_year = date("L",mktime(0,0,0, 1, 1,$year));
    $shrove = ($leap_year==1) ? 49 : 48;
    $leap_day = ($leap_year==1) ? 49 : 48;
    $easter = easter_date($year);
	$holidays = array (
		date('Y-n-j', mktime(0,0,0, 1, 25,$year)) => WT_I18N::translate('Saint Pauls Day'), /* Pauli conversion */
		date('Y-n-j', mktime(0,0,0, 11, 2,$year)) => WT_I18N::translate('Thanksgiving (Denmark)'), /* Festo Liberationis Patriae */
		date('Y-n-j', mktime(0,0,0, 2, 22,$year)) => WT_I18N::translate('Saint Petris Chair'), /* Cathedra Petri */
		date('Y-n-j', mktime(0,0,0, 3, 9,$year)) => WT_I18N::translate('40 Knights'), /* 40 milities martyres */

		date('Y-n-j', mktime(0,0,0, 5, 1,$year)) => WT_I18N::translate('Saint Valpurgis Day'), /* Valpurgis */
		date('Y-n-j', mktime(0,0,0, 5, 1,$year)) => WT_I18N::translate('Saint Jacobi Day'), /* Philippi et Jacobi */

		date('Y-n-j', mktime(0,0,0, 6, 24,$year)) => WT_I18N::translate('Midsummer Day'), /* Johannes Baptista Nativitas */
		date('Y-n-j', mktime(0,0,0, 6, 27,$year)) => WT_I18N::translate('7 September Day'), /* 7 septem dormientes */

		date('Y-n-j', mktime(0,0,0, 11, 1,$year)) => WT_I18N::translate('Halloween Day'), /* Omnium Sanctorum */
		date('Y-n-j', mktime(0,0,0, 11, 2,$year)) => WT_I18N::translate('Day after Halloween'), /* Omnium Animarum */
		date('Y-n-j', mktime(0,0,0, 11, 11,$year)) => WT_I18N::translate('Saint Martin Day'), /* Martinus ep. conf. */


		date('Y-n-j', mktime(0,0,0, 8, 10,$year)) => WT_I18N::translate('Saint Lorentz Day'), /* Laurentius */
		date('Y-n-j', mktime(0,0,0, 8, 15,$year)) => WT_I18N::translate('Mary Prayers Day'), /* Assumptio Maria */
		date('Y-n-j', mktime(0,0,0, 8, 24,$year)) => WT_I18N::translate('Saint Bartholomeus Day'), /* Bartholomeus */

		date('Y-n-j', mktime(0,0,0, 10, 23,$year)) => WT_I18N::translate('Saint Severinus Day'), /* Severinus */


		/* Sunday before Ephiphania - HOW TO CALCULATE???? */
		date('Y-n-j', mktime(0,0,0, 0, 0,$year)) => WT_I18N::translate('Sunday before Holly 3 Kings'), /* Dominica Ephiphania */

		/* Ephiphania */
		date('Y-n-j', mktime(0,0,0,1, 6,$year)) => WT_I18N::translate('Holly 3 Kings'), /* Ephiphania */
		date('Y-n-j', mktime(0,0,0, 1, 6,$year)) => WT_I18N::translate('Birth of Christ'), /* Baptismus Christi */

		/* Before Jejunium */
		 date('Y-n-j', ($easter -32*A_DAY)) => WT_I18N::translate('Shrove Tuesday'), /* Carnivora */
		 date('Y-n-j', ($easter -33*A_DAY)) => WT_I18N::translate('Ash Wedensday'), /* Dies Cinerum */
		 date('Y-n-j', $easter - $shrove*A_DAY) => WT_I18N::translate('Shrove Sunday'), /* Esto Mihi */

		/* Jejunium - 5 sundays */
		date('Y-n-j', ($easter -42*A_DAY)) => WT_I18N::translate('1st Shrove Sunday'), /* Quintana/Quadragesima (Jejunium) */
		date('Y-n-j', ($easter -35*A_DAY)) => WT_I18N::translate('2nd Shrove Sunday'), /* Reminiscere (Jejunium) */
		date('Y-n-j', ($easter -28*A_DAY)) => WT_I18N::translate('3rd Shrove Sunday'), /* Oculi (Jejunium) */
		date('Y-n-j', ($easter -21*A_DAY)) => WT_I18N::translate('4th Shrove Sunday'), /* Laetare (Jejunium) */
		date('Y-n-j', ($easter -14*A_DAY)) => WT_I18N::translate('5th Shrove Sunday'), /* Judica (Jejunium) */

		/* Moveable sundays before Easter - 9 sundays */
		date('Y-n-j', ($easter -63*A_DAY)) => WT_I18N::translate('9th Sunday before Easter'), /* Septuagesima */
		date('Y-n-j', ($easter -56*A_DAY)) => WT_I18N::translate('8th Sunday before Easter'), /* Sexagesima */
		date('Y-n-j', ($easter -49*A_DAY)) => WT_I18N::translate('7th Sunday before Easter'), /* Quinquagesima */
		date('Y-n-j', ($easter -42*A_DAY)) => WT_I18N::translate('6th Sunday before Easter'), /* Invocavit */
		date('Y-n-j', ($easter -35*A_DAY)) => WT_I18N::translate('5th Sunday before Easter'), /* Reminiscere */
		date('Y-n-j', ($easter -28*A_DAY)) => WT_I18N::translate('4th Sunday before Easter'), /* Oculi */
		date('Y-n-j', ($easter -21*A_DAY)) => WT_I18N::translate('3rd Sunday before Easter'), /* Laetare*/
		date('Y-n-j', ($easter -14*A_DAY)) => WT_I18N::translate('2nd Sunday before Easter'), /* Judica */
		date('Y-n-j', ($easter - 7*A_DAY)) => WT_I18N::translate('1st Sunday before Easter'), /* Domini Palmarum (Dies) */

		/* Other moveable days before Easter */
		date('Y-n-j', ($easter -39*A_DAY)) => WT_I18N::translate('Tamper Day'), /* Quatuor Tempora */
		date('Y-n-j', ($easter -6*A_DAY)) => WT_I18N::translate('Restdays/week'), /* Dimmel Ferie */
		date('Y-n-j', ($easter -5*A_DAY)) => WT_I18N::translate('Restdays/week'), /* Dimmel Ferie */
		date('Y-n-j', ($easter -4*A_DAY)) => WT_I18N::translate('Restdays/week'), /* Dimmel Ferie */


		/* Easter */
		date('Y-n-j', ($easter -1*A_DAY)) => WT_I18N::translate('Saint Pauls Day'), /* Sanctum sabbatum */
		date('Y-n-j', ($easter -2*A_DAY)) => WT_I18N::translate('Good Friday'), /* Passio Domini (Sorteris) */
		date('Y-n-j', ($easter -3*A_DAY)) => WT_I18N::translate('Maundy Thursday'), /* Dies Viridium/Coena Domini */

		date('Y-n-j', ($easter +1*A_DAY)) => WT_I18N::translate('Easter Monday'), /* Pascha 2 */

		/* If date is >1700-03-01 then easter-date is */
		date('Y-n-j', ($easter +0*A_DAY)) => WT_I18N::translate('Easter Sunday'), /* Dominica Sancti Domini (Pascha) */
		date('Y-n-j', ($easter +0*A_DAY)) => WT_I18N::translate('Easter Sunday'), /* Festum resurrectionis Domini (Pascha) */
		date('Y-n-j', ($easter +0*A_DAY)) => WT_I18N::translate('Easter Sunday'), /* Festum Sancti Spiritus (Pascha) */
		/* Else date') */
		date('Y-n-j', ($easter +10*A_DAY)) => WT_I18N::translate('Easter Sunday'),/* Dominica Sancti Domini (Pascha) */
		date('Y-n-j', ($easter +10*A_DAY)) => WT_I18N::translate('Easter Sunday'), /* Festum resurrectionis Domini (Pascha) */
		date('Y-n-j', ($easter +10*A_DAY)) => WT_I18N::translate('Easter Sunday'), /* Festum Sancti Spiritus (Pascha) */

		/* Moveable sundays after Easter */
		date('Y-n-j', ($easter + 7*A_DAY)) => WT_I18N::translate('1st Sunday after Easter'), /* Quasimodogeniti */
		date('Y-n-j', ($easter +14*A_DAY)) => WT_I18N::translate('2nd Sunday after Easter'), /* Miserecordia */
		date('Y-n-j', ($easter +21*A_DAY)) => WT_I18N::translate('3rd Sunday after Easter'), /* Jubilate */
		date('Y-n-j', ($easter +28*A_DAY)) => WT_I18N::translate('4th Sunday after Easter'), /* Cantate */
		date('Y-n-j', ($easter +35*A_DAY)) => WT_I18N::translate('5th Sunday after Easter'), /* Rogate */
		date('Y-n-j', ($easter +42*A_DAY)) => WT_I18N::translate('6th Sunday after Easter'), /* Exaudi */

		/* Other moveable days after Easter */
		date('Y-n-j', ($easter + 26*A_DAY)) => WT_I18N::translate('Ascension Day'), /* Festo communi Praecum */
		date('Y-n-j', ($easter + 39*A_DAY)) => WT_I18N::translate('Prior Day (Denmark)'), /* Festo eucharistio */

		/* Pentecostes 49s after Easter */
		date('Y-n-j', ($easter + 49*A_DAY)) => WT_I18N::translate('Pentecost'), /* Pentecostes/Festum Sancti Spiritus */
		date('Y-n-j', ($easter + 50*A_DAY)) => WT_I18N::translate('2 Pentecost'), /* Pentecostes 1 */

		/* Benedicta (Trinitatem) - Always at least 22 sundays after easter */
		date('Y-n-j', ($easter + 56*A_DAY)) => WT_I18N::translate('Trinity Sunday'), /*  Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 63*A_DAY)) => WT_I18N::translate('1st Sunday after Trinity Sunday'), /*  1 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 70*A_DAY)) => WT_I18N::translate('2nd Sunday after Trinity Sunday'), /*  2 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 77*A_DAY)) => WT_I18N::translate('3rd Sunday after Trinity Sunday'), /*  3 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 84*A_DAY)) => WT_I18N::translate('4th Sunday after Trinity Sunday'), /*  4 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 91*A_DAY)) => WT_I18N::translate('5th Sunday after Trinity Sunday'), /*  5 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 98*A_DAY)) => WT_I18N::translate('6th Sunday after Trinity Sunday'), /*  6 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 105*A_DAY)) => WT_I18N::translate('7th Sunday after Trinity Sunday'), /*  7 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 112*A_DAY)) => WT_I18N::translate('8th Sunday after Trinity Sunday'), /*  8 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 119*A_DAY)) => WT_I18N::translate('9th Sunday after Trinity Sunday'), /*  9 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 126*A_DAY)) => WT_I18N::translate('10th Sunday after Trinity Sunday'), /* 10 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 133*A_DAY)) => WT_I18N::translate('11th Sunday after Trinity Sunday'), /* 11 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 140*A_DAY)) => WT_I18N::translate('12th Sunday after Trinity Sunday'), /* 12 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 147*A_DAY)) => WT_I18N::translate('13th Sunday after Trinity Sunday'), /* 13 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 154*A_DAY)) => WT_I18N::translate('14th Sunday after Trinity Sunday'), /* 14 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 161*A_DAY)) => WT_I18N::translate('15th Sunday after Trinity Sunday'), /* 15 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 168*A_DAY)) => WT_I18N::translate('16th Sunday after Trinity Sunday'), /* 16 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 175*A_DAY)) => WT_I18N::translate('17th Sunday after Trinity Sunday'), /* 17 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 182*A_DAY)) => WT_I18N::translate('18th Sunday after Trinity Sunday'), /* 18 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 189*A_DAY)) => WT_I18N::translate('19th Sunday after Trinity Sunday'), /* 19 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 196*A_DAY)) => WT_I18N::translate('20th Sunday after Trinity Sunday'), /* 20 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 203*A_DAY)) => WT_I18N::translate('21st Sunday after Trinity Sunday'), /* 21 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 211*A_DAY)) => '22 Benedicta (Trinitatem)', /* I18N::translate('22nd Sunday after Trinity Sunday*/

		/* Advent or Benedicta - HOW TO CALCULATE???? */

		/* If 23 Benedicta is Adventus 1 then stop calculate Benedicta */
		date('Y-n-j', ($easter + 218*A_DAY)) => WT_I18N::translate('23rd Sunday after Trinity Sunday'), /* 23 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 218*A_DAY)) => WT_I18N::translate('Advent Sunday'), /* Adventus 1 Domini */
		/* If 24 Benedicta is Adventus 1 then stop calculate Benedicta') */
		date('Y-n-j', ($easter + 225*A_DAY)) => WT_I18N::translate('24th Sunday after Trinity Sunday'), /* 24 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 225*A_DAY)) => WT_I18N::translate('Advent Sunday'), /* Adventus 1 Domini */
		/* If 25 Benedicta is Adventus 1 then stop calculate Benedicta') */
		date('Y-n-j', ($easter + 232*A_DAY)) => WT_I18N::translate('25th Sunday after Trinity Sunday'), /* 25 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 232*A_DAY)) => WT_I18N::translate('Advent Sunday'), /* Adventus 1 Domini */
		/* If 26 Benedicta is Adventus 1 then stop calculate Benedicta') */
		date('Y-n-j', ($easter + 239*A_DAY)) => WT_I18N::translate('26th Sunday after Trinity Sunday'), /* 26 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 239*A_DAY)) => WT_I18N::translate('Advent Sunday'), /* Adventus 1 Domini */
		/* If 27 Benedicta is Adventus 1 then stop calculate Benedicta') */
		date('Y-n-j', ($easter + 246*A_DAY)) => WT_I18N::translate('27th Sunday after Trinity Sunday'), /* 27 Benedicta (Trinitatem) */
		date('Y-n-j', ($easter + 246*A_DAY)) => WT_I18N::translate('Advent Sunday'), /* Adventus 1 Domini */

		/* Advent Sundays - HOW TO CALCULATE???? */
		date('Y-n-j', mktime(0,0,0, 0, 0,$year)) => WT_I18N::translate('2nd Advent Sunday'), /* Adventus 2 Domini */
		date('Y-n-j', mktime(0,0,0, 0, 0,$year)) => WT_I18N::translate('3rd Advent Sunday'), /* Adventus 3 Domini */

		/* Last day of year until 1812 - HOW TO CALCULATE???? */
		date('Y-n-j', mktime(0,0,0, 0, 0,$year)) => WT_I18N::translate('4th Advent Sunday'), /* Adventus 4 Domini */


		/* Christmas */
		date('Y-n-j', mktime(0,0,0,12,25,$year)) => WT_I18N::translate('Christmas Day'), /* Natalis Domino */
		date('Y-n-j', mktime(0,0,0,12,26,$year)) => WT_I18N::translate('1st Day after Christmas'), /* Stephanus Protomartyr */
		date('Y-n-j', mktime(0,0,0, 12, 27,$year)) => WT_I18N::translate('2nd Day after Christmas'), /* Natalis 2 */

		/* Every 25 year after 1450- HOW TO CALCULATE???? */
		date('Y-n-j', mktime(0,0,0, 0, 0,$year)) => WT_I18N::translate('Jubilee year'), /* Annus jubilationis */

		/* If leap_year then */
		date('Y-n-j', mktime(0,0,0, 2, 24,$year)) => WT_I18N::translate('Leap Day'), /* Bissextilis */
		date('Y-n-j', $easter - $leap_day*A_DAY) => WT_I18N::translate('Leap Year'), /* Bissextilis */

		/* Before 1770 - HOW TO CALCULATE???? */
		date('Y-n-j', ($easter + 51*A_DAY)) => WT_I18N::translate('2 Pentecost'), /* Pentecostes 2 */
		date('Y-n-j', ($easter +2*A_DAY)) => WT_I18N::translate('Easter Tuesday'), /* Pascha 3 */
		date('Y-n-j', mktime(0,0,0, 8, 15,$year)) => WT_I18N::translate('Visitatio Assumption'), /* Visitation of the Virgin Mary*/
		date('Y-n-j', mktime(0,0,0, 3, 25,$year)) => WT_I18N::translate('Annunciation of the Virgin Mary'), /* I18N::translate('Annuntatio Assumption Virginis') */
		date('Y-n-j', mktime(0,0,0, 9, 29,$year)) => WT_I18N::translate('Saint Michaelmas'), /* Festo Michaelis */

		/* After 1812 - HOW TO CALCULATE???? */
		date('Y-n-j', mktime(0,0,0, 1, 1,$year)) => WT_I18N::translate('New Year') /* Novi Anni/Circumsiio Domino */
	);

	$month_name= array();
	for ($i=1; $i<13; ++$i) {
		$month_name[$i]=WT_Date_Gregorian::NUM_TO_MONTH_NOMINATIVE($i, false);
	}

	$day_letter= array();
	for ($i=1; $i<8; ++$i) {
		$day_letter[$i]=WT_Date_Gregorian::SHORT_DAYS_OF_WEEK($i-1);
	}

	$html = '';
    $html.= '
		<table class="cal"><tr>';
			for ($i=$start; $i<=$end; $i++) {
				$html.=  '<th>' .$month_name[$i] . '</th>';
			}
		$html.= '</tr>';
		for ($day=1; $day<=31; $day++) {
			$html.=  '<tr>';
				for ($mth=$start; $mth<=$end; $mth++) {
					if ( day_exists($year,$mth,$day) ) {
						$fmt = $year. '-' .$mth. '-' .$day;
						$week = date('N', mktime(0,0,0,$mth,$day,$year));
						$print_day = $day_letter[$week];
						$style = ( $week == 7 ) ? ' class="sunday"' : '';
						$weekno = ( $week == 1 ) ? '<span class="rw">' .(int)date('W', mktime(0,0,0,$mth,$day,$year)). '</span>' : '';
						$right2 = '';
						foreach ($holidays as $e_event  => $e_date) {
							if ( $e_event == $fmt) {
								$style = ' class="hol"';
								$right2 = '<span class="r2">' .$e_date. '</span>';
							}
						}
						$html.= '<td' .$style. '><span class="l">' .$day. '</span><span class="r">' .$print_day. '</span>' .$right2.$weekno. '</td>';
					} else {
						$html.= '<td>&nbsp;</td>';
					}
				}
			$html.=  '</tr>';
		}
    $html.=  '</table>';
	return $html;
}

// DISPLAY

$html.='
	<style type="text/css">
		#yearly {margin-bottom:30px;}
		#yearly select {font-size:14px;}
		#yearly h2 {font-weight:bold;margin:10px;text-align:left;}
		#yearly h3 {font-size:10px; font-weight:bold; margin-top:3px; margin-bottom:3px;}
        #yearly form h2 {display: inline-block;}
		th {background-color:#8F8F8F; width:180px;}
		p {margin-top:0px; margin-bottom:5px;}
		.l {width:15px; float:left; text-align:right;}
		.r {width:24px; margin-left:4px; float:left;}
		.r2 {margin-left:4px; float:left;}
		.rw {font-size:3em;font-weight: bold;position: absolute;padding-left:7%;margin-top:2%;display: block; opacity: 0.15;}
		.cal {border-collapse:collapse; font-size:90%; width:95%; margin:10px auto;}
		.cal td {border: 1px solid; border-collapse:collapse; padding:2px;}
		.cal th {border: 1px solid; border-collapse:collapse; padding:2px; width:16%}
		.sunday {background-color:#ddd;}
		.hol {background-color:#D0AFAF;}
	</style>
	<div id="yearly">
		<form action="'.$_SERVER["PHP_SELF"].'?mod=calendar_utilities&amp;mod_action=show#yearly_calendar" method="post">
			<h2>' . WT_I18N::translate('Choose Year') . '</h2>
			<select name="year">';
			for ($i=1970; $i<=2038; $i++) {//mktime() can only be used betwee 1970 and 2038
				$selected = ($i==$year) ? " selected=\"selected\"":"";
				$html.=  " <option value=\"".$i."\"".$selected.">".$i."</option>\n";
			}

			$html.= '
				</select>
				<input class="button_ec" type="submit" name="submit" value="Get Calendar" onclick="calc_calendar()">
		</form>
		<h2>'.$year.' - ' . WT_I18N::translate('First part of year') . '</h2>'.print_month(1,6,$year).'
		<div style="page-break-after:always;"></div>
		<h2>'.$year.' - ' . WT_I18N::translate('Second part of year') . '</h2>'.print_month(7,12,$year).'
	</div>';
