function Add()
{
  document.getElementById("reservations_filter").value =

    '['+document.getElementById("FirstNo").value+' '+
    document.getElementById("SecondNo").value+' '+
    document.getElementById("ThirdNo").value+']' +
    document.getElementById("reservations_filter").value;
}

function helpFilters()
{
  var Output  = "<div style='border:solid 1px #FFF141; padding: 3px; width:98%; ";
      Output += "background:#FFFDFD;'>";
      Output += "<p><small>Filters can be set for Rooms and Offers; <a target='_blank' href='http://feryaz.de/dokumentation/filters/'>More</a></small></p>";
      Output += "<p><code>[price]</code> <small>Set Price for specific Time Period</small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small>dd.mm.yyyy-dd.mm.yyyy</small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small>Price in the selected Period</small></p>";
      Output += "<p><code>[stay]</code> <small>Set Discount for longer Stays</small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small>minimum Stay in Days</small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small>Discount (XX or XX%)</small></p>";
      Output += "<p><code>[loyal]</code> <small>Set Discount for recurring Guests</small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small>Visits to have for Discount</small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small>Discount (XX or XX%)</small></p>";
      Output += "<p><code>[avail]</code> <small>Set unavailable for period of Time</small></p><p style='margin-left:15px;'><code style='background-color:#FF9393'>[condition]</code> <small>dd.mm.yyyy-dd.mm.yyyy</small></p><p style='margin-left:15px;'><code style='background-color:#AAFFC5'>[value]</code> <small>empty</small></p>";
      Output += "</div><br>";
  document.getElementById("Text").innerHTML = Output;
}