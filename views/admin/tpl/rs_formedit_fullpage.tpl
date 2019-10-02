[{include file="headitem.tpl" title="" box=" "}]

[{assign var=params value="?"}]
[{*assign var=p value=$oViewConf->getModuleUrl("rs-formedit","../../")|cat:$formedit_project*}]
[{assign var=p value=$formedit_project}]
[{assign var=p value=$p|escape:'url'}]
[{assign var=params value=$params|cat:"&projecturlload="|cat:$p}]
[{assign var=params value=$params|cat:"&index1value="|cat:$formedit_index1}]
[{assign var=params value=$params|cat:"&index2value="|cat:$formedit_index2}]
[{assign var=params value=$params|cat:"&languageedit="|cat:$adminlang}]
[{assign var=params value=$params|cat:"&languageadmin="|cat:$adminlang}]
[{assign var=params value=$params|cat:"&navi="|cat:$formedit_navi}]

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

</body>
</html>