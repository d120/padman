
function PadManager(self_url, group) {
	
	$(document).on('click', ".open_popup", function(e) {
	  window.open($(e.target).closest("a").attr('href'), "", "width=auto,height=auto,toolbar=no,status=no,resizable=yes");
	  return false;
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
	  else
	    $("#pad_shortlink").val("(nur für öffentliche Pads verfügbar)");
	  $("#pad_passw").val($line.attr("data-passw"));
  
	  var shortName = currentEditPadID.substr(currentEditPadID.indexOf("$")+1);
	  $dlg.find(".modal-title").html("Einstellungen zum Pad <b><u>"+shortName+"</u></b>");
	  $dlg.find("#delete_dlg p").text("Pad "+shortName+" wirklich endgültig löschen?");
  
	  $("#delete_dlg").hide();
	  $dlg.modal("show");
	});

	function setAccessPublicToggle(value) {
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
	})

	function savePassword() {
	  $.post(self_url, { "pad_id" : currentEditPadID, "set_passw" : $("#pad_passw").val() },
	  function(data) {
	    loadPadList();
	  }, "json");
	}

	$("#access_public").click(function() {
	  setAccessPublicToggle(true);
	  setPadPublic("true");
	});

	$("#access_private").click(function() {
	  setAccessPublicToggle(false);
	  setPadPublic("false");
	});

	function setPadPublic(value) {
	  $.post(self_url, { "pad_id" : currentEditPadID, "set_public" : value },
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
	})
	$("#delete_yes").click(function() {
	  $.post(self_url, { "pad_id" : currentEditPadID, "delete_this_pad" : $("#delete_password").val() },
	  function(data) {
	    loadPadList();
	    $("#modal_options").modal("hide");
	  }, "json");
	})

 	$("#createSitzungPad").click(function() {
      var $form = $(this).closest('form'); 
      e.preventDefault();
      $('#modal_sitzungsconfirm').modal({ backdrop: 'static', keyboard: false })
          .one('click', '#confirm', function() {
              $form.trigger('submit');
          });
      return false;
    });

	function loadPadList() {
	  $("#pad_list").html("<div class='loader'></div>");
	  $.get(self_url + "?group=" + group + "&list_pads=1", function(result) {
	    $("#pad_list").html(result);
	  }, "html");
	}
	
	this.loadPadList = loadPadList;
	
}
