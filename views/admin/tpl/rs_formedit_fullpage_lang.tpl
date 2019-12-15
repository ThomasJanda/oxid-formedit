[{include file="headitem.tpl" title="" box=" "}]


<div style="background-color: #dcdcdc; height: 35px; margin: 0 -20px; ">
    <form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="[{$oView->getClassName()}]">
    <input type="hidden" id="language" name="language" value="[{ $editlanguage }]">
    <input type="hidden" id="editlanguage" name="editlanguage" value="[{ $editlanguage }]">

    <input type="hidden" name="project" value="[{$formedit_project}]">
    <input type="hidden" name="index1value" value="[{$formedit_index1}]">
    <input type="hidden" name="index2value" value="[{$formedit_index2}]">
    <input type="hidden" name="navi" value="[{$formedit_navi}]">
    
    <script>
        function rschangelang()
        {
            var e = document.getElementById('changelang');
            var v = e.options[e.selectedIndex].value;
            document.getElementById('language').value=v;
            document.getElementById('editlanguage').value=v;
            document.getElementById('transfer').submit();        
        }
    </script>
    <div style="float:right; padding:5px; ">
    <select name="changelang" id="changelang" class="editinput" onChange="rschangelang(); ">
    [{foreach from=$languages item=lang}]
        <option value="[{ $lang->id }]" [{ if $lang->selected}]SELECTED[{/if}]>[{ $lang->name }]</option>
    [{/foreach}]
    </select>
    </div>
    </form>    
</div>


[{assign var=params value="?"}]
[{*assign var=p value=$oViewConf->getModuleUrl("rs-formedit","../../")|cat:$formedit_project*}]
[{assign var=p value=$formedit_project}]
[{assign var=p value=$p|escape:'url'}]
[{assign var=params value=$params|cat:"&projecturlload="|cat:$p}]
[{assign var=params value=$params|cat:"&index1value="|cat:$formedit_index1}]
[{assign var=params value=$params|cat:"&index2value="|cat:$formedit_index2}]
[{assign var=params value=$params|cat:"&languageedit="|cat:$formedit_language}]
[{assign var=params value=$params|cat:"&languageadmin="|cat:$adminlang}]
[{assign var=params value=$params|cat:"&navi="|cat:$formedit_navi}]

<iframe frameborder="0" style="
border: 0 solid black;
height: calc(100% - 35px);
left: 0;
padding: 0;
position: absolute;
top: 35px;
left: 0;
width: 100%; "
        src="[{$oViewConf->getModuleUrl("rs-formedit", "formedit/interpreter.php")}][{$params}]"></iframe>

</body>
</html>