<?php
  /**
   * View manager
   * 
   * @package    MVC_Framework
   * @subpackage System
   * @author     Max Weller <max.weller@teamwiki.net>
   **/
   
  $GLOBALS["globalViewVars"] = array();
  
  /**
   * Zuweisen einer global gültigen Ansichtsvariablen
   **/
  function set_view_var($name, $value) {
    trace("VIEW...set_var '$name' := ",$value);
    $GLOBALS["globalViewVars"][$name] = $value;
  }
  
  /**
   * Abfragen einer global gültigen Ansichtsvariablen
   **/
  function get_view_var($name) {
    if (isset($GLOBALS["globalViewVars"][$name])) return $GLOBALS["globalViewVars"][$name];
  }
  
  /**
   * Laden einer Ansicht mit unmittelbarer Anzeige
   * @param   $viewName   Name der Ansicht
   * @param   $data       Assoziatives Array mit lokal gültigen Ansichtsvariablen
   **/
  function load_view($viewName, $_DATA = null) {
    if ($_DATA == null) $_DATA = array();
    $fn = VIEW_DIR."/local/".$viewName.".php";
    if (!file_exists($fn))
      $fn = VIEW_DIR."/core/".$viewName.".php";
    if (!file_exists($fn)) {
      echo "<div class='alert alert-block alert-error'><h4>An error has occured: </h4>Viewloader was unable to load the requested view $viewName, because the file core/view/$viewName.php does not exist.</div>";
      return;
    }
    trace("VIEW...loading view",$viewName);
    extract($GLOBALS["globalViewVars"]);
    extract($_DATA);
    include ($fn);
  }
  
  /**
   * Laden einer Ansicht mit Rückgabe des erzeugten Quelltextes
   * @param   $viewName   Name der Ansicht
   * @param   $data       Assoziatives Array mit lokal gültigen Ansichtsvariablen
   **/
  function get_view($viewName, $data = null) {
    ob_start();
    load_view($viewName, $data);
    return ob_get_clean();
  }
  
?>
