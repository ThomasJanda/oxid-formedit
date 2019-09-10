<?php

require_once __DIR__ . "/core/hsinitinterpreter.php";

$hsConfig = getHsConfig();

// The "ini" array contains all keys from the request array, plus the mandatory values that we want to have at least set.
$ini = $hsConfig->getIni();

#region Interpreter id

// Note:
// Interpreter id is used throughout formedit like this: global $interpreterid

// Try to reuse the last interpreter id generated from previous navigation, if interpreter id exists, it means user
// is navigating within a single form, like going to the detail page and back to the main page, etc.
$interpreterid = $ini['interpreterid'];
if (!$interpreterid) {
    // If interpreter id didn't exist in the request, it means the user is arriving from a link, e.g. like the BASE menu
    // in this case create a meaningful predictable interpreter id that represents this view, this is to be able to reuse
    // session variables, cache, etc.

    //$interpreterid = uniqid(); // not random anymore!

    // put together parameters that make this form unique, mainly the project url load, and the start params.
    $toFormInterpreterId = ['project'       => "no_project",
                            'index1value'   => "no_index1value",
                            'index2value'   => "no_index2value",
                            'startparam'    => "no_startparam",
                            'startformname' => "no_startformname",
                            'startform'     => "no_startform"];
    // get those values from the request to overwrite the default values I set above.
    foreach ($toFormInterpreterId as $k => &$v) {
        if ($ini[$k]) {
            $v = $ini[$k];
        }
    }
    // now serialize it into a unique id, this will be passed to the next interactions like going to a detail page.
    $interpreterid = substr(md5(serialize($toFormInterpreterId)), 0, 13); // it cannot be too long or it doesn't post.
    // if we are generating a new id, it means we don't want to remember the previous search, so initialize it.
    $_SESSION[$interpreterid] = [];
}
#endregion


$errorlist = [];
$newNavi   = "";

$index1value       = $ini["index1value"];
$index2value       = $ini["index2value"];
$kennzeichen1value = $ini["kennzeichen1value"];
$throughValue      = $ini["through"];
$redirectValue     = $ini["redirect"];

// temp solution to recursion in oldoelements/oelements
$tmpElements = null;

$isfirstnew  = false;
$isfirstedit = false;
$isnew       = false;
$isedit      = false;

$languageadmin = $ini["languageadmin"];
$languageedit  = $ini["languageedit"];

#region change startfrom

$errorStartForm = false;
// Start form id
$startForm = $ini['startform'];
$hsConfig->setInterpreterValue("startform", $startForm);
// Start form name (customer id)
$startFormName = $ini["startformname"];
$hsConfig->setInterpreterValue("startformname", $startFormName);

#endregion

// find the form id in the request
// notice: it can be overwritten by previous form if there are errors. see setStartForm()
$newFormId = $ini["form"] ? : $ini['formularid']; // form is the new parameter name, formularid is also valid.

#region Load Project

if (!$ini["project"]) {
    // now it is mandatory to send the project name on each load
    echo "No project detected.";
    die;
}

$path = $ini["project"];
if(substr($path,0,strlen(shopInterface::getInstance()->getModulesDir()))!=shopInterface::getInstance()->getModulesDir())
{
    $path = shopInterface::getInstance()->getModulesDir().$path;
}

// setting absolute path of the current used project.
$hsConfig->setInterpreterValue("projectname", $path);

$parser       = cpfFileParser::getInstance();
$projectForms = $parser->parseCpf($path);
$hsConfig->setInterpreterValue("formulare", $projectForms);

if ($newFormId) {
    // see if the formId that was passed is not a real form id,
    // in that case, the form id might be a custom id.
    if (!array_key_exists($newFormId, $projectForms)) {
        // now adjust for the real form id if found.
        $newFormId = findAlternativeFormNames("bezeichnung", $projectForms)[$newFormId]
            ?? findAlternativeFormNames("customerid", $projectForms)[$newFormId]
            ?? $newFormId;
    }
}

