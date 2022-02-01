<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

get_gedcom_setting(KT_GED_ID, 'COMMON_TYPES_THRESHOLD') ? $minMediaTypes = get_gedcom_setting(KT_GED_ID, 'COMMON_TYPES_THRESHOLD') : $minMediaTypes = 6;
?>

<script>

	// VERTICAL BAR CHART
	function barChart(element) {
		var element	= "#" + element;
		var linkUrl = "";
		switch(element) {
			case "#chartStatsBirth":
				try {var data	= JSON.parse(`<?php echo $stats->statsBirth(); ?>`);}
				catch(e){break;}
				var width	= 500;
				var height	= 200;
				var viewportSize = "0 0 500 200";
				var linkUrl = "statisticsTables.php?ged=<?php echo $GEDCOM; ?>&table=century&tag=birt&option=";
			break;
			case "#chartStatsDeath":
				try {var data	= JSON.parse(`<?php echo $stats->statsDeath(); ?>`);}
				catch(e){break;}
				var width	= 500;
				var height	= 200;
				var viewportSize = "0 0 500 200";
				var linkUrl = "statisticsTables.php?ged=<?php echo $GEDCOM; ?>&table=century&tag=deat&option=";
			break;
			case "#chartMarr":
				try {var data	= JSON.parse(`<?php echo $stats->statsMarr(); ?>`);}
				catch(e){break;}
				var width	= 500;
				var height	= 200;
				var viewportSize = "0 0 500 200";
				var linkUrl = "statisticsTables.php?ged=<?php echo $GEDCOM; ?>&table=century&tag=marr&option=";
			break;
			case "#chartDiv":
				try {var data	= JSON.parse(`<?php echo $stats->statsDiv(); ?>`);}
				catch(e){break;}
				var width	= 500;
				var height	= 200;
				var viewportSize = "0 0 500 200";
				var linkUrl = "statisticsTables.php?ged=<?php echo $GEDCOM; ?>&table=century&tag=div&option=";
			break;
			case "#chartMedia":
				try {var data	= JSON.parse(`<?php echo $stats->chartMedia($minMediaTypes); ?>`);}
				catch(e){break;}
				var width	= 960;
				var height	= 200;
				var viewportSize = "0 0 960 200";
				var linkUrl = "medialist.php?action=filter&search=yes&folder=&subdirs=on&sortby=title&max=20&filter=&apply_filter=apply_filter&form_type=";
			break;
			case "#chartChild":
				try {var data	= JSON.parse(`<?php echo $stats->statsChildren(); ?>`);}
				catch(e){break;}
				var width	= 500;
				var height	= 200;
				var viewportSize = "0 0 500 200";
		break;
			case "#chartNoChild":
				try {var data	= JSON.parse(`<?php echo $stats->chartNoChildrenFamilies(); ?>`);}
				catch(e){break;}
				var width	= 500;
				var height	= 200;
				var viewportSize = "0 0 500 200";
			break;
		}
		if (data) {
			// set the dimensions and margins of the graph
			var margin = {top: 0, right: 20, bottom: 20, left: 40},
				w	= width - margin.left - margin.right,
				h	= height - margin.top - margin.bottom;

			// set the ranges
			var x = d3.scaleBand().range([0, w]).padding(0.1);
			var y = d3.scaleLinear().range([h, 20]);

			// format the data
			data.forEach(function(d) {
				d.count = +d.count;
			});

			// Scale the range of the data in the domains
			x.domain(data.map(function(d) { return d.category; }));
			y.domain([0, d3.max(data, function(d) { return d.count; })]);

			var svg = d3.select(element).append("svg")
				.attr("preserveAspectRatio", "xMinYMin meet")
				.attr("viewBox", viewportSize)
				.append("g")
					.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

			// append the rectangles for the bar chart
			svg.selectAll(".bar")
				.data(data)
					.enter().append("rect")
						.attr("class", function(d){ return "bar-" +  d.color; })
						.attr("x", function(d) { return x(d.category); })
						.attr("width", x.bandwidth())
						.attr("y", function(d) { return y(d.count); })
						.attr("height", function(d) { return h - y(d.count); });


			// Labels at the top of each bar.
			svg.selectAll(".text")
				.data(data)
					.enter().append("text")
						.attr("x", (function(d) { return x(d.category) + (x.bandwidth() / 2) ; }))
						.attr("y", function(d) { return y(d.count) - 5; })
						.style("text-anchor", "middle")
						.style("font-size", "10px")
						.append("a")
							.attr("xlink:href", function(d){ return linkUrl + d.type })
							.attr("target", "blank")
							.html(function(d) { return d.percent; })
							.style("fill", "#3383bb");

			// Add the X Axis
			svg.append("g")
				.attr("transform", "translate(0," + h + ")")
				.call(d3.axisBottom(x));

			// Add the Y Axis
			svg.append("g")
				.call(d3.axisLeft(y).ticks(5));

		}

	}

	// HORIZONTAL BAR CHART
	function horizontalChart(element) {
		var element	= "#" + element;
		switch(element) {
			case "#chartCommonSurnames":
				try {var data = JSON.parse(`<?php echo $stats->chartCommonSurnames(array(25,10)); ?>`);}
				catch(e){break;}
				var width	= 400;
				var height	= 200;
				var viewportSize = "0 0 400 200";
			break;
			case "#chartCommonGiven":
				try {var data = JSON.parse(`<?php echo $stats->chartCommonGiven(array(0,10)); ?>`);}
				catch(e){break;}
				var width	= 400;
				var height	= 200;
				var viewportSize = "0 0 400 200";
			break;
		}

		if (data) {
			//sort bars based on value
	        data = data.sort(function (a, b) {
	            return d3.descending(a.count, b.count);
	        })

			// set the dimensions and margins of the graph
			var margin = {top: 0, right: 50, bottom: 10, left: 70},
				w = width - margin.left - margin.right,
				h = height - margin.top - margin.bottom;

			// set the ranges
			var x = d3.scaleLinear().range([0, w]);
			var y = d3.scaleBand().range([0, h]).padding(0.1);

			// Scale the range of the data in the domains
			x.domain([0, d3.max(data, function(d) { return d.count; })]);
			y.domain(data.map(function(d) { return d.category; }));

			// format the data
			data.forEach(function(d) {
				d.count = +d.count;
			});

			var svg = d3.select(element).append("svg")
				.attr("preserveAspectRatio", "xMinYMin meet")
				.attr("viewBox", viewportSize)
				.attr("class", "horizontalChart")
				.append("g")
					.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

			// append the rectangles for the bar chart
			svg.selectAll(".bar")
				.data(data)
					.enter().append("rect")
						.attr("class", function(d){ return "bar-" +  d.color; })
						.attr("width", function(d) { return x(d.count); })
						.attr("y", function(d) { return y(d.category); })
						.attr("height", y.bandwidth());

			// Labels at the end of each bar.
			svg.selectAll("text")
				.data(data)
					.enter().append("text")
						.attr("y", (function(d) { return y(d.category) + (y.bandwidth() / 2 + 5) ; }  ))
						.attr("x", function(d) { return x(d.count) + 5; })
						.style("font-size", "8px")
						.text(function(d) { return d.percent; });

		    // add the y Axis
		    svg.append("g")
				.style("font-size", "8px")
		        .call(d3.axisLeft(y).tickSize(0));
		}
	}

	// GROUPED BAR CHART
	function groupChart(element) {
		var element	= "#" + element;
		switch(element) {
			case "#chartStatsAge":
				try {var data	= JSON.parse(`<?php echo $stats->statsAge(); ?>`);}
				catch(e){break;}
				var width	= 960;
				var height	= 260;
				var viewportSize = "0 0 960 260";
			break;
			case "#chartMarrAge":
				try {var data	= JSON.parse(`<?php echo $stats->statsMarrAge(); ?>`);}
				catch(e){break;}
				var width	= 960;
				var height	= 260;
				var viewportSize = "0 0 960 260";
			break;
		}

		if (data) {
			var margin = {top: 30, right: 30, bottom: 50, left: 30},
				w = width - margin.left - margin.right,
				h = height - margin.top - margin.bottom;

			// The scale spacing the groups:
			var x0 = d3.scaleBand().rangeRound([0, w]).paddingInner(0.1);
			// The scale for spacing each group's bar:
			var x1 = d3.scaleBand().padding(0.05);
			var y = d3.scaleLinear().rangeRound([h, 0]);
			var z = d3.scaleOrdinal().range([
				"<?php echo $KT_STATS_CHART_COLOR1; ?>",
				"<?php echo $KT_STATS_CHART_COLOR2 ?>",
				"<?php echo $KT_STATS_CHART_COLOR3; ?>"
			]);
			var keys = d3.keys(data[0]).slice(1);

			x0.domain(data.map(function(d) { return d.century; }));
			x1.domain(keys).rangeRound([0, x0.bandwidth()]);
			y.domain([0, d3.max(data, function(d) { return d3.max(keys, function(key) { return d[key]; }); })]).nice();

			var svg = d3.select(element).append("svg")
				.attr("preserveAspectRatio", "xMinYMin meet")
				.attr("viewBox", viewportSize)
				.append("g")
					.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

			// columns
			svg.append("g")
				.selectAll("g")
					.data(data)
					.enter().append("g")
					.attr("class", "bar")
					.attr("transform", function(d) { return "translate(" + x0(d.century) + ",0)"; })
				.selectAll("rect")
					.data(function(d) {
						return keys.map(function(key) { return { key: key, value: d[key] }; });
					})
					.enter().append("rect")
					.attr("x", function(d) { return x1(d.key); })
					.attr("y", function(d) { return y(d.value); })
					.attr("width", x1.bandwidth())
					.attr("height", function(d) { return h - y(d.value); })
					.attr("fill", function(d) { return z(d.key); });

			//Column labels
			svg.append("g")
				.selectAll("g")
					.data(data)
					.enter().append("g")
					.attr("transform", function(d) { return "translate(" + x0(d.century) + ",0)"; })
				.selectAll("text")
					.data(function(d) {
						return keys.map(function(key) { return { key: key, value: d[key] }; });
					})
					.enter().append("text")
					.attr("x", function(d) { return x1(d.key) + x1.bandwidth() / 2 - 10; })
					.attr("y", function(d) { return y(d.value) - 5; })
					.text(function(d) { return d.value; })
					.style("font-size", "11px")

			// x-axis
			svg.append("g")
				.attr("transform", "translate(0," + h + ")")
				.style("font-size", "11px")
				.call(d3.axisBottom(x0));

			// y-axis
			svg.append("g")
				.attr("y", y(y.ticks().pop()) + 1)
				.style("font-size", "11px")
				.call(d3.axisLeft(y).ticks());

			// Legend
			var legend = svg.append("g")
				.attr("text-anchor", "end")
				.selectAll("g")
		  		    .data(keys.slice())
				    .enter().append("g")
						  .attr("transform", function (d,i) { return "translate(" + ((w / 3) + (i * 110)) + "," + (h + margin.bottom - 10) + ")";});
				legend.append("circle")
		  		    .attr("cx", 0)
		  		    .attr("cy", -4)
		  		    .attr("r", 5)
		  		    .style("fill", z);
				legend.append("text")
					.attr("x", 8)
					.attr("y", 0)
					.style("text-anchor", "start")
					.style("font-size", "11px")
					.text(function(d) { return d; });
		}

	}

	// PIE CHART
	function pieChart(element) {
		var element	= "#" + element;
		switch(element) {
			case "#chartSex":
				try {var data = JSON.parse(`<?php echo $stats->chartSex(); ?>`);}
				catch(e){break;}
				var width	= 200;
				var height	= 200;
				var viewportSize = "0 0 200 200";
			break;
			case "#chartMortality":
				try {var data = JSON.parse(`<?php echo $stats->chartMortality(); ?>`);}
				catch(e){break;}
				var width	= 200;
				var height	= 200;
				var viewportSize = "0 0 200 200";
			break;
			case "#chartIndisWithSources":
				try {var data = JSON.parse(`<?php echo $stats->chartIndisWithSources(); ?>`);}
				catch(e){break;}
				var width	= 200;
				var height	= 200;
				var viewportSize = "0 0 200 200";
			break;
			case "#chartFamsWithSources":
				try {var data = JSON.parse(`<?php echo $stats->chartFamsWithSources(); ?>`);}
				catch(e){break;}
				var width	= 200;
				var height	= 200;
				var viewportSize = "0 0 200 200";
			break;
		}

		if (data) {
			var color = d3.scaleOrdinal(d3.schemeCategory20);
			var margin = {top: 0, right: 30, bottom: 0, left: 10},
				w = width - margin.left - margin.right,
				h = height - margin.top - margin.bottom;
			var padding		= 50;
			var radius		= Math.min(width - padding, height - padding) / 2;

			var svg = d3.select(element)
				.append("svg")
					.attr("width", '100%')
					.attr("height", '100%')
					.attr("viewBox", viewportSize);

			var g = svg.append("g")
				.attr("transform", "translate(" + (w / 2) + "," + (h / 2 - 10) + ")");

			var arc = d3.arc()
				.innerRadius(0)
				.outerRadius(radius);

			var pie = d3.pie()
				.value(function(d) { return d.count; })
				.sort(null);

			var path = g.selectAll("path")
				.data(pie(data))
					.enter().append("g")
						.append("path")
						.attr("fill", function(d, i) { return color(i); } )
						.attr("class", function(d){ return "bar-" +  d.data.color; }) // css over-rides fill color if d.data.color exists
						.attr("d", arc);

			//new legend code
			var legendCircRad = 5;
			var legendSpacing = 4;

			var legendWrap = svg.append('g')
			.attr('class', 'legendwrap')
			.attr("transform", function (d,i) { return "translate(" + (5) + "," + (h - 20) + ")";});

			var legend = svg.select('.legendwrap').selectAll('.legend')
				.data(pie(data))
				.enter()
				.append('g')
				.attr('class', 'legend');

			legend.append('circle')
				.attr("cx", 0)
				.attr("cy", 0)
				.attr("r", legendCircRad)
				.attr("fill", function(d, i) { return color(i); } )
				.attr("class", function(d){ return "bar-" +  d.data.color; }); // css over-rides fill color if d.data.color exists

			legend.append('text')
				.attr('x', legendCircRad + legendSpacing)
				.attr('y', legendCircRad - legendSpacing + 2)
				.style("text-anchor", "start")
				.style("font-size", "8px")
				.text(function(d){ return d.data.category + " (" + d.data.percent + ")"; });

			var ypos = 0, newxpos = 0, rowOffsets = [];
			var legendItemsCount = svg.selectAll('.legend').size();

			legend.attr("transform", function (d, index) {
			var length = d3.select(this).select("text").node().getComputedTextLength() + (legendCircRad + legendSpacing * 3);

			if (width < newxpos + length) {
				rowOffsets.push((width - newxpos) / 2);
				newxpos = 0;
				ypos += 15;
			}

			d.x = newxpos;
			d.y = ypos;
			d.rowNo = rowOffsets.length;
			newxpos += length;

			if (index === legendItemsCount - 1)
				rowOffsets.push((width - newxpos) / 2);
			});

			legend.attr("transform", function (d, i) {
				var x = d.x + rowOffsets[d.rowNo];
				return 'translate(' + x + ',' + d.y + ')';
			});
		}
	}

	// MAP CHART
	function mapChart(element) {
		var element	= "#" + element;
		switch(element) {
			case "#chartDistribution":
				try {var data = JSON.parse(`<?php echo $stats->chartDistribution(); ?>`);}
				catch(e){break;}
				var width	= 400;
				var height	= 300;
				var viewportSize = "0 0 400 300";
			break;
		}

		if (data) {
			// Set <<High populatin>> to greater than 80% of total counts for color threshold
			var totalCount = 0;
			for (i = 0, len = data.length; i < len; ++i) {
		        country = data[i];
				totalCount += country.count;
		    }
			var eightyPercent =  Math.round((totalCount * 0.80) / 1000) * 1000;

			// Set color ranges
			var colorScale = d3.scaleThreshold()
				.range(["<?php echo $KT_STATS_CHART_COLOR3; ?>", "<?php echo $KT_STATS_CHART_COLOR2 ?>", "<?php echo $KT_STATS_CHART_COLOR1; ?>"])
				.domain([1, eightyPercent]);

			// Create map
			var margin = {top: 10, right: 10, bottom: 80, left: 10},
				w = width - margin.left - margin.right,
				h = height - margin.top - margin.bottom;

			// The svg
			var svg = d3.select(element)
				.append("svg")
				.attr("width", '100%')
				.attr("height", '100%')
				.attr("viewBox", viewportSize);

			// Map and projection
			var path = d3.geoPath();
			var projection = d3.geoMercator()
			  .scale(70)
			  .center([0,50])
			  .translate([w / 2, h / 2]);

			// Draw the map
			d3.json("<?php echo KT_MODULES_DIR . 'chart_statistics/world.geojson'; ?>", function(error, countries) {
			    if (error)
			    return console.error(error);
			    console.log(countries.features);

			    var countById = {};
			    data.forEach(function(d) {countById[d.country] = +d.count;});

				svg.append("g")
					.selectAll("path")
					.data(countries.features)
					.enter()
					.append("path")
						// draw each country
						.attr("d", d3.geoPath()
							.projection(projection)
						)
						// set the color of each country
						.attr("fill", function (d) {
							d.color = countById[d.properties.ISO_A2] || 0;
							return colorScale(d.color);
						})

					.append("svg:title")
						.attr("class", function(d) { return "path " + d.id; })
						.attr("transform", function(d) { return "translate(" + path.centroid(d) + ")"; })
						.attr("dy", ".35em")
						.text(function(d) { return (
							countById[d.properties.ISO_A2] ? countById[d.properties.ISO_A2] : "")
						})

				// Add legend
				svg.append("circle").attr("cx",0).attr("cy",276).attr("r", 7).style("fill", "<?php echo $KT_STATS_CHART_COLOR1; ?>")
				svg.append("circle").attr("cx",150).attr("cy",276).attr("r", 7).style("fill", "<?php echo $KT_STATS_CHART_COLOR2; ?>")
				svg.append("circle").attr("cx",300).attr("cy",276).attr("r", 7).style("fill", "<?php echo $KT_STATS_CHART_COLOR3; ?>")
				svg.append("text").attr("x", 20).attr("y", 280).text("<?php echo KT_I18N::translate('High population'); ?>").style("text-anchor", "start").style("font-size", "12px")
				svg.append("text").attr("x", 170).attr("y", 280).text("<?php echo KT_I18N::translate('Low population'); ?>").style("text-anchor", "start").style("font-size", "12px")
				svg.append("text").attr("x", 320).attr("y", 280).text("<?php echo KT_I18N::translate('Nobody at all'); ?>").style("text-anchor", "start").style("font-size", "12px")

			});
		}

	}

</script>
<?php
