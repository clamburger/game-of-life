<!DOCTYPE html>
<html>
<head>
<title>The Game of Life</title>
</head>
<style type="text/css">
.title {
  font-weight: bold;
  font-size: 60px;
  line-height: 60px;
}
.subtitle {
  font-size: 35px;
  margin-bottom: 5px;
  line-height: 35px;
}
#properties .change, #properties .value, #properties .details {
  text-align: center;
}
td {
  padding: 3px;
}
.console {
  background-color: lime;
  text-align: center;
  margin: 10px auto;
  font-size: 25px;
  border: 2px solid black;
  font-family: Consolas, "Liberation Mono", Courier, monospace;
}
#loading {
  background-color: black;
  color: white;
  padding: 5px;
}
.center {
  text-align: center;
}
.destroyed {
  text-decoration: line-through;
}
td.happy {
  font-weight: bold;
  color: green;
}
td.sad {
  font-weight: bold;
  color: red;
}
</style>
<link href="resources/bootstrap.min.css" rel="stylesheet">
<script src="resources/jquery-1.8.3.min.js"></script>
<script src="resources/jquery-ui-1.9.2.custom.min.js"></script>
<script src="resources/jquery.number.min.js"></script>
<body>
<div class="container">

<div class="row">
  <div class="console span12">
  <div class="title">The Game of Life</div>
  <div class="subtitle">Property Status Screen</div>
  <div id="loading">Loading data...</div>
  </div>
</div>

<table id="properties" class="table table-bordered" style="display: none;">
<thead>
<tr>
  <th>Property Name</th>
  <th>Location</th>
  <th>Owner</th>
  <th style="width: 150px;">Current Value</th>
  <th style="width: 80px;">Change</th>
</tr>
</thead>
<tbody>
</tbody>
</table>

</div>

<script type="text/javascript">
var updateInterval;
var updateIn;
jQuery.get("ajax.php", {"action": "get-config"}, function(data) {
  updateInterval = data.config.updateInterval;
  updateIn = updateInterval;
}, "json");

jQuery.get("ajax.php", {"action": "view"}, function(data) {
  var properties = data.properties;
  jQuery.each(properties, function(key, value) {
    addProperty(key, value);
  });
  $("#properties").fadeIn("slow");
  setInterval(countdown, 1000);
}, "json");
  
function countdown() {
  updateIn -= 1;
  if (updateIn <= 0) {
    updateProperties();
    updateIn = updateInterval;
  }
  
  $("#loading").text("Updating in "+updateIn+" seconds");
  
}

function addProperty(ID, details) {
  var row = $("<tr>", {id: ID});
  if (details.Destroyed) {
    row.addClass("destroyed error");
  }
  row.append("<td>"+details.Name+"</td>");
  row.append("<td>"+details.Location+"</td>");
  if (details.Owner) {
    row.append("<td>"+details.Owner+"</td>");
  } else {
    row.append("<td style='color: grey;'>The Bank</td>");
  }
  //row.append("<td class='details'>"+details.Bed+" <img src='bed.png'> &nbsp; "+details.Bath+" <img src='bath.png'> &nbsp; " + details.Car+" <img src='car.png'></td>");
  row.append("<td class='value' data-value='"+details.Value+"'>$"+$.number(details.Value)+"</td>");
  row.append("<td class='change'>0%</td>");

  $("#properties tbody").append(row);
}
  
function updateProperties() {
  jQuery.get("ajax.php", {"action": "view"}, function(data) {
    var properties = data.properties;
    jQuery.each(properties, function(ID, value) {
    
      // Get the appropriate columns that we need to change
      var property = $("#"+ID);
      var valueColumn = property.find(".value");
      var changeColumn = property.find(".change");
      
      // Grab the old value and update the new one
      var oldValue = valueColumn.attr("data-value");
      valueColumn.text("$"+$.number(value.Value));
      valueColumn.attr("data-value", value.Value);
      
      // Update the change column
      changeColumn.removeClass("happy sad");
      var diff = Math.abs(oldValue - value.Value);
      var pcDiff = Math.round((diff / oldValue) * 100);
      if (value.Value < oldValue) {
        changeColumn.text("-" + pcDiff + "%");
        changeColumn.addClass("sad");
        valueColumn.animateHighlight("salmon", 2000);
      } else if (value.Value > oldValue) {
        changeColumn.text("+" + pcDiff + "%");
        changeColumn.addClass("happy");
        valueColumn.animateHighlight("springgreen", 2000);
      } else {
        changeColumn.text("0%");
      }
      
    });
  }, "json");
};

$.fn.animateHighlight = function(highlightColor, duration) {
    var highlightBg = highlightColor || "#FFFF9C";
    var animateMs = duration || 1500;
    var originalBg = this.css("backgroundColor");
    this.stop().css("background-color", highlightBg).animate({backgroundColor: originalBg}, animateMs);
};

</script>

</body>
</html>