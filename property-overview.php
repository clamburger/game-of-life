<!DOCTYPE html>
<html>
<head>
<title>The Game of Life</title>
</head>
<style type="text/css">
body {
  overflow-y: scroll;
}
</style>
<link href="resources/style.css" rel="stylesheet">
<link href="resources/bootstrap.min.css" rel="stylesheet">
<link href="resources/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="resources/jquery-1.8.3.min.js"></script>
<script src="resources/jquery-ui-1.9.2.custom.min.js"></script>
<script src="resources/jquery.number.min.js"></script>
<body>

<div class="container">

<div class="row">
  <div class="span12">
    <div class="console well">
      <div class="title">The Game of Life</div>
      <div class="subtitle">Loading...</div>
    </div>
  </div>
</div>

<div class="row">
  <div class="span12">
    <table class="table table-bordered options" id="property-list">
      <tr>
        <th>Property Name</th>
        <th>Location</th>
        <th>Value</th>
        <th style="width: 120px;">Insurance</th>
        <th>Owner</th>
      </tr>
    </table>  
  </div>
</div>

<script type="text/javascript">
jQuery.get("ajax.php", {"action": "view-overview"}, function(data) {
  $(".subtitle").text("Property Overview Screen");
  jQuery.each(data.properties, function(ID, details) {
    var row = $("<tr>", {id: ID});
    if (details.Destroyed) {
      row.addClass("destroyed error");
    }
    row.append("<td>"+details.Name+"</td>");
    row.append("<td>"+details.Location+"</td>");
    row.append("<td class='value' data-value='"+details.Value+"'>$"+$.number(details.Value)+"</td>");
    var insuranceTypes = ["fire", "earthquake", "tornado", "Godzilla"];

    var icons = [];
        
    for (var i = 0; i < insuranceTypes.length; i++) {
      var disaster = insuranceTypes[i];
      var filename = disaster + "-off";
      if ($.inArray(disaster, details.Insurance) !== -1) {
        filename = disaster + "-on";
      }
      icons.push($("<img src='resources/icons/"+filename.toLowerCase()+".png'>"));
    }
    row.append($("<td>", {"class": "icons"}).append(icons));

    if (details.Owner) {
      row.append("<td>"+details.Owner+"</td>");
    } else {
      row.append("<td style='color: grey;'>The Bank</td>");
    }

    $("#property-list").append(row);
  });
}, "json");
</script>

</div> <!-- container -->
</body>
</html>