# Oxid formedit (beta)

## Description

Build your admin views with drag and drop.

Module was created for Oxid 6.x

### IDE
![](ide.png)

### Results
![](view1.png)
![](view2.png)

## Install

1. Copy files into following directory

        source/modules/rs/formedit
    
2. Add to composer.json at shop root
  
        "autoload": {
            "psr-4": {
                "rs\\formedit\\": "./source/modules/rs/formedit"
            }
        },
        "require": {
            "mustangostang/spyc": "^0.6.2"
        },

3. Refresh autoloader files with composer in the oxid root directory.

        composer dump-autoload


## Modifications

Modify following file:

    out/admin/src/oxid.js
    
Search for function **changeEditBar**. Copy following snippet on the top of the function.

    /* cpformedit start */
    var input = document.createElement("input");
    input.setAttribute("type", "hidden");
    input.setAttribute("name", "rs_formedit_sPos");
    input.setAttribute("value", sPos);
    top.basefrm.edit.document.getElementById( "transfer" ).appendChild(input);

    var sNode="";
    var aInput = top.basefrm.list.document.getElementById( "search" ).getElementsByTagName('input');
    if(aInput && aInput.length > 0)
    {
        for(x=0;x<aInput.length;x++)
        {
            if(aInput[x].name=="cl")
            {
                sNode=aInput[x].value;
            }
        }
    }
    var input = document.createElement("input");
    input.setAttribute("type", "hidden");
    input.setAttribute("name", "rs_formedit_sNode");
    input.setAttribute("value", sNode);
    top.basefrm.edit.document.getElementById( "transfer" ).appendChild(input);
    /* cpformedit end */


## How to use

You can add formedit project to all modules from this shop.
Create a folder 

    formeditprojects
     
within the modules.

You can extend the menu.xml from the module to call the project.


## Example "menu.xml"

    <?xml version="1.0" encoding="ISO-8859-15"?>
    <OX>
        <OXMENU id="NAVIGATION_ESHOPADMIN">
            <MAINMENU id="mxmanageprod">
                <SUBMENU id="mxarticles" cl="article" list="article_list">
                    <!--
                    attribute
                    cl = rs_formedit_half|rs_formedit_full
                        rs_formedit_half = like the bottom part of the article view
                        rs_formedit_full = a fullpage view
                    clparam = combination from parameters seprated with &amp;
                        parameter:
                        project=path within the module folder to the projectfile (example: project=cpoxshop/cpformedit/oxshop.cpf)
                        optional: navi= in which mode should the formeid start. values=NEW|EDIT. default=NEW
                        optional: index1value=unique identifier what should load at the beginning. if you want load data directly (parameter navi must set to EDIT) Only on cpformedit_fullpage|cpformedit_fullpage_lang
                        optional: useindex=index1value|index2value. Default: index1. Normally in a tab the oxid from a article,user... is set into the index1value parameter, but if you like to bild a n:m relation it have to start with index2value. navi have to set to NEW. Only on cpformedit_halfpage
                    -->
                    <TAB id="tbclcpformedit_article" cl="rs_formedit_halfpage" clparam="project=MODULEFOLDER/cpformedit/oxarticles.cpf" />
                </SUBMENU>
            </MAINMENU>
        </OXMENU>
    </OX>
    
## Other featues

* You can extend the formedit views by php files to implement new validations
* You can develop your own controls for easy reuse them
* ...