<!DOCTYPE html>
<html>
<head>
<title>The Game of Life</title>
</head>

<style type="text/css">
a.btn-main {
  font-size: 30px;
  line-height: 27px;
  height: 150px;
  display: block;
  padding-top: 43px;
}
</style>

<link href="resources/style.css" rel="stylesheet">
<link href="resources/bootstrap.min.css" rel="stylesheet">
<script src="resources/jquery-1.8.3.min.js"></script>

<body>
<div class="container">

<div class="row">
  <div class="span12">
    <div class="console well">
      <div class="title">The Game of Life</div>
    </div>
  </div>
</div>

<div class="row" class="main-buttons">
  <div class="span6">
    <a href="property.php" class="btn btn-large btn-block btn-main">Property Management<br><code>Shortcut: P</code></a>
    
    <a href="property-monitor.php" class="btn btn-large btn-block">Property Monitor (for campers)</a>
    <a href="property-overview.php" class="btn btn-large btn-block">Property Overview (for leaders)</a>    
  </div>
  <div class="span6">
    <a href="lottery.php" class="btn btn-large btn-block btn-main">The Lottery<br><code>Shortcut: L</code></a>
  </div>
</div>

<div class="row" style="margin-top: 50px;">
  <div class="span12">
    <a href="_reset.php" class="btn btn-large btn-block btn-danger" id="reset">Apocalypse event: reset all properties and the lottery</a>
  </div>
</div>

<script type="text/javascript">
$(window).keyup(function(event) {
  var character = String.fromCharCode(event.which).toUpperCase();
  if (character == "P") {
    document.location = "property.php";
  } else if (character == "L") {
    document.location = "lottery.php";
  }
});
$("#reset").click(function(event) {
  if (!confirm("Are you sure you want to reset everything to the default state?")) {
    event.preventDefault();  
  }
});
</script>
</body>
</html>