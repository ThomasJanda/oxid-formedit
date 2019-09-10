var copyelements=null;


function getMousePosition(e)
{
    var relativePosition;
    var tabid=getCurrentTabId();
    if(tabid)
    {
        relativePosition = {
        left: e.pageX - $(document).scrollLeft() - $("#" + tabid).offset().left,
        top : e.pageY - $(document).scrollTop() - $("#" + tabid).offset().top
        };        
    }
    return relativePosition;
}


function blocksite()
{
    $('#blocksite').css('display','block');
}
function unblocksite()
{
    $('#blocksite').css('display','none');
}


/*ALLES MIT EDITOR*/
function editorNew()
{
    addNewTab();
}
function editorNewClick()
{
    $( "#dialog-editor-new-click" ).dialog({
    	resizable: false,
    	height:140,
    	modal: true,
    	buttons: {
    		"Yes": function() {
    		    $('#dialog-editor-new-click-form').submit();
    			$( this ).dialog( "close" );
    		},
    		"No": function() {
    			$( this ).dialog( "close" );
    		}
    	}
    });
}
function editorLoadClick()
{
    /*
	$( "#dialog-editor-load-click" ).dialog({
	    resizable: false,
		height: 300,
		width: 500,
		modal: true,
		buttons: {
			"Load project": function() {
			    var tmp=$('#select_projectsave').val();
			    if(tmp!="")
			    {
    			    $('#dialog-editor-load-click-form').submit();
    			    $( this ).dialog( "close" );
			    }
			},
			"Cancel": function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {

		}
	});
    */
    blocksite();
    returnfunction = "editorLoadReturn";
    windowname = "Load project";
    popup = window.open('filebrowseropen/index.php?returnfunction=' + returnfunction, windowname, "height=430,width=900, scrollbars=1, resizable=1");
    popup.focus();
}
function editorLoadReturn(path)
{
    $('#select_projectload').val(path); 
    $('#dialog-editor-load-click-form').submit();
    unblocksite();
}
function editorSaveClick()
{
    /* 
	$( "#dialog-editor-save-click" ).dialog({
	    resizable: false,
		height: 300,
		width: 500,
		modal: true,
		buttons: {
			"Save project": function() {
			    var tmp=$('#textbox_projectsave').val();
			    if(tmp!="")
			    {
    			    $('#dialog-editor-save-click-form').submit();
    			    $( this ).dialog( "close" );
			    }
			},
			"Cancel": function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {

		}
	});
    */
    var path = $('#textbox_projectsave').val();
    if(path=="")
    {
        editorSaveAsClick();
    }
    else
    {
        blocksite();
        editorSaveAsReturn(path);
    }
    /*
    blocksite();
    returnfunction = "editorSaveReturn";
    windowname = "Save project";
    window.open('filebrowsersave/index.php?returnfunction=' + returnfunction, windowname, "height=430, width=900, scrollbars=1, resizable=1");
    */
}
function editorSaveAsClick()
{
    blocksite();
    returnfunction = "editorSaveAsReturn";
    windowname = "Save as project";
    window.open('filebrowsersave/index.php?returnfunction=' + returnfunction, windowname, "height=430, width=900, scrollbars=1, resizable=1");
}
function editorSaveAsReturn(path)
{
    suffix=".cpf"
    if(path.indexOf(suffix, path.length - suffix.length)==-1)
        path=path+suffix
  
    $('#textbox_projectsave').val(path);
    $('#dialog-editor-save-click-form').submit();
    unblocksite();
}

function editorSessionVariablenClick()
{
    var params={session_name: session_name};
    $.ajax({
        type: "POST",
        cache: false,
        url: "editor_getinterformular.php",
        data: params,
        dataType: "html",
        success: function(data){
            alert("Variables: "+data);
        }
    });
}

/*ALLES MIT TABS*/

function addNewTab()
{
    var params={session_name: session_name };
    var id = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_createtab.php",
        dataType: "html",
        data: params,
        async: false
    }).responseText;

    addNewTab2(id);
}
function delTab()
{
    var id=getCurrentTabId();
    if(id)
    {
        if(confirm("Delete form?"))
        {
            var params={containerid: id, session_name: session_name };
            $.ajax({
                type: "POST",
                cache: false,
                url: "editor_deletetab.php",
                data: params,
                dataType: "html",
                async: false
            });
            
            $('#'+id).remove();
            $("#formsall option[value="+id+"]").remove();
            
            if($('#formsall option').size()==0)
            {
                addNewTab();
            }
            else
            {
                id=$("#formsall option:first").val();
                selectTab(id);
            }
        }
    }
}
function setTabName(id, name)
{
    var o=$("#formsall").children().filter("option[value='"+id+"']");
    o.html(name);
}
function addNewTab2(id)
{
    if(id!="")
    {
        $("#destkoptabs").append("<div class='desktoptab' id='" + id + "'></div>");
        $("#formsall").append("<option value='"+id+"'>"+id+"</option>");
        selectTab(id);
        
        $("#destkoptabs *").mousedown( function() {
            $('#menucontrol').css({'display':'none' });
            $('#menudesktop').css({'display':'none' });
        });

        if(id!="" && $("#destkoptabs #" + id).length > 0) {
            $("#destkoptabs #" + id)
                .click(function () {
                    dragged = false;
                })
                .rightMouseUp(function (e) {

                    $('#menudesktop .paste').css('display', 'none');
                    if (copyelements) {
                        $('#menudesktop .paste').css('display', 'block');
                    }
                    $('#menudesktop').css({'left': e.pageX + 'px', 'top': e.pageY + 'px', 'display': 'block'});
                })
                .droppable({
                    accept: ".control",
                    drop: function (event, ui) {
                        $(this).children().filter(".element").removeClass("ui-selected");

                        var element = ui.draggable;
                        var classname = $(element).children().filter("input[name='classname']").val();
                        var left = Math.floor(ui.position.left / 10) * 10;
                        var top = Math.floor(ui.position.top / 10) * 10;
                        var tabid = $(this).attr('id');
                        createElement(classname, tabid, left, top);
                    }
                })
                .selectable({
                    stop: function (event, ui) {

                    },
                    selected: function (event, ui) {
                        $(this).attr('data-hasselected', '1');
                    },
                    unselected: function (event, ui) {
                        $(this).attr('data-hasselected', '');
                    }
                });
        }
    }
}

function selectTab(id)
{
    if(id!="" && $("#destkoptabs #" + id).length > 0) {
        $("#destkoptabs .desktoptab").hide();
        if ($("#destkoptabs #" + id).length > 0) {
            $("#destkoptabs #" + id).show();
            $("#formsall").val(id);
            $(".control").draggable("option", "appendTo", $("#destkoptabs #" + id));
            refreshElements(id);
            getPropertyTab(id);
        }
    }
}

function moveTabDown(id)
{
    blocksite();

    var params={containerid: id, session_name: session_name };
    var response = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_formmovedown.php",
        data: params,
        dataType: "text",
        async: false
    });
    refreshForms(id);
    refreshElements(id);
    getPropertyTab(id);

    unblocksite();
}
function moveTabUp(id)
{
    blocksite();

    var params={containerid: id, session_name: session_name };
    var response = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_formmoveup.php",
        data: params,
        dataType: "text",
        async: false
    });
    refreshForms(id);
    refreshElements(id);
    getPropertyTab(id);

    unblocksite();
}


function loadDefinition(tabid)
{
    blocksite();
    var table = $('input[name=_table]').val();

    //$('#propertyform').submit();

    var params={containerid: tabid, session_name: session_name, table: table};
    var response = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_loaddefinition.php",
        data: params,
        dataType: "text",
        async: false
    }).responseText;

if($.trim(response)!="")
{
    var asource=response.split("\n--||||--\n");
    for(x=0;x<asource.length;x++)
    {
        elementhtml=asource[x];
        $.trim(elementhtml);
        if(elementhtml!="")
        {
            initElement(elementhtml, tabid);
        }
    }
}


    //initElement(elementhtml, tabid);

    refreshForms(tabid);
    refreshElements(tabid);
    getPropertyTab(tabid);
    //selectTab(id);

    unblocksite();
}

