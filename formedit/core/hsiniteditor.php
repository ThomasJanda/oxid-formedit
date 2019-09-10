<?php

function editor_refreshelementform($id)
{
    $hsconfig=getHsConfig();
    
    $e = unserialize($_SESSION['editor'][$id]['property']);
    $name=$e->getTabName();
    if(trim($name)=="")
        $name="Formular".$e->getTabId();
    else
        $name.=" (".$e->getTabId().")";
    return $name;
}
function editor_loadelement($containerid, $id)
{
    $ret="";
    
    $e = $_SESSION['editor'][$containerid]['elements'][$id];
    $e = unserialize($e);
    if(is_object($e))
    {
        if(get_class($e)!="stdClass")
        {
            try
            {
                $ret=$e->getEditorRender();
            }
            catch (Exception $e)
            {
                $ret="";
            }
        }
        else
        {
            $e = unserialize($_SESSION['editor'][$containerid]['property']);
            $name=$e->getTabName();
            echo 'alert("a element couldn´t be load on formular ´'.$name.'´"); ';
        }
    }
    return $ret;
}




/**
 * Pretty-print JSON string
 *
 * Use 'format' option to select output format - currently html and txt supported, txt is default
 * Use 'indent' option to override the indentation string set in the format - by default for the 'txt' format it's a tab
 *
 * @param string $json Original JSON string
 * @param array $options Encoding options
 * @return string
 */
function json_pretty($json, $options = array())
{
    $tokens = preg_split('|([\{\}\]\[,])|', $json, -1, PREG_SPLIT_DELIM_CAPTURE);
    $result = '';
    $indent = 0;

    $format = 'txt';

    //$ind = "\t";
    $ind = "    ";

    if (isset($options['format'])) {
        $format = $options['format'];
    }

    switch ($format) {
        case 'html':
            $lineBreak = '<br />';
            $ind = '&nbsp;&nbsp;&nbsp;&nbsp;';
            break;
        default:
        case 'txt':
            $lineBreak = "\n";
            //$ind = "\t";
            $ind = "    ";
            break;
    }

    // override the defined indent setting with the supplied option
    if (isset($options['indent'])) {
        $ind = $options['indent'];
    }

    $inLiteral = false;
    foreach ($tokens as $token) {
        if ($token == '') {
            continue;
        }

        $prefix = str_repeat($ind, $indent);
        if (!$inLiteral && ($token == '{' || $token == '[')) {
            $indent++;
            if (($result != '') && ($result[(strlen($result) - 1)] == $lineBreak)) {
                $result .= $prefix;
            }
            $result .= $token . $lineBreak;
        } elseif (!$inLiteral && ($token == '}' || $token == ']')) {
            $indent--;
            $prefix = str_repeat($ind, $indent);
            $result .= $lineBreak . $prefix . $token;
        } elseif (!$inLiteral && $token == ',') {
            $result .= $token . $lineBreak;
        } else {
            $result .= ( $inLiteral ? '' : $prefix ) . $token;

            // Count # of unescaped double-quotes in token, subtract # of
            // escaped double-quotes and if the result is odd then we are 
            // inside a string literal
            if ((substr_count($token, "\"") - substr_count($token, "\\\"")) % 2 != 0) {
                $inLiteral = !$inLiteral;
            }
        }
    }
    return $result;
}