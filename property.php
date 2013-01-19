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

<div class="countdown" style="display: none;">
  Next update in
  <div id="countdown"></div>
</div>

<div class="last-action">
  <button type="button" class="btn">View full log<br><code>Shortcut: L</code></button>
  <div id="last-action">
  </div>
  <br>
</div>

<div id="log" style="display: none;">
</div>

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
  <div class="button-panel" id="main-buttons" style="display: none;">
    <button type="button" id="btn-buy" class="btn btn-success btn-large span3" data-role="buy" data-style="success">Buy Property<br><code>Shortcut: B</code></button>
    <button type="button" id="btn-sell" class="btn btn-success btn-large span3" data-role="sell" data-style="success">Sell Property<br><code>Shortcut: S</code></button>
    <button type="button" id="btn-insurance" class="btn btn-info btn-large span3" data-role="insurance" data-style="info">Buy Insurance<br><code>Shortcut: I</code></button>
    <button type="button" id="btn-disaster" class="btn btn-danger btn-large span3" data-role="disaster" data-style="danger">Natural Disaster<br><code>Shortcut: D</code></button>
  </div>  
</div>

<div class="row">
  <div class="button-panel" id="header" style="display: none;">
    <div class="span9">
      <div class="alert" id="header-text">
        Natural Disaster
      </div>
    </div>
    <button type="button" id="btn-cancel" class="btn btn-inverse btn-large span3" data-role="cancel">Cancel<br><code>Shortcut: ESC</code></button>
  </div>
</div>

<div class="row">  
  <div class="span12">
    <input type="text" class="span12" style="margin-top: 10px; display: none;" id="camper-input" placeholder="Enter camper name and hit enter">
  </div>
</div>

<div class="row">
  <div class="span12">
    <table class="table table-bordered options" id="options-buy" style="display: none;">
      <tr>
        <th class="span4">Property Name</th>
        <th class="span3">Location</th>
        <th class="span3">Price</th>
        <th class="span2"><code>Shortcut: ENTER</code></th>
      </tr>
    </table>
    
    <table class="table table-bordered options" id="options-sell" style="display: none;">
      <tr>
        <th class="span3">Property Name</th>
        <th class="span3">Location</th>
        <th class="span2">Value</th>
        <th class="span2">Insurance</th>
        <th class="span2"><code>Shortcut: ENTER</code></th>
      </tr>
    </table>
    
    <table class="table table-bordered options" id="options-insurance" style="display: none;">
      <tr>
        <th>Property Name</th>
        <th>Location</th>
        <th class="icn-header">Fire</th>
        <th class="icn-header">Earthquake</th>
        <th class="icn-header">Tornado</th>
        <th class="icn-header">Godzilla</th>
        <th class="span2"><code>Shortcut: ENTER</code></th>
      </tr>
    </table>  
  </div>
</div>

<div class="row options" id="options-disaster" style="display: none;">
  <div class="disaster-panel">
    <button type="button" class="btn btn-warning btn-large span3" data-role="fire">Fire<img src="resources/icons/fire-on.png"><br><code>Shortcut: F</code></button>
    <button type="button" class="btn btn-primary btn-large span3" data-role="earthquake">Earthquake<img src="resources/icons/earthquake-on.png"><br><code>Shortcut: E</code></button>
    <button type="button" class="btn btn-info btn-large span3" data-role="tornado">Tornado<img src="resources/icons/tornado-on.png"><br><code>Shortcut: T</code></button>
    <button type="button" class="btn btn-success btn-large span3" data-role="Godzilla">Godzilla Attack<img src="resources/icons/godzilla-on.png"><br><code>Shortcut: G</code></button>
  </div>  
</div>

<script type="text/javascript">
var currentAction = false;
var activeTable = false;
var currentRow = 0;
var disasterReady = false;
var pageLoaded = false;

