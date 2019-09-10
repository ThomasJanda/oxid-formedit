<?php

class xmlview extends basecontrol
{
    var $name="xmlview";

    var $editorname="XML-View";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Shows a xml-code from the database';

    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        return false;
    }
    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        return false; 
    }
    
    public function getInterpreterRender()
    {
        $value="";
        if(parent::getInterpreterIsFirstNew())
        {
        }
        else
        {
            $value=parent::getInterpreterRequestValue();

            if(parent::getInterpreterIsFirstEdit() && $this->property['compress'])
            {
                //was load from the db
                if($value!="")
                    $value = gzuncompress(base64_decode($value));
            }
        }

        $e = '<div data-customerid="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().'overflow:scroll; '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
        <pre>'.htmlentities($this->formatXmlString($value)).'</pre>
        </div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorPropertyFooter(true,true,false);
        return $html;
    }
    
private function formatXmlString($xml) {

  // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
  $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

  // now indent the tags
  $token      = strtok($xml, "\n");
  $result     = ''; // holds formatted version as it is built
  $pad        = 0; // initial indent
  $matches    = array(); // returns from preg_matches()

  // scan each line and adjust indent based on opening/closing tags
  while ($token !== false) :

    // test for the various tag states

    // 1. open and closing tags on same line - no change
    if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
      $indent=0;
    // 2. closing tag - outdent now
    elseif (preg_match('/^<\/\w/', $token, $matches)) :
      $pad--;
    // 3. opening tag - don't pad this one, only subsequent tags
    elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
      $indent=1;
    // 4. no indentation needed
    else :
      $indent = 0;
    endif;

    // pad the line with the required number of leading spaces
    $line    = str_pad($token, strlen($token)+$pad, ' ', STR_PAD_LEFT);
    $result .= $line . "\n"; // add to the cumulative result, with linefeed
    $token   = strtok("\n"); // get the next token
    $pad    += $indent; // update the pad size for subsequent lines
  endwhile;

  return $result;
}

}

?>