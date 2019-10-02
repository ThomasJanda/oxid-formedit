[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]


[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
    [{else}]
    [{assign var="readonly" value=""}]
    [{/if}]

<script type="text/javascript">
    <!--
    window.onload = function ()
    {
        [{if $updatelist == 1}]
        top.oxid.admin.updateList('[{$oxid}]');
        [{/if}]
        top.reloadEditFrame();
    }
    function editThis( sID )
    {
        var oTransfer = top.basefrm.edit.document.getElementById( "transfer" );
        oTransfer.oxid.value = sID;
        oTransfer.cl.value = top.basefrm.list.sDefClass;

        //forcing edit frame to reload after submit
        top.forceReloadingEditFrame();

        var oSearch = top.basefrm.list.document.getElementById( "search" );
        oSearch.oxid.value = sID;
        oSearch.actedit.value = 0;
        oSearch.submit();
    }
    function processUnitInput( oSelect, sInputId )
    {
        document.getElementById( sInputId ).disabled = oSelect.value ? true : false;
    }
    //-->
</script>

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="rs_formedit_halfpage">
    <input type="hidden" name="editlanguage" value="[{$editlanguage}]">
    <input type="hidden" name="rs_formedit_sPos" value="[{$formedit_spos}]">
    <input type="hidden" name="rs_formedit_sNode" value="[{$formedit_snode}]">
</form>

[{assign var=params value="?project="|cat:$formedit_project}]
[{if $formedit_index1}][{assign var=params value=$params|cat:"&index1value="|cat:$formedit_index1}][{/if}]
[{if $formedit_index2}][{assign var=params value=$params|cat:"&index2value="|cat:$formedit_index2}][{/if}]
[{if $formedit_language}][{assign var=params value=$params|cat:"&languageedit="|cat:$formedit_language}][{/if}]
[{if $adminlang}][{assign var=params value=$params|cat:"&languageadmin="|cat:$adminlang}][{/if}]
[{if $formedit_navi}][{assign var=params value=$params|cat:"&navi="|cat:$formedit_navi}][{/if}]

<iframe frameborder="0" style="
border: 0 solid black;
height: 100%;
left: 0;
padding: 0;
position: absolute;
top: 0;
left: 0;
width: 100%; "
        src="[{$oViewConf->getModuleUrl("rs-formedit", "formedit/interpreter.php")}][{$params}]"></iframe>

[{include file="bottomitem.tpl"}]
