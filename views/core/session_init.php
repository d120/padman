
<h2>Eile mit Weile</h2>
<p id="text1">Etherpad-Lite-Sitzung wird initialisiert... Dieser Vorgang kann einige Sekunden dauern.</p>
<div class='loader'></div>
<script>
$.post("<?= SELF_URL ?>", "create_sessions=true", function(e) {
  if (e.success === true)
    location=location;
  else {
    $("#text1").html("FEHLER: Etherpad ist zur Zeit nicht erreichbar. Diese Seite lädt sich in 30 Sekunden automatisch neu.");
    setTimeout(function() {
      location.reload();
    });
  }
});
</script>