$(window).keydown(function(event) {
  if (!pageLoaded) {
    return;
  }
  if (activeTable && (event.keyCode == 40 || event.keyCode == 38)) {
    if (event.keyCode == 38) {
      newRow = currentRow - 1;
    } else if (event.keyCode == 40) {
      newRow = currentRow + 1;
    }
    var nextRow = $($("#options-"+currentAction+" .delete-me")[newRow]);
    
    if (nextRow.length > 0) {
      event.preventDefault();
      $("#options-"+currentAction+" .delete-me").removeClass("info");
      nextRow.addClass("info");
      
      var bottomOfRow = nextRow.offset().top + nextRow.height();
      var visibleArea = window.pageYOffset + window.innerHeight;
      if (bottomOfRow > visibleArea) {
        $(window).scrollTop(bottomOfRow - window.innerHeight + 5);
      }
      
      var topOfRow = nextRow.offset().top;
      if (topOfRow < window.pageYOffset) {
        $(window).scrollTop(topOfRow - 5);
      }
     
      currentRow = newRow;
      
    }
  } else if (activeTable && event.which == 13) {
    var selectedItem = $($("#options-"+currentAction+" .delete-me")[currentRow]);
    if (currentAction == "buy") {
      buy(selectedItem);
    } else if (currentAction == "sell") {
      if (!selectedItem.hasClass("destroyed")) {
        sell(selectedItem);
      }
    } else if (currentAction == "insurance") {
      if (selectedItem.find("button").hasClass("btn-primary")) {
        insurance(selectedItem);
      }
    }   
  }
});

$(window).keypress(function(event) {
  if (!pageLoaded) {
    return;
  }
  if (currentAction != "insurance" || !activeTable) {
    return;
  }
  
  var selectedItem = $($("#options-"+currentAction+" .delete-me")[currentRow]);
  var character = String.fromCharCode(event.which);
  var disaster = false;
  if (character == "1") {
    disaster = "fire";
  } else if (character == "2") {
    disaster = "earthquake";
  } else if (character == "3") {
    disaster = "tornado";
  } else if (character == "4") {
    disaster = "Godzilla";
  } else {
    return;
  }
  
  var checkbox = selectedItem.find(".checkbox-"+disaster);
  checkbox.prop("checked", !checkbox.prop("checked"));
  
  updateInsuranceCost(selectedItem);
});

function updateInsuranceCost(selectedItem) {
  var checkedCount = selectedItem.find("input[type=checkbox]:checked").length;
  if (checkedCount > 0) {
    selectedItem.find("button").addClass("btn-primary");
    selectedItem.find("button").removeAttr("disabled");
  } else {
    selectedItem.find("button").removeClass("btn-primary");
    selectedItem.find("button").attr("disabled", "disabled");
  }
  
  var cost = selectedItem.attr("data-cost");
  selectedItem.find(".insurance-cost").text($.number(cost * checkedCount));
  
}  

$(window).keyup(function(event) {
  if (!pageLoaded) {
    return;
  }
  var character = String.fromCharCode(event.which).toUpperCase();
  var tag = event.target.tagName.toLowerCase();
  if (event.keyCode == 27) {
    open("cancel");
  }
  if (tag != "body") {
    return;
  }
  if (disasterReady) {
    if (character == "F") {
      disaster("fire");
    } else if (character == "E") {
      disaster("earthquake");
    } else if (character == "T") {
      disaster("tornado");
    } else if (character == "G") {
      disaster("Godzilla");
    }
    return;
  }
  if (character == "L") {
    openLog();
  }
  if (currentAction) {
    return;
  }
  event.preventDefault();
  if (character == "B") {
    open("buy");
  } else if (character == "S") {
    open("sell");
  } else if (character == "I") {
    open("insurance");
  } else if (character == "D") {
    open("disaster");
  }
});

$(".button-panel button").click(function() {
  var action = $(this).attr("data-role");
  open(action);
});

$(".disaster-panel button").click(function() {
  var type = $(this).attr("data-role");
  disaster(type);
});

$(".last-action button").click(function() {
  openLog();
});

function open(action) {
  if (action == "cancel") {
    $(".button-panel").show();
    $("#header").hide();
    $("#header .alert").removeClass("alert-danger alert-success alert-info");
    $("#camper-input").blur();
    $("#camper-input").val("");
    $("#camper-input").hide();
    $("#camper-input").removeAttr("readonly");
    $(".options").hide();
    $(".delete-me").remove();
    currentAction = false;
    activeTable = false;
    currentRow = 0;
    disasterReady = false;
    return;
  }
  currentAction = action;
  $(".button-panel").hide();
  $("#header").show();
  $("#header-text").text($("#btn-"+action).contents()[0].nodeValue);
  $("#header .alert").addClass("alert-"+$("#btn-"+action).attr("data-style"));
  if (action == "disaster") {
    $("#camper-input").attr("placeholder", "Enter a location and hit enter");
    $("#camper-input").autocomplete("option", "source", locations);
  } else {
    $("#camper-input").attr("placeholder", "Enter a camper name and hit enter");
    $("#camper-input").autocomplete("option", "source", users)
  }
  $("#camper-input").show().focus();
  $("#camper-input").autocomplete("search", "");
}

