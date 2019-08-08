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

?>
<script>

	// VERTICAL BAR CHART
	function barChart(element) {
		var element	= "#" + element;
		switch(element) {
			case "#chartStatsBirth":
				var data	= JSON.parse(`<?php echo $stats->statsBirth(); ?>`);
				var width	= 500;
				var height	= 200;
				var viewportSize = "0 0 500 200";
			break;
			case "#chartStatsDeath":
				var data	= JSON.parse(`<?php echo $stats->statsDeath(); ?>`);
				var width	= 500;
				var height	= 200;
				var viewportSize = "0 0 500 200";
			break;
			case "#chartMarr":
				var data	= JSON.parse(`<?php echo $stats->statsMarr(); ?>`);
				var width	= 500;
				var height	= 200;
				var viewportSize = "0 0 500 200";
			break;
			case "#chartDiv":
				var data	= JSON.parse(`<?php echo $stats->statsDiv(); ?>`);
				var width	= 500;
				var height	= 200;
				var viewportSize = "0 0 500 200";
			break;
			case "#chartMedia":
				var data	= JSON.parse(`<?php echo $stats->chartMedia(5); ?>`);
				var width	= 960;
				var height	= 200;
				var viewportSize = "0 0 960 200";
			break;
			case "#chartChild":
				var data	= JSON.parse(`<?php echo $stats->statsChildren(); ?>`);
				var width	= 500;
				var height	= 200;
				var viewportSize = "0 0 500 200";
		break;
			case "#chartNoChild":
				var data	= JSON.parse(`<?php echo $stats->chartNoChildrenFamilies(); ?>`);
				var width	= 500;
				var height	= 200;
				var viewportSize = "0 0 500 200";
			break;
			default:
				var data	= "";
				var width	= 0;
				var height	= 0;
				var viewportSize = "0 0 0 0";
			break;
		}

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
					.style("font-size", "10")
					.style("text-anchor", "middle")
					.attr("x", (function(d) { return x(d.category) + (x.bandwidth() / 2) ; }  ))
					.attr("y", function(d) { return y(d.count) - 5; })
					.text(function(d) { return d.percent; });

		// Add the X Axis
		svg.append("g")
			.attr("transform", "translate(0," + h + ")")
			.call(d3.axisBottom(x));

		// Add the Y Axis
		svg.append("g")
			.call(d3.axisLeft(y).ticks(5));

	}

	// HORIZONTAL BAR CHART
	function horizontalChart(element) {
		var element	= "#" + element;
		switch(element) {
			case "#chartCommonSurnames":
				var data = JSON.parse(`<?php echo $stats->chartCommonSurnames(array(25,10)); ?>`);
				var width	= 400;
				var height	= 200;
				var viewportSize = "0 0 400 200";
			break;
			case "#chartCommonGiven":
				var data = JSON.parse(`<?php echo $stats->chartCommonGiven(array(0,10)); ?>`);
				var width	= 400;
				var height	= 200;
				var viewportSize = "0 0 400 200";
			break;
			default:
				var data = "";
			break;
		}

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
					.style("font-size", "8")
					.text(function(d) { return d.percent; });

	    // add the y Axis
	    svg.append("g")
			.style("font-size", "8")
	        .call(d3.axisLeft(y).tickSize(0));

	}

	// GROUPED BAR CHART
	function groupChart(element) {
		var element	= "#" + element;
		switch(element) {
			case "#chartStatsAge":
				var data	= JSON.parse(`<?php echo $stats->statsAge(); ?>`);
				var width	= 960;
				var height	= 260;
				var viewportSize = "0 0 960 260";
			break;
			case "#chartMarrAge":
				var data	= JSON.parse(`<?php echo $stats->statsMarrAge(); ?>`);
				var width	= 960;
				var height	= 260;
				var viewportSize = "0 0 960 260";
			break;
			default:
				var data = "";
		}

		var margin = {top: 10, right: 30, bottom: 50, left: 30},
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
				.attr("x", function(d) { return x1(d.key) + x1.bandwidth() / 2; })
				.attr("y", function(d) { return y(d.value) - 5; })
				.text(function(d) { return d.value; })

		// x-axis
		svg.append("g")
			.attr("transform", "translate(0," + h + ")")
			.style("font-size", "11")
			.call(d3.axisBottom(x0));

		// y-axis
		svg.append("g")
			.attr("y", y(y.ticks().pop()) + 1)
			.style("font-size", "11")
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
				.style("font-size", "11")
				.text(function(d) { return d; });

	}

	// PIE CHART
	function pieChart(element) {
		var element	= "#" + element;
		switch(element) {
			case "#chartSex":
				var data = JSON.parse(`<?php echo $stats->chartSex(); ?>`);
				var width	= 200;
				var height	= 200;
				var viewportSize = "0 0 200 200";
			break;
			case "#chartMortality":
				var data = JSON.parse(`<?php echo $stats->chartMortality(); ?>`);
				var width	= 200;
				var height	= 200;
				var viewportSize = "0 0 200 200";
			break;
			case "#chartIndisWithSources":
				var data = JSON.parse(`<?php echo $stats->chartIndisWithSources(); ?>`);
				var width	= 200;
				var height	= 200;
				var viewportSize = "0 0 200 200";
			break;
			case "#chartFamsWithSources":
				var data = JSON.parse(`<?php echo $stats->chartFamsWithSources(); ?>`);
				var width	= 200;
				var height	= 200;
				var viewportSize = "0 0 200 200";
			break;
			default:
				var data = "";
		}

		var color = d3.scaleOrdinal(d3.schemeCategory20);
		var margin = {top: 0, right: 30, bottom: 0, left: 10},
			w = width - margin.left - margin.right,
			h = height - margin.top - margin.bottom;
		var padding	= 50;
		var radius	= Math.min(width - padding, height - padding) / 2;

		var svg = d3.select(element)
			.append("svg")
				.attr("width", '100%')
				.attr("height", '100%')
				.attr("viewBox", viewportSize);

		var g = svg.append("g")
			.attr("transform", "translate(" + (w / 2) + "," + (h / 2 - 20) + ")");

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

		// Legend
		var legend = svg.append("g")
			.selectAll("g")
	  		    .data(pie(data))
			    .enter().append("g")
					  .attr("transform", function (d,i) { return "translate(" + (0 + i * 100) + "," + (h - 20) + ")";});
			legend.append("circle")
	  		    .attr("cx", 0)
	  		    .attr("cy", -4)
	  		    .attr("r", 5)
				.attr("fill", function(d, i) { return color(i); } )
				.attr("class", function(d){ return "bar-" +  d.data.color; }); // css over-rides fill color if d.data.color exists

			legend.append("text")
				.attr("x", 8)
				.attr("y", 0)
				.style("text-anchor", "start")
				.style("font-size", "8")
				.text(function(d){ return d.data.category + " (" + d.data.percent + ")"; });

	}

	// MAP CHART
	function mapChart(element) {
		var element	= "#" + element;
		switch(element) {
			case "#chartDistribution":
			var data = JSON.parse(`<?php echo $stats->chartDistribution(); ?>`);
			var width	= 400;
			var height	= 300;
			var viewportSize = "0 0 400 300";
			break;
			default:
				var data = "";
		}

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
			svg.append("text").attr("x", 20).attr("y", 280).text("<?php echo KT_I18N::translate('High population'); ?>").style("text-anchor", "start").style("font-size", "12")
			svg.append("text").attr("x", 170).attr("y", 280).text("<?php echo KT_I18N::translate('Low population'); ?>").style("text-anchor", "start").style("font-size", "12")
			svg.append("text").attr("x", 320).attr("y", 280).text("<?php echo KT_I18N::translate('Nobody at all'); ?>").style("text-anchor", "start").style("font-size", "12")

		});

	}

</script>
<?php