function findAlternativeFormNames($property, $projectForms)
{
    return array_flip(array_filter(array_map(function ($formData) use($property) {
        /** @var baseTab $baseTab */
        $baseTab    = unserialize($formData["property"]);
        $properties = $baseTab->property;
        return trim($properties[$property]);
    }, $projectForms)));
}

// if no form id is found then either we are just arriving to this project, or there is a start form name present.
if (!$newFormId) {
    // sets: $errorStartForm
    $newFormId = getStartForm($projectForms, $startForm, $startFormName, $errorStartForm);
    // remember the start form for future page loads.
    $hsConfig->setInterpreterValue("startform", $newFormId);
}

/**
 * @param array  $projectForms   result from calling cpfFileParser::parseCpf
 * @param string $startForm      id of form, normally it is a uniqid
 * @param string $startFormName  name of form, can be customer id or title of tab.
 * @param string $errorStartForm id of form that couldn't be loaded.
 *
 * @return string  the resolved id of the form that should be loaded.
 */
function getStartForm($projectForms, $startForm, $startFormName, &$errorStartForm)
{
    $theFormId = null;

    // priority to startFormName. if exists and is found it overwrites startForm
    if ($startFormName) {
        foreach ($projectForms as $formId => $formData) {
            /** @var basetab $e */
            $e = unserialize($formData['property']);
            if (in_array($startFormName, [$e->getTabName(), $e->getCustomerId(), $e->getTabCustomerId()])) {
                $startForm = $formId;
                break;
            }
        }
    }
    // this is the id of a form, if not present it will look for the first form it can find.
    if ($startForm) {
        foreach ($projectForms as $formId => $formData) {
            if ($formId == $startForm) {
                $theFormId = $formId;
                break;
            }
        }
        if (!$theFormId) {
            $errorStartForm = $startForm;
        }
    }

    // if no start form present (could also mean there was an errorStartForm)
    // then just set the form id and form data to the first one found.
    if (!$theFormId) {
        $theFormId = key($projectForms);
    }

    return $theFormId;
}

// if project inside a module (normally all projects must be in modules now)
if ($projectBaseDir = $hsConfig->getProjectBaseDir()) {
    $otherScripts = glob("$projectBaseDir/scriptphp/*.php");
    foreach ($otherScripts as $otherScript) {
        require_once $otherScript;
    }
    $otherSqlParsers = glob("$projectBaseDir/sqlparser/*.php");
    foreach ($otherSqlParsers as $otherSqlParser) {
        require_once $otherSqlParser;
    }
}

#endregion

/* the form can start with parameters, which will write into the session at the first start */
$hsConfig->setInterpreterValue("startparam", $ini['startparam']);

$newnavi        = "";
$newindex1value = "";

// the previous behaviour was, if there was no old form id it used the new form id. this should still be the same.
$oldFormId = $ini["oldformid"] ? : $newFormId;

$oldAForm = $projectForms[$oldFormId];
/** @var basetab $oldForm */
$oldForm = unserialize($oldAForm['property']);
$hsConfig->setOldTab($oldForm);

/** @var basecontrol[] $oldoelements */
$oldoelements = [];
// initially tmpElements points to old elements
$tmpElements = &$oldoelements;

$oldnavi        = $ini['navi'];
$oldindex1value = $ini['index1value'];

// setting array old elements. it is widely used as: global $oldoelements from interfacephp.php classes
if ($oldAForm && is_array($oldAForm) && count($oldAForm["elements"])) {

    foreach ($oldAForm["elements"] as $elementId => $value) {
        $oldoelements[] = unserialize($value);
    }

    foreach ($oldoelements as $oe) {
        $oe->interpreterInit();
        $oe->setTab($oldForm);
    }
}

