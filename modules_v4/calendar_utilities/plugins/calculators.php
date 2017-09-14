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

// Plugin name - this needs double quotes, as file is scanned/parsed by script
$plugin_name = "Calculators"; /* I18N: Name of a plugin. */ KT_I18N::translate('Calculators');

global $WEEK_START;
$months = '';
for ($i=0; $i<12; ++$i) {
	$months .= '"' . KT_Date_Gregorian::NUM_TO_MONTH_NOMINATIVE($i+1, false) . '",';
}
$months = rtrim($months, ",");
$days_in_week = 7;
$days = '';
// We use JD%7 = 0/Mon...6/Sun.  Config files use 0/Sun...6/Sat.  Add 6 to convert.
$week_start=($WEEK_START+6)%$days_in_week;
for ($week_day=0; $week_day<$days_in_week; ++$week_day) {
	$days .= '"' . KT_Date_Gregorian::LONG_DAYS_OF_WEEK(($week_day+$week_start) % $days_in_week) . '",';
}
$days = rtrim($days, ",");
// OVERALL DISPLAY
$html .= '
<style type="text/css">.result {color:blue;font-weight: 900;}
	#utility_tools{min-height:280px;width: 100%;margin: auto;text-align: center;}
	#utility_tools h3{text-align:center;}
	#utility_tools h3.header{background-color:#d3d3d3;border-bottom:1px solid #c9c9c9;margin-top:0;padding:3px 0;border-top-left-radius: 8px;border-top-right-radius:8px;font-weight:900;font-size:12px;text-align:left;}
	#utility_tools h3 span{padding:0 5px;}
	.utility{background-color: #DDD; border:1px solid #c0c0c0;border-radius:8px;height:200px;margin:10px;display:inline-block;width:31%;min-width: 380px;overflow: hidden;}
	.utility table{margin:auto;}
	.utility label{margin:0 10px;}
	.bold {font-weight: 900;}
	#days .button {cursor: pointer;font-size: 80%;}
	#relationships .result{margin:0 20px;padding:5px;width:250px;}
	#relationships td{padding:5px;}
	#dob_calc p.main {margin:20px auto;}
	#dob_calc p.main span {margin-right:20px;}
	#dob_calc label {margin:0 20px 0 0;}
	#dob_calc label.age_part {margin:0 15px 0 0;}
	#dob_calc input {padding:3px;}
	.age_part {width:20px;}
	.icon-button_bday {display:inline-block; background-image:url(http://our-families.info/themes/simpl_grey/images/silk-sprite.png); background-color:transparent;background-repeat:no-repeat;margin:0 2px;vertical-align:middle; height:16px;width:16px;background-position:-64px -32px}
</style>
<div id="utility_tools">';

// UTILITY 1 - DAY OF THE WEEK -->
//$d = '';
$html .= '
<div class="utility" id="days">
	<h3 class="header"><span>' . KT_I18N::translate('Day of the Week Calculator') . '</span></h3>
	<form name="form">
		<table>
			<tbody>
				<tr>
					<td valign="top">
						<p>
							<label for="day">' . KT_I18N::translate('Day') . '</label>
							<select id="day" name="day" size="1">';
								for ($d=0; $d<31; ++$d) {
									$day = $d+1;
									$html .= '<option value="' . $d . '"';
									if ($day == 1) { $html .=' selected="selected"';}
									$html .= '>' . $day . '</option>';
								}
							$html .= '</select>
							<label for="month">' . KT_I18N::translate('Month') . '</label>
							<select id="month" name="month" size="1">';
								for ($m=0; $m<12; ++$m) {
									$month = KT_Date_Gregorian::NUM_TO_MONTH_NOMINATIVE($m+1, false);
									$html .= '<option value="' . $m . '"';
									if ($m == 0) { $html .=' selected="selected"';}
									$html .= '>' . $month . '</option>';
								}
							$html .= '</select>
							<label for="year">' . KT_I18N::translate('Year') . '</label>
							<input id="year" name="year" size="4" type="text" />
						</p>

						<h3>
							<input class="button" name="gdi" onclick="getDateInfo()" type="button" value="' . KT_I18N::translate('Get Date') . '" />
						</h3>

						<p>
							<label for="dow">' . KT_I18N::translate('Day of the week') . '</label>
								<input class="result" id="dow" name="dw" size="12" type="text" />
							<label for="time">' . KT_I18N::translate('Time') . '</label>
								<input class="result" id="time" name="time" size="10" type="text" />
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>';
?>
<!-- SCRIPTS -->
<script>
<!-- Original:  Abraham I. (abraham_824@hotmai.com) -->
<!-- Idea:  Peter Bonnett (PeterBonnett@hotmail.com) -->
<!-- This script and many more are available free online at -->
<!-- The JavaScript Source!! http://javascript.internet.com -->
<!-- Begin
var months = new Array(<?php echo $months; ?>);
var days = new Array(<?php echo $days; ?>);
var mtend = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
var opt = new Array("<?php echo KT_I18N::translate('Past'); ?>","<?php echo KT_I18N::translate('Future'); ?>");
function getDateInfo() {
	var y = document.form.year.value;
	var m = document.form.month.options[document.form.month.options.selectedIndex].value;
	var d = document.form.day.options[document.form.day.options.selectedIndex].value;
	var hlpr = mtend[m];
	if (d < mtend[m] + 1) {
	if (m == 1 && y % 4 == 0) { hlpr++; }
		var c = new Date(y,m,d);
		var dayOfWeek = c.getDay();
		document.form.dw.value = days[dayOfWeek];
		if(c.getTime() > new Date().getTime()) {
			document.form.time.value = opt[1];
		}
		else {
			document.form.time.value = opt[0];
	   }
	}
	else {
		alert("<?php echo KT_I18N::translate('That date is invalid'); ?>");
   }
}
function setY() {
	var y = new Date().getYear();
	if (y < 2000) y += 1900;
	document.form.year.value = y;
}
</script>
<?php
// close UTILITY 1 -->

// UTILITY 2 - RELATIONSHIP CALCULATOR
$html .= '
<div class="utility" id="relationships">
	<h3 class="header"><span>' . KT_I18N::translate('Relationship Calculator') . '</span></h3>

	<form action="" method="post" name="generations">
	<table>
		<tbody>
			<tr>
				<td colspan="2">' . KT_I18N::translate('Given a common blood ancestor, <strong>X</strong>') . '</td>
			</tr>
			<tr>
				<td>' . KT_I18N::translate('The first relationship to <strong>X</strong> is') . '</td>
				<td>
					<input name="yores" type="text" value="" >
					<input onclick="incGen(1)" type="button" value="+" >
					<input onclick="decGen(1)" type="button" value="-" >
				</td>
			</tr>
			<tr>
				<td>' . KT_I18N::translate('The relationship of <strong>D</strong> to <strong>X</strong> is') . '</td>
				<td>
					<input name="thares" type="text" value="" >
					<input onclick="incGen(2)" type="button" value="+" >
					<input onclick="decGen(2)" type="button" value="-" >
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input class="result" name="therelation" type="text" value="" >
				</td>
			</tr>
		</tbody>
	</table>
	</form>
</div>';
?>
<script language="JavaScript">
  var generationsData = new Object();
  generationsData.genYou = 0;
  generationsData.genD = 0;
  generationsData.genArray = new Array(3);
  generationsData.genArray[0] = "<?php echo KT_I18N::translate('Child'); ?>";
  generationsData.genArray[1] = "<?php echo KT_I18N::translate('Grandchild'); ?>";
  generationsData.genArray[2] = "<?php echo KT_I18N::translate('Great grandchild'); ?>";

  function initialBuild()
  {
    updateDisplays(); // initialize form text contents
  }


  function numSuffix(n)
  {
    var numString = " " + n;
    var retStr = "th"; // default return

    if(numString.length > 0)
    {
       if( numString.match(/11$/)
            || numString.match(/12$/)
            || numString.match(/13$/)
         )
            retStr = "th";
       else if( numString.charAt(numString.length - 1) == '1' )
            retStr = "st";
       else if( numString.charAt(numString.length - 1) == '2' )
            retStr = "nd";
       else if( numString.charAt(numString.length - 1) == '3' )
            retStr = "rd";
    }

   return n + retStr;
  }

  function generationDisplay(genNumber)
  {
     var lgth = generationsData.genArray.length;
     if( genNumber >= lgth )
     {
       for(var ix=lgth; ix<genNumber+1; ix++)
         ///
         // Increase array size and init values
         ///
         generationsData.genArray[ix] = numSuffix(ix -1) + " "
                     + generationsData.genArray[2];
     }
    return( generationsData.genArray[genNumber] );
  }

  function relationshipDisplay(genAsker, genComp)
  {
    var theirrelation	= "<?php echo KT_I18N::translate('You and D are siblings'); ?>";
    var t1				= genAsker; // your generation
    var t2				= genComp;  // generation to compare
    ///
    //  return the relationship display for the two generations
    ///
        if( t1 == t2 )
        {
                if( t1 == 0 ) // both 1st gen
                {
		            theirrelation="<?php echo KT_I18N::translate('You and D are siblings'); ?>";
		}
                else
                {
                    theirrelation = "<?php echo KT_I18N::translate('You and D are '); ?>"
                           + numSuffix(t1) + "<?php echo KT_I18N::translate(' Cousins'); ?>";
                }
        }
        else if( t1 == 0 && t2 > 0 )
        {
                var grandind="";
                if( t2 == 2 )
                {
                    grandind="<?php echo KT_I18N::translate('Grand'); ?>";
                }
                else if( t2 == 3 )
                {
                    grandind="<?php echo KT_I18N::translate('Great Grand'); ?>";
                }
                else if( t2 > 3 )
                {
                    grandind=  numSuffix(t2 - 2) + "<?php echo KT_I18N::translate(' Great Grand'); ?>";
                }

                theirrelation="<?php echo KT_I18N::translate('D is your '); ?>'" + grandind + "<?php echo KT_I18N::translate(' niece/nephew'); ?>";

        }
        else if( t1 > 0 && t2 == 0 )
        {
                var grandind="";
                if( t1 == 2 )
                {
                    grandind="<?php echo KT_I18N::translate('Grand'); ?>";
                }
                else if( t1 == 3 )
                {
                    grandind="<?php echo KT_I18N::translate('Great Grand'); ?>";
                }
                else if( t1 > 3 )
                {
                    grandind = numSuffix(t1 - 2) + "<?php echo KT_I18N::translate(' Great Grand'); ?>";
                }

                theirrelation="<?php echo KT_I18N::translate('You are the '); ?>" + grandind + "<?php echo KT_I18N::translate(' niece/nephew of D'); ?>";
        }
        else
        {
            var lesser = 1;
            var removed = 0;
            if( t1 > t2 )
            {
                lesser = t2;
                removed = t1 - t2;
            }
            else
            {
                lesser = t1;
                removed = t2 - t1;
            }

            if( removed > 0 )
            {
                theirrelation = "<?php echo KT_I18N::translate('You and D are '); ?>"
                     + numSuffix(lesser) + "<?php echo KT_I18N::translate(' cousins '); ?>" + removed;

                if( removed == 1 )
                    theirrelation += "<?php echo KT_I18N::translate(' time removed'); ?>";
                else
                    theirrelation += "<?php echo KT_I18N::translate(' times removed'); ?>";
            }
            else
                theirrelation="<?php echo KT_I18N::translate('You and D are '); ?>" + numSuffix(lesser)
                    + "<?php echo KT_I18N::translate(' cousins'); ?>";
        }
     return theirrelation;
  }

  function updateDisplays()
  {
    var t1 = generationsData.genYou;
    var t2 = generationsData.genD;

	document.generations.yores.value = generationDisplay(t1);
	document.generations.thares.value = generationDisplay(t2);
	document.generations.therelation.value = relationshipDisplay(t1, t2);
  }

  function incGen(gen)
  {
     var lgth = generationsData.genArray.length;
     if( gen == 1 )
        generationsData.genYou++;
     else
        generationsData.genD++;

     if( generationsData.genYou == lgth ||
         generationsData.genD == lgth)
     {
         ///
         // Increase array size and init value
         ///
         generationsData.genArray[lgth] = numSuffix(lgth -1) + " "
                     + generationsData.genArray[2];
     }
     updateDisplays();
  }

  function decGen(gen)
  {
     if( gen == 1 && generationsData.genYou > 0 )
          generationsData.genYou--;

     if( gen == 2 && generationsData.genD > 0 )
          generationsData.genD--;

     updateDisplays();
  }

</script><script language="JavaScript">
	  initialBuild();
</script>
<?php
// close UTILITY 2 -->

// UTILITY 3 - DATE OF BIRTH CALCULATOR -->
$html .= '
<div class="utility" id="dob_calc">
	<h3 class="header"><span>' . KT_I18N::translate('Birth Date Calculator') . '</span></h3>
	<form name="theForm">
		<p class="main">
			<label for="eventDate" class="bold">' . KT_I18N::translate('Event Date') . '</label>
			<input id="eventDate" name="eventDate" onchange="setEventDateGui()" placeholder="31/12/1905" size="10" type="text" />
		</p>
		<p class="main">
			<label class="bold">' . KT_I18N::translate('Age') . '</label>
				<input class="age_part" id="ageYY" name="ageYY" onchange="setAgeYYGui()" placeholder="27" size="2" type="text" />
			<label for="ageYY">' . KT_I18N::translate('years') . '</label>
				<input class="age_part" id="ageMM" name="ageMM" onchange="setAgeMMGui()" placeholder="0" size="2" type="text" />
			<label for="ageMM">' . KT_I18N::translate('months') . '</label>
				<input class="age_part" id="ageDD" name="ageDD" onchange="setAgeDDGui()" placeholder="0" size="2" type="text" />
			<label for="ageDD">' . KT_I18N::translate('days') . '</label>
		</p>
		<p class="main">
			<span class="bold">' . KT_I18N::translate('Estimated date of birth') . '</span>
			<span id="showResult"></span>
		</p>
	</form>
</div>';
?>
<script>
	var eventDate;
	var yy, mm, dd;
	var dlm = "/";

	function initPage() {
		setEventDate();
		setAgeYY();
		setAgeMM();
		setAgeDD();
		doCalc();
	}

	function setEventDateGui() {
		setEventDate();
		doCalc();
	}

	function setAgeYYGui() {
		setAgeYY();
		doCalc();
	}

	function setAgeMMGui() {
		setAgeMM();
		if (theForm.ageYY.value == null) {
			theForm.ageYY.value = 0;
			setAgeYY();
		}
		doCalc();
	}

	function setAgeDDGui() {
		setAgeDD();
		if (theForm.ageMM.value == null) {
			theForm.ageMM.value = 0;
			setAgeMM();
		}
		doCalc();
	}


	function setEventDate() {
		var dateFields = theForm.eventDate.value.split(dlm);
		for (i=0; i<dateFields.length; i++) {
			if (isNaN(parseInt(dateFields[i]))) {
				i = 4;
			}
		}
		if (i != 3) {
			alert ("<?php echo KT_I18N::translate('Please enter a valid date.'); ?>");
		} else {
			eventDate = newDate(dateFields[0], dateFields[1]-1, dateFields[2]);
			theForm.eventDate.value = dateToStr(eventDate);
		}
	}

	function setAgeYY() {
		var value = theForm.ageYY.value;
		yy = toInt(value);
		theForm.ageYY.value = yy;
		doCalc();
	}

	function setAgeMM() {
		var value = theForm.ageMM.value;
		mm = toInt(value);
		theForm.ageMM.value = mm;
		doCalc();
	}

	function setAgeDD() {
		var value = theForm.ageDD.value;
		dd = toInt(value);
		theForm.ageDD.value = dd;
		doCalc();
	}

	function doCalc() {
		var result = "";
		if (dd != null) {
			var resD = eventDate.getDate() - dd;
			var resM = eventDate.getMonth() - mm;
			var resY = eventDate.getFullYear() - yy;
			var resDt = newDate(resD, resM, resY);
			result = dateToStr(resDt);
		} else if (mm != null) {
			var resM = eventDate.getMonth() - mm;
			if (eventDate.getDate() < 16) {
				resM = resM - 1;
			}
			var resY = eventDate.getFullYear() - yy;
			var resDt = newDate(1, resM, resY);
			result = "ABT " + dateToMonthStr(resDt);
		} else if (yy != null) {
			var resM = eventDate.getMonth() + 1;
			var resY = eventDate.getFullYear() - yy;
			var resDt1 = newDate(1, resM, resY - 1);
			var resDt2 = newDate(1, resM - 1, resY); // Berts version has var resDt2 = newDate(1, resM - 2, resY); which I disagree with
			result = dateToMonthStr(resDt1) + " - " + dateToMonthStr(resDt2);
		} else {
			result = dateToStr(eventDate);
		}
		showResult.innerHTML = result;
	}

	function toInt(value) {
		var newVal = parseInt(value);
		if (isNaN(newVal)) {
			newVal = null;
		}
		return newVal;
	}

	function newDate(d, m, y) {
		var result = new Date();
		result.setFullYear(y);
		result.setDate(d);
		result.setMonth(m);
		return result;
	}

	function dateToStr(value) {
		return format99(value.getDate()) + dlm + format99(value.getMonth()+1) + dlm + value.getFullYear();
	}

	function format99(value) {
		if (value < 10) {
			return "0" + value;
		} else {
			return value;
		}
	}

	function dateToMonthStr(value) {
		return monthName(value.getMonth()) + " " + value.getFullYear();
	}

	function monthName(value) {
		if (value == 0) return "JAN";
		else if (value == 1) return "FEB";
		else if (value == 2) return "MAR";
		else if (value == 3) return "APR";
		else if (value == 4) return "MAY";
		else if (value == 5) return "JUN";
		else if (value == 6) return "JUL";
		else if (value == 7) return "AUG";
		else if (value == 8) return "SEP";
		else if (value == 9) return "OCT";
		else if (value == 10) return "NOV";
		else if (value == 11) return "DEC";
		else return "???";
	}
</script>
<?php
// close UTILITY 3 -->

$html .= '</div>'; // Close OVERALL DISPLAY
