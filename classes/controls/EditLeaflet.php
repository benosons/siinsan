<?php
class EditLeaflet extends UserControl
{
    public function initUserControl()
    {
        $this->positionMapLat = 1.14;
        $this->positionMapLng = 43.4;
        $this->displayXY = "Oui";
        $this->latField = "Y";
        $this->lonField = "X";
        $this->width = 300;
        $this->height =300;
        $this->zoomLevel = 8;
        $this->ignKey = "choisirgeoportail";
        $this->geometryType = "POINT";

        if (isset($this->settings["positionMapLat"]))
            $this->positionMapLat = $this->settings["positionMapLat"];
        if (isset($this->settings["positionMapLng"]))
            $this->positionMapLng = $this->settings["positionMapLng"];
        if (isset($this->settings["displayXY"]))
            $this->displayXY = $this->settings["displayXY"];
        if (isset($this->settings["latField"]))
            $this->latField = $this->settings["latField"];
        if (isset($this->settings["lonField"]))
            $this->lonField = $this->settings["lonField"];
        if (isset($this->settings["width"]))
            $this->width = $this->settings["width"];
        if (isset($this->settings["height"]))
            $this->height = $this->settings["height"];
        if (isset($this->settings["zoomLevel"]))
            $this->zoomLevel = $this->settings["zoomLevel"];
        if (isset($this->settings["ignKey"]))
            $this->ignKey = $this->settings["ignKey"];
        if (isset($this->settings["geometryType"]))
            $this->geometryType = $this->settings["geometryType"];
        //Recuperation de la valeur du champ si il existe
        if (isset($values["FieldName"]))
            $this->ignKey = $this->settings["ignKey"];

        $this->addJSSetting("positionMapLat", $this->positionMapLat);
        $this->addJSSetting("positionMapLng", $this->positionMapLng);
        $this->addJSSetting("displayXY", $this->displayXY);
        $this->addJSSetting("latField", $this->latField);
        $this->addJSSetting("lonField", $this->lonField);
        $this->addJSSetting("width", $this->width);
        $this->addJSSetting("height", $this->height);
        $this->addJSSetting("zoomLevel", $this->zoomLevel);
        $this->addJSSetting("ignKey", $this->ignKey);
        $this->addJSSetting("geometryType", $this->geometryType);
    }

    public function buildUserControl($value, $mode, $fieldNum = 0, $validate, $additionalCtrlParams, $data)
    {
        $display = 'block';
        if (($this->displayXY === "Non")||($this->geometryType !== 'POINT')){
            $display = 'none';
        }
        $mapData = "map".$this->pageObject->jsKeys[0];
        $mapId = "mapid".$this->pageObject->jsKeys[0];
        $x = "X".$this->pageObject->jsKeys[0];
        $y = "Y".$this->pageObject->jsKeys[0];

            echo
                '<div id="'.$mapData.'" class="test" data-positionLat="'.$this->positionMapLat.'"
                data-positionLng="'.$this->positionMapLng.'" data-zoom="'.$this->zoomLevel.'" data-page="'.$this->pageObject->pSet->_page.'" data-key="'.$this->ignKey.'"
                data-field="'.$this->cfield.'" data-value="'.$value.'" data-id="'.$this->pageObject->jsKeys[0].'" data-type="'.$this->geometryType.'" data-mapid="'.$mapId.'">
                <div id="'.$mapId.'" class="showmap" style="width:'.$this->width.'px;height:'.$this->height.'px"></div>
                <div class="row" style="margin-top: 15px">
		            <div class="col-md-2" style="display:'.$display.';">
		                <label>'.$this->lonField.'</label>
		            </div>
		            <div class="col-md-2" style="display:'.$display.';">
		                <input class="Lng_position" id="'.$x.'" type="text" />
                    </div>
                </div>
                <div class="row" style="margin-top: 10px">    
		            <div class="col-md-2" style="display:'.$display.';">
		                <label>'.$this->latField.'</label>
		            </div>
		            <div class="col-md-2"style="display:'.$display.';">
		                <input class="Lat_position" id="'.$y.'" type="text" />
		            </div>
		        </div>
		        <input id="'.$this->cfield.'" name="'.$this->cfield.'" class="output" type="hidden" value="">
                </div>';
    }

    function getUserSearchOptions()
    {
        return array(EQUALS, STARTS_WITH, NOT_EMPTY, NOT_EQUALS);
    }

    /**
     * addJSFiles
     * Add control JS files to page object
     */
    function addJSFiles()
    {
		$this->pageObject->AddJSFile("include/js/jquery-ui.js");
		$this->pageObject->AddJSFile("include/js/leaflet.js","include/js/jquery-ui.js");
		$this->pageObject->AddJSFile("include/js/http_ignf.github.io_geoportal-extensions_leaflet-latest_dist_GpPluginLeaflet.js","include/js/leaflet.js");
		$this->pageObject->AddJSFile("include/js/leaflet.measurecontrol.js","include/js/http_ignf.github.io_geoportal-extensions_leaflet-latest_dist_GpPluginLeaflet.js");
		$this->pageObject->AddJSFile("include/js/leaflet-geocoder-ban.js","include/js/leaflet.measurecontrol.js");
    }

    /**
     * addCSSFiles
     * Add control CSS files to page object
     */
    function addCSSFiles()
    {
        $this->pageObject->AddCSSFile("include/css/http_ignf.github.io_geoportal-extensions_leaflet-latest_dist_GpPluginLeaflet.css");
        $this->pageObject->AddCSSFile("include/css/http_unpkg.com_leaflet@1.6.0_dist_leaflet.css");
        $this->pageObject->AddCSSFile("include/css/leaflet-geocoder-ban.min.css");
        $this->pageObject->AddCSSFile("include/css/leaflet.measurecontrol.css");
    }
}
?>