function refreshForms(id)
{
    if(!id)
    {
        id = getCurrentTabId();
    }
    $("#formsall").empty();

    var params={containerid: id, session_name: session_name };
    var elements = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_refreshforms.php",
        data: params,
        dataType: "text",
        async: false
    }).responseText;

    var elements2=elements.split('||');
    for(x=0;x<elements2.length;x++)
    {
        var elements3=elements2[x];
        var elements4=elements3.split('::');
        var optionid=elements4[0];
        var optiontext=elements4[1];
        $("#formsall").append("<option value='"+optionid+"'>"+optiontext+"</option>");
    }

    $("#formsall").val(id);
}
function refreshElements(id)
{
    $("#elementsall").empty();
    var optionid="";
    var optiontext="";
    $("#elementsall").append("<option value='"+optionid+"'>"+optiontext+"</option>");
    
    var params={containerid: id, session_name: session_name };
    var elements = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_refreshelements.php",
        data: params,
        dataType: "text",
        async: false
    }).responseText;
    
    var elements2=elements.split('||');
    for(x=0;x<elements2.length;x++)
    {
        var elements3=elements2[x];
        var elements4=elements3.split('::');
        var optionid=elements4[0];
        var optiontext=elements4[1];
        $("#elementsall").append("<option value='"+optionid+"'>"+optiontext+"</option>");
    }
    //alert(elements);
}
function getCurrentTabId()
{
    return $("#formsall").val();
}
function refreshTab(id)
{
    var params={containerid: id, session_name: session_name };
    var elementhtml = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_refreshelementform.php",
        data: params,
        dataType: "html",
        async: false
    }).responseText;

    setTabName(id, elementhtml); 
}
var getpropertyform_ajax=null;
function getPropertyTab(id)
{
    if(getpropertyform_ajax)
        getpropertyform_ajax.abort();

    var params={'id': id, session_name: session_name};
    getpropertyform_ajax = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_getpropertyform.php",
        data: params,
        dataType: "html",
        success: function(data){
            $('#propertybox').html(data);
            getpropertyfrom_id="";

            $('#propertysubmit')
            .button()
            .click(function() {
                $('#propertyformwait').addClass('active');
                var params=$('#propertyform').serialize();
                params+="&session_name=" + session_name; 
                $.ajax({
                    type: "POST",
                    cache: false,
                    url: "editor_setpropertyform.php",
                    data: params,
                    dataType: "html",
                    async: false
                });

                refreshTab(id);

                $('#propertyformwait').removeClass('active');

                return false;
            });

            $("#propertybox input[type=text]").change(function() {
                $('#propertysubmit').click();
            });

            $("#propertybox input[type=textbox]").change(function() {
                $('#propertysubmit').click();
            });

            $("#propertybox input[type=password]").change(function() {
                $('#propertysubmit').click();
            });

            $("#propertybox textarea").change(function() {
                $('#propertysubmit').click();
            });

            $("#propertybox :checkbox").change(function() {
                $('#propertysubmit').click();
            });

            $("#propertybox select").change(function() {
                $('#propertysubmit').click();
            });
        }
    });
}

function setTabOrder(id)
{
    var params={id: id, session_name: session_name};
    $.ajax({
        type: "POST",
        cache: false,
        url: "editor_settaborder.php",
        data: params,
        dataType: "html",
        success: function(data){
            alert("Tab-order is set");
        }
    });
}

function setLanguageIds(id)
{
    boverride=0;
    var r = confirm("Override current language ids?");
    if (r == true)
        boverride=1;

    var params={id: id, session_name: session_name, override: boverride};
    $.ajax({
        type: "POST",
        cache: false,
        url: "editor_setlanguageids.php", 
        data: params,
        dataType: "html",
        success: function(data){
            alert("Language ids are set");
        }
    });
}

function getSql(id)
{
    var params={id: id, session_name: session_name};
    $.ajax({
        type: "POST",
        cache: false,
        url: "editor_getsql.php",
        data: params,
        dataType: "html",
        success: function(data){
            alert("SQL: "+data);
        }
    });
}

function getLanguageArray(id)
{
    var params={id: id, session_name: session_name};
    $.ajax({
        type: "POST",
        cache: false,
        url: "editor_getlanguagearray.php",
        data: params,
        dataType: "html",
        success: function(data){
            alert("Language array: "+data);
        }
    });
}

/*ALLES MIT ELEMENTEN*/
var getproperty_ajax=null;
var getproperty_obj=null;
function getPropertyElement(obj)
{

    if($(getproperty_obj).attr('id')==$(obj).attr('id'))
        return;
    getproperty_obj=obj;

    if(getproperty_ajax)
        getproperty_ajax.abort();

    var id = $(obj).attr('id');
    var classname = $(obj).children().filter("input[name='classname']").val();
    var containerid = $(obj).parent().attr('id');
    
    /* select element in selectbox */
    $("#elementsall").val(id);

    var params={'id': id, 'containerid': containerid, 'classname': classname, session_name: session_name};
    getproperty_ajax = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_getproperty.php",
        data: params,
        dataType: "html",
        success: function(data){
            $('#propertybox').html(data);
            if(document.getElementById('propertysubmit'))
            {
                $('#propertysubmit')
                .button()
                .click(function() {

                    $('#propertyformwait').addClass('active');
                
                    var params=$('#propertyform').serialize();
                    params+="&session_name=" + session_name;

                    $.ajax({
                        type: "POST",
                        cache: false,
                        url: "editor_setproperty.php",
                        data: params,
                        dataType: "script",
                        async: false
                    });
                    
                    var id = $('#propertyform').children().filter("input[name='id']").val();
                    refreshElement('#'+id);

                    $('#propertyformwait').removeClass('active');
                    return false;
                });

                $("#propertybox input[type=text]").change(function() {
                    $('#propertysubmit').click();
                });

                $("#propertybox input[type=textbox]").change(function() {
                    $('#propertysubmit').click();
                });

                $("#propertybox input[type=password]").change(function() {
                    $('#propertysubmit').click();
                });

                $("#propertybox textarea").change(function() {
                    $('#propertysubmit').click();
                });

                $("#propertybox :checkbox").change(function() {
                    $('#propertysubmit').click();
                });

                $("#propertybox select").change(function() {
                    $('#propertysubmit').click();
                });

            }
            getproperty_obj=null;
        }
    });
}
function createElement(classname, tabid, left, top)
{
    var params={'classname': classname, 'left': left, 'top': top, 'containerid': tabid, session_name: session_name };
    var elementhtml = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_getelement.php",
        data: params,
        dataType: "html",
        async: false
    }).responseText;
    initElement(elementhtml, tabid);
}
var dragged=false;
function initElement(elementhtml, tabid)
{
    var elementnew = $(elementhtml);

    elementnew
    /*.click(function(e) {
        getPropertyElement(this);
    })
    /*.click(function(e) {
        getPropertyElement(this);
        
        if(dragged)
        {
            dragged=false;
        }
        else
        {
            if(e.ctrlKey) {
                //Ctrl+Click
                if($(this).hasClass('ui-selected'))
                    $(this).removeClass('ui-selected');
                else
                    $(this).addClass('ui-selected');
            }
            else
            {
                $(this).parent().children('.ui-selected').removeClass("ui-selected");
                $(this).addClass('ui-selected');
            }
        }
        dragged=false;
    })
    */
    .mousedown(function(e) {
        if(e.ctrlKey) {
            //Ctrl+Click
            if($(this).hasClass('ui-selected'))
                $(this).removeClass('ui-selected');
            else
                $(this).addClass('ui-selected');
        }
        else
        {
            //$(this).parent().children('.ui-selected').removeClass("ui-selected");
            if($(this).parent().attr('data-hasselected')!="1")
            {
                $(this).parent().children('.ui-selected').not(this).removeClass("ui-selected");
            }  
            $(this).addClass('ui-selected');
            //getPropertyElement(this);
        }            

    })
    .mouseup(function(e) {
        if(e.which==3)
        {
            $('#menucontrol').children().filter("input[name='id']").val($(this).attr('id'));
            $('#menucontrol').css({'left':e.pageX + 'px', 'top':e.pageY + 'px', 'display':'block' });
        }
        else
        {
            if(dragged==false)
            {
                if(e.ctrlKey) {
                }
                else
                {
                    $(this).parent().children('.ui-selected').not(this).removeClass("ui-selected");    
                }
            }            
        }
        dragged=false;
        
        getPropertyElement(this);
    })
    .rightMouseUp( function(e) {
    })
    .draggable({
        grid: [ 10,10 ],
        opacity: 0.7,
        cursor: "move",
        stack: ".element",
        start: function(event, ui)
        {
//            if(!$(this).hasClass('ui-selected'))
//                $(this).addClass('ui-selected');
            dragged=true;
            
            var tabid=getCurrentTabId();
            if(tabid!="")
            {
                $("#"+tabid+" div.element.ui-selected:visible").each(function() {
                    var left=parseInt($(this).css("left"),10);
                    var top=parseInt($(this).css("top"),10);
                    $(this).attr('data-dragstartleft',left);
                    $(this).attr('data-dragstarttop',top);
                });
            } 
        },
        drag: function(event, ui) {
            var sleft=parseInt($(this).attr('data-dragstartleft'));  
            var stop=parseInt($(this).attr('data-dragstarttop')); 
            var cleft=parseInt($(this).css("left"),10);
            var ctop=parseInt($(this).css("top"),10);
            var oleft=(sleft-cleft)*-1;
            var otop=(stop-ctop)*-1;
            //console.log(sleft + '-' + cleft + '-' + oleft);
            var tabid=getCurrentTabId();
            if(tabid!="")
            {
                //$("#"+tabid+" div.element.ui-selected").not("#"+$(this).attr('id')).css('color','red');
                $("#"+tabid+" div.element.ui-selected:visible").not("#"+$(this).attr('id')).each(function() {
                    var sleft=parseInt($(this).attr('data-dragstartleft'));  
                    var stop=parseInt($(this).attr('data-dragstarttop')); 
                    var nleft=sleft+oleft;
                    var ntop=stop+otop;
                    //console.log(nleft + '-' + ntop);
                    $(this).css('left',nleft+'px');
                    $(this).css('top',ntop+'px');
                });
            }
        },
        stop: function(event, ui) {
            
            setElementSize(this);
            
            var sleft=parseInt($(this).attr('data-dragstartleft'));  
            var stop=parseInt($(this).attr('data-dragstarttop')); 
            var cleft=parseInt($(this).css("left"),10);
            var ctop=parseInt($(this).css("top"),10);
            var oleft=(sleft-cleft)*-1;
            var otop=(stop-ctop)*-1;
            
            var tabid=getCurrentTabId();
            if(tabid!="")
            {
                $("#"+tabid+" div.element.ui-selected:visible").not("#"+$(this).attr('id')).each(function() {
                    var sleft=parseInt($(this).attr('data-dragstartleft'));  
                    var stop=parseInt($(this).attr('data-dragstarttop')); 
                    var nleft=sleft+oleft;
                    var ntop=stop+otop;
                    //console.log(nleft + '-' + ntop);
                    $(this).css('left',nleft+'px');
                    $(this).css('top',ntop+'px');
                    
                    setElementSize(this);
                });
            }
        }
    })
    .resizable({
        grid: [ 10,10 ],
        
        resize: function(event, ui) { 
        },
        start: function(event, ui) {
        },
        stop: function(event, ui) {
            setElementSize(this);
        }
    })

    $('#' + tabid).children('.ui-selected').removeClass("ui-selected");
    $(elementnew).addClass('ui-selected');
    $('#' + tabid).append(elementnew);
    //$('#' + tabid).selectable();

    setElementSize(elementnew);
    getPropertyElement(elementnew);

}
function setElementSize(obj)
{
    var left=parseInt($(obj).css("left"),10);
    var top=parseInt($(obj).css("top"),10);
    //console.log(left);
    //console.log(top);
    left=Math.round(left/10)*10;
    top=Math.round(top/10)*10;
    $(obj).css("left",left+'px');
    $(obj).css("top",top+'px');
                    
    var width = $(obj).css('width').replace("px","");
    var height = $(obj).css('height').replace("px","");
    
    var width=parseInt($(obj).css("width"),10);
    var height=parseInt($(obj).css("height"),10);
    width=Math.round(width/10)*10;
    height=Math.round(height/10)*10;
    if(width<10)
        width=10;
    if(height<10)
        height=10;
    $(obj).css("width",width+'px');
    $(obj).css("height",height+'px');
    
    var zindex = $(obj).css('z-index');

    var tmp=$(obj).attr('data-zindex');
    if(tmp) {
        zindex=tmp;
        $(obj).css('z-index',tmp);
    }

    var id = $(obj).attr('id');
    var classname = $(obj).children().filter("input[name='classname']").val();
    var containerid = $(obj).parent().attr('id');
    var params={'id': id, 'containerid':containerid, 'classname': classname, 'top': top, 'left': left, 'width': width, 'height': height, 'zindex': zindex, session_name: session_name};
    $.ajax({
        type: "POST",
        cache: false,
        url: "editor_setsize.php",
        data: params,
        dataType: "html"
    });
}
function refreshElement(obj)
{
    var id = $(obj).attr('id');
    var classname = $(obj).children().filter("input[name='classname']").val();
    var containerid = $(obj).parent().attr('id');

    var params={classname: classname, id: id, containerid: containerid , session_name: session_name};
    var elementhtml = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_refreshelement.php",
        data: params,
        dataType: "html",
        async: false
    }).responseText;

    $(obj).remove();

    initElement(elementhtml, containerid);
}
function deleteElement(obj)
{
    var id = $(obj).attr('id');
    var classname = $(obj).children().filter("input[name='classname']").val();
    var containerid = $(obj).parent().attr('id');

    getPropertyTab(containerid);

    var params={'id': id, 'containerid': containerid, 'classname': classname, session_name: session_name};
    var tmp = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_deleteelement.php",
        data: params,
        /*dataType: "html"*/
        dataType: "script",
        async: false
    }).responseText;
    /*alert(tmp);*/
    $(obj).remove();
}
function cloneElement(obj, offsetleft, offsettop)
{
    var id = $(obj).attr('id');
    var classname = $(obj).children().filter("input[name='classname']").val();
    var containerid = $(obj).parent().attr('id');
    var currentcontainerid=getCurrentTabId();
    $(obj).removeClass('ui-selected');

    var params={'id': id, 'containerid': containerid, 'classname': classname, 'newcontainerid': currentcontainerid, session_name: session_name};

    var elementhtml = $.ajax({
        type: "POST",
        cache: false,
        url: "editor_cloneelement.php",
        data: params,
        dataType: "html",
        async: false
    }).responseText;

    var elementnew = $(elementhtml);

    var p=$(obj).position();
    var zindex = parseInt($(obj).css('zIndex'));
    if(zindex=="auto")
        zindex=1;
    zindex++;
    //$(elementnew).css({left: (p.left + 10) + "px", top: (p.top + 10) + "px", 'zIndex':zindex });
    $(elementnew).css({left: offsetleft + "px", top: offsettop + "px", 'zIndex':zindex });

    initElement(elementnew, currentcontainerid);
    
    return $(elementnew).attr('id');
}
function sourceElements(objs)
{
    var source="";
    
    objs.each(function() {
        var id = $(this).attr('id');
        var classname = $(this).children().filter("input[name='classname']").val();
        var containerid = $(this).parent().attr('id');
        
        if(source!="")
        {
            source+="\n--||||--\n";
        }
        
        var params={'id': id, 'containerid': containerid, 'classname': classname, session_name: session_name};

        source += $.ajax({
            type: "POST",
            cache: false,
            url: "editor_copyelements.php",
            data: params,
            dataType: "html",
            async: false
        }).responseText;
    });
    
    $('#textarea_copyinterformular').val(source).css({width:'320px', height:'150px'});
    
	$( "#dialog-editor-copyinterformular-click" ).dialog({
	    resizable: false,
		height: 300,
		width: 350,
		modal: true,
		buttons: {
			"Close": function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
		}
	});
}
function addSourceElements()
{
    $('#textarea_pasteinterformular').val('').css({width:'320px', height:'150px'});
    
	$( "#dialog-editor-pasteinterformular-click" ).dialog({
	    resizable: false,
		height: 300,
		width: 350,
		modal: true,
		buttons: {
            "Past": function() {
                var source=$('#textarea_pasteinterformular').val();
                $( this ).dialog( "close" );
                
                var tabid=getCurrentTabId();
                
                var asource=source.split("\n--||||--\n");
                for(x=0;x<asource.length;x++)
                {
                    //alert(asource[x]);
                    
                    source=asource[x];
                    $.trim(source);

                    var params={'source': source, 'containerid': tabid, session_name: session_name };
                    var elementhtml = $.ajax({
                        type: "POST",
                        cache: false,
                        url: "editor_pasteelements.php",
                        data: params,
                        dataType: "html",
                        async: false
                    }).responseText;
                    if(elementhtml!="")
                    {
                        initElement(elementhtml, tabid);    
                    }
                    
                }
                
                
            },
			"Close": function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
		}
	});
}


