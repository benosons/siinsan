<?php
require_once( getabspath('connections/rest.php') );

class RestManager
{
	protected $_tablesConnectionIds;
	protected $_connectionsData;
	protected $_connectionsIdByName = array();

	function __construct()
	{
		$this->_setConnectionsData();
//		$this->_setTablesConnectionIds();
	}

	protected function _setTablesConnectionIds()
	{
		$connectionsIds = array();
		$connectionsIds["TPS3R"] = "rest";
		$connectionsIds["Sanimas"] = "rest";
		$connectionsIds["PadatKarya"] = "rest";
		$connectionsIds["Sanimas_prov"] = "rest";
		$connectionsIds["PadatKarya_prov"] = "rest";
		$connectionsIds["TPS3R_prov"] = "rest";
		$this->_tablesConnectionIds = &$connectionsIds;
	}

	protected function _setConnectionsData()
	{
		// content of this function can be modified on demo account
		// variable names $data and $connectionsData are important

		$connectionsData = array();

		$data = array();
		$data["connId"] = "rest";
		$data["connName"] = "PLPBM";

		$this->_connectionsIdByName["PLPBM"] = "rest";

		$data["url"] = "http://plpbm.pu.go.id/tfl/production/read3.php?";
		$data["authType"] = "none";
				
	$connectionsData["rest"] = $data;
		$this->_connectionsData = &$connectionsData;
	}

	public function getConnection( $id ) {
		if( $id == spidGOOGLEDRIVE ) {
			return getGoogleDriveConnection();
		}
		if( $id == spidONEDRIVE ) {
			return getOneDriveConnection();
		}
		if( $id == spidDROPBOX ) {
			return getDropboxConnection();
		}
		if( !$this->_connectionsData[$id] ) {
			return null;
		}
		return new RestConnection( $this->_connectionsData[ $id ] );
	}

	public function idByName( $name ) {
		foreach( $this->_connectionsData as $id => $data ) {
			if( $data["connName"] == $name ) {
				return $id;
			}
		}
		
		//	return first available
		foreach( $this->_connectionsData as $id => $data ) {
			return $id;
		}

	}


}


?>