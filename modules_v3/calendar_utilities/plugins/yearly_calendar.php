<?php
// Plugin for calendar_utilities module
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010 PGV Development Team. All rights reserved.
//
// Modifications Copyright (c) 2010 Greg Roach
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

$plugin_name = "Yearly Calendar"; // need double quotes, as file is scanned/parsed by script

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
		date('Y-n-j', mktime(0,0,0, 1, 25,$year))=> 'Pauli conversion', /* TRANSLATE Saint Pauls Day */
		date('Y-n-j', mktime(0,0,0, 11, 2,$year))=> 'Festo Liberationis Patriae', /* TRANSLATE Thanksgiving (Denmark) */
		date('Y-n-j', mktime(0,0,0, 2, 22,$year))=> 'Cathedra Petri', /* TRANSLATE Saint Petris Chair */
		date('Y-n-j', mktime(0,0,0, 3, 9,$year))=> '40 milities martyres', /* TRANSLATE 40 Knights */

		date('Y-n-j', mktime(0,0,0, 5, 1,$year))=> 'Valpurgis', /* TRANSLATE Saint Valpurgis Day */
		date('Y-n-j', mktime(0,0,0, 5, 1,$year))=> 'Philippi et Jacobi', /* TRANSLATE Saint Jacobi Day */

		date('Y-n-j', mktime(0,0,0, 6, 24,$year))=> 'Johannes Baptista Nativitas', /* TRANSLATE Midsummer Day */
		date('Y-n-j', mktime(0,0,0, 6, 27,$year))=> '7 septem dormientes', /* TRANSLATE 7 September Day */

		date('Y-n-j', mktime(0,0,0, 11, 1,$year))=> 'Omnium Sanctorum', /* TRANSLATE Halloween Day */
		date('Y-n-j', mktime(0,0,0, 11, 2,$year))=> 'Omnium Animarum', /* TRANSLATE Day after Halloween */
		date('Y-n-j', mktime(0,0,0, 11, 11,$year))=> 'Martinus ep. conf.', /* TRANSLATE Saint Martin Day */


		date('Y-n-j', mktime(0,0,0, 8, 10,$year))=> 'Laurentius', /* TRANSLATE Saint Lorentz Day */
		date('Y-n-j', mktime(0,0,0, 8, 15,$year))=> 'Assumptio Maria', /* TRANSLATE Mary Prayers Day */
		date('Y-n-j', mktime(0,0,0, 8, 24,$year))=> 'Bartholomeus', /* TRANSLATE Saint Bartholomeus Day */

		date('Y-n-j', mktime(0,0,0, 10, 23,$year))=> 'Severinus', /* TRANSLATE Saint Severinus Day */


		/* Sunday before Ephiphania - HOW TO CALCULATE???? */
		date('Y-n-j', mktime(0,0,0, 0, 0,$year))=> 'Dominica Ephiphania', /* TRANSLATE Sunday before Holly 3 Kings */

		/* Ephiphania */
		date('Y-n-j', mktime(0,0,0,1, 6,$year))=> 'Ephiphania', /* TRANSLATE Holly 3 Kings */
		date('Y-n-j', mktime(0,0,0, 1, 6,$year))=> 'Baptismus Christi', /* TRANSLATE Birth of Christ */

		/* Before Jejunium */
		 date('Y-n-j', ($easter -32*A_DAY))=> 'Carnivora', /* TRANSLATE Shrove Tuesday */
		 date('Y-n-j', ($easter -33*A_DAY))=> 'Dies Cinerum', /* TRANSLATE Ash Wedensday */
		 date('Y-n-j', $easter - $shrove*A_DAY)=> 'Esto Mihi', /* TRANSLATE Shrove Sunday */

		/* Jejunium - 5 sundays*/
		date('Y-n-j', ($easter -42*A_DAY))=> 'Quintana/Quadragesima (Jejunium)', /* TRANSLATE 1st Shrove Sunday */
		date('Y-n-j', ($easter -35*A_DAY))=> 'Reminiscere (Jejunium)', /* TRANSLATE 2nd Shrove Sunday */
		date('Y-n-j', ($easter -28*A_DAY))=> 'Oculi (Jejunium)', /* TRANSLATE 3rd Shrove Sunday */
		date('Y-n-j', ($easter -21*A_DAY))=> 'Laetare (Jejunium)', /* TRANSLATE 4th Shrove Sunday */
		date('Y-n-j', ($easter -14*A_DAY))=> 'Judica (Jejunium)', /* TRANSLATE 5th Shrove Sunday */

		/* Moveable sundays before Easter - 9 sundays*/
		date('Y-n-j', ($easter -63*A_DAY))=> 'Septuagesima', /* TRANSLATE 9th Sunday before Easter */
		date('Y-n-j', ($easter -56*A_DAY))=> 'Sexagesima', /* TRANSLATE 8th Sunday before Easter */
		date('Y-n-j', ($easter -49*A_DAY))=> 'Quinquagesima', /* TRANSLATE 7th Sunday before Easter */
		date('Y-n-j', ($easter -42*A_DAY))=> 'Invocavit', /* TRANSLATE 6th Sunday before Easter */
		date('Y-n-j', ($easter -35*A_DAY))=> 'Reminiscere', /* TRANSLATE 5th Sunday before Easter */
		date('Y-n-j', ($easter -28*A_DAY))=> 'Oculi', /* TRANSLATE 4th Sunday before Easter */
		date('Y-n-j', ($easter -21*A_DAY))=> 'Laetare', /* TRANSLATE 3rd Sunday before Easter*/
		date('Y-n-j', ($easter -14*A_DAY))=> 'Judica', /* TRANSLATE 2nd Sunday before Easter */
		date('Y-n-j', ($easter - 7*A_DAY))=> 'Domini Palmarum (Dies)', /* TRANSLATE 1st Sunday before Easter */

		/* Other moveable days before Easter */
		date('Y-n-j', ($easter -39*A_DAY))=> 'Quatuor Tempora', /* TRANSLATE Tamper Day */
		date('Y-n-j', ($easter -6*A_DAY))=> 'Dimmel Ferie', /* TRANSLATE Restdays/week */
		date('Y-n-j', ($easter -5*A_DAY))=> 'Dimmel Ferie', /* TRANSLATE Restdays/week */
		date('Y-n-j', ($easter -4*A_DAY))=> 'Dimmel Ferie', /* TRANSLATE Restdays/week */


		/* Easter */
		date('Y-n-j', ($easter -1*A_DAY))=> 'Sanctum sabbatum', /* TRANSLATE Saint Pauls Day */
		date('Y-n-j', ($easter -2*A_DAY))=> 'Passio Domini (Sorteris)', /* TRANSLATE Good Friday */
		date('Y-n-j', ($easter -3*A_DAY))=> 'Dies Viridium/Coena Domini', /* TRANSLATE Maundy Thursday */

		date('Y-n-j', ($easter +1*A_DAY))=> 'Pascha 2', /* TRANSLATE Easter Monday */

		/* If date is >1700-03-01 then easter-date is */
		date('Y-n-j', ($easter +0*A_DAY))=> 'Dominica Sancti Domini (Pascha)', /* TRANSLATE Easter Sunday */
		date('Y-n-j', ($easter +0*A_DAY))=> 'Festum resurrectionis Domini (Pascha)', /* TRANSLATE Easter Sunday */
		date('Y-n-j', ($easter +0*A_DAY))=> 'Festum Sancti Spiritus (Pascha)', /* TRANSLATE Easter Sunday */
		/* Else date */
		date('Y-n-j', ($easter +10*A_DAY))=> 'Dominica Sancti Domini (Pascha)',/* TRANSLATE Easter Sunday */
		date('Y-n-j', ($easter +10*A_DAY))=> 'Festum resurrectionis Domini (Pascha)', /* TRANSLATE Easter Sunday */
		date('Y-n-j', ($easter +10*A_DAY))=> 'Festum Sancti Spiritus (Pascha)', /* TRANSLATE Easter Sunday */

		/* Moveable sundays after Easter */
		date('Y-n-j', ($easter + 7*A_DAY))=> 'Quasimodogeniti', /* TRANSLATE 1st Sunday after Easter */
		date('Y-n-j', ($easter +14*A_DAY))=> 'Miserecordia', /* TRANSLATE 2nd Sunday after Easter */
		date('Y-n-j', ($easter +21*A_DAY))=> 'Jubilate', /* TRANSLATE 3rd Sunday after Easter */
		date('Y-n-j', ($easter +28*A_DAY))=> 'Cantate', /* TRANSLATE 4th Sunday after Easter */
		date('Y-n-j', ($easter +35*A_DAY))=> 'Rogate', /* TRANSLATE 5th Sunday after Easter */
		date('Y-n-j', ($easter +42*A_DAY))=> 'Exaudi', /* TRANSLATE 6th Sunday after Easter */

		/* Other moveable days after Easter */
		date('Y-n-j', ($easter + 26*A_DAY))=> 'Festo communi Praecum', /* TRANSLATE Ascension Day */
		date('Y-n-j', ($easter + 39*A_DAY))=> 'Festo eucharistio', /* TRANSLATE Prior Day (Denmark) */

		/* Pentecostes 49s after Easter */
		date('Y-n-j', ($easter + 49*A_DAY))=> 'Pentecostes/Festum Sancti Spiritus', /* TRANSLATE Pentecost */
		date('Y-n-j', ($easter + 50*A_DAY))=> 'Pentecostes 1', /* TRANSLATE 2 Pentecost */

		/* Benedicta (Trinitatem) - Always at least 22 sundays after easter */
		date('Y-n-j', ($easter + 56*A_DAY))=> ' Benedicta (Trinitatem)', /* TRANSLATE Trinity Sunday */
		date('Y-n-j', ($easter + 63*A_DAY))=> ' 1 Benedicta (Trinitatem)', /* TRANSLATE 1st s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 70*A_DAY))=> ' 2 Benedicta (Trinitatem)', /* TRANSLATE 2nd s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 77*A_DAY))=> ' 3 Benedicta (Trinitatem)', /* TRANSLATE 3rd s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 84*A_DAY))=> ' 4 Benedicta (Trinitatem)', /* TRANSLATE 4th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 91*A_DAY))=> ' 5 Benedicta (Trinitatem)', /* TRANSLATE 5th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 98*A_DAY))=> ' 6 Benedicta (Trinitatem)', /* TRANSLATE 6th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 105*A_DAY))=> ' 7 Benedicta (Trinitatem)', /* TRANSLATE 7th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 112*A_DAY))=> ' 8 Benedicta (Trinitatem)', /* TRANSLATE 8th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 119*A_DAY))=> ' 9 Benedicta (Trinitatem)', /* TRANSLATE 9th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 126*A_DAY))=> '10 Benedicta (Trinitatem)', /* TRANSLATE 10th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 133*A_DAY))=> '11 Benedicta (Trinitatem)', /* TRANSLATE 11th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 140*A_DAY))=> '12 Benedicta (Trinitatem)', /* TRANSLATE 12th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 147*A_DAY))=> '13 Benedicta (Trinitatem)', /* TRANSLATE 13th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 154*A_DAY))=> '14 Benedicta (Trinitatem)', /* TRANSLATE 14th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 161*A_DAY))=> '15 Benedicta (Trinitatem)', /* TRANSLATE 15th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 168*A_DAY))=> '16 Benedicta (Trinitatem)', /* TRANSLATE 16th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 175*A_DAY))=> '17 Benedicta (Trinitatem)', /* TRANSLATE 17th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 182*A_DAY))=> '18 Benedicta (Trinitatem)', /* TRANSLATE 18th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 189*A_DAY))=> '19 Benedicta (Trinitatem)', /* TRANSLATE 19th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 196*A_DAY))=> '20 Benedicta (Trinitatem)', /* TRANSLATE 20th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 203*A_DAY))=> '21 Benedicta (Trinitatem)', /* TRANSLATE 21st s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 211*A_DAY))=> '22 Benedicta (Trinitatem)', /* TRANSLATE 22nd s.a. Trinity Sunday*/

		/* Advent or Benedicta - HOW TO CALCULATE???? */

		/* If 23 Benedicta is Adventus 1 then stop calculate Benedicta */
		date('Y-n-j', ($easter + 218*A_DAY))=> '23 Benedicta (Trinitatem)', /* TRANSLATE 23rd s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 218*A_DAY))=> 'Adventus 1 Domini', /* TRANSLATE Advent Sunday */
		/* If 24 Benedicta is Adventus 1 then stop calculate Benedicta */
		date('Y-n-j', ($easter + 225*A_DAY))=> '24 Benedicta (Trinitatem)', /* TRANSLATE 24th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 225*A_DAY))=> 'Adventus 1 Domini', /* TRANSLATE Advent Sunday */
		/* If 25 Benedicta is Adventus 1 then stop calculate Benedicta */
		date('Y-n-j', ($easter + 232*A_DAY))=> '25 Benedicta (Trinitatem)', /* TRANSLATE 25th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 232*A_DAY))=> 'Adventus 1 Domini', /* TRANSLATE Advent Sunday */
		/* If 26 Benedicta is Adventus 1 then stop calculate Benedicta */
		date('Y-n-j', ($easter + 239*A_DAY))=> '26 Benedicta (Trinitatem)', /* TRANSLATE 26th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 239*A_DAY))=> 'Adventus 1 Domini', /* TRANSLATE Advent Sunday */
		/* If 27 Benedicta is Adventus 1 then stop calculate Benedicta */
		date('Y-n-j', ($easter + 246*A_DAY))=> '27 Benedicta (Trinitatem)', /* TRANSLATE 27th s.a. Trinity Sunday */
		date('Y-n-j', ($easter + 246*A_DAY))=> 'Adventus 1 Domini', /* TRANSLATE Advent Sunday */

		/* Advent Sundays - HOW TO CALCULATE???? */
		date('Y-n-j', mktime(0,0,0, 0, 0,$year))=> 'Adventus 2 Domini', /* TRANSLATE 2nd Advent Sunday */
		date('Y-n-j', mktime(0,0,0, 0, 0,$year))=> 'Adventus 3 Domini', /* TRANSLATE 3rd Advent Sunday */

		/* Last day of year until 1812 - HOW TO CALCULATE???? */
		date('Y-n-j', mktime(0,0,0, 0, 0,$year))=> 'Adventus 4 Domini', /* TRANSLATE 4th Advent Sunday */


		/* Christmas */
		date('Y-n-j', mktime(0,0,0,12,25,$year))=> 'Natalis Domino', /* TRANSLATE Christmas Day */
		date('Y-n-j', mktime(0,0,0,12,26,$year))=> 'Stephanus Protomartyr', /* TRANSLATE 1st Day after Christmas */
		date('Y-n-j', mktime(0,0,0, 12, 27,$year))=> 'Natalis 2', /* TRANSLATE 2nd Day after Christmas */

		/* Every 25 year after 1450- HOW TO CALCULATE???? */
		date('Y-n-j', mktime(0,0,0, 0, 0,$year))=> 'Annus jubilationis', /* TRANSLATE Jubilee year */

		/* If leap_year then */
		date('Y-n-j', mktime(0,0,0, 2, 24,$year))=> 'Bissextilis', /* TRANSLATE Leap Day */
		date('Y-n-j', $easter - $leap_day*A_DAY)=> 'Bissextilis', /* TRANSLATE Leap Year */

		/* Before 1770 - HOW TO CALCULATE???? */
		date('Y-n-j', ($easter + 51*A_DAY))=> 'Pentecostes 2', /* TRANSLATE 2 Pentecost */
		date('Y-n-j', ($easter +2*A_DAY))=> 'Pascha 3', /* TRANSLATE Easter Tuesday */
		date('Y-n-j', mktime(0,0,0, 8, 15,$year))=> 'Visitation of the Virgin Mary', /* TRANSLATE Visitatio Assumption*/
		//DUPLICATE DATE//		date('Y-n-j', mktime(0,0,0, 8, 15,$year))=> 'Purification of the Virgin Mary', /* Purificatio Assumption Virginis */
		date('Y-n-j', mktime(0,0,0, 3, 25,$year))=> WT_I18N::translate('Annunciation of the Virgin Mary'), /* TRANSLATE Annuntatio Assumption Virginis */
		date('Y-n-j', mktime(0,0,0, 9, 29,$year))=> 'Festo Michaelis', /* TRANSLATE Saint Michaelmas */

		/* After 1812 - HOW TO CALCULATE???? */
		date('Y-n-j', mktime(0,0,0, 1, 1,$year))=> 'Novi Anni/Circumsiio Domino' /* TRANSLATE New Year */
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
						foreach ($holidays as $e_event => $e_date) {
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
		#yearly h1 {font-size:15px; font-weight:bold;}
		#yearly h3 {font-size:10px; font-weight:bold; margin-top:3px; margin-bottom:3px;}
		th {background-color:#8F8F8F; color:#000000; width:180px;}
		p {margin-top:0px; margin-bottom:5px;}
		.l {width:15px; float:left; text-align:right;}
		.r {width:24px; margin-left:4px; float:left;}
		.r2 {margin-left:4px; float:left;}
		.rw {font-family: Arial, Verdana, sans-serif;font-size: 36px;font-weight: bold;margin-left: 4px;float: right;text-align: right;position: absolute;color: #8F8F8F;padding-left: 120px;margin-top: -4px;display: block; opacity: 0.5;}
		.cal {border:solid black 2px; border-collapse:collapse; font-size:10px; width:95%; margin:10px auto;}
		.cal td {border:solid black 1px; border-collapse:collapse; padding:2px;}
		.cal th {border:solid black 1px; border-collapse:collapse; padding:2px; width:16%}
		.sunday {background-color:#C1C1C1; color:#000000;}
		.hol {background-color:#D0AFAF; color:#000000;}
	</style>
	<div id="yearly">
		<form action="'.$_SERVER["PHP_SELF"].'?mod=simpl_utilities&amp;mod_action=show#calculator" method="post">
			<h1>Choose Year:</h1>
			<select name="year">';
			for ($i=1970; $i<=2038; $i++) {//mktime() can only be used betwee 1970 and 2038
				$selected = ($i==$year) ? " selected=\"selected\"":"";
				$html.=  " <option value=\"".$i."\"".$selected.">".$i."</option>\n";
			}

			$html.= '
				</select>
				<input class="button_ec" type="submit" name="submit" value="Get Calendar" onclick="calc_calendar()">
		</form>
		<h1>'.$year.' - First part of year</h1>'.print_month(1,6,$year).'
		<div style="page-break-after:always;"></div>
		<h1>'.$year.' - Second part of year</h1>'.print_month(7,12,$year).'
	</div>';