// this is the form that will be loaded. below this value can change to oldformularid in case validation failed.
switch ($oldnavi) {
    case "DELETE" :
        foreach ($oldoelements as $oe) {
            $oe->interpreterBeforeDelete();
        }
        foreach ($oldoelements as $oe) {
            $oe->interpreterDelete($oldForm->property['table'], $oldForm->property['colindex'], $oldindex1value);
        }
        foreach ($oldoelements as $oe) {
            $oe->interpreterAfterDelete();
        }
        break;
    case "DELETEKENNZEICHEN1" :
        foreach ($oldoelements as $oe) {
            $oe->interpreterBeforeDeleteKennzeichen1();
        }
        foreach ($oldoelements as $oe) {
            $oe->interpreterDeleteKennzeichen1($oldForm->property['table'], $oldForm->property['colindex'], $oldindex1value);
        }
        foreach ($oldoelements as $oe) {
            $oe->interpreterAfterDeleteKennzeichen1();
        }
        break;
    case "NEW" :
        $isfirstnew  = true;
        $newNavi     = "NEW_SAVE";
        $index1value = uniqid();
        break;
    case "NEW_SAVE" :
        $isnew = true;

        foreach ($oldoelements as $oe) {
            $oe->interpreterBeforeProveNew();
        }
        foreach ($oldoelements as $oe) {
            $e = $oe->interpreterProveNew($oldForm->property['table'], $oldForm->property['colindex'], $oldindex1value);
            if ($e) {
                $errorlist = array_merge($errorlist, $e);
            }
        }
        foreach ($oldoelements as $oe) {
            $oe->interpreterAfterProveNew();
        }
        if (count($errorlist) == 0) {
            if ($oldindex1value != "") {

                //create row with primary key and foreign keys
                $oldForm->interpreterNew($oldindex1value, $oldoelements);

                //call the event to all elements
                foreach ($oldoelements as $oe) {
                    $oe->interpreterBeforeSaveNew();
                }

                /*
                $cols = [];
                foreach ($oldoelements as $oe) {
                    if ($s = $oe->interpreterSaveNew($oldForm->property['table'], $oldForm->property['colindex'], $oldindex1value)) {
                        $cols["$s[col]"] = $s['value'];
                    }
                }
                */
                // temp. correct because people had used this as a feature.
                //$oldForm->interpreterInsertRecord($oldindex1value, $cols);
                $oldForm->interpreterUpdateRecordNew($oldindex1value, $oldoelements);

                foreach ($oldoelements as $oe) {
                    $oe->interpreterAfterSaveNew();
                }
                $index1value = "";
                $newNavi     = "";
                foreach ($oldoelements as $oe) {
                    $oe->interpreterFinishedSaveNew();
                }
            }
        } else {
            $newFormId   = $oldFormId;
            $index1value = $oldindex1value;
            $newNavi     = "NEW_SAVE";
        }
        break;
    case "EDIT":
        $isfirstedit = true;
        $index1value = $oldindex1value;
        $newNavi     = "EDIT_SAVE";
        break;
    case "EDIT_SAVE" :
        $isedit = true;

        foreach ($oldoelements as $oe) {
            $oe->interpreterBeforeProveEdit();
        }
        foreach ($oldoelements as $oe) {
            $e = $oe->interpreterProveEdit($oldForm->property['table'], $oldForm->property['colindex'], $oldindex1value);
            if ($e) {
                $errorlist = array_merge($errorlist, $e);
            }
        }
        foreach ($oldoelements as $oe) {
            $oe->interpreterAfterProveEdit();
        }

        $valid = true;
        if ($oldForm->property['passwordvalidation'] == "1" && $oldForm->property['passwordvalidation_class'] == "1") {
            $valid = false;
        }

        if (count($errorlist) == 0 && $valid) {
            if ($oldindex1value != "") {

                //call the event
                foreach ($oldoelements as $oe) {
                    $oe->interpreterBeforeSaveEdit();
                }

                /*
                $cols = [];
                foreach ($oldoelements as $oe) {
                    if ($s = $oe->interpreterSaveEdit($oldForm->property['table'], $oldForm->property['colindex'], $oldindex1value)) {
                        //$oldotab->interpreterUpdate($oldindex1value, $s['col'], $s['value']);
                        $cols["$s[col]"] = $s['value'];
                    }
                }
                */
                if (is_object($oldForm)) {
                    $oldForm->interpreterUpdateRecordEdit($oldindex1value, $oldoelements);
                    //$oldForm->interpreterUpdateRecord($oldindex1value, $cols);
                }

                //call the event
                foreach ($oldoelements as $oe) {
                    $oe->interpreterAfterSaveEdit();
                }

                $index1value = "";
                $newNavi     = "";

                //call the event
                foreach ($oldoelements as $oe) {
                    $oe->interpreterFinishedSaveEdit();
                }

                //Redirect to a given path
                if ($redirectValue) {
                    $url = $_SERVER['HTTP_HOST'] . "/" . $redirectValue;
                    echo "<script>window.top.location.href = '//$url';</script>";
                    exit();
                }
            }
        } else {
            $newFormId   = $oldFormId;
            $index1value = $oldindex1value;
            $newNavi     = "EDIT_SAVE";
        }
        break;
    case 'BULK_DELETE':
        foreach ($oldoelements as $oe) {
            $oe->interpreterBeforeBulkDelete();
        }
        foreach ($oldoelements as $oe) {
            $oe->interpreterBulkDelete($oldForm->property['table'], $oldForm->property['colindex'], $oldindex1value);
        }
        foreach ($oldoelements as $oe) {
            $oe->interpreterAfterBulkDelete();
        }
        break;
}