/* INIT */
var myLayout;
$(function() {

    $("body").on("keydown", ".uses-tab-btn", function(e) {
        if(e.keyCode === 9) { // tab was pressed
            // get caret position/selection
            var start = this.selectionStart;
            var end = this.selectionEnd;

            var $this = $(this);
            var value = $this.val();

            // set textarea value to: text before caret + tab + text after caret
            $this.val(value.substring(0, start)
                + "\t"
                + value.substring(end));

            // put caret at right position again (add one for the tab)
            this.selectionStart = this.selectionEnd = start + 1;

            // prevent the focus lose
            e.preventDefault();
        }
    });

    $("*").click( function() {
        $('#menucontrol').css({'display':'none' });
        $('#menudesktop').css({'display':'none' });
    });
    
    $("#controlnavi").accordion({
        header: "h3",
        autoHeight:false,
        collapsible:true,
        heightStyle: "content",
    });

    /*
    $('body').layout({
        slidable:true,
        north__resizable:false,
    });
    */
    myLayout=$('body').layout({
        slidable:true
    });
    myLayout.sizePane("west", 330);
    myLayout.sizePane("east", 400);
    
    $('#select_projectsave')
    .click(function() {
        $('#textbox_projectsave').val($('#select_projectsave').val());
    });
    
    //$('#emselectboxforms').emselectbox({type:'link'});
    $('#formlink').button().click(function() {
        var p=$(this).attr('data-projectname');
        if(p!="")
        {
            alert(p);
        }
    });
    var path = $('#textbox_projectsave').val();
    if(path=="")
    {
        $('#formlink').button( "option", "disabled", true );
    }
    
    
    $('#fromadd').button().click(function() {
        addNewTab();
    });
    
    $('#fromdel').button().click(function() {
        delTab();
    });
    
    $('#formsall').change(function() {
        selectTab(getCurrentTabId());
    });
    
    $("#elementsall").change(function() {
        var id = $(this).val();
        /*alert(id);*/
        $('#' + id).mousedown();
        $('#' + id).mouseup();
    });
    
    $('#formpropertys').button().click(function() {
        getPropertyTab(getCurrentTabId());
    });
    
    $('#formtaborder').button().click(function() {
        setTabOrder(getCurrentTabId());
    });
    
    $('#formsql').button().click(function() {
        getSql(getCurrentTabId());
    }); 
    
    $('#formlanguageids').button().click(function() {
        setLanguageIds(getCurrentTabId());
    });
    
    $('#formlanguagearray').button().click(function() {
        getLanguageArray(getCurrentTabId());
    });    
    
    $('#menutopright').button().click(function() {
        var open = $(this).attr('data-open');
        if(open=="1")
        {
            $('#menutopright_popup').css('display','none');
            $(this).attr('data-open',0);
        }
        else
        {
            $('#menutopright_popup').css('display','block');
            $(this).attr('data-open',1);
        }
    });
    $('#menutopright_popup').mouseleave(function() {
        $(this).css('display','none');
        $('#menutopright').attr('data-open',0);
    });
    
    $('#editornew').button().click(function() {
        editorNewClick();
    });
    
    $('#editorload').button().click(function() {
        editorLoadClick();
    });
    
    $('#editorsave').button().click(function() {
        editorSaveClick();
    });
    var path = $('#textbox_projectsave').val();
    if(path=="")
    {
        $('#editorsave').button( "option", "disabled", true );
    }
    
    $('#editorsaveas').button().click(function() {
        editorSaveAsClick();
    });
    
    /*
    $('#editortest').button().click(function() {
        editorTestClick();
    });
    */
    
    $('#editorsessionvariablen').button().click(function() {
        editorSessionVariablenClick();
    });
    
    $('#resetposition').click(function() {
        var id=$("#elementsall").val();
        if(id!="")
        {
            var obj=$('#' + id);
            $(obj).css("left",'0px');
            $(obj).css("top",'0px');
            setElementSize(obj);
        }
    });
    
	$(".control").draggable({
        grid: [ 10,10 ],
        opacity: 0.7,
        helper: "clone",
        cursor: "move",
        cursorAt: {left:0, top:0},
        start: function(event, ui)
        {
            $('#menucontrol').css({'display':'none' });
            $('#menudesktop').css({'display':'none' });
            ui.helper.addClass('controldrag');
        }
    });
    
    $('#desktop').scroll(function() {
        var id=getCurrentTabId();
        if(id!="")
        {
            $('#'+id).css('height',$(this).prop("scrollHeight") + 'px');
            $('#'+id).css('width',$(this).prop("scrollWidth") + 'px');
        }
    });
    
    $('#menucontrol div a')
	.click(function () {
        
        var tabid=getCurrentTabId();
	    var command = ($(this).attr('href')).replace("#","");
        
	    if(command=="copy")
	    {
            copyelements=$("#"+tabid+" div.element.ui-selected");
            
            copyelements.each(function() {
                var p=$(this).position();
                $(this).attr('data-copyleft',p.left);
                $(this).attr('data-copytop',p.top);
            });
	    }
	    else if(command=="copyinterformular")
	    {
            var es=$("#"+tabid+" div.element.ui-selected:visible");
            
            sourceElements(es);
	    }
	    else if(command=="delete")
	    {
            if(confirm("Delete element(s)?"))
            {
                $("#"+tabid+" div.element.ui-selected:visible").each(function() {
                    deleteElement(this);
                });                
            }

	    }
	    else if(command=="pasteinterformular")
	    {
            addSourceElements();
	    }
	    return false;
	});
	$('#menudesktop div a')
	.click(function (e) {
        
	    var command = ($(this).attr('href')).replace("#","");
	    if(command=="pasteinterformular")
	    {
            addSourceElements();
	    }
	    else if(command=="paste")
	    {
            var tabid=getCurrentTabId();
            
            var relativePosition = getMousePosition(e);
            
            var newids=new Array();
            var tabid=getCurrentTabId();
            $("#"+tabid+" div.element").removeClass('ui-selected');
            
            var minpl=null;
            var minpt=null;
            copyelements.each(function() {
                var pl=$(this).attr('data-copyleft');
                var pt=$(this).attr('data-copytop');
                if(minpl===null)
                {
                    minpl=pl;
                    minpt=pt;
                }
                else
                {
                    if(minpl > pl && minpt > pt)
                    {
                        minpl=pl;
                        minpt=pt;
                    }
                }
            });
            if(minpt!==null)
            {
                copyelements.each(function() {
                    var pl=$(this).attr('data-copyleft');
                    var pt=$(this).attr('data-copytop');
                    
                    var ol= pl - minpl + relativePosition.left;
                    var ot= pt - minpt + relativePosition.top;
//console.log("l:" + ol);
//console.log("t:" + ot);
                    var newid=cloneElement(this, ol, ot);
                    //$('#'+newid).addClass('ui-selected');
                    newids.push(newid);
                });
                
                $.each(newids, function(key, newid) {
                    $('#'+newid).addClass('ui-selected');
                });
            }
                
            //$("#" + tabid).append("<div style='position:absolute; left:" + relativePosition.left + "px; top:" + relativePosition.top + "px; background-color:red; width:200px; height:20px; '>x: " + relativePosition.left + ' y: ' + relativePosition.top + "</div>");
            
        }
	    return false;
	});
    
    $(this).bind("contextmenu", function(e) {
        e.preventDefault();
    });
    

});