var updateInterval;
var users;
var locations;
jQuery.get("ajax.php", {"action": "page-load"}, function(data) {
  users = data.users;
  locations = data.locations;
  updateInterval = data.config.updateInterval;
  setInterval(countdown, "1000");
  countdown();
  pageLoaded = true;
  $(".subtitle").text("Property Management Screen");
  $(".countdown").show();
  $("#main-buttons").show();
}, "json");

function countdown() {
  var time = Math.floor((new Date).getTime() / 1000);
  var timeToNextUpdate = Math.ceil((time+1)/updateInterval)*updateInterval - time;
  $("#countdown").text(timeToNextUpdate);
  if (timeToNextUpdate <= 5) {
    $("#countdown").addClass("destroyed");
  } else {
    $("#countdown").removeClass("destroyed");
  }
}

$("#camper-input").autocomplete({
  autoFocus: true,
  source: users,
  minLength: 0,
  delay: 0
});
var b;
$("#camper-input").on("autocompleteselect", function(event, ui) {
  var user = ui.item.value;
  $("#camper-input").blur();
  $("#camper-input").attr("readonly", "readonly");
  $("#options-"+currentAction).show();
  if (currentAction == "buy") {
    jQuery.get("ajax.php", {"action": "view-available"}, function(data) {
      var properties = data.properties;
      jQuery.each(properties, function(ID, details) {
        var row = $("<tr>", {id: ID, "class": "delete-me"});
        row.append("<td class='name'>"+details.Name+"</td>");
        row.append("<td>"+details.Location+"</td>");
        row.append("<td class='value' data-value='"+details.Value+"'>$"+$.number(details.Value)+"</td>");
        
        var button = $("<button class='btn btn-primary btn-block btn-small' type='button'>Buy Property</button>");
        button.click(function() {
          buy( $(this).parents("tr") )
        });
        
        row.append($("<td>").append(button));

        $("#options-buy").append(row);
      });
      
      if (properties.length === 0) {
        var row = $("<tr class='delete-me'><td colspan='4'>There are currently no properties available to purchase.</td></tr>");
        $("#options-buy").append(row);
      } else {
        activeTable = true;
        currentRow = 0;
        $($("#options-buy .delete-me")[0]).addClass("info");
      }
    }, "json");
  } else if (currentAction == "sell") {
    jQuery.get("ajax.php", {"action": "view-owned", "owner": user}, function(data) {
      var properties = data.properties;
      jQuery.each(properties, function(ID, details) {
        var row = $("<tr>", {id: ID, "class": "delete-me"});
        row.append("<td class='name'>"+details.Name+"</td>");
        if (details.Destroyed) {
          row.append("<td><strong>Destroyed by "+details.Destroyed+"</strong></td>");
        } else {
          row.append("<td>"+details.Location+"</td>");
        }
        row.append("<td class='value' data-value='"+details.Value+"'>$"+$.number(details.Value)+"</td>");
        
        var icons = [];
        
        var insuranceTypes = ["fire", "earthquake", "tornado", "Godzilla"];
        
        for (var i = 0; i < insuranceTypes.length; i++) {
          var disaster = insuranceTypes[i];
          var filename = disaster + "-off";
          if ($.inArray(disaster, details.Insurance) !== -1) {
            filename = disaster + "-on";
          }
          icons.push($("<img src='resources/icons/"+filename.toLowerCase()+".png'>"));
        }
        row.append($("<td>", {"class": "icons"}).append(icons));

        var button = false;
        if (details.Destroyed) {
          if ($.inArray(details.Destroyed, details.Insurance) !== -1) {
            button = $("<button class='btn btn-info btn-block btn-small' type='button'>Claim Insurance</button>");
            row.attr("data-sale-type", "insurance");
          } else if (details.Insurance.length === 0) {
            row.append("<td><strong>Not insured</strong></td>");
          } else {
            row.append("<td><strong>Insurance not valid</strong></td>");
          }
        } else {
          button = $("<button class='btn btn-warning btn-block btn-small' type='button'>Sell Property</button>");
          row.attr("data-sale-type", "sell");
        }
        
        if (button) {
          button.click(function() {
            sell( $(this).parents("tr") )
          });
          
          row.append($("<td>").append(button));;
        } else {
          row.addClass("destroyed error");
        }

        $("#options-sell").append(row);
      });
      
      if (properties.length === 0) {
        var row = $("<tr class='delete-me'><td colspan='5'>"+user+" doesn't own any properties.</td></tr>");
        $("#options-sell").append(row);
      } else {
        activeTable = true;
        currentRow = 0;
        $($("#options-sell .delete-me")[0]).addClass("info");
      }
    }, "json");
  } else if (currentAction == "insurance") {
    insuranceSelect(user);
  } else if (currentAction == "disaster") {
    disasterReady = true;
  }
});