foreach ($oldoelements as $oe) {
    $oe->interpreterFinish();
}

// todo: find if this set is necessary, see if other parts of formedit read it.
$hsConfig->setInterpreterValue("formularid", $newFormId);

$newAForm = $projectForms[$newFormId];
/** @var baseTab $newForm */
$newForm = unserialize($newAForm['property']);

/** @var basecontrol[] $oelements */
$oelements = [];
// now tmpElements points to the new elements.
$tmpElements = &$oelements;

if (is_array($newAForm["elements"])) {

    foreach ($newAForm["elements"] as $elementData) {
        $oelements[] = unserialize($elementData);
    }

    foreach ($oelements as $oe) {
        $oe->interpreterInit();
        $oe->setTab($newForm);
        $oe->setInterpreterErrorlist($errorlist);
    }
}

if ($oelements) {
    foreach ($oelements as $oe) {
        if ($isfirstnew) {
            $oe->setInterpreterIsFirstNew();
        }
        if ($isfirstedit) {
            $oe->setInterpreterIsFirstEdit();
            $col               = $oe->getCol();
            $_REQUEST[$oe->id] = $newForm->interpreterLoad($index1value, $col);
        }
        if ($isnew) {
            $oe->setInterpreterIsNew();
        }
        if ($isedit) {
            $oe->setInterpreterIsEdit();
        }
    }
}

// this will be the form action to be posted.
//$formAction = $hsConfig->getBaseUrl() . "/?/$ini[project]";
$formAction  = $hsConfig->getBaseUrl() . "/interpreter.php?project=$ini[project]";
$otherParams = [//"form" => $newFormId,
];
// todo: move into function
if ($otherParams) {
    $formAction .= "&" . http_build_query($otherParams);
}

// parameters that must be included in the next page load, but not as url, but as hidden inputs.
$reloadParameters = ["startformname", "startform"];

// find all the kinds of elements that are present in the element array, in case they have static content to render.
$uniqueElements = [];
$uniqueElements = array_fill_keys(array_map(function ($e) {
    return $e->name;
}, $oelements), true);

// useful to see if form encryption type must be multi part or normal.
$hasFileUpload = count(array_filter($oelements, function ($e) {
        return preg_match('~fileupload~i', $e->name);
    })) > 0;

