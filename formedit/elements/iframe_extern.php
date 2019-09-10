<?php

class iframe_extern extends basecontrol
{
    var $name = "iframe_extern";

    var $editorname = "iFrame Extern";
    var $editorcategorie = "Container";
    var $editorshow = true;
    var $editordescription = 'iFrame, that call php script in the folder "scriptiframe2". The current index will attach to the url.';


    public function getInterpreterRender()
    {
        $e = "";
        $hsconfig = getHsConfig();

        //default values
        $this->property['borderless'] = $this->property['borderless']??true;

        // test if parent form has saved it´s data
        $bIsNewMode = false;
        if ($this->property['readonlyuntilsaved']) {
            $oTab = $this->getTab();
            $sql = "select count(*) from `".$oTab->getTableName()."` where `".$oTab->getColIndex()."`='{$hsconfig->getIndex1Value()}'";
            if ($hsconfig->getScalar($sql) == "0") {
                $bIsNewMode = true;
            }
        }

        $css = array(
            "width"    => $this->property['fixwidth'] == "0" ? "calc(100% - ".($this->left * 2)."px)" : "{$this->width}px",
            "position" => "absolute",
            "left"     => "{$this->left}px",
            "top"      => "{$this->top}px",
        );
        if (array_key_exists($this->id, $this->ainterpretererrorlist)) {
            $css["background-color"] = "red";
        }

        $css["overflow"] = 'hidden';
        $css["padding"] = ($this->property['borderless'] ? '0px' : '10px');
        $css["height"] = "{$this->height}px";
        $css["line-height"] = "{$this->height}px";
        $css['box-sizing'] = "border-box";
        if(!$this->property['borderless'])
            $css["border"] = "1px solid #ccc";

        if ($this->property['invisible']) {
            $css["display"] = "none";
        }

        $css = $this->buildCssString($css);

        // always on top of everything the change that the user wants.
        if ($this->property['style']) {
            $css = $this->property['style']." ".$css;
        }

        $iframeCss = $this->buildCssString(array(
            "width"  => '100%',
            "height" => '100%',
        ));
        /*$iframeClass = $this->property['autoheight'] ? "iframeautoheight" : "";*/
        $iframeClass = "";


        $tabIndex = $this->property['taborder'];

        if ($this->property['readonly']) {
            //display this, when you was set as "readonly"
            $e = <<<html
            <div data-customeridbox="{$this->getCustomerId()}" data-hasparentcontrol="{$this->getParentControl()}" 
                class="{$this->property['classname']}" id="$this->id" style="{$this->getParentControlCss()};$css">
                <div data-customerid="{$this->getCustomerId()}" id="iframe$this->id" style="$iframeCss" class="$iframeClass" tabindex="$tabIndex">
                    <div style="text-align:center; font-size:16px; color:darkgray; ">Disabled</div>
                </div>
            </div>
html;
        } elseif ($this->property['readonlyuntilsaved'] && $bIsNewMode) {
            //display this, when you was set as "disabled until the parent form was successfully saved"
            $e = <<<html
            <div data-customeridbox="{$this->getCustomerId()}" data-hasparentcontrol="{$this->getParentControl()}" 
                class="{$this->property['classname']}" id="$this->id" style="{$this->getParentControlCss()};$css">
                <div data-customerid="{$this->getCustomerId()}" id="iframe$this->id" style="$iframeCss" class="$iframeClass" tabindex="$tabIndex">
                    <div style="text-align:center; font-size:16px; color:darkgray; ">You have to save first</div>
                </div>
            </div>
html;
        } else {

            $url = $this->property['filename'];
            if (strpos($url, "?") === false) {
                $url .= "?";
            } else {
                $url .= "&";
            }

            $url = $hsconfig->parseSQLString($url);

            if (str_contains($url, 'url:')) {
                $url = url(str_replace('url:', '', $url));
            } else {
                $url = $hsconfig->getProjectBaseUrl()
                    ."/scriptiframe2/{$url}index1value={$hsconfig->getIndex1Value()}&index2value={$hsconfig->getIndex2Value()}&uid="
                    .uniqid();
            }


            $e = <<<html
            <div data-customeridbox="{$this->getCustomerId()}" data-hasparentcontrol="{$this->getParentControl()}" 
                class="{$this->property['classname']}" id="$this->id" style="{$this->getParentControlCss()};$css">
                <iframe
                    data-customerid="{$this->getCustomerId()}" 
                    scrolling="yes" 
                    src="$url" 
                    frameborder="0" 
                    id="iframe$this->id" 
                    style="$iframeCss" 
                    class="$iframeClass" 
                    tabindex="$tabIndex">
                </iframe>
            </div>
html;
            /*
            $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'"
                class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' '
                .(array_key_exists($this->id, $this->ainterpretererrorlist) ? 'background-color:red; ' : '').' position:absolute; left:'
                .$this->left.'px; top:'.$this->top.'px; width:'.($this->property['fixwidth'] == "0" ? "calc(100% - ".($this->left * 2)."px)"
                    : "{$this->width}px").'; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']
                == "1" ? ' display:none; ' : '').'">
                <iframe 
                    data-customerid="'.$this->getCustomerId().'" 
                    scrolling="yes" 
                    src="'.$url.'" 
                    frameborder="0" 
                    id="iframe'.$this->id.'" 
                    style="border:'.($this->property['css'] == '' ? '0px solid black;' : '1px solid black;').' width:100%; height:'
                .$this->height.'px; "
                    tabindex="'.$this->property['taborder'].'"
                ></iframe>
            </div>';
            */
        }

        return $e;
    }

    public function getEditorRender($text = "")
    {
        return parent::getEditorRender($this->property['bezeichnung']);
    }

    public function getEditorProperty()
    {
        $html = '';
        $html .= parent::getEditorPropertyHeader();
        $html .= parent::getEditorProperty_Textbox("Script iFrame<br>For BASE Urls, prepend 'url:' like <b>url:/base/products/#INDEX1#/resubmissions</b>",
            'filename');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Frameless", 'borderless');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Disabled", 'readonly');
        $html .= parent::getEditorProperty_Checkbox("Disabled until parent form successfully saved", 'readonlyuntilsaved');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Fix width from the element, otherwise 100% - 2 times left", 'fixwidth', '1');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorPropertyFooter(true, false, true);

        return $html;
    }

}
