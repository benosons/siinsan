<?php
class DB
{
	public static function CurrentConnection()
	{
		global $currentConnection;
		return $currentConnection ? $currentConnection : DB::DefaultConnection();
	}

	public static function CurrentConnectionId()
	{
		$conn = DB::CurrentConnection();
		return $conn->connId;
	}

	public static function DefaultConnection()
	{
		global $cman;
		return $cman->getDefault();
	}

	public static function ConnectionByTable( $table )
	{
		global $cman;
		return $cman->byTable($table);
	}

	public static function ConnectionByName( $name )
	{
		global $cman;
		return $cman->byName( $name );
	}

	public static function SetConnection( $connection )
	{
		global $currentConnection;
		if ( is_string( $connection ) )
		{
			$currentConnection = DB::ConnectionByName( $connection );
		}
		else if ( is_a($connection, 'Connection') ) {
		 	$currentConnection = $connection;
		}
	}

	public static function LastId()
	{
		return DB::CurrentConnection()->getInsertedId();
	}

	public static function Query( $sql )
	{
		return DB::CurrentConnection()->querySilent( $sql );
	}

	public static function Exec( $sql )
	{
		return DB::CurrentConnection()->execSilent( $sql ) != NULL;
	}

	public static function LastError()
	{
		return DB::CurrentConnection()->lastError();
	}

	public static function Select($table, $userConditions = array() )
	{
		$tableInfo = DB::_getTableInfo($table);

		if ( !$tableInfo )
			return false;

		$whereSql = DB::_getWhereSql($userConditions, $tableInfo["fields"]);

		$sql = "SELECT * FROM ".DB::CurrentConnection()->addTableWrappers( $tableInfo['fullName'] ) . $whereSql;
		$queryResult = DB::CurrentConnection()->querySilent( $sql );

		return $queryResult;
	}

	public static function Delete($table, $userConditions = array() )
	{
		$tableInfo = DB::_getTableInfo($table);

		if ( !$tableInfo )
			return false;

		$whereSql = DB::_getWhereSql($userConditions, $tableInfo["fields"]);

		if( $whereSql == "" )
			return false;

		$sql = "DELETE FROM ".DB::CurrentConnection()->addTableWrappers( $tableInfo['fullName'] ) . $whereSql;
		$ret = DB::CurrentConnection()->execSilent( $sql );

		return $ret;
	}

	public static function Insert($table, $data)
	{
		$dataSource = getDbTableDataSource( $table, DB::CurrentConnectionId() );
		if( !$dataSource ) {
			return false;
		}
		$dc = new DsCommand();
		$dc->values = $data;
		$result = $dataSource->insertSingle( $dc );
		return !!$result;
	}

	public static function Update($table, $data, $userConditions)
	{

		$dataSource = getDbTableDataSource( $table, DB::CurrentConnectionId() );
		if( !$dataSource ) {
			return false;
		}
		if( !$userConditions ) {
			return false;
		}
		$dc = new DsCommand();
		$dc->values = $data;
		$dc->filter = DB::_createFilterCondition( $userConditions );
		$result = $dataSource->updateSingle( $dc, false );
		return !!$result;
	}

	protected static function _getWhereSql($userConditions, $founedfields)
	{
		if( !is_array( $userConditions ) )
		{
			$whereSql = trim( $userConditions );
			if( $whereSql != "")
				$whereSql = " WHERE " . $whereSql;
			return $whereSql;
		}

		$conditions = array();
		foreach($userConditions as $fieldname => $value)
		{
			$field = getArrayElementNC($founedfields, $fieldname);
			// user field not found in table
			if ( is_null($field) )
				continue;

			$wrappedField = DB::CurrentConnection()->addFieldWrappers( $field["name"] );
			if ( is_null($value) )
			{
				$conditions[] = $wrappedField . " IS NULL";
			}
			else
			{
				$conditions[] = $wrappedField . "=" . DB::_prepareValue($value, $field["type"]);
			}
		}

		$whereSql = "";
		if( count($conditions) > 0 )
		{
			$whereSql .= " WHERE " . implode(" AND ", $conditions);
		}

		return $whereSql;
	}

	protected static function _createFilterCondition( $userConditions )
	{
		if( !is_array( $userConditions ) ) {
			return DataCondition::SQLCondition( $userConditions );
		}

		$conditions = array();
		foreach($userConditions as $fieldName => $value)
		{
			if ( is_null($value) ) {
				$conditions[] = DataCondition::FieldIs( $fieldName, dsopEMPTY, '' );
			} else {
				$conditions[] = DataCondition::FieldEquals( $fieldName, $value );
			}
		}
		return DataCondition::_And( $conditions );
	}


	/**
	 * @param Array blobs
	 * @param String dalSQL
	 * @param Array tableinfo
	 */
	protected static function _execSilentWithBlobProcessing($blobs, $dalSQL, $tableinfo, $autoincField = null)
	{
		$blobTypes = array();
		if( DB::CurrentConnection()->dbType == nDATABASE_Informix )
		{
			foreach( $blobs as $fname => $fvalue )
			{
				$blobTypes[ $fname ] = $tableinfo[ $fname ]["type"];
			}
		}

		DB::CurrentConnection()->execSilentWithBlobProcessing( $dalSQL, $blobs, $blobTypes, $autoincField );
	}

	protected static function _prepareValue($value, $type)
	{
		if ( is_null($value) )
			return "NULL";

		if( DB::CurrentConnection()->dbType == nDATABASE_Oracle || DB::CurrentConnection()->dbType == nDATABASE_DB2 || DB::CurrentConnection()->dbType == nDATABASE_Informix )
		{
			if( IsBinaryType($type) )
			{
				if( DB::CurrentConnection()->dbType == nDATABASE_Oracle )
					return "EMPTY_BLOB()";

				return "?";
			}

			if( DB::CurrentConnection()->dbType == nDATABASE_Informix  && IsTextType($type) )
				return "?";
		}

		if( IsNumberType($type) && !is_numeric($value) )
		{
			$value = trim($value);
			$value = str_replace(",", ".", $value);
			if ( !is_numeric($value) )
				return "NULL";
		}

		if( IsDateFieldType($type) || IsTimeType($type) )
		{
			if( !$value )
				return "NULL";

			// timestamp
			if ( is_int($value) )
			{
				if ( IsDateFieldType($type) )
				{
					$value = getYMDdate($value) . " " . getHISdate($value);
				}
				else if ( IsTimeType($type) )
				{
					$value = getHISdate($value);
				}
			}

			return DB::CurrentConnection()->addDateQuotes( $value );
		}

		if( NeedQuotes($type) )
			return DB::CurrentConnection()->prepareString( $value );

		return $value;
	}

	/**
	 * 	Find table info stored in the project file
	 *
	 */
	public static function _findDalTable( $table, $conn = null )
	{
		global $dalTables;
		if( !$conn )
			$conn = DB::CurrentConnection();
		$tableName = $conn->getTableNameComponents( $table );

		DB::_fillTablesList( $conn );

		//	exact match
		foreach( $dalTables[$conn->connId] as $t ) {
			if( ( !$tableName["schema"] || $t["schema"] == $tableName["schema"] )
				&& $t["name"] == $tableName["table"] )
				return $t;
		}

		//	case-insensitive
		$tableName["schema"] = strtoupper( $tableName["schema"] );
		$tableName["table"] = strtoupper( $tableName["table"] );

		foreach( $dalTables[$conn->connId] as $t )
		{
			if( ( !$tableName["schema"] || strtoupper( $t["schema"] ) == $tableName["schema"] )
				&& strtoupper( $t["name"] ) == $tableName["table"] )
				return $t;
		}
		return null;
	}

	/**
	 * 	Get list of table field names and types
	 *	Check tables stored in the project first, then fetch it from the database.
	 *
	 */
	public static function _getTableInfo($table, $connId = null )
	{
		global $dal_info, $tableinfo_cache, $cman;
		if( !$connId )
			$connId = DB::CurrentConnectionId();

		//	prepare cache
		if( !isset($tableinfo_cache[ $connId ] ) )
			$tableinfo_cache[ $connId ] = array();

		$tableInfo = array();


		$tableDescriptor = DB::_findDalTable( $table, $cman->byId( $connId ) );

		if ( $tableDescriptor )
		{
			importTableInfo( $tableDescriptor["varname"] );

			$tableInfo["fields"] = $dal_info[ $tableDescriptor["varname"] ];

			if( $tableDescriptor["schema"] )
				$tableInfo["fullName"] = $tableDescriptor["schema"] . "." . $tableDescriptor["name"];
			else
				$tableInfo["fullName"] = $tableDescriptor["name"];
		}
		else
		{
			//	check cache first
			if( isset($tableinfo_cache[ $connId ][ $table ] ) )
				return $tableinfo_cache[ $connId ][ $table ];

			//	fetch table info from the database
			$helpSql = "select * from " . DB::CurrentConnection()->addTableWrappers( $table ) . " where 1=0";

			$tableInfo["fullName"] = $table;
			$tableInfo["fields"] = array();

			// in case getFieldsList throws error
			$tableinfo_cache[ $connId ][ $table ] = false;

			$fieldList = DB::CurrentConnection()->getFieldsList($helpSql);
			foreach ($fieldList as $f )
			{
				$tableInfo["fields"][ $f["fieldname"] ] = array( "type" => $f["type"], "name" => $f["fieldname"] );
			}
			$tableinfo_cache[ $connId ][ $table ] = $tableInfo;
		}

		return $tableInfo;
	}


	protected static function _fillTablesList( $conn )
	{
		global $dalTables;
		if( !$conn )
			$conn = DB::CurrentConnection();
		if( $dalTables[ $conn->connId ] )
			return;
		$dalTables[ $conn->connId ] = array();
		if( "siinsan_at_localhost" == $conn->connId )
		{
			$dalTables[$conn->connId][] = array("name" => "al_babs_penderita", "varname" => "siinsan_at_localhost__al_babs_penderita", "altvarname" => "al_babs_penderita", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_cakupanairlimbah", "varname" => "siinsan_at_localhost__al_cakupanairlimbah", "altvarname" => "al_cakupanairlimbah", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_dataumum", "varname" => "siinsan_at_localhost__al_dataumum", "altvarname" => "al_dataumum", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_iplt", "varname" => "siinsan_at_localhost__al_iplt", "altvarname" => "al_iplt", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_kualitasair", "varname" => "siinsan_at_localhost__al_kualitasair", "altvarname" => "al_kualitasair", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_mck", "varname" => "siinsan_at_localhost__al_mck", "altvarname" => "al_mck", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_penggunasaranabab", "varname" => "siinsan_at_localhost__al_penggunasaranabab", "altvarname" => "al_penggunasaranabab", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_peranserta", "varname" => "siinsan_at_localhost__al_peranserta", "altvarname" => "al_peranserta", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_programkegiatan", "varname" => "siinsan_at_localhost__al_programkegiatan", "altvarname" => "al_programkegiatan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_sdm_institusi", "varname" => "siinsan_at_localhost__al_sdm_institusi", "altvarname" => "al_sdm_institusi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_sdm_pengelola", "varname" => "siinsan_at_localhost__al_sdm_pengelola", "altvarname" => "al_sdm_pengelola", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_setempat", "varname" => "siinsan_at_localhost__al_setempat", "altvarname" => "al_setempat", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_spalds", "varname" => "siinsan_at_localhost__al_spalds", "altvarname" => "al_spalds", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_t_jenis_infrastruktur", "varname" => "siinsan_at_localhost__al_t_jenis_infrastruktur", "altvarname" => "al_t_jenis_infrastruktur", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_t_jenis_pengolahan_akhir", "varname" => "siinsan_at_localhost__al_t_jenis_pengolahan_akhir", "altvarname" => "al_t_jenis_pengolahan_akhir", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_t_opsi_teknologi", "varname" => "siinsan_at_localhost__al_t_opsi_teknologi", "altvarname" => "al_t_opsi_teknologi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_t_sistem_pengolahan", "varname" => "siinsan_at_localhost__al_t_sistem_pengolahan", "altvarname" => "al_t_sistem_pengolahan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "al_t_suplai_air", "varname" => "siinsan_at_localhost__al_t_suplai_air", "altvarname" => "al_t_suplai_air", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_airlimbah_kabkota", "varname" => "siinsan_at_localhost__dash_airlimbah_kabkota", "altvarname" => "dash_airlimbah_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_airlimbah_nas", "varname" => "siinsan_at_localhost__dash_airlimbah_nas", "altvarname" => "dash_airlimbah_nas", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_airlimbah_prov", "varname" => "siinsan_at_localhost__dash_airlimbah_prov", "altvarname" => "dash_airlimbah_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_persampahan_kabkota", "varname" => "siinsan_at_localhost__dash_persampahan_kabkota", "altvarname" => "dash_persampahan_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_persampahan_nas", "varname" => "siinsan_at_localhost__dash_persampahan_nas", "altvarname" => "dash_persampahan_nas", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_persampahan_prov", "varname" => "siinsan_at_localhost__dash_persampahan_prov", "altvarname" => "dash_persampahan_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_saluran_drainase_kabkota", "varname" => "siinsan_at_localhost__dash_saluran_drainase_kabkota", "altvarname" => "dash_saluran_drainase_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_saluran_drainase_nas", "varname" => "siinsan_at_localhost__dash_saluran_drainase_nas", "altvarname" => "dash_saluran_drainase_nas", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_saluran_drainase_prov", "varname" => "siinsan_at_localhost__dash_saluran_drainase_prov", "altvarname" => "dash_saluran_drainase_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_spaldt_infra", "varname" => "siinsan_at_localhost__dash_spaldt_infra", "altvarname" => "dash_spaldt_infra", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_verifikasi_prov_kabkota", "varname" => "siinsan_at_localhost__dash_verifikasi_prov_kabkota", "altvarname" => "dash_verifikasi_prov_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_verifikasi_prov_nas", "varname" => "siinsan_at_localhost__dash_verifikasi_prov_nas", "altvarname" => "dash_verifikasi_prov_nas", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_verifikasi_prov_prov", "varname" => "siinsan_at_localhost__dash_verifikasi_prov_prov", "altvarname" => "dash_verifikasi_prov_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_verifikasi_pusat_kabkota", "varname" => "siinsan_at_localhost__dash_verifikasi_pusat_kabkota", "altvarname" => "dash_verifikasi_pusat_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_verifikasi_pusat_nas", "varname" => "siinsan_at_localhost__dash_verifikasi_pusat_nas", "altvarname" => "dash_verifikasi_pusat_nas", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dash_verifikasi_pusat_prov", "varname" => "siinsan_at_localhost__dash_verifikasi_pusat_prov", "altvarname" => "dash_verifikasi_pusat_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dra_data_genangan", "varname" => "siinsan_at_localhost__dra_data_genangan", "altvarname" => "dra_data_genangan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dra_data_pembiayaan", "varname" => "siinsan_at_localhost__dra_data_pembiayaan", "altvarname" => "dra_data_pembiayaan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dra_data_umum", "varname" => "siinsan_at_localhost__dra_data_umum", "altvarname" => "dra_data_umum", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dra_t_bentuk_saluran", "varname" => "siinsan_at_localhost__dra_t_bentuk_saluran", "altvarname" => "dra_t_bentuk_saluran", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dra_t_jenis_bahan", "varname" => "siinsan_at_localhost__dra_t_jenis_bahan", "altvarname" => "dra_t_jenis_bahan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "dra_t_jenis_bangunan", "varname" => "siinsan_at_localhost__dra_t_jenis_bangunan", "altvarname" => "dra_t_jenis_bangunan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "ibm_lpk", "varname" => "siinsan_at_localhost__ibm_lpk", "altvarname" => "ibm_lpk", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "ibm_padatkarya_ver", "varname" => "siinsan_at_localhost__ibm_padatkarya_ver", "altvarname" => "ibm_padatkarya_ver", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "ibm_sanimas_ver", "varname" => "siinsan_at_localhost__ibm_sanimas_ver", "altvarname" => "ibm_sanimas_ver", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "ibm_tps3r_ver", "varname" => "siinsan_at_localhost__ibm_tps3r_ver", "altvarname" => "ibm_tps3r_ver", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_kesiapan_eval", "varname" => "siinsan_at_localhost__mne_kesiapan_eval", "altvarname" => "mne_kesiapan_eval", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_kesiapan_penentu", "varname" => "siinsan_at_localhost__mne_kesiapan_penentu", "altvarname" => "mne_kesiapan_penentu", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_pasca_eval", "varname" => "siinsan_at_localhost__mne_pasca_eval", "altvarname" => "mne_pasca_eval", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_pelaksanaan_eval", "varname" => "siinsan_at_localhost__mne_pelaksanaan_eval", "altvarname" => "mne_pelaksanaan_eval", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_jenis_infrastruktur", "varname" => "siinsan_at_localhost__mne_t_jenis_infrastruktur", "altvarname" => "mne_t_jenis_infrastruktur", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_jenis_kontrak", "varname" => "siinsan_at_localhost__mne_t_jenis_kontrak", "altvarname" => "mne_t_jenis_kontrak", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_kategori_perencanaan", "varname" => "siinsan_at_localhost__mne_t_kategori_perencanaan", "altvarname" => "mne_t_kategori_perencanaan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_kriteria_evaluasi", "varname" => "siinsan_at_localhost__mne_t_kriteria_evaluasi", "altvarname" => "mne_t_kriteria_evaluasi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_param_sub_pasca_konstruksi", "varname" => "siinsan_at_localhost__mne_t_param_sub_pasca_konstruksi", "altvarname" => "mne_t_param_sub_pasca_konstruksi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_param_sub_pelaksanaan", "varname" => "siinsan_at_localhost__mne_t_param_sub_pelaksanaan", "altvarname" => "mne_t_param_sub_pelaksanaan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_param_sub_tahap_perencanaan", "varname" => "siinsan_at_localhost__mne_t_param_sub_tahap_perencanaan", "altvarname" => "mne_t_param_sub_tahap_perencanaan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_pasca_konstruksi", "varname" => "siinsan_at_localhost__mne_t_pasca_konstruksi", "altvarname" => "mne_t_pasca_konstruksi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_pelaksanaan_konstruksi", "varname" => "siinsan_at_localhost__mne_t_pelaksanaan_konstruksi", "altvarname" => "mne_t_pelaksanaan_konstruksi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_sektor", "varname" => "siinsan_at_localhost__mne_t_sektor", "altvarname" => "mne_t_sektor", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_statususulan", "varname" => "siinsan_at_localhost__mne_t_statususulan", "altvarname" => "mne_t_statususulan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_sub_pasca_konstruksi", "varname" => "siinsan_at_localhost__mne_t_sub_pasca_konstruksi", "altvarname" => "mne_t_sub_pasca_konstruksi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_sub_pelaksanaan_konstruksi", "varname" => "siinsan_at_localhost__mne_t_sub_pelaksanaan_konstruksi", "altvarname" => "mne_t_sub_pelaksanaan_konstruksi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_sub_tahap_perencanaan", "varname" => "siinsan_at_localhost__mne_t_sub_tahap_perencanaan", "altvarname" => "mne_t_sub_tahap_perencanaan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_sumberdana", "varname" => "siinsan_at_localhost__mne_t_sumberdana", "altvarname" => "mne_t_sumberdana", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_t_tahap_perencanaan", "varname" => "siinsan_at_localhost__mne_t_tahap_perencanaan", "altvarname" => "mne_t_tahap_perencanaan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "mne_umum", "varname" => "siinsan_at_localhost__mne_umum", "altvarname" => "mne_umum", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "psn_capaian", "varname" => "siinsan_at_localhost__psn_capaian", "altvarname" => "psn_capaian", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "psn_komposisi", "varname" => "siinsan_at_localhost__psn_komposisi", "altvarname" => "psn_komposisi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "san_peraturan", "varname" => "siinsan_at_localhost__san_peraturan", "altvarname" => "san_peraturan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "san_profil", "varname" => "siinsan_at_localhost__san_profil", "altvarname" => "san_profil", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "san_rencanainduk", "varname" => "siinsan_at_localhost__san_rencanainduk", "altvarname" => "san_rencanainduk", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "san_spm_ald", "varname" => "siinsan_at_localhost__san_spm_ald", "altvarname" => "san_spm_ald", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "san_spm_stbm", "varname" => "siinsan_at_localhost__san_spm_stbm", "altvarname" => "san_spm_stbm", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "Sanimas", "varname" => "siinsan_at_localhost__Sanimas", "altvarname" => "Sanimas", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sigdrainase_kolam_retensi", "varname" => "siinsan_at_localhost__sigdrainase_kolam_retensi", "altvarname" => "sigdrainase_kolam_retensi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sigdrainase_kontruksi_drainase", "varname" => "siinsan_at_localhost__sigdrainase_kontruksi_drainase", "altvarname" => "sigdrainase_kontruksi_drainase", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sigdrainase_saluran_drainase", "varname" => "siinsan_at_localhost__sigdrainase_saluran_drainase", "altvarname" => "sigdrainase_saluran_drainase", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sigdrainase_sumur", "varname" => "siinsan_at_localhost__sigdrainase_sumur", "altvarname" => "sigdrainase_sumur", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "siinsan_uggroups", "varname" => "siinsan_at_localhost__siinsan_uggroups", "altvarname" => "siinsan_uggroups", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "siinsan_ugmembers", "varname" => "siinsan_at_localhost__siinsan_ugmembers", "altvarname" => "siinsan_ugmembers", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "siinsan_ugrights", "varname" => "siinsan_at_localhost__siinsan_ugrights", "altvarname" => "siinsan_ugrights", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "simdak_v_rekap_dak", "varname" => "siinsan_at_localhost__simdak_v_rekap_dak", "altvarname" => "simdak_v_rekap_dak", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "simdak_v_rekap_drainase", "varname" => "siinsan_at_localhost__simdak_v_rekap_drainase", "altvarname" => "simdak_v_rekap_drainase", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "simdak_v_rekap_ipal", "varname" => "siinsan_at_localhost__simdak_v_rekap_ipal", "altvarname" => "simdak_v_rekap_ipal", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "simdak_v_rekap_ipal_mck", "varname" => "siinsan_at_localhost__simdak_v_rekap_ipal_mck", "altvarname" => "simdak_v_rekap_ipal_mck", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sippa_renja1", "varname" => "siinsan_at_localhost__sippa_renja1", "altvarname" => "sippa_renja1", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_banksampah", "varname" => "siinsan_at_localhost__sp_banksampah", "altvarname" => "sp_banksampah", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_pemrosesanakhir", "varname" => "siinsan_at_localhost__sp_pemrosesanakhir", "altvarname" => "sp_pemrosesanakhir", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_pemrosesanakhir_jenis", "varname" => "siinsan_at_localhost__sp_pemrosesanakhir_jenis", "altvarname" => "sp_pemrosesanakhir_jenis", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_pemrosesanakhir_jenispengelola", "varname" => "siinsan_at_localhost__sp_pemrosesanakhir_jenispengelola", "altvarname" => "sp_pemrosesanakhir_jenispengelola", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_pemrosesanakhir_kategoripelayanan", "varname" => "siinsan_at_localhost__sp_pemrosesanakhir_kategoripelayanan", "altvarname" => "sp_pemrosesanakhir_kategoripelayanan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_pemrosesanakhir_kondisi", "varname" => "siinsan_at_localhost__sp_pemrosesanakhir_kondisi", "altvarname" => "sp_pemrosesanakhir_kondisi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_pemrosesanakhir_pengembangan", "varname" => "siinsan_at_localhost__sp_pemrosesanakhir_pengembangan", "altvarname" => "sp_pemrosesanakhir_pengembangan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_pemrosesanakhir_sistem", "varname" => "siinsan_at_localhost__sp_pemrosesanakhir_sistem", "altvarname" => "sp_pemrosesanakhir_sistem", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_pengangkutan", "varname" => "siinsan_at_localhost__sp_pengangkutan", "altvarname" => "sp_pengangkutan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_pengolahan", "varname" => "siinsan_at_localhost__sp_pengolahan", "altvarname" => "sp_pengolahan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_profil", "varname" => "siinsan_at_localhost__sp_profil", "altvarname" => "sp_profil", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_profil_prov", "varname" => "siinsan_at_localhost__sp_profil_prov", "altvarname" => "sp_profil_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_t_jenis_alatberat", "varname" => "siinsan_at_localhost__sp_t_jenis_alatberat", "altvarname" => "sp_t_jenis_alatberat", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_t_jenis_saranaangkut", "varname" => "siinsan_at_localhost__sp_t_jenis_saranaangkut", "altvarname" => "sp_t_jenis_saranaangkut", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_t_opsi_teknologi_anorganik", "varname" => "siinsan_at_localhost__sp_t_opsi_teknologi_anorganik", "altvarname" => "sp_t_opsi_teknologi_anorganik", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_t_opsi_teknologi_organik", "varname" => "siinsan_at_localhost__sp_t_opsi_teknologi_organik", "altvarname" => "sp_t_opsi_teknologi_organik", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_tpa_alatberat", "varname" => "siinsan_at_localhost__sp_tpa_alatberat", "altvarname" => "sp_tpa_alatberat", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "sp_tpa_saranapengangkutan", "varname" => "siinsan_at_localhost__sp_tpa_saranapengangkutan", "altvarname" => "sp_tpa_saranapengangkutan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "ssk_dokumen", "varname" => "siinsan_at_localhost__ssk_dokumen", "altvarname" => "ssk_dokumen", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "ssk_file", "varname" => "siinsan_at_localhost__ssk_file", "altvarname" => "ssk_file", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "ssk_pokja", "varname" => "siinsan_at_localhost__ssk_pokja", "altvarname" => "ssk_pokja", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "ssk_tahun", "varname" => "siinsan_at_localhost__ssk_tahun", "altvarname" => "ssk_tahun", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_ada_rusak_tidak", "varname" => "siinsan_at_localhost__t_ada_rusak_tidak", "altvarname" => "t_ada_rusak_tidak", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_ada_tidak", "varname" => "siinsan_at_localhost__t_ada_tidak", "altvarname" => "t_ada_tidak", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_aktif", "varname" => "siinsan_at_localhost__t_aktif", "altvarname" => "t_aktif", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_al_kabupatenkota", "varname" => "siinsan_at_localhost__t_al_kabupatenkota", "altvarname" => "t_al_kabupatenkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_al_kecamatan", "varname" => "siinsan_at_localhost__t_al_kecamatan", "altvarname" => "t_al_kecamatan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_al_kelurahan", "varname" => "siinsan_at_localhost__t_al_kelurahan", "altvarname" => "t_al_kelurahan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_al_provinsi", "varname" => "siinsan_at_localhost__t_al_provinsi", "altvarname" => "t_al_provinsi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_al_sektor", "varname" => "siinsan_at_localhost__t_al_sektor", "altvarname" => "t_al_sektor", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_al_skalapelayanan", "varname" => "siinsan_at_localhost__t_al_skalapelayanan", "altvarname" => "t_al_skalapelayanan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_ba_status_aset", "varname" => "siinsan_at_localhost__t_ba_status_aset", "altvarname" => "t_ba_status_aset", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_dimanfaatkan", "varname" => "siinsan_at_localhost__t_dimanfaatkan", "altvarname" => "t_dimanfaatkan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_dra_kecamatan", "varname" => "siinsan_at_localhost__t_dra_kecamatan", "altvarname" => "t_dra_kecamatan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_dra_kelurahan", "varname" => "siinsan_at_localhost__t_dra_kelurahan", "altvarname" => "t_dra_kelurahan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_dra_kota", "varname" => "siinsan_at_localhost__t_dra_kota", "altvarname" => "t_dra_kota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_dra_propinsi", "varname" => "siinsan_at_localhost__t_dra_propinsi", "altvarname" => "t_dra_propinsi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_jenis_pengelola", "varname" => "siinsan_at_localhost__t_jenis_pengelola", "altvarname" => "t_jenis_pengelola", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_jenis_peraturan", "varname" => "siinsan_at_localhost__t_jenis_peraturan", "altvarname" => "t_jenis_peraturan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_kabupatenkota", "varname" => "siinsan_at_localhost__t_kabupatenkota", "altvarname" => "t_kabupatenkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_kategori_pelayanan", "varname" => "siinsan_at_localhost__t_kategori_pelayanan", "altvarname" => "t_kategori_pelayanan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_kecamatan", "varname" => "siinsan_at_localhost__t_kecamatan", "altvarname" => "t_kecamatan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_kelurahan", "varname" => "siinsan_at_localhost__t_kelurahan", "altvarname" => "t_kelurahan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_kondisi", "varname" => "siinsan_at_localhost__t_kondisi", "altvarname" => "t_kondisi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_kondisi_fasilitas", "varname" => "siinsan_at_localhost__t_kondisi_fasilitas", "altvarname" => "t_kondisi_fasilitas", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_kualitas_keberfungsian", "varname" => "siinsan_at_localhost__t_kualitas_keberfungsian", "altvarname" => "t_kualitas_keberfungsian", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_mne_tahap", "varname" => "siinsan_at_localhost__t_mne_tahap", "altvarname" => "t_mne_tahap", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_mne_tahap_kekersiapan", "varname" => "siinsan_at_localhost__t_mne_tahap_kekersiapan", "altvarname" => "t_mne_tahap_kekersiapan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_mne_tahap_kepasca", "varname" => "siinsan_at_localhost__t_mne_tahap_kepasca", "altvarname" => "t_mne_tahap_kepasca", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_mne_tahap_kepelaksanaan", "varname" => "siinsan_at_localhost__t_mne_tahap_kepelaksanaan", "altvarname" => "t_mne_tahap_kepelaksanaan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_provinsi", "varname" => "siinsan_at_localhost__t_provinsi", "altvarname" => "t_provinsi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_status_keberfungsian", "varname" => "siinsan_at_localhost__t_status_keberfungsian", "altvarname" => "t_status_keberfungsian", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_status_lahan", "varname" => "siinsan_at_localhost__t_status_lahan", "altvarname" => "t_status_lahan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_sumber_dana_pembangunan", "varname" => "siinsan_at_localhost__t_sumber_dana_pembangunan", "altvarname" => "t_sumber_dana_pembangunan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_verifikasi", "varname" => "siinsan_at_localhost__t_verifikasi", "altvarname" => "t_verifikasi", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "t_yatidak", "varname" => "siinsan_at_localhost__t_yatidak", "altvarname" => "t_yatidak", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "TPS3R", "varname" => "siinsan_at_localhost__TPS3R", "altvarname" => "TPS3R", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "tr_pengumuman", "varname" => "siinsan_at_localhost__tr_pengumuman", "altvarname" => "tr_pengumuman", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "user", "varname" => "siinsan_at_localhost__user", "altvarname" => "user", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "usertype", "varname" => "siinsan_at_localhost__usertype", "altvarname" => "usertype", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_al_dataumum", "varname" => "siinsan_at_localhost__v_al_dataumum", "altvarname" => "v_al_dataumum", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_al_iplt_keterisian_kabkota", "varname" => "siinsan_at_localhost__v_al_iplt_keterisian_kabkota", "altvarname" => "v_al_iplt_keterisian_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_al_iplt_keterisian_prov", "varname" => "siinsan_at_localhost__v_al_iplt_keterisian_prov", "altvarname" => "v_al_iplt_keterisian_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_banksampah_jumlah_bytahun", "varname" => "siinsan_at_localhost__v_banksampah_jumlah_bytahun", "altvarname" => "v_banksampah_jumlah_bytahun", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_dra_keterisian_genangan_kabkota", "varname" => "siinsan_at_localhost__v_dra_keterisian_genangan_kabkota", "altvarname" => "v_dra_keterisian_genangan_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_dra_keterisian_genangan_prov", "varname" => "siinsan_at_localhost__v_dra_keterisian_genangan_prov", "altvarname" => "v_dra_keterisian_genangan_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_dra_keterisian_saluran_kabkota", "varname" => "siinsan_at_localhost__v_dra_keterisian_saluran_kabkota", "altvarname" => "v_dra_keterisian_saluran_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_dra_keterisian_saluran_prov", "varname" => "siinsan_at_localhost__v_dra_keterisian_saluran_prov", "altvarname" => "v_dra_keterisian_saluran_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_dra_lokasi_genangan", "varname" => "siinsan_at_localhost__v_dra_lokasi_genangan", "altvarname" => "v_dra_lokasi_genangan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_iplt", "varname" => "siinsan_at_localhost__v_iplt", "altvarname" => "v_iplt", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_iplt_jumlah_bytahun", "varname" => "siinsan_at_localhost__v_iplt_jumlah_bytahun", "altvarname" => "v_iplt_jumlah_bytahun", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_iplt_prov", "varname" => "siinsan_at_localhost__v_iplt_prov", "altvarname" => "v_iplt_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_kesiapan_eval", "varname" => "siinsan_at_localhost__v_kesiapan_eval", "altvarname" => "v_kesiapan_eval", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_kesiapan_eval_total", "varname" => "siinsan_at_localhost__v_kesiapan_eval_total", "altvarname" => "v_kesiapan_eval_total", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_kesiapan_eval_total_kategori", "varname" => "siinsan_at_localhost__v_kesiapan_eval_total_kategori", "altvarname" => "v_kesiapan_eval_total_kategori", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_mne_keterisian_kesiapan", "varname" => "siinsan_at_localhost__v_mne_keterisian_kesiapan", "altvarname" => "v_mne_keterisian_kesiapan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_mne_keterisian_kesiapan_kabkota", "varname" => "siinsan_at_localhost__v_mne_keterisian_kesiapan_kabkota", "altvarname" => "v_mne_keterisian_kesiapan_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_mne_keterisian_kesiapan_prov", "varname" => "siinsan_at_localhost__v_mne_keterisian_kesiapan_prov", "altvarname" => "v_mne_keterisian_kesiapan_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_mne_keterisian_pasca", "varname" => "siinsan_at_localhost__v_mne_keterisian_pasca", "altvarname" => "v_mne_keterisian_pasca", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_mne_keterisian_pasca_kabkota", "varname" => "siinsan_at_localhost__v_mne_keterisian_pasca_kabkota", "altvarname" => "v_mne_keterisian_pasca_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_mne_keterisian_pasca_prov", "varname" => "siinsan_at_localhost__v_mne_keterisian_pasca_prov", "altvarname" => "v_mne_keterisian_pasca_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_mne_keterisian_pelaksanaan", "varname" => "siinsan_at_localhost__v_mne_keterisian_pelaksanaan", "altvarname" => "v_mne_keterisian_pelaksanaan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_mne_keterisian_pelaksanaan_kabkota", "varname" => "siinsan_at_localhost__v_mne_keterisian_pelaksanaan_kabkota", "altvarname" => "v_mne_keterisian_pelaksanaan_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_mne_keterisian_pelaksanaan_prov", "varname" => "siinsan_at_localhost__v_mne_keterisian_pelaksanaan_prov", "altvarname" => "v_mne_keterisian_pelaksanaan_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_mne_keterisian_perencanaan", "varname" => "siinsan_at_localhost__v_mne_keterisian_perencanaan", "altvarname" => "v_mne_keterisian_perencanaan", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_mne_keterisian_perencanaan_kabkota", "varname" => "siinsan_at_localhost__v_mne_keterisian_perencanaan_kabkota", "altvarname" => "v_mne_keterisian_perencanaan_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_mne_keterisian_perencanaan_prov", "varname" => "siinsan_at_localhost__v_mne_keterisian_perencanaan_prov", "altvarname" => "v_mne_keterisian_perencanaan_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_pasca_eval", "varname" => "siinsan_at_localhost__v_pasca_eval", "altvarname" => "v_pasca_eval", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_pasca_eval_total", "varname" => "siinsan_at_localhost__v_pasca_eval_total", "altvarname" => "v_pasca_eval_total", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_pasca_eval_total_kategori", "varname" => "siinsan_at_localhost__v_pasca_eval_total_kategori", "altvarname" => "v_pasca_eval_total_kategori", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_pelaksanaan_eval", "varname" => "siinsan_at_localhost__v_pelaksanaan_eval", "altvarname" => "v_pelaksanaan_eval", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_pelaksanaan_eval_total", "varname" => "siinsan_at_localhost__v_pelaksanaan_eval_total", "altvarname" => "v_pelaksanaan_eval_total", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_pelaksanaan_eval_total_kategori", "varname" => "siinsan_at_localhost__v_pelaksanaan_eval_total_kategori", "altvarname" => "v_pelaksanaan_eval_total_kategori", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_sanimas_jumlah_by_tahun", "varname" => "siinsan_at_localhost__v_sanimas_jumlah_by_tahun", "altvarname" => "v_sanimas_jumlah_by_tahun", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_sp_pemrosesan_keterisian_kabkota", "varname" => "siinsan_at_localhost__v_sp_pemrosesan_keterisian_kabkota", "altvarname" => "v_sp_pemrosesan_keterisian_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_sp_pemrosesan_keterisian_prov", "varname" => "siinsan_at_localhost__v_sp_pemrosesan_keterisian_prov", "altvarname" => "v_sp_pemrosesan_keterisian_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_sp_pengolahan_keterisian_kabkota", "varname" => "siinsan_at_localhost__v_sp_pengolahan_keterisian_kabkota", "altvarname" => "v_sp_pengolahan_keterisian_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_sp_pengolahan_keterisian_prov", "varname" => "siinsan_at_localhost__v_sp_pengolahan_keterisian_prov", "altvarname" => "v_sp_pengolahan_keterisian_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_spaldt_kabkota", "varname" => "siinsan_at_localhost__v_spaldt_kabkota", "altvarname" => "v_spaldt_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_spaldt_pemukiman_jumlah_bytahun", "varname" => "siinsan_at_localhost__v_spaldt_pemukiman_jumlah_bytahun", "altvarname" => "v_spaldt_pemukiman_jumlah_bytahun", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_spaldt_perkotaan_jumlah_bytahun", "varname" => "siinsan_at_localhost__v_spaldt_perkotaan_jumlah_bytahun", "altvarname" => "v_spaldt_perkotaan_jumlah_bytahun", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_spaldt_perkotaan_keterisian_kabkota", "varname" => "siinsan_at_localhost__v_spaldt_perkotaan_keterisian_kabkota", "altvarname" => "v_spaldt_perkotaan_keterisian_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_spaldt_perkotaan_keterisian_prov", "varname" => "siinsan_at_localhost__v_spaldt_perkotaan_keterisian_prov", "altvarname" => "v_spaldt_perkotaan_keterisian_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_spaldt_permukiman_keterisian_kabkota", "varname" => "siinsan_at_localhost__v_spaldt_permukiman_keterisian_kabkota", "altvarname" => "v_spaldt_permukiman_keterisian_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_spaldt_permukiman_keterisian_prov", "varname" => "siinsan_at_localhost__v_spaldt_permukiman_keterisian_prov", "altvarname" => "v_spaldt_permukiman_keterisian_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_spaldt_prov", "varname" => "siinsan_at_localhost__v_spaldt_prov", "altvarname" => "v_spaldt_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_spaldt_tertentu_jumlah_bytahun", "varname" => "siinsan_at_localhost__v_spaldt_tertentu_jumlah_bytahun", "altvarname" => "v_spaldt_tertentu_jumlah_bytahun", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_spaldt_tertentu_keterisian_kabkota", "varname" => "siinsan_at_localhost__v_spaldt_tertentu_keterisian_kabkota", "altvarname" => "v_spaldt_tertentu_keterisian_kabkota", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_spaldt_tertentu_keterisian_prov", "varname" => "siinsan_at_localhost__v_spaldt_tertentu_keterisian_prov", "altvarname" => "v_spaldt_tertentu_keterisian_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_tpa", "varname" => "siinsan_at_localhost__v_tpa", "altvarname" => "v_tpa", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_tpa_jumlah_bytahun", "varname" => "siinsan_at_localhost__v_tpa_jumlah_bytahun", "altvarname" => "v_tpa_jumlah_bytahun", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_tpa_prov", "varname" => "siinsan_at_localhost__v_tpa_prov", "altvarname" => "v_tpa_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_tpa_stat", "varname" => "siinsan_at_localhost__v_tpa_stat", "altvarname" => "v_tpa_stat", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_tparegional_jumlah_bytahun", "varname" => "siinsan_at_localhost__v_tparegional_jumlah_bytahun", "altvarname" => "v_tparegional_jumlah_bytahun", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_tparegional_prov", "varname" => "siinsan_at_localhost__v_tparegional_prov", "altvarname" => "v_tparegional_prov", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_tps3r_jumlah_bytahun", "varname" => "siinsan_at_localhost__v_tps3r_jumlah_bytahun", "altvarname" => "v_tps3r_jumlah_bytahun", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
			$dalTables[$conn->connId][] = array("name" => "v_tpst_jumlah_bytahun", "varname" => "siinsan_at_localhost__v_tpst_jumlah_bytahun", "altvarname" => "v_tpst_jumlah_bytahun", "connId" => "siinsan_at_localhost", "schema" => "", "connName" => "siinsan at localhost");
		}
	}


	public static function PrepareSQL($sql)
	{
		$args = func_get_args();

		$conn = DB::CurrentConnection();

		$tokens = DB::scanTokenString($sql);

		$replacements = array();
		// build array of replacements in this format:
		//	"offset" => position in the string where replacement should be done
		//  "len" => length of original substring to cut out
		//  "insert" => string to insert in place of cut out

		foreach ($tokens["matches"] as $i => $match) {
			$offset = $tokens["offsets"][$i];
			$token = $tokens["tokens"][$i];

			$repl = array(
				"offset" => $offset,
				"len" => strlen($match)
			);
			
			$val = "";
			if (is_numeric($token) && count( $args ) > $token) {
				$val = $args[(int)$token];
			} else {
				$val = RunnerContext::getValue($token);
			}
			
			
			/**
			 * Don't ever dare to alter this code!
			 * Everything outside quotes must be converted to number to avoid SQL injection
			 */
			 $inQuotes = $conn->positionQuoted( $sql, $offset );
			 if( is_array( $val ) ) {
				$_values = array();
				foreach( $val as $v ) {
					if ( $inQuotes ) {
						$_values[] = '\''.$conn->addSlashes( $v ).'\'';
					} else {
						$_values[] = DB::prepareNumberValue( $v );
					}
				}
				$glued = implode( ",", $_values );
				$repl["insert"] = $inQuotes ? substr( $glued, 1, strlen( $glued ) - 2 ) : $glued;
			} else {
				if( $inQuotes ) {
					$repl["insert"] = $conn->addSlashes( $val );
				} else {
					$repl["insert"] = DB::prepareNumberValue( $val );
				}
			}
			
			$replacements[] = $repl;
		}

		//	do replacements
		return RunnerContext::doReplacements( $sql, $replacements );
	}

	/**
	 *	@return Array
	 */
	public static function readSQLTokens( $sql )
	{
		$arr = DB::scanTokenString( $sql );
		return $arr["tokens"];
	}

	/**
	 *	@return Array
	 */
	public static function readMasterTokens( $sql )
	{
		$masterTokens = array();

		$allTokens = DB::readSQLTokens( $sql );
		foreach ( $allTokens as $key => $token )
		{
			$dotPos = strpos(  $token, "." );
			if( $dotPos !== FALSE && strtolower( substr( $token, 0, $dotPos ) ) == "master")
			{
				$masterTokens[] = $token;
			}
		}

		return $masterTokens;
	}

	/**
	 *	Scans SQL string, finds all tokens. Returns three arrays - 'tokens', 'matches' and 'offsets'
	 *  Offsets are positions of corresponding 'matches' items in the string
	 *  Example:
	 *  insert into table values (':aaa', :old.bbb, ':{master.order date}')
	 *  tokens: ["aaa", "old.bbb", "master.order date"]
	 *  matches: [":aaa", ":old.bbb", ":{master.order date}"]
	 *  offsets: [28, 35, 46]
	 *
	 *	Exceptions for tokens without {}
	 *	1. shouldn't start with number
	*		:62aaa
	 *	2. shouldn't follow letter
	 *		x:aaa
	 *	3. shouldn't follow :
	 *		::aaa
	 *
 	 *	@return Array [ "tokens" => Array, "matches" => Array, "offsets" => Array ]
	 */
	public static function scanTokenString($sql)
	{
		$tokens = array();
		$offsets = array();
		$matches = array();

		//	match aaa, old.bbb, master.order date from:
		//	insert into table values (':aaa', :old.bbb, ':{master.order date}')

		$pattern = '/(?:[^\w\:]|^)(\:([a-zA-Z_]{1}[\w\.]*))|\:\{([^\:]*?)\}|(?:[^\w\:]|^)(\:([1-9]+[0-9]*))/';

		$result = findMatches($pattern, $sql);
		foreach ($result as $m) {
			if ($m["submatches"][0] != "") {
				// first variant, no {}
				$matches[] = $m["submatches"][0];
				$tokens[] = $m["submatches"][1];
				$offsets[] = $m["offset"] + strpos($m["match"], $m["submatches"][0]);
			} else if ($m["submatches"][2] != "") {
				// second variant, in {}
				$matches[] = $m["match"];
				$tokens[] = $m["submatches"][2];
				$offsets[] = $m["offset"];
			} else if ($m["submatches"][3] != "") {
				// third variant, numeric like (:1, ':2')
				$matches[] = $m["submatches"][3];
				$tokens[] = $m["submatches"][4];
				$offsets[] = $m["offset"] + strpos($m["match"], $m["submatches"][3]);
			}
		}

		return array("tokens" => $tokens, "matches" => $matches, "offsets" => $offsets);
	}

	public static function scanNewTokenString($sql)
	{
		$tokens = array();
		$offsets = array();
		$matches = array();

		//	match aaa, old.bbb, master.order date from:
		//	insert into table values (':aaa', :old.bbb, ':{master.order date}')

		$pattern = "/\\\${[^\\s\{\\}]+}/";


		$result = findMatches($pattern, $sql);
		foreach ($result as $m) {
			$match = $m["match"];
			if ( $match != "" ) {
				$matches[] = $match;
				$tokens[] = substr( $match, 2, strlen( $match ) - 3 );
				$offsets[] = $m["offset"];
			}
		}

		return array("tokens" => $tokens, "matches" => $matches, "offsets" => $offsets);
	}


	public static function prepareNumberValue( $value )
	{
		$strvalue = str_replace( ",", ".", (string)$value );
		if( is_numeric($strvalue) )
			return $strvalue;
		return 0;
	}

	public static function Lookup( $sql ) {
		$result = DB::Query( $sql );
		if( !$result ) {
			return null;
		}
		$data = $result->fetchNumeric();
		if( !$data ) {
			return null;
		}
		return $data[0];
	}

	public static function DBLookup( $sql ) {
		return DB::Lookup( $sql );
	}

}

?>