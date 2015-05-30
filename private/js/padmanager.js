function PadManager(self_url, group) {
    
  $(document).on('click', ".open_popup", function(e) {
    window.open($(e.target).closest("a").attr('href'), "", "width=auto,height=auto,toolbar=no,status=no,resizable=yes");
    return false;
  });
  $(document).on('click', 'td.name', function(e) {
    location.href = $(e.target).closest("td").find("a").attr("href");
  });

  var currentEditPadID;
  $(document).on('click', ".pad_opts", function(e) {
    //alert(1)
    var $dlg = $("#modal_options");
    var $line = $(e.target).closest("[data-padID]");
    currentEditPadID = $line.attr("data-padID");
    var is_public = $line.attr("data-public");

    setAccessPublicToggle(is_public == "true");
    if (is_public == "true")
      $("#pad_shortlink").val($line.attr("data-shortlnk"));
    $("#pad_passw").val($line.attr("data-passw"));

    var shortName = currentEditPadID.substr(currentEditPadID.indexOf("$")+1);
    $dlg.find(".modal-title").html("Einstellungen zum Pad <b><u>"+shortName+"</u></b>");
    $dlg.find("#delete_dlg p").text("Pad "+shortName+" wirklich endgültig löschen?");

    $("#delete_dlg").hide();
    $dlg.modal("show");
  });

  window.onscroll = function() {
    if (window.scrollY > 110) $(".navbar").addClass("navbar-fixed-top");
    else $(".navbar").removeClass("navbar-fixed-top");
  }
  
  function setAccessPublicToggle(value) {
    $("#group_shortlink").toggle(value);

    $("#access_private").toggleClass("btn-primary", !value);
    $("#access_private").toggleClass("btn-default", value);

    $("#access_public").toggleClass("btn-primary", value);
    $("#access_public").toggleClass("btn-default", !value);
  }

  $("#passw_store").click(function() {
      if (!$("#pad_passw").val()) {
        var random = ("00000"+Math.floor(Math.random()*100000));
        random = random.substr(random.length-5);
      $("#pad_passw").val(random);
    }
    savePassword();
  });

  $("#passw_clear").click(function() {
    $("#pad_passw").val("");
    savePassword();
  });

  function savePassword() {
    $.post(self_url, { "pad_id" : currentEditPadID, "set_passw" : $("#pad_passw").val() },
    function(data) {
      loadPadList();
    }, "json");
  }

  $("#edit_shortlink").click(function() {
    var shortlnk = $("tr[data-padid='"+currentEditPadID+"']").attr("data-shortlnk");
    shortlnk = shortlnk.replace(/^.*\//, '');
    var p = prompt("Neuer Shortlink-Name:", shortlnk);
    if (p)
      setPadPublic("true", p);
  });

  
  $("#access_public").click(function() {
    setAccessPublicToggle(true);
    setPadPublic("true");
  });

  $("#access_private").click(function() {
    setAccessPublicToggle(false);
    setPadPublic("false");
  });

  function setPadPublic(value, shortlnk) {
    $.post(self_url, { "pad_id" : currentEditPadID, "set_public" : value, "shortlnk" : shortlnk },
    function(data) {
      if (value=="true")
        $("#pad_shortlink").val(data.shortlnk);
      else
        $("#pad_shortlink").val("(nur für öffentliche Pads verfügbar)");
      loadPadList();
    }, "json");
  }

  $("#delete_pad").click(function() {
    $("#delete_dlg").slideDown();$("#delete_password").val("");
  });
  $("#delete_yes").click(function() {
    $.post(self_url, { "pad_id" : currentEditPadID, "delete_this_pad" : $("#delete_password").val() },
    function(data) {
      loadPadList();
      $("#modal_options").modal("hide");
    }, "json");
  });

  $("#createSitzungPad").click(function() {
    if ( (new Date()).getDay() === 3 ) {
      return true;
    } else {
      var $form = $(this).closest('form');
      $('#modal_sitzungconfirm').modal({ backdrop: 'static', keyboard: false });
      return false;
    }
  });
  $('#confirm_createsitzungpad').click(function() {
    $('#createSitzungPad').closest('form').trigger('submit');
  });

  //--> Rename pad
  
  for(var k in padman_data.groups) $("#rename_group").append("<option value='"+padman_data.groups[k]+"'>"+k+"</option>");

  $(document).on('click', '.pad_rename', function(e) {
    var $dlg = $("#modal_rename");
    var $line = $(e.target).closest("[data-padID]");
    currentEditPadID = $line.attr("data-padID");
    
    var shortName = currentEditPadID.substr(currentEditPadID.indexOf("$")+1);
    $("#rename_pad").val(shortName);
    
    //$(".navbar-nav a").each(function() { $("#rename_group").append("<option>"+$(this).text()+"</option>"); });
    $("#rename_group").val(padman_data.groups[padman_data.activegroup]);
    $dlg.modal("show");
  });
  $("#confirm_rename").click(function() {
    renamePad(currentEditPadID, $("#rename_group").val()+'$'+$("#rename_pad").val());
  });
  function renamePad(oldID, newID) {
    $.post(self_url, { "pad_id" : oldID, "rename" : newID },
    function(data) {
      loadPadList();
      $("#modal_rename").modal("hide");
    }, "json");
  }
  
  $("#grouplist-navbar li").droppable({
    activeClass: "ui-state-hover",
    hoverClass: "ui-state-active",
    tolerance: "pointer",
    drop: function(event, ui) {
      var oldPadID = ui.helper.context.getAttribute("data-padid"), p = oldPadID.split(/\$/);
      var newID = padman_data['groups'][this.getAttribute("data-id")] + '$' + p[1];
      console.log(p, this.getAttribute("data-name"), this, newID);
      renamePad(oldPadID, newID);
    }
  });
  
  function loadPadList() {
    $("#pad_list").html("<div class='loader'></div>");
    $.get(self_url + "?group=" + group + "&list_pads=1", function(result) {
      $("#pad_list").html(result);

      $("#pad_list tr").draggable({ handle: ".pad_icon", revert: true, helper: "clone",
                                    cursorAt: { top: 15, left: 15 }, opacity: 0.7
                                  });
      
    }, "html");
  }
  
  this.loadPadList = loadPadList;
}
