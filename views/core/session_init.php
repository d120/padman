<div class="with-script" style="display:none">
<h2>Eile mit Weile</h2>
<p id="text1">Etherpad-Lite-Sitzung wird initialisiert... Dieser Vorgang kann einige Sekunden dauern.</p>
<div class='loader'></div>
</div>

<noscript>
<h2>Hinweis</h2>
<p>Bitte Javascript aktivieren!</p>
</noscript>

<script>
$(".with-script").show();
$.ajax({ url: "<?= SELF_URL ?>", data: "create_sessions=true", method: "POST",
  success: function(e,f,g) {
    if (e.success === true)
      location=location;
    else {
      $("#text1").html("FEHLER: Etherpad ist zur Zeit nicht erreichbar. Es wird in <span id=cd>30</span> Sekunden automatisch erneut versucht.").css("color","red");
      setTimeout(function() {
        location.reload();
      },30000);
      setTimeout(countdown, 1000);
    }
  },
  error: function(a,b,c) {
    $("#text1").html("FEHLER: Etherpad ist zur Zeit nicht erreichbar. Es wird in <span id=cd>30</span> Sekunden automatisch erneut versucht.<br><pre></pre>").css("color","red");
    $("pre").text(a.responseText);
    setTimeout(function() {
      location.reload();
    },30000);
    setTimeout(countdown, 1000);
  },
  dataType: "json"
});
function countdown(){
  var v=parseInt($("#cd").text()) - 1;
  $("#cd").text(v);
  if(v>0) setTimeout(countdown,1000);
}
</script>