function insuranceSelect(user) {
  jQuery.get("ajax.php", {"action": "view-owned", "owner": user, "insurable": "1"}, function(data) {
    var properties = data.properties;
    jQuery.each(properties, function(ID, details) {
      var cost = details.Value * 0.05;
      var row = $("<tr>", {id: ID, "class": "delete-me", "data-cost": cost});
      row.append("<td class='name'>"+details.Name+"</td>");
      row.append("<td>"+details.Location+"</td>");
      
      var icons = [];        
      var insuranceTypes = ["fire", "earthquake", "tornado", "Godzilla"];
      
      for (var i = 0; i < insuranceTypes.length; i++) {
        var disaster = insuranceTypes[i];
        var contents;
        var cell = $("<td>", {"class": "icons "+disaster});
        if ($.inArray(disaster, details.Insurance) !== -1) {
          contents = "<img src='resources/icons/"+disaster.toLowerCase()+"-on.png'> <span class='tick'>&#x2714;</span>";
        } else {
          contents = $("<label><input type='checkbox' data-disaster='"+disaster+"' class='checkbox-"+disaster+"'> <code>"+(i+1)+"</code>");
          cell.attr("data-can-buy", "true");
        }
        row.append(cell.append(contents));
      }
      
      row.append("<td><button class='btn btn-block btn-small' type='button' disabled='disabled'>Buy for $<span class='insurance-cost'>0</span></button></td>");

      row.find("input[type=checkbox]").click(function() {
        updateInsuranceCost($($(this).parents("tr")));
      });

      row.find("button").click(function() {
        insurance(row);
      });
      
      
      
      $("#options-insurance").append(row);
    });
    
    if (properties.length === 0) {
      var msg;
      if (data.skipped) {
        msg = user+" doesn't own any properties that haven't been destroyed.";
      } else {
        msg = user+" doesn't own any properties.";
      }
      var row = $("<tr class='delete-me'><td colspan='7'>"+msg+"</td></tr>");
      $("#options-insurance").append(row);
    } else {
      activeTable = true;
      currentRow = 0;
      $($("#options-insurance .delete-me")[0]).addClass("info");
    }
  }, "json");
}

function resetConsole() {
  $(".console").stop(true, true);
  $(".console").removeAttr("style");
  $(".console .title").text("The Game of Life");
  $(".console .subtitle").text("Property Management Screen");
}

var timeout = false;
function showMessage(type, subtitle) {
  resetConsole();
  clearTimeout(timeout);
  
  var title, colour;
  if (type == "success") {
    title = "Success!";
    colour = "green";
  } else {
    title = "Error!";
    colour = "red";
  }
  
  $(".console .title").text(title);
  $(".console .subtitle").text(subtitle);
  $(".console").animateHighlight(colour, 5000);
  timeout = setTimeout(resetConsole, 5000);
  $(window).scrollTop(0);
}

function lastAction(user, change) {
  $("#last-action").html("<strong>"+user+"</strong><br><strong>"+change+"</strong>");
}

function buy(row) {
  var id = row.attr("id");
  var user = $("#camper-input").val();
  var name = row.find(".name").text();
  var displayValue = row.find(".value").text();
  var realValue = row.find(".value").attr("data-value");
  if (confirm("Confirm purchase of "+name+" for "+user+" for "+displayValue+"?")) {
    jQuery.post("ajax.php?action=buy", {"id": id, "user": user, "value": realValue}, function(data) {
      if (data.result == "success") {
        showMessage("success", name + " purchased by " + user + ".");
        lastAction(user, "-"+displayValue);
        if (confirm("Would you like to purchase insurance for your new house?")) {
          open("cancel");
          open("insurance");
          $("#camper-input").val(user);
          $("#camper-input").blur();
          $("#camper-input").attr("readonly", "readonly");
          $("#options-insurance").show();
          insuranceSelect(user);
        } else {
          open("cancel");
        }
      } else if (data.result == "error") {
        showMessage("error", data.error);
      }
    }, "json");
  }
}

function sell(row) {
  var id = row.attr("id");
  var user = $("#camper-input").val();
  var name = row.find(".name").text();
  var displayValue = row.find(".value").text();
  var realValue = row.find(".value").attr("data-value");
  
  var saleType = row.attr("data-sale-type");
  var confirmText;
  if (saleType == "insurance") {
    confirmText = user+" will receive a "+displayValue+" insurance payout for "+name+"."
  } else {
    confirmText = "Confirm sale of "+name+" for "+user+" for "+displayValue+"?"
  }
  if (confirm(confirmText)) {
    jQuery.post("ajax.php?action=sell", {"id": id, "user": user, "value": realValue}, function(data) {
      if (data.result == "success") {
        if (saleType == "insurance") {
          showMessage("success", user + " claimed " + name + " on insurance.");
        } else {
          showMessage("success", name + " sold by " + user + ".");
        }
        lastAction(user, "+"+displayValue);
        open("cancel");
      } else if (data.result == "error") {
        showMessage("error", data.error);
      }
    }, "json");
  }
}

function insurance(row) {
  var id = row.attr("id");
  var user = $("#camper-input").val();
  var name = row.find(".name").text();
  var displayCost = "$"+row.find(".insurance-cost").text();
  var checked = row.find("input[type=checkbox]:checked");
  var realCost = row.attr("data-cost") * checked.length;
  
  var types = [];
  for (var i = 0; i < checked.length; i++) {
    types.push($(checked[i]).attr("data-disaster"));
  }
  if (confirm("Confirm purchase of insurance for " + user + "'s property " + name + " for " + displayCost +"?")) {
    jQuery.post("ajax.php?action=insurance", {"id": id, "user": user, "types": types, "cost": realCost}, function(data) {
      if (data.result == "success") {
        showMessage("success", "Insurance purchased for " + name + ".");
        for (var i = 0; i < types.length; i++) {
          var type = types[i];
          var cell = row.find("."+type);
          cell.removeAttr("data-can-buy");
          cell.html("<img src='resources/icons/"+type.toLowerCase()+"-on.png'> <span class='tick'>&#x2714;</span>");
        }
        updateInsuranceCost(row);
        lastAction(user, "-"+displayCost);
      } else if (data.result == "error") {
        showMessage("error", data.error);
      }
    }, "json");
  }
}

function disaster(type) {
  var location = $("#camper-input").val();
  var flavourText;
  if (type == "fire") {
    flavourText = "Are you sure that you want to release the ravenous fires of the Australian bushland upon the residents of "+location+"?";
  } else if (type == "earthquake") {
    flavourText = "Are you sure that you want to smash some tectonic plates together and level the houses in "+location+"?";
  } else if (type == "tornado") {
    flavourText = "Are you sure that you want to send violent columns of spinning wind to decimate the houses in "+location+"?";
  } else if (type == "Godzilla") {
    flavourText = "Are you sure that you want to release Godzilla from his cage and let him stomp around "+location+"?";
  }
  
  if (confirm(flavourText)) {
    jQuery.post("ajax.php?action=disaster", {"location": location, "disaster": type}, function(data) {
      if (data.result == "success") {
        showMessage("success", "All properties in "+location+" destroyed by "+type+".");
        open("cancel");
      } else if (data.result == "error") {
        showMessage("error", data.error);
      }
    }, "json");
  }
  
}

function openLog() {
  jQuery.get("ajax.php", {"action": "view-log"}, function(data) {
    $("#log").text("");
    $.each(data.log, function(key, value) {
      var time = new Date(value.Time*1000);
      var timeStr = time.toLocaleTimeString();
      $("#log").append("<div>"+timeStr+": "+value.Message+"</div>");
    });
    $("#log").toggle();
  }, "json");
}

$.fn.animateHighlight = function(highlightColor, duration) {
    var highlightBg = highlightColor || "#FFFF9C";
    var animateMs = duration || 1500;
    var originalBg = this.css("backgroundColor");
    this.stop().css("background-color", highlightBg).animate({backgroundColor: originalBg}, animateMs);
};
</script>

</div> <!-- container -->
</body>
</html>