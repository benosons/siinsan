<?php
class UserControl extends EditControl
{
	function buildControl($value, $mode, $fieldNum, $validate, $additionalCtrlParams, $data)
	{
		parent::buildControl($value, $mode, $fieldNum, $validate, $additionalCtrlParams, $data);
		$this->buildUserControl($value, $mode, $fieldNum, $validate, $additionalCtrlParams, $data);
		$this->buildControlEnd($validate, $mode);
	}
	
	public function buildUserControl($value, $mode, $fieldNum, $validate, $additionalCtrlParams, $data)
	{
	}
	
	public function initUserControl()
	{		
	}
	
	function getUserSearchOptions()
	{
		return array();		
	}
	
	/**
	 * Form the control specified search options array and built the control's search options markup
	 * @param String selOpt		The search option value	
	 * @param Boolean not		It indicates if the search option negation is set 	
	 * @param Boolean both		It indicates if the control needs 'NOT'-options
	 * @return String			A string containing options markup
	 */		
	function getSearchOptions($selOpt, $not, $both)
	{
		return $this->buildSearchOptions($this->getUserSearchOptions(), $selOpt, $not, $both);		
	}
	
	function init()
	{
	$tName = $this->pageObject->tName;
	$field = $this->field;
	if( $this->pageObject->pSet ) {
		if($this->pageObject->pageType == PAGE_SEARCH && $this->pageObject->pSet->getTableType() == PAGE_DASHBOARD)
		{
			$dashFields = $this->pageObject->pSet->getDashboardSearchFields();
			$tName = $dashFields[$field][0]["table"];
			$field = $dashFields[$field][0]["field"];
		}
	}
				if($tName=="dra_data_genangan" && $field=="koordinat")
	{
		$this->settings["positionMapLat"] = -6.24; // Peta posisi lintang (antara -90 dan 90)
$this->settings["positionMapLng"] = 106.8; // Peta posisi bujur (angka antara -180 dan 180)
$this->settings["displayXY"] = "Oui"; // Tampilan bidang X dan Y, "Oui" atau "Non"
$this->settings["latField"] = "Y"; // Nama garis latitude
$this->settings["lonField"] = "X"; // Nama garis longitude
$this->settings["width"] = 300; // lebar peta
$this->settings["height"] = 300; // tinggi peta
$this->settings["zoomLevel"] = 8; // zoom peta saat inisialisasi (bilangan bulat antara 0 dan 18)
$this->settings["ignKey"] = "choisirgeoportail"; // Kunci IGN
$this->settings["geometryType"] = "POLYGON"; // Type : POINT, MULTIPOINT, LINE, MULTILINE, POLYGON, MULTIPOLYGON;
		return;
	}	
	$tName = $this->pageObject->tName;
	$field = $this->field;
	if( $this->pageObject->pSet ) {
		if($this->pageObject->pageType == PAGE_SEARCH && $this->pageObject->pSet->getTableType() == PAGE_DASHBOARD)
		{
			$dashFields = $this->pageObject->pSet->getDashboardSearchFields();
			$tName = $dashFields[$field][0]["table"];
			$field = $dashFields[$field][0]["field"];
		}
	}
				if($tName=="sigdrainase_saluran_drainase" && $field=="koordinat")
	{
		$this->settings["positionMapLat"] = -6.24; // Peta posisi lintang (antara -90 dan 90)
$this->settings["positionMapLng"] = 106.8; // Peta posisi bujur (angka antara -180 dan 180)
$this->settings["displayXY"] = "Oui"; // Tampilan bidang X dan Y, "Oui" atau "Non"
$this->settings["latField"] = "Y"; // Nama garis latitude
$this->settings["lonField"] = "X"; // Nama garis longitude
$this->settings["width"] = 300; // lebar peta
$this->settings["height"] = 300; // tinggi peta
$this->settings["zoomLevel"] = 8; // zoom peta saat inisialisasi (bilangan bulat antara 0 dan 18)
$this->settings["ignKey"] = "choisirgeoportail"; // Kunci IGN
$this->settings["geometryType"] = "LINE"; // Type : POINT, MULTIPOINT, LINE, MULTILINE, POLYGON, MULTIPOLYGON;
		return;
	}	
	$tName = $this->pageObject->tName;
	$field = $this->field;
	if( $this->pageObject->pSet ) {
		if($this->pageObject->pageType == PAGE_SEARCH && $this->pageObject->pSet->getTableType() == PAGE_DASHBOARD)
		{
			$dashFields = $this->pageObject->pSet->getDashboardSearchFields();
			$tName = $dashFields[$field][0]["table"];
			$field = $dashFields[$field][0]["field"];
		}
	}
				if($tName=="sigdrainase_kontruksi_drainase" && $field=="koordinat")
	{
		$this->settings["positionMapLat"] = -6.24; // Peta posisi lintang (antara -90 dan 90)
$this->settings["positionMapLng"] = 106.8; // Peta posisi bujur (angka antara -180 dan 180)
$this->settings["displayXY"] = "Oui"; // Tampilan bidang X dan Y, "Oui" atau "Non"
$this->settings["latField"] = "Y"; // Nama garis latitude
$this->settings["lonField"] = "X"; // Nama garis longitude
$this->settings["width"] = 300; // lebar peta
$this->settings["height"] = 300; // tinggi peta
$this->settings["zoomLevel"] = 8; // zoom peta saat inisialisasi (bilangan bulat antara 0 dan 18)
$this->settings["ignKey"] = "choisirgeoportail"; // Kunci IGN
$this->settings["geometryType"] = "LINE"; // Type : POINT, MULTIPOINT, LINE, MULTILINE, POLYGON, MULTIPOLYGON;
		return;
	}	
	$tName = $this->pageObject->tName;
	$field = $this->field;
	if( $this->pageObject->pSet ) {
		if($this->pageObject->pageType == PAGE_SEARCH && $this->pageObject->pSet->getTableType() == PAGE_DASHBOARD)
		{
			$dashFields = $this->pageObject->pSet->getDashboardSearchFields();
			$tName = $dashFields[$field][0]["table"];
			$field = $dashFields[$field][0]["field"];
		}
	}
				if($tName=="sigdrainase_sumur" && $field=="koordinat")
	{
		$this->settings["positionMapLat"] = -6.24; // Peta posisi lintang (antara -90 dan 90)
$this->settings["positionMapLng"] = 106.8; // Peta posisi bujur (angka antara -180 dan 180)
$this->settings["displayXY"] = "Oui"; // Tampilan bidang X dan Y, "Oui" atau "Non"
$this->settings["latField"] = "Y"; // Nama garis latitude
$this->settings["lonField"] = "X"; // Nama garis longitude
$this->settings["width"] = 300; // lebar peta
$this->settings["height"] = 300; // tinggi peta
$this->settings["zoomLevel"] = 8; // zoom peta saat inisialisasi (bilangan bulat antara 0 dan 18)
$this->settings["ignKey"] = "choisirgeoportail"; // Kunci IGN
$this->settings["geometryType"] = "POINT"; // Type : POINT, MULTIPOINT, LINE, MULTILINE, POLYGON, MULTIPOLYGON;
		return;
	}	
	$tName = $this->pageObject->tName;
	$field = $this->field;
	if( $this->pageObject->pSet ) {
		if($this->pageObject->pageType == PAGE_SEARCH && $this->pageObject->pSet->getTableType() == PAGE_DASHBOARD)
		{
			$dashFields = $this->pageObject->pSet->getDashboardSearchFields();
			$tName = $dashFields[$field][0]["table"];
			$field = $dashFields[$field][0]["field"];
		}
	}
				if($tName=="sigdrainase_kolam_retensi" && $field=="koordinat")
	{
		$this->settings["positionMapLat"] = -6.24; // Peta posisi lintang (antara -90 dan 90)
$this->settings["positionMapLng"] = 106.8; // Peta posisi bujur (angka antara -180 dan 180)
$this->settings["displayXY"] = "Oui"; // Tampilan bidang X dan Y, "Oui" atau "Non"
$this->settings["latField"] = "Y"; // Nama garis latitude
$this->settings["lonField"] = "X"; // Nama garis longitude
$this->settings["width"] = 300; // lebar peta
$this->settings["height"] = 300; // tinggi peta
$this->settings["zoomLevel"] = 8; // zoom peta saat inisialisasi (bilangan bulat antara 0 dan 18)
$this->settings["ignKey"] = "choisirgeoportail"; // Kunci IGN
$this->settings["geometryType"] = "POINT"; // Type : POINT, MULTIPOINT, LINE, MULTILINE, POLYGON, MULTIPOLYGON;
		return;
	}	
	$tName = $this->pageObject->tName;
	$field = $this->field;
	if( $this->pageObject->pSet ) {
		if($this->pageObject->pageType == PAGE_SEARCH && $this->pageObject->pSet->getTableType() == PAGE_DASHBOARD)
		{
			$dashFields = $this->pageObject->pSet->getDashboardSearchFields();
			$tName = $dashFields[$field][0]["table"];
			$field = $dashFields[$field][0]["field"];
		}
	}
				if($tName=="dra_data_genangan_manual" && $field=="koordinat")
	{
		$this->settings["positionMapLat"] = -6.24; // Peta posisi lintang (antara -90 dan 90)
$this->settings["positionMapLng"] = 106.8; // Peta posisi bujur (angka antara -180 dan 180)
$this->settings["displayXY"] = "Oui"; // Tampilan bidang X dan Y, "Oui" atau "Non"
$this->settings["latField"] = "Y"; // Nama garis latitude
$this->settings["lonField"] = "X"; // Nama garis longitude
$this->settings["width"] = 300; // lebar peta
$this->settings["height"] = 300; // tinggi peta
$this->settings["zoomLevel"] = 8; // zoom peta saat inisialisasi (bilangan bulat antara 0 dan 18)
$this->settings["ignKey"] = "choisirgeoportail"; // Kunci IGN
$this->settings["geometryType"] = "POLYGON"; // Type : POINT, MULTIPOINT, LINE, MULTILINE, POLYGON, MULTIPOLYGON;
		return;
	}	
	$tName = $this->pageObject->tName;
	$field = $this->field;
	if( $this->pageObject->pSet ) {
		if($this->pageObject->pageType == PAGE_SEARCH && $this->pageObject->pSet->getTableType() == PAGE_DASHBOARD)
		{
			$dashFields = $this->pageObject->pSet->getDashboardSearchFields();
			$tName = $dashFields[$field][0]["table"];
			$field = $dashFields[$field][0]["field"];
		}
	}
				if($tName=="sigdrainase_saluran_drainase_prov" && $field=="koordinat")
	{
		$this->settings["positionMapLat"] = -6.24; // Peta posisi lintang (antara -90 dan 90)
$this->settings["positionMapLng"] = 106.8; // Peta posisi bujur (angka antara -180 dan 180)
$this->settings["displayXY"] = "Oui"; // Tampilan bidang X dan Y, "Oui" atau "Non"
$this->settings["latField"] = "Y"; // Nama garis latitude
$this->settings["lonField"] = "X"; // Nama garis longitude
$this->settings["width"] = 300; // lebar peta
$this->settings["height"] = 300; // tinggi peta
$this->settings["zoomLevel"] = 8; // zoom peta saat inisialisasi (bilangan bulat antara 0 dan 18)
$this->settings["ignKey"] = "choisirgeoportail"; // Kunci IGN
$this->settings["geometryType"] = "LINE"; // Type : POINT, MULTIPOINT, LINE, MULTILINE, POLYGON, MULTIPOLYGON;
		return;
	}	
	$tName = $this->pageObject->tName;
	$field = $this->field;
	if( $this->pageObject->pSet ) {
		if($this->pageObject->pageType == PAGE_SEARCH && $this->pageObject->pSet->getTableType() == PAGE_DASHBOARD)
		{
			$dashFields = $this->pageObject->pSet->getDashboardSearchFields();
			$tName = $dashFields[$field][0]["table"];
			$field = $dashFields[$field][0]["field"];
		}
	}
				if($tName=="dra_data_genangan_prov" && $field=="koordinat")
	{
		$this->settings["positionMapLat"] = -6.24; // Peta posisi lintang (antara -90 dan 90)
$this->settings["positionMapLng"] = 106.8; // Peta posisi bujur (angka antara -180 dan 180)
$this->settings["displayXY"] = "Oui"; // Tampilan bidang X dan Y, "Oui" atau "Non"
$this->settings["latField"] = "Y"; // Nama garis latitude
$this->settings["lonField"] = "X"; // Nama garis longitude
$this->settings["width"] = 300; // lebar peta
$this->settings["height"] = 300; // tinggi peta
$this->settings["zoomLevel"] = 8; // zoom peta saat inisialisasi (bilangan bulat antara 0 dan 18)
$this->settings["ignKey"] = "choisirgeoportail"; // Kunci IGN
$this->settings["geometryType"] = "POLYGON"; // Type : POINT, MULTIPOINT, LINE, MULTILINE, POLYGON, MULTIPOLYGON;
		return;
	}	
	$tName = $this->pageObject->tName;
	$field = $this->field;
	if( $this->pageObject->pSet ) {
		if($this->pageObject->pageType == PAGE_SEARCH && $this->pageObject->pSet->getTableType() == PAGE_DASHBOARD)
		{
			$dashFields = $this->pageObject->pSet->getDashboardSearchFields();
			$tName = $dashFields[$field][0]["table"];
			$field = $dashFields[$field][0]["field"];
		}
	}
				if($tName=="dra_data_genangan_ver_prov" && $field=="koordinat")
	{
		$this->settings["positionMapLat"] = -6.24; // Peta posisi lintang (antara -90 dan 90)
$this->settings["positionMapLng"] = 106.8; // Peta posisi bujur (angka antara -180 dan 180)
$this->settings["displayXY"] = "Oui"; // Tampilan bidang X dan Y, "Oui" atau "Non"
$this->settings["latField"] = "Y"; // Nama garis latitude
$this->settings["lonField"] = "X"; // Nama garis longitude
$this->settings["width"] = 300; // lebar peta
$this->settings["height"] = 300; // tinggi peta
$this->settings["zoomLevel"] = 8; // zoom peta saat inisialisasi (bilangan bulat antara 0 dan 18)
$this->settings["ignKey"] = "choisirgeoportail"; // Kunci IGN
$this->settings["geometryType"] = "POLYGON"; // Type : POINT, MULTIPOINT, LINE, MULTILINE, POLYGON, MULTIPOLYGON;
		return;
	}	
	$tName = $this->pageObject->tName;
	$field = $this->field;
	if( $this->pageObject->pSet ) {
		if($this->pageObject->pageType == PAGE_SEARCH && $this->pageObject->pSet->getTableType() == PAGE_DASHBOARD)
		{
			$dashFields = $this->pageObject->pSet->getDashboardSearchFields();
			$tName = $dashFields[$field][0]["table"];
			$field = $dashFields[$field][0]["field"];
		}
	}
				if($tName=="dra_data_genangan_ver" && $field=="koordinat")
	{
		$this->settings["positionMapLat"] = -6.24; // Peta posisi lintang (antara -90 dan 90)
$this->settings["positionMapLng"] = 106.8; // Peta posisi bujur (angka antara -180 dan 180)
$this->settings["displayXY"] = "Oui"; // Tampilan bidang X dan Y, "Oui" atau "Non"
$this->settings["latField"] = "Y"; // Nama garis latitude
$this->settings["lonField"] = "X"; // Nama garis longitude
$this->settings["width"] = 300; // lebar peta
$this->settings["height"] = 300; // tinggi peta
$this->settings["zoomLevel"] = 8; // zoom peta saat inisialisasi (bilangan bulat antara 0 dan 18)
$this->settings["ignKey"] = "choisirgeoportail"; // Kunci IGN
$this->settings["geometryType"] = "POLYGON"; // Type : POINT, MULTIPOINT, LINE, MULTILINE, POLYGON, MULTIPOLYGON;
		return;
	}	
	$tName = $this->pageObject->tName;
	$field = $this->field;
	if( $this->pageObject->pSet ) {
		if($this->pageObject->pageType == PAGE_SEARCH && $this->pageObject->pSet->getTableType() == PAGE_DASHBOARD)
		{
			$dashFields = $this->pageObject->pSet->getDashboardSearchFields();
			$tName = $dashFields[$field][0]["table"];
			$field = $dashFields[$field][0]["field"];
		}
	}
				if($tName=="sigdrainase_saluran_drainase_ver_prov" && $field=="koordinat")
	{
		$this->settings["positionMapLat"] = -6.24; // Peta posisi lintang (antara -90 dan 90)
$this->settings["positionMapLng"] = 106.8; // Peta posisi bujur (angka antara -180 dan 180)
$this->settings["displayXY"] = "Oui"; // Tampilan bidang X dan Y, "Oui" atau "Non"
$this->settings["latField"] = "Y"; // Nama garis latitude
$this->settings["lonField"] = "X"; // Nama garis longitude
$this->settings["width"] = 300; // lebar peta
$this->settings["height"] = 300; // tinggi peta
$this->settings["zoomLevel"] = 8; // zoom peta saat inisialisasi (bilangan bulat antara 0 dan 18)
$this->settings["ignKey"] = "choisirgeoportail"; // Kunci IGN
$this->settings["geometryType"] = "LINE"; // Type : POINT, MULTIPOINT, LINE, MULTILINE, POLYGON, MULTIPOLYGON;
		return;
	}	
	$tName = $this->pageObject->tName;
	$field = $this->field;
	if( $this->pageObject->pSet ) {
		if($this->pageObject->pageType == PAGE_SEARCH && $this->pageObject->pSet->getTableType() == PAGE_DASHBOARD)
		{
			$dashFields = $this->pageObject->pSet->getDashboardSearchFields();
			$tName = $dashFields[$field][0]["table"];
			$field = $dashFields[$field][0]["field"];
		}
	}
				if($tName=="sigdrainase_saluran_drainase_ver" && $field=="koordinat")
	{
		$this->settings["positionMapLat"] = -6.24; // Peta posisi lintang (antara -90 dan 90)
$this->settings["positionMapLng"] = 106.8; // Peta posisi bujur (angka antara -180 dan 180)
$this->settings["displayXY"] = "Oui"; // Tampilan bidang X dan Y, "Oui" atau "Non"
$this->settings["latField"] = "Y"; // Nama garis latitude
$this->settings["lonField"] = "X"; // Nama garis longitude
$this->settings["width"] = 300; // lebar peta
$this->settings["height"] = 300; // tinggi peta
$this->settings["zoomLevel"] = 8; // zoom peta saat inisialisasi (bilangan bulat antara 0 dan 18)
$this->settings["ignKey"] = "choisirgeoportail"; // Kunci IGN
$this->settings["geometryType"] = "LINE"; // Type : POINT, MULTIPOINT, LINE, MULTILINE, POLYGON, MULTIPOLYGON;
		return;
	}	
	}
}
?>