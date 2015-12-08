function PadManager() {
  var self = this;
  var selected_tag = "";
  var groupInfo;
  
  function API_Get(method, params, callback) {
    $.post(SELF_URL + "?api=" + method + "&" + params, callback, "json");
  }
  function API_Post(data, callback) {
    $.post(SELF_URL, data, callback, "json");
  }
  
  $('#main_nav [data-id]').click(function() {
    if (!$('#pad_list').length) return false;
    var group = this.getAttribute("data-id");
    self.loadGroup(group);
    window.history.pushState('', 'Pad: '+group, SELF_URL + group);
    return false;
  });
  
  this.loadGroup = function(groupName) {
    for(var i in padman_data.groups)
      if (padman_data.groups[i].group_mapper == groupName) groupInfo = padman_data.groups[i];
    selected_tag = "";
    $('#main_nav li.active').removeClass('active'); $('#main_nav li[data-id="'+groupName+'"]').addClass('active').closest('li.dropdown').addClass('active');
    updateTags();
    loadPadList();
  }

  $(document).on('click', ".open_popup", function(e) {
    window.open($(e.target).closest("a").attr('href'), "", "width=auto,height=auto,toolbar=no,status=no,resizable=yes");
    return false;
  });
  $(document).on('click', 'td.name', function(e) {
    location.href = $(e.target).closest("td").find("a").attr("href");
  });

  var currentEditPadID, currentEditPad;
  $(document).on('click', ".pad_opts", function(e) {
    //alert(1)
    var $line = $(e.target).closest("[data-padID]");
    self.editPad($line);
  });

  this.editPad = function($line) {
    var $dlg = $("#modal_options");
    $("#delete_dlg").hide();
    $dlg.modal("show");
    currentEditPadID = $line.attr("data-padID");
    API_Get("pad_info", "pad_id=" + currentEditPadID, function(result) {
      var pad = result[0];
      currentEditPad = pad;
      
      // 1=public, 0=private
      setAccessPublicToggle(pad.access_level == 1);
      if (pad.access_level == 1)
        $("#pad_shortlink").val(SHORTLNK_PREFIX + pad.shortlink);
      $("#pad_passw").val(pad.password);
      $("#pad_tags").val(pad.tags);
      
      $dlg.find(".modal-title").html("Einstellungen zum Pad <b><u>"+pad.pad_name+"</u></b>");
      $dlg.find("#delete_dlg p").text("Pad "+pad.pad_name+" wirklich endgültig löschen?");
      
    });
  }

  window.onscroll = function() {
    if (window.scrollY > 120 && window.innerWidth > 768) $(".navbar").addClass("navbar-fixed-top");
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
    API_Post({ "pad_id" : currentEditPadID, "set_passw" : $("#pad_passw").val() },
    function(data) {
      loadPadList();
    });
  }

  $("#pad_tags").change(function() {
    API_Post({ "pad_id" : currentEditPadID, "set_tags" : $("#pad_tags").val() });
  });
  
  $("#edit_shortlink").click(function() {
    var shortlnk = currentEditPad.shortlink || '';
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
    API_Post({ "pad_id" : currentEditPadID, "set_public" : value, "shortlnk" : shortlnk },
    function(data) {
      if (value=="true")
        $("#pad_shortlink").val(data.shortlnk);
      else
        $("#pad_shortlink").val("(nur für öffentliche Pads verfügbar)");
      loadPadList();
    });
  }

  $("#delete_pad").click(function() {
    $("#delete_dlg").slideDown();$("#delete_password").val("");
  });
  $("#delete_yes").click(function() {
    API_Post({ "pad_id" : currentEditPadID, "delete_this_pad" : $("#delete_password").val() },
    function(data) {
      loadPadList();
      $("#modal_options").modal("hide");
    });
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
  if (padman_data)
      for(var k in padman_data.groups) $("#rename_group").append("<option value='"+padman_data.groups[k].group_id+"'>"+padman_data.groups[k].group_mapper+"</option>");

  $(document).on('click', '.pad_rename', function(e) {
    var $dlg = $("#modal_rename");
    $(".rename", $dlg).show(); $(".pleasewait", $dlg).hide(); $(".modal-footer button",$dlg).attr("disabled",false);
    var $line = $(e.target).closest("[data-padID]");
    currentEditPadID = $line.attr("data-padID");
    
    var shortName = currentEditPadID.substr(currentEditPadID.indexOf("$")+1);
    $("#rename_pad").val(shortName);
    
    //$(".navbar-nav a").each(function() { $("#rename_group").append("<option>"+$(this).text()+"</option>"); });
    $("#rename_group").val(groupInfo.group_id);
    $dlg.modal("show");
  });
  $("#confirm_rename").click(function() {
    renamePad(currentEditPadID, $("#rename_group").val()+'$'+$("#rename_pad").val());
  });
  function renamePad(oldID, newID) {
    $("#modal_rename .rename").hide(); $("#modal_rename .pleasewait").show(); $("#modal_rename .modal-footer button").attr("disabled",true);
    var x=0,progress=setInterval(function (){ $("#modal_rename .progress-bar").css("width",x+"%"); x++; }, 100);
    API_Post({ "pad_id" : oldID, "rename" : newID },
    function(data) {
      if (data.msg) alert(data.msg);
      clearInterval(progress);
      loadPadList();
      $("#modal_rename").modal("hide");
    });
  }
  
  $("#grouplist-navbar li").droppable({
    activeClass: "ui-state-hover",
    hoverClass: "ui-state-active",
    tolerance: "pointer",
    drop: function(event, ui) {
      $("#modal_rename").modal("show");
      var oldPadID = ui.helper.context.getAttribute("data-padid"), p = oldPadID.split(/\$/);
      var newID = getGroupByMapper(this.getAttribute("data-id")).group_id + '$' + p[1];
      console.log(p, this.getAttribute("data-name"), this, newID);
      renamePad(oldPadID, newID);
    }
  });
  
  function getGroupById(id) {
    for(var i in padman_data.groups)
      if (padman_data.groups[i].group_id == id) return padman_data.groups[i];
  }
  function getGroupByMapper(mapper) {
    for(var i in padman_data.groups)
      if (padman_data.groups[i].group_mapper == mapper) return padman_data.groups[i];
  }
  
  function updateTags() {
    var $tags = $("#taglist").html("&nbsp;");
    $tags.append("<span class='btn btn-xs btn-primary'>(alle)</span> ");
    
    var tags = groupInfo.tags;
    if (tags) tags.match(/[^ ]+/g).forEach(function(tag) {
      $tags.append("<span class='btn btn-xs btn-default'>"+tag+"</span> ");
    });
  }
  
  function loadPadList() {
    $("#pad_list").html("<div class='loader'></div>");
    API_Get("list", "group=" + groupInfo.group_mapper + "&tag=" + selected_tag, function(result) {
      var q = '<div class="table-responsive"><table class="table table-hover">\
        <thead><tr><th width=30></th><th>Name</th><th width=350>Passwort</th><th width=100></th></tr></thead><tbody>';
      result.pads.forEach(function(PAD) {
        if (PAD["access_level"] == 1) {
          var icon_html = '<span class="glyphicon glyphicon-globe"></span> ';
        } else{
          var icon_html = '<span class="glyphicon glyphicon-home"></span> ';
        }
        
        q+= '\
        <tr data-padID="'+PAD.group_id+'$'+PAD.pad_name+'"> \
          <td class="pad_icon icon"><!--button type="button" class="btn btn-link btn-xs"-->\
            '+icon_html+'\
          <!--/button--></td>\
          <td class="name"><a href="'+SELF_URL+'?group='+PAD.group_mapper+'&show='+PAD.pad_name+'">'+PAD.pad_name+'</a></td><td>';
        if (PAD.password) q+= ' <code>'+PAD.password+'</code>';
        q+= ' <span class="pull-right"> ';
        if (PAD.access_level==1) q+= '<span class="label label-success ">Öffentlich</span> ';
        if (PAD.last_edited_formatted)
          q+= '<span class="label label-default ">'+PAD.last_edited_formatted+'</span> ';
        q+= '</span></td><td><button class="btn btn-xs btn-default pad_opts" title="Einstellungen"><i class="glyphicon glyphicon-cog"></i></button>\
           <button class="btn btn-xs btn-default pad_rename" title="Umbenennen"><i class="glyphicon glyphicon-pencil"></i></button>\
          <a href="'+SELF_URL+'?group='+PAD.group_mapper+'&show='+PAD.pad_name+'" target="_blank" class="btn btn-xs btn-default open_popup" title="In neuem Fenster öffnen"><i class="glyphicon glyphicon-new-window"></i></a>\
          </td></tr>';
      });
      q += "</tbody></table></div>";
      if (result.pads.length == 0) q += "<div style='padding:100px 0;text-align:center;color:#aaa;'>- In dieser Kategorie gibt es noch keine Pads -</div>";
      
      
      $("#pad_list").html(q);
      
      $("#pad_list tr").draggable({ handle: ".pad_icon", revert: true, helper: "clone",
                                    cursorAt: { top: 15, left: 15 }, opacity: 0.7  });
      
    });
    $("#createSitzungPadForm").toggle(groupInfo.group_mapper == "sitzung");
    $("#cur_group_name").text(groupInfo.group_mapper);
    $(".group_form").attr("action", SELF_URL + "?group=" + escape(groupInfo.group_mapper));
    $(".create_pad_name").attr("placeholder", "neues Pad in " + groupInfo.group_mapper);
  }
  
  $("#taglist").click(function(e) {
    $("#taglist span").attr("class", "btn btn-xs btn-default");
    var $tag = $(e.target).attr("class", "btn btn-xs btn-primary");
    selected_tag = $tag.text();
    if (selected_tag == "(alle)") selected_tag = "";
    loadPadList();
  });
  
  this.loadPadList = loadPadList;
}