// see if any control has debug mode enabled.
$anyDebug             = $ini["debug"] || $newForm->property['debugmode'] || count(array_filter($oelements, function ($e) {
        return $e->property['debugmode'];
    })) > 0;
$debugBarBottom       = $anyDebug ? 300 : -7;
$debugBarResizeHeight = 7;

$stylesRelativePath = $hsConfig->getBaseUrl();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Formedit - <?php echo basename($hsConfig->getInterpreterValue("projectname")); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $hsConfig->isUtf8() ? 'UTF-8' : 'ISO-8859-15'; ?>">

    <meta http-equiv="cache-control" content="max-age=0"/>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="expires" content="0"/>
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT"/>
    <meta http-equiv="pragma" content="no-cache"/>

    <link type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/themes/flick/jquery-ui.css" rel="stylesheet"/>
    <link type="text/css" href="<?php echo $stylesRelativePath; ?>/css/jhtmlarea/jHtmlArea.css" rel="stylesheet"/>
    <link type="text/css" href="<?php echo $stylesRelativePath; ?>/css/anytime/anytime.css" rel="stylesheet"/>
    <link type="text/css" href="<?php echo $stylesRelativePath; ?>/css/interpreter.css" rel="stylesheet"/>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-migrate/1.4.1/jquery-migrate.js"></script>
    <script>jQuery.migrateMute</script>

    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
    <script src="<?php echo $stylesRelativePath; ?>/js/jHtmlArea-0.7.0.js"></script>
    <script src="<?php echo $stylesRelativePath; ?>/js/anytime.js"></script>
    <script src="<?php echo $stylesRelativePath; ?>/js/jquery.iframe-auto-height.plugin.1.9.0.min.js"></script>
    <script src="<?php echo $stylesRelativePath; ?>/js/js.cookie.js"></script>

</head>
<body style="overflow:hidden">
<?php
if ($errorStartForm !== false)
    echo "<span style='font-weight:bold; color:red; '>Can´t find start form. ($errorStartForm)</span>";
?>
<div data-fe-console-resizes="bottom"
     style="position:fixed;left:0;right:0;top:0;bottom:<?php echo $debugBarBottom; ?>px; min-height:10%; overflow:auto;margin-bottom:<?php echo $debugBarResizeHeight; ?>px">
    <form id="formular" action="<?php echo $formAction; ?>" method="post" <?php echo ($hasFileUpload?'enctype="multipart/form-data"':''); ?>>
        <?php
        if(isset($_REQUEST['cpf-no-title']))
            echo '<input type="hidden" name="cpf-no-title">';
        ?>

        <!-- this will be received in the next request and cannot change in the browser. -->
        <input type="hidden" name="oldformid" value="<?php echo $newFormId; ?>">
        <?php
        foreach ($reloadParameters as $rp)
        {
            if ($ini[$rp])
                {
                echo '<input type="hidden" name="'.$rp.'" value="'.$ini[$rp].'">';
                }
        }
        ?>

        <!-- no name, this is not for automatic posting, it is for javascript to find the project name -->
        <input type="hidden" id="fe-project" value="<?php echo $ini['project']; ?>">

        <?php
        echo $hsConfig->getInterpreterParameterPost();

        foreach ($oelements as $oe) {
            echo $oe->interpreterBeforeRender();
        }
        foreach ($oelements as $oe) {
            echo $oe->getInterpreterRender();
        }
        foreach ($oelements as $oe) {
            echo $oe->interpreterAfterRender();
        }
        foreach ($oelements as $oe) {
            echo $oe->interpreterFinish();
        }

        /** @var basecontrol $name */
        foreach ($uniqueElements as $name => $true) {
            echo $name::interpreterFinish_static();
        }

        ?>
    </form>
    <?php
    if ($newForm->property['passwordvalidation'] == "1" && $newForm->property['passwordvalidation_class'] != "")
    {
    ?>
        <div style="display:none;">
            <div id="dialog-form-passwordvalidation" title="Password">
                <p id="dialog-form-passwordvalidation-description" class="validateTips"></p>
                <fieldset style="border:0; ">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="dialog-form-passwordvalidation-password" value=""
                           class="text ui-widget-content ui-corner-all">
                </fieldset>
            </div>
        </div>
    <?php
    }
    ?>
</div>

<script type="text/javascript">
    // the global formedit namespace.
    window.fe = {};

    <?php if($newForm->property['passwordvalidation'] == "1" && $newForm->property['passwordvalidation_class'] != "")
    {
    ?>

    var params = $("#formular").serialize();
    $.ajax({
        type    : "POST",
        cache   : false,
        url     : "interpreter_interfacevalidation_init.php",
        data    : params,
        dataType: "html",
        success : function (data) {
            if (data != "")
                alert('interpreter_interfacevalidation_init:' + data);
        }
    });

    var dialogpasswordvalidation = $("#dialog-form-passwordvalidation").dialog({
        autoOpen: false,
        height  : 200,
        width   : 350,
        modal   : true,
        buttons : {
            "Ok"  : function () {
                var p = $('#dialog-form-passwordvalidation-password').val();

                if (p != "") {
                    //password request
                    var params = $("#formular").serialize();
                    params += '&formularid=<?php echo $newFormId; ?>';
                    params += "&interpreter_interfacevalidation_passwordapproval=" + p;
                    //alert(params);

                    $.ajax({
                        type    : "POST",
                        cache   : false,
                        url     : "interpreter_interfacevalidation_passwordapproval.php",
                        data    : params,
                        dataType: "html",
                        success : function (data) {
                            if (data == "1") {
                                passwordvalidation_allow = true;
                                $("#formular").submit();
                            }
                            else {
                                alert('Wrong password');
                            }
                        }
                    });
                }

            },
            Cancel: function () {
                dialogpasswordvalidation.dialog("close");
            }
        },
        close   : function () {
            $('#dialog-form-passwordvalidation-password').val('');
        }
    });

    var passwordvalidation_allow = false;
    $("#formular").submit(function (e) {

        if (passwordvalidation_allow == true)
            return true;

        e.preventDefault();

        //validate form
        var params = $(this).serialize();
        params += '&formularid=<?php echo $newFormId; ?>';
        $.ajax({
            type    : "POST",
            cache   : false,
            url     : "interpreter_interfacevalidation_validate.php",
            data    : params,
            dataType: "html",
            success : function (data) {
                if (data != "") {
                    //p=prompt(data);
                    $('#dialog-form-passwordvalidation-description').html(data);
                    dialogpasswordvalidation.dialog("open");
                }
                else {
                    passwordvalidation_allow = true;
                    $("#formular").submit();
                }
            }
        });

        return false; //is superfluous, but I put it here as a fallback
    });

    <?php } ?>

    // global namespace for formedit elements to store functions and values.
    fe.e   = {};
    fe.doc = $(document); // shortcut to attack events to document.

    // elements js. one per project:

    <?php

    /** @var basecontrol $name */
    foreach ($uniqueElements as $name => $true) {
        echo $name::interpreterFinishJavascript_static();
    }

    ?>

    // elements js. one per instance.

    <?php

    foreach ($oelements as $oe) {
        echo $oe->interpreterFinishJavascript();
    }

    ?>

    // end elements js:

    $(function () {
        $(".enableelement_clipboard").click(function () {
            try {
                if($(this).parent().find('input').length)
                {
                    var v = $(this).parent().find('input').val();
                    alert(v);
                }
            }
            catch (err) {
            }
        });
    });

</script>
<?php

#region Formedit Debug
if($anyDebug) {

?>
<!-- debug -->
<div data-fe-console-resizes="height"
     style="position:fixed;left:0;right:0;bottom:0;height:<?php echo max($debugBarBottom, 0); ?>px;max-height:90%; overflow:auto;background-color:#fafafa;padding:0 <?php echo max(0, min($debugBarBottom, 10)); ?>px 0;box-sizing:border-box">
    <!-- interpreter -->
    <div>
        Contents of Ini:
        <!-- print contents of ini -->
        <pre><?php echo htmlentities(implode("\n", array_map("ltrim", array_filter(explode("\n", print_r($ini, true)), function ($v) {
                return preg_match('~^ ~', $v);
            })))); ?></pre>
    </div>

    <table style="border-collapse:collapse;width:100%">
        <tr style="border:1px solid gray;">
            <th width="150" style="text-align:left">Id</th>
            <th style="text-align:left">Name</th>
            <th style="text-align:left">Debug Info</th>
        </tr>
        <!-- old elements -->

        <?php
        foreach ([$oldoelements, $oelements] as $i => $els) { ?>
            <tr style="border:1px solid gray;">
                <th width="150" style="text-align:left" colspan="3"><?php echo $i ? "" : "Old"; ?> elements</th>
            </tr>
            <?php foreach ($els as $e) { $di = $e->getDebugInfo(); ?>
                <tr style="border:1px solid gray;">
                    <td width="150" data-fedi-toggle style="cursor:pointer"><?php echo $di["id"]; ?></td>
                    <td width="200"><?php echo $di["name"]; ?></td>
                    <td><span data-fedi style=white-space:pre-wrap;font-family:monospace><?php echo $di["debugInfo"] ? '' : "-- no debug --"; ?></span></td>
                </tr>
            <?php } ?>
        <?php } ?>
    </table>

</div>
<div data-fe-console-resizes="bottom" data-fe-console-resize
     style="background:lightgray;position:fixed;left:0;right:0;
             bottom:<?php echo $debugBarBottom; ?>px;height:<?php echo $debugBarResizeHeight; ?>px;
             cursor:ns-resize;user-select:none">
    <div style="width:15px;margin:0 auto">
        <?php
        for ($i = 0; $i < 3; $i++)
        {
        ?>
            <div style="height:1px;margin-top:1px;background-color:gray;"></div>
        <?php
        }
        ?>
    </div>
</div>
<script>

    // behaviour for formedit debug console.
    fe.doc.on("click", "[data-fedi-toggle]", function (e) {
        // toggle show/hide debug rows
        $(this).parent().find("[data-fedi]").slideToggle();
    });
    fe.doc.on("mousemove", function (e) {
        // resize the bottom bar on mouse drag.
        if (window.feConsoleResize) {
            let h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
            feConsoleDoResize(Math.max(h - e.clientY - window.feConsoleResize, 0));
            e.preventDefault();
            window.feConsoleJustClicked = 0;
        }
    });
    fe.doc.on("mousedown", '[data-fe-console-resize]', function (e) {
        $("body").css("cursor", "ns-resize");
        if (e.button !== 0) return; // just left click please
        window.feConsoleResize      = this.offsetHeight - e.offsetY; // status for dragging or not
        window.feConsoleJustClicked = 1;
    });
    fe.doc.on("mouseup", function (e) {
        $("body").css("cursor", "");
        window.feConsoleResize = false; // status for dragging or not
        if (window.feConsoleJustClicked) {
            feConsoleDoResize(10);
        }
        window.feConsoleJustClicked = 0;
    });

    if($('[data-fe-console-resize]').length > 0)
    {
        let h = $('[data-fe-console-resizes="height"]').height();
        $('[data-fe-console-resize]').css('bottom',h + 'px')
    }

    function feConsoleDoResize(newValue) {
        $("[data-fe-console-resizes]").each(function () {
            let $this = $(this);
            $this.css($this.data("feConsoleResizes"), newValue + "px");
        });
    }

</script>
<?php

}
#endregion

?>

</body>
</html>
