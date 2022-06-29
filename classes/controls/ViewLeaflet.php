<?php
class ViewLeaflet extends ViewUserControl
{
	public function initUserControl()
	{

        $this->width = 300;
        $this->height =300;
        $this->zoomLevel = 4;
        $this->ignKey = "choisirgeoportail";
        $this->geometryType = "POINT";

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

        $this->addJSControlSetting("width", $this->width);
        $this->addJSControlSetting("height", $this->height);
        $this->addJSControlSetting("zoomLevel", $this->zoomLevel);
        $this->addJSControlSetting("ignKey", $this->ignKey);
        $this->addJSControlSetting("geometryType", $this->geometryType);
	}

	public function showDBValue(&$data, $keylink, $html = true)
	{
	    $key = substr($keylink,6);
        $result = '<div id="viewmap'.$key.'" data-zoom="'.$this->zoomLevel.'" data-page="'.$this->pageObject->pSet->_page.'" data-type="'.$this->geometryType.'" data-key="'.$this->ignKey.'"
        data-value="'.$data[$this->field].'" data-field="'.$this->field.'" data-style="width:'.$this->width.'px;height:'.$this->height.'px">
        </div>
        <div id="map'.$key.'" data-key="'.$key.'" style="width:'.$this->width.'px;height:'.$this->height.'px"></div>';

        return $result;
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