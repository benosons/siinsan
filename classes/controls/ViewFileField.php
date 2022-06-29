<?php
/**
 * ViewFileField
 * "Abstract" parent for all file fields (image, video, audio)
 */
class ViewFileField extends ViewControl
{
	/**
	 * Instanse of UploadHandler class
	 * @var {object}
	 */
	var $upload_handler = null;
	
	public function getPdfValue(&$data, $keylink = "")
	{
		$textVal = $this->getTextValue($data);
		if ( !strlen($textVal) ) 
			return "\"\"";

		$filesList = explode(", ", $textVal);
		return my_json_encode( array(
			"stack" => $filesList
		) );
	}

	/**
	 * @param &Array data
	 * @return String	 
	 */
	public function getTextValue(&$data)
	{		
		if( !strlen( $data[ $this->field ] ) )
			return "";
			
		if( $this->container->pSet->isVideoUrlField( $this->field ) )
			return $data[ $this->field ];		
		
		$fileNames = array();
		
		$filesData = $this->getFilesData( $data[ $this->field ] );		
		foreach( $filesData as $file )
		{	
			$fileNames[] = $this->getElementTextValue( $file, $data );
		}		
		
		return implode(", ", $fileNames);
	}

	protected function getElementTextValue( $fileData, &$data ) {
		return $fileData["usrName"];
	}
	
	
	/**
	 * @return Array of arrays (
	 * 		mandatory:
	 * 		"name" => fileId
	 * 		"usrName" => user-readable filename
	 *		optional:
	 * 		"size" => number
	 * 		"type" => mime type
	 * 		"thumbnail" => fileId
	 * 		"thumbnail_size" => number
	 * )
	 * 	
	 */
	function getFilesData( $fieldValue ) {
		if( !$fieldValue ) {
			return array();
		}
		
		if( $this->isUrl() ) {
			$fileData = array();
			$fileData["usrName"] = basename( $fieldValue, "" );
			$fileData["name"] = $fieldValue;
			return array( $fileData );
		}

		$files = my_json_decode( $fieldValue );
		
		if( !is_array( $files ) || !$files && $fieldValue != "[]" ) {
			// filename in the field as is
			$fileData = array();
			$fileData["usrName"] = basename( $fieldValue, "" );
			$fileData["name"] = DiskFileSystem::normalizePath( $this->pSettings()->getUploadFolder( $this->field ) ) .$fieldValue;
			return array( $fileData );
		}
		//	normal field value, multiupload
		return $files;
	}



	protected function fastFileExists( $filename, $fs = null ) {
		if( !$filename ) {
			return false;
		}

		if( !$fs ) {
			$fs = getStorageProvider( $this->pSettings(), $this->field );			
		}
		//	don't verify
		if( !$fs->fast() ) {
			return true;
		}
		return !!$fs->getFileInfo( $filename );
	}

	/**
	 * @param Array fileData - element of getFilesData function return value
	 * @return String url
	 */
	protected function getFileUrl( $fileData, $keylink, $thumbnail = false, $additionalParams = array() ) {
	    $pSet = $this->pSettings();
		$fs = getStorageProvider( $pSet, $this->field );
		$lastModified = time();
		if( $fs->fast() ) {
			$fsInfo = $fs->getFileInfo( $fileData["name"] );
			if( $fsInfo && $fsInfo["lastModified"]) {
				$lastModified = $fsInfo["lastModified"];
			}
		}
		$params = array();
		$params["file"] = $fileData["usrName"];
		$params["table"] = $pSet->table();
		$params["field"] = $this->field;
		$params["nodisp"] = 1;
		$params["hash"] = fileAttrHash( $keylink, $file["size"], $lastModified );
		if( $thumbnail ) 
			$params["thumbnail"] = 1;
		
		foreach( $additionalParams as $k => $val ) {
			$params[ $k ] = $val;
		}
		return GetTableLink("file", "", prepareUrlQuery( $params ).$keylink );
	}

	/**
	 * @return Boolean when true, the field contents is consifered a URL
	 */
	protected function isUrl() {
		return false;
	}

}
?>