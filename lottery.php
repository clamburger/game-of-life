<!DOCTYPE html>
<html>
<head>
<title>The Game of Life</title>
</head>

<style type="text/css">
#number-input {
  font-size: 30px;
  height: 40px;
  line-height: normal;
  font-family: Arial;
}
#ticket-price {
  font-size: 30px;
  height: 35px;
  width: 113px;
  margin-bottom: 0px;
  font-family: Arial;
}
.alert, .alert code {
  font-size: 20px;
  line-height: 35px;
}
#instructions {
  padding-top: 3px;
  padding-bottom: 3px;
}
.alert code {
  padding: 3px;
}
.thumbnail {
  text-align: center;
}
.thumbnails li {
  width: 172px;
}
.thumbnails {
  margin-top: 10px;
}
#go {
  height: 78px;
}
#total-cost {
  position: absolute;
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
      <div class="subtitle">The Lottery</div>
    </div>
    
    <div class="console well alert-success" id="winner" style="padding-bottom: 30px; display: none;">
      <div class="subtitle"><strong>Douglas "Long Name" McCatchpole</strong> has won the lottery!</div>
      <a class="close" href="#">&times; close message</a>
    </div>
  </div>
</div>


<div class="row">
  <div class="span9">
    <div class="alert alert-info alert-block" id="instructions">
      <strong>Instructions:</strong> enter a name and hit <code>ENTER</code>, then enter the number of tickets and hit <code>ENTER</code>. To cancel, press <code>ESCAPE</code>.
    </div>
  </div>
  <div class="span3">
    <button class="btn btn-large btn-block" type="button" id="go" disabled>Run the lottery!</button>
  </div>
</div>

<div class="row">  
  <div class="span3">
    <div class="well well-small control-group" style="padding: 5px; margin-top: -3px;">
      <div style="float: left; margin-right: 20px; margin-top: 4px;">Ticket<br>price:</div>
      <div style="font-size: 30px; font-family: Arial; float: left; margin-right: 5px; margin-top: 12px;">$ </div><input type="number" step="any" min="1" value="10" id="ticket-price">
    </div>
  </div>
  <div class="span6">
    <input type="text" class="span6" id="camper-input" placeholder="Camper's name">
  </div>
  <div class="span3" style="height: 80px;">
    <form style="margin: 0px; display: inline;"><input type="number" class="span3" id="number-input" step="1" min="1" placeholder="Tickets" disabled="disabled" required></form>
    <div id="current-cost" style="display: none;">Total cost: $0</div>
  </div>
</div>

<ul class="thumbnails">

</ul>

<script type="text/javascript">
var users;
var ticketHolders = {};
jQuery.get("ajax.php", {"action": "load-lottery"}, function(data) {
  users = data.users;
  ticketHolders = data.lottery;
  for (var user in ticketHolders) {
    var id = user.replace(" ", "-");
    var tickets = ticketHolders[user];
    var plural = "ticket";
    if (tickets > 1) {
      plural = "tickets";
    }
    $("ul.thumbnails").append("<li id='"+id+"'><div class='thumbnail'><p><strong class='name'>"+user+"</strong></p><p class='tickets' data-tickets='"+tickets+"'>"+$.number(tickets)+" "+plural+"</div></li>");
    $("#go").removeAttr("disabled");
    $("#go").addClass("btn-primary");
  }
  
  $("#camper-input").autocomplete({
    source: users,
    autoFocus: true,
    minLength: 0,
    delay: 0
  });
}, "json");

$(document).ready(function() {
  $("#camper-input").focus();
});

var user;
$("#camper-input").on("autocompleteselect", function(event, ui) {
  user = ui.item.value;
  $("#camper-input").attr("readonly", "readonly");
  $("#number-input").removeAttr("disabled");
  $("#number-input").focus();
  $("#ticket-price").attr("disabled", "disabled");
  $("#current-cost").show();
});

function updateCost() {
  if ($("#current-cost:visible").length > 0) {
    var val = $("#number-input").val();
    var newCost;
    if (!$.isNumeric(val) || val < 0) {
      newCost = 0;
    } else {
      newCost = $("#ticket-price").val() * val;
    }
    $("#current-cost").text("Total cost: $"+$.number(newCost)); 
  }
}

setInterval(updateCost, 200);

$(window).keyup(function(event) {
  if (event.keyCode == 27) {
    cancel();
  }
});

function cancel() {
  $("#camper-input").removeAttr("readonly");
  $("#camper-input").focus();
  $("#camper-input").val("");
  $("#number-input").val("");
  $("#number-input").attr("disabled", "disabled");
  $("#ticket-price").removeAttr("disabled");
  $("#current-cost").hide();
  user = undefined;
}

$("#ticket-price").change(function(event) {
  var val = $(event.target).val();
  var box = $($(event.target).parent());
  var valid = true;
  if (val == "" || val < 0) {
    valid = false;
  }
  if (!valid) {
    box.addClass("error");
    $("#camper-input").attr("disabled", "disabled");
  } else {
    box.removeClass("error");
    $("#camper-input").removeAttr("disabled");
  }
});

$("form").submit(function(event) {
  event.preventDefault();
  var tickets = parseInt($("#number-input").val());
  var plural = "ticket";
  var id = user.replace(" ", "-");
  var cost = $("#ticket-price").val() * tickets;
  if (tickets > 1) {
    plural = "tickets";
  }
  if (confirm("Purchase "+tickets+" "+plural+" for "+user+" for a total of $"+$.number(cost)+"?")) {
    if ($("#"+id).length == 0) {
      $("ul.thumbnails").append("<li id='"+id+"'><div class='thumbnail'><p><strong class='name'>"+user+"</strong></p><p class='tickets' data-tickets='"+tickets+"'>"+$.number(tickets)+" "+plural+"</div></li>");
      ticketHolders[user] = tickets;
    } else {
      var box = $("#"+id);
      var newCount = ticketHolders[user] + tickets;
      $(box.find(".tickets")).attr("data-tickets", newCount);
      $(box.find(".tickets")).text($.number(newCount) + " tickets");
      ticketHolders[user] = newCount;
    }
    jQuery.post("ajax.php?action=lottery-add", {"user": user, "tickets": tickets});
    cancel();
    $("#go").removeAttr("disabled");
    $("#go").addClass("btn-primary");
  }
});

$("#go").click(function(event) {
  if (confirm("Are you sure that you want to run the lottery?")) {
  
    var chances = [];
    for (var property in ticketHolders) {
      var chance = ticketHolders[property];
      if (chance == 1) {
        chance = 0.25;
      } else {
        chance = Math.log(chance) / Math.LN10;
      }
      chance = Math.round(chance * 1000, 3);
      for (var i = 1; i <= chance; i++) {
        chances.push(property);
      }
    }
    
    var winner = chances[Math.floor(Math.random() * chances.length)];
    $("#winner").show();
    $("#winner .subtitle").html("<strong>"+winner+"</strong> has won the lottery!");
    $("#go").attr("disabled", "disabled");
    $("#go").removeClass("btn-primary");
    $("ul.thumbnails").empty();
    ticketHolders = {};
    jQuery.ajax("ajax.php?action=lottery-reset");
  }
});

$("#winner .close").click(function(event) {
  event.preventDefault();
  $(this).parent().hide();
});
</script>
</body>
</html>