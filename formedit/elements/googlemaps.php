<?php

class googlemaps extends basecontrol
{
    var $name="googlemaps";

    var $editorname="Google Maps";
    var $editorcategorie="Style";
    var $editorshow=true;
    var $editordescription='Display google maps in a iframe. Latitude, Longitude have to be in a textbox and the information is read from them.';

    public function getInterpreterRender()
    {
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().' '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.$this->property['style'].' '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <span style="color:blue; cursor:pointer; text-decoration:underline; " onclick="callGoogleMaps'.$this->id.'(); ">Googe maps</span>
        </div>
        <script type="text/javascript">
            var google_maps_url'.$this->id.'="https://www.google.com.mx/maps?";
            function callGoogleMaps'.$this->id.'()
            {
                var lat = $("div[data-customeridbox='.$this->property['cidlatitude'].'] input").val();
                var lng = $("div[data-customeridbox='.$this->property['cidlongitude'].'] input").val();
                var url = google_maps_url'.$this->id.' + "q=" + lat + "," + lng + "&z=12";

                window.open(url,"googlemaps");
            }
        </script>';
        return $e;
    }

    public function getEditorRender($text = "")
    {
        return parent::getEditorRender($this->property['bezeichnung']);
    }


    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Customer ID Latitude",'cidlatitude');
        $html.=parent::getEditorProperty_Textbox("Customer ID Longitude",'cidlongitude');
        $html.=parent::getEditorPropertyFooter(true,false,false,true);
        return $html;
    }

}