<?php
@ini_set("display_errors","1");
@ini_set("display_startup_errors","1");

require_once("include/dbcommon.php");
header("Expires: Thu, 01 Jan 1970 00:00:01 GMT"); 

//	CSRF protection
if( !isPostRequest() )
	return;

if(!isLogged())
{ 
	return;
}
if( !Security::isAdmin() )
{
	return;
}
$nonAdminTablesArr = array();
$nonAdminTablesArr[] = "t_kabupatenkota";
$nonAdminTablesArr[] = "t_provinsi";
$nonAdminTablesArr[] = "user";
$nonAdminTablesArr[] = "sp_pemrosesanakhir";
$nonAdminTablesArr[] = "sp_pemrosesanakhir_jenis";
$nonAdminTablesArr[] = "sp_pemrosesanakhir_kondisi";
$nonAdminTablesArr[] = "sp_pemrosesanakhir_kategoripelayanan";
$nonAdminTablesArr[] = "sp_pemrosesanakhir_jenispengelola";
$nonAdminTablesArr[] = "t_sumber_dana_pembangunan";
$nonAdminTablesArr[] = "t_ada_tidak";
$nonAdminTablesArr[] = "t_ba_status_aset";
$nonAdminTablesArr[] = "sp_pengangkutan";
$nonAdminTablesArr[] = "sp_pengolahan";
$nonAdminTablesArr[] = "sp_profil";
$nonAdminTablesArr[] = "san_profil";
$nonAdminTablesArr[] = "t_ada_rusak_tidak";
$nonAdminTablesArr[] = "al_dataumum";
$nonAdminTablesArr[] = "t_al_provinsi";
$nonAdminTablesArr[] = "t_al_kabupatenkota";
$nonAdminTablesArr[] = "t_al_kecamatan";
$nonAdminTablesArr[] = "t_al_kelurahan";
$nonAdminTablesArr[] = "dra_data_umum";
$nonAdminTablesArr[] = "t_dra_propinsi";
$nonAdminTablesArr[] = "t_dra_kota";
$nonAdminTablesArr[] = "t_dra_kecamatan";
$nonAdminTablesArr[] = "t_dra_kelurahan";
$nonAdminTablesArr[] = "al_cakupanairlimbah";
$nonAdminTablesArr[] = "al_sdm_institusi";
$nonAdminTablesArr[] = "al_sdm_pengelola";
$nonAdminTablesArr[] = "al_iplt";
$nonAdminTablesArr[] = "al_kualitasair";
$nonAdminTablesArr[] = "al_mck";
$nonAdminTablesArr[] = "al_babs_penderita";
$nonAdminTablesArr[] = "al_penggunasaranabab";
$nonAdminTablesArr[] = "t_al_sektor";
$nonAdminTablesArr[] = "al_programkegiatan";
$nonAdminTablesArr[] = "al_setempat";
$nonAdminTablesArr[] = "al_peranserta";
$nonAdminTablesArr[] = "dra_t_jenis_bahan";
$nonAdminTablesArr[] = "dra_data_genangan";
$nonAdminTablesArr[] = "dra_data_pembiayaan";
$nonAdminTablesArr[] = "dra_t_bentuk_saluran";
$nonAdminTablesArr[] = "dra_t_jenis_bangunan";
$nonAdminTablesArr[] = "mne_t_sektor";
$nonAdminTablesArr[] = "mne_t_jenis_infrastruktur";
$nonAdminTablesArr[] = "mne_t_sumberdana";
$nonAdminTablesArr[] = "mne_t_statususulan";
$nonAdminTablesArr[] = "mne_t_kategori_perencanaan";
$nonAdminTablesArr[] = "mne_t_tahap_perencanaan";
$nonAdminTablesArr[] = "mne_t_sub_tahap_perencanaan";
$nonAdminTablesArr[] = "mne_t_param_sub_tahap_perencanaan";
$nonAdminTablesArr[] = "mne_t_pelaksanaan_konstruksi";
$nonAdminTablesArr[] = "mne_t_sub_pelaksanaan_konstruksi";
$nonAdminTablesArr[] = "mne_t_param_sub_pelaksanaan";
$nonAdminTablesArr[] = "san_spm_stbm";
$nonAdminTablesArr[] = "ssk_file";
$nonAdminTablesArr[] = "ssk_dokumen";
$nonAdminTablesArr[] = "ssk_tahun";
$nonAdminTablesArr[] = "ssk_pokja";
$nonAdminTablesArr[] = "san_profil_ver";
$nonAdminTablesArr[] = "t_verifikasi";
$nonAdminTablesArr[] = "san_spm_stbm_ver";
$nonAdminTablesArr[] = "sp_pemrosesanakhir_sistem";
$nonAdminTablesArr[] = "t_dimanfaatkan";
$nonAdminTablesArr[] = "sp_tparegional";
$nonAdminTablesArr[] = "sp_profil_prov";
$nonAdminTablesArr[] = "sp_banksampah";
$nonAdminTablesArr[] = "san_spm_ald";
$nonAdminTablesArr[] = "t_kecamatan";
$nonAdminTablesArr[] = "t_kelurahan";
$nonAdminTablesArr[] = "san_spm_ald_ver";
$nonAdminTablesArr[] = "al_dataumum_tertentu";
$nonAdminTablesArr[] = "al_dataumum_pemukiman";
$nonAdminTablesArr[] = "v_tpa_Report";
$nonAdminTablesArr[] = "v_tpa_stat_Report";
$nonAdminTablesArr[] = "usertype";
$nonAdminTablesArr[] = "mne_t_pasca_konstruksi";
$nonAdminTablesArr[] = "mne_t_sub_pasca_konstruksi";
$nonAdminTablesArr[] = "mne_t_param_sub_pasca_konstruksi";
$nonAdminTablesArr[] = "mne_t_kriteria_evaluasi";
$nonAdminTablesArr[] = "t_aktif";
$nonAdminTablesArr[] = "san_rencanainduk";
$nonAdminTablesArr[] = "v_tpa_jumlah_bytahun_Chart";
$nonAdminTablesArr[] = "v_tparegional_jumlah_bytahun_Chart";
$nonAdminTablesArr[] = "v_tps3r_jumlah_bytahun_Chart";
$nonAdminTablesArr[] = "v_tpst_jumlah_bytahun_Chart";
$nonAdminTablesArr[] = "v_iplt_jumlah_bytahun_Chart";
$nonAdminTablesArr[] = "v_sanimas_jumlah_by_tahun_Chart";
$nonAdminTablesArr[] = "v_spaldt_pemukiman_jumlah_bytahun_Chart";
$nonAdminTablesArr[] = "v_spaldt_perkotaan_jumlah_bytahun_Chart";
$nonAdminTablesArr[] = "v_spaldt_tertentu_jumlah_bytahun_Chart";
$nonAdminTablesArr[] = "v_banksampah_jumlah_bytahun_Chart";
$nonAdminTablesArr[] = "Dashboard";
$nonAdminTablesArr[] = "v_dra_lokasi_genangan_Report";
$nonAdminTablesArr[] = "san_peraturan";
$nonAdminTablesArr[] = "t_jenis_peraturan";
$nonAdminTablesArr[] = "sp_tpa_alatberat";
$nonAdminTablesArr[] = "sp_tpa_saranapengangkutan";
$nonAdminTablesArr[] = "sp_t_jenis_alatberat";
$nonAdminTablesArr[] = "t_kondisi";
$nonAdminTablesArr[] = "sp_t_jenis_saranaangkut";
$nonAdminTablesArr[] = "t_status_lahan";
$nonAdminTablesArr[] = "al_t_jenis_infrastruktur";
$nonAdminTablesArr[] = "al_cakupanairlimbah_pemukiman";
$nonAdminTablesArr[] = "al_cakupanairlimbah_tertentu";
$nonAdminTablesArr[] = "al_dataumum_ver_prov";
$nonAdminTablesArr[] = "al_dataumum_pemukiman_ver_prov";
$nonAdminTablesArr[] = "al_dataumum_tertentu_ver_prov";
$nonAdminTablesArr[] = "al_iplt_rev_prov";
$nonAdminTablesArr[] = "al_iplt_rev_pusat";
$nonAdminTablesArr[] = "san_rencanainduk_ver_prov";
$nonAdminTablesArr[] = "san_rencanainduk_ver";
$nonAdminTablesArr[] = "mne_umum";
$nonAdminTablesArr[] = "mne_umum_kesiapan";
$nonAdminTablesArr[] = "t_mne_tahap";
$nonAdminTablesArr[] = "mne_umum_transisi_pelaksanaan";
$nonAdminTablesArr[] = "mne_umum_pelaksanaan";
$nonAdminTablesArr[] = "mne_t_jenis_kontrak";
$nonAdminTablesArr[] = "mne_umum_transisi_pasca";
$nonAdminTablesArr[] = "mne_umum_pasca";
$nonAdminTablesArr[] = "mne_umum_transisi_kesiapan";
$nonAdminTablesArr[] = "t_mne_tahap_kekersiapan";
$nonAdminTablesArr[] = "t_mne_tahap_kepasca";
$nonAdminTablesArr[] = "t_mne_tahap_kepelaksanaan";
$nonAdminTablesArr[] = "sp_pemrosesanakhir_ver_prov";
$nonAdminTablesArr[] = "sp_pemrosesanakhir_ver";
$nonAdminTablesArr[] = "al_dataumum_pemukiman_ver";
$nonAdminTablesArr[] = "al_dataumum_ver";
$nonAdminTablesArr[] = "al_dataumum_tertentu_ver";
$nonAdminTablesArr[] = "TPS3R";
$nonAdminTablesArr[] = "Sanimas";
$nonAdminTablesArr[] = "PadatKarya";
$nonAdminTablesArr[] = "san_spm_ald_ver_prov";
$nonAdminTablesArr[] = "sp_pengolahan_ver_prov";
$nonAdminTablesArr[] = "sp_pengolahan_ver";
$nonAdminTablesArr[] = "Sanimas_prov";
$nonAdminTablesArr[] = "PadatKarya_prov";
$nonAdminTablesArr[] = "TPS3R_prov";
$nonAdminTablesArr[] = "mne_umum_kabkota";
$nonAdminTablesArr[] = "tr_pengumuman";
$nonAdminTablesArr[] = "sp_tparegional_ver_prov";
$nonAdminTablesArr[] = "sp_tparegional_ver";
$nonAdminTablesArr[] = "sp_banksampah_ver";
$nonAdminTablesArr[] = "sp_banksampah_ver_prov";
$nonAdminTablesArr[] = "t_status_keberfungsian";
$nonAdminTablesArr[] = "t_kualitas_keberfungsian";
$nonAdminTablesArr[] = "t_kondisi_fasilitas";
$nonAdminTablesArr[] = "t_kategori_pelayanan";
$nonAdminTablesArr[] = "t_jenis_pengelola";
$nonAdminTablesArr[] = "mne_umum_verifikasi_perencanaan";
$nonAdminTablesArr[] = "mne_kesiapan_eval";
$nonAdminTablesArr[] = "mne_pelaksanaan_eval";
$nonAdminTablesArr[] = "mne_pasca_eval";
$nonAdminTablesArr[] = "v_spaldt_kabkota_Report";
$nonAdminTablesArr[] = "v_spaldt_prov";
$nonAdminTablesArr[] = "v_iplt_prov";
$nonAdminTablesArr[] = "v_kesiapan_eval_Report";
$nonAdminTablesArr[] = "v_pelaksanaan_eval_Report";
$nonAdminTablesArr[] = "v_pasca_eval_Report";
$nonAdminTablesArr[] = "v_kesiapan_eval_total_Report";
$nonAdminTablesArr[] = "v_pelaksanaan_eval_total_Report";
$nonAdminTablesArr[] = "v_pasca_eval_total_Report";
$nonAdminTablesArr[] = "v_kesiapan_eval_total_kategori_Report";
$nonAdminTablesArr[] = "v_pelaksanaan_eval_total_kategori_Report";
$nonAdminTablesArr[] = "v_pasca_eval_total_kategori_Report";
$nonAdminTablesArr[] = "al_dataumum_admin";
$nonAdminTablesArr[] = "v_kesiapan_eval_prov_Report";
$nonAdminTablesArr[] = "v_al_iplt_keterisian_prov";
$nonAdminTablesArr[] = "v_sp_pemrosesan_keterisian_prov";
$nonAdminTablesArr[] = "v_al_iplt_keterisian_kabkota_Report";
$nonAdminTablesArr[] = "v_sp_pemrosesan_keterisian_kabkota_Report";
$nonAdminTablesArr[] = "mne_umum_renonly";
$nonAdminTablesArr[] = "mne_umum_verifiedonly";
$nonAdminTablesArr[] = "mne_kesiapan_penentu";
$nonAdminTablesArr[] = "t_yatidak";
$nonAdminTablesArr[] = "sigdrainase_saluran_drainase";
$nonAdminTablesArr[] = "sigdrainase_kontruksi_drainase";
$nonAdminTablesArr[] = "al_iplt_admin";
$nonAdminTablesArr[] = "sp_pemrosesanakhir_admin";
$nonAdminTablesArr[] = "sp_banksampah_admin";
$nonAdminTablesArr[] = "sp_pengolahan_admin";
$nonAdminTablesArr[] = "al_t_jenis_pengolahan_akhir";
$nonAdminTablesArr[] = "al_t_opsi_teknologi";
$nonAdminTablesArr[] = "al_t_sistem_pengolahan";
$nonAdminTablesArr[] = "sigdrainase_sumur";
$nonAdminTablesArr[] = "sigdrainase_kolam_retensi";
$nonAdminTablesArr[] = "v_spaldt_perkotaan_keterisian_prov";
$nonAdminTablesArr[] = "v_spaldt_permukiman_keterisian_prov";
$nonAdminTablesArr[] = "v_spaldt_tertentu_keterisian_prov";
$nonAdminTablesArr[] = "v_sp_pengolahan_keterisian_prov";
$nonAdminTablesArr[] = "v_sp_pengolahan_keterisian_kabkota_Report";
$nonAdminTablesArr[] = "v_spaldt_perkotaan_keterisian_kabkota_Report";
$nonAdminTablesArr[] = "v_spaldt_permukiman_keterisian_kabkota_Report";
$nonAdminTablesArr[] = "v_spaldt_tertentu_keterisian_kabkota_Report";
$nonAdminTablesArr[] = "dra_data_genangan_manual";
$nonAdminTablesArr[] = "sp_t_opsi_teknologi_organik";
$nonAdminTablesArr[] = "v_mne_keterisian_kesiapan";
$nonAdminTablesArr[] = "v_mne_keterisian_kesiapan_prov";
$nonAdminTablesArr[] = "v_mne_keterisian_pasca";
$nonAdminTablesArr[] = "v_mne_keterisian_pasca_prov";
$nonAdminTablesArr[] = "v_mne_keterisian_pelaksanaan";
$nonAdminTablesArr[] = "v_mne_keterisian_pelaksanaan_prov";
$nonAdminTablesArr[] = "v_mne_keterisian_perencanaan";
$nonAdminTablesArr[] = "v_mne_keterisian_perencanaan_prov";
$nonAdminTablesArr[] = "v_mne_keterisian_kesiapan_kabkota_Report";
$nonAdminTablesArr[] = "v_mne_keterisian_pasca_kabkota_Report";
$nonAdminTablesArr[] = "v_mne_keterisian_pelaksanaan_kabkota_Report";
$nonAdminTablesArr[] = "v_mne_keterisian_perencanaan_kabkota_Report";
$nonAdminTablesArr[] = "sp_pemrosesanakhir_pengembangan";
$nonAdminTablesArr[] = "sp_t_opsi_teknologi_anorganik";
$nonAdminTablesArr[] = "v_dra_keterisian_genangan_prov";
$nonAdminTablesArr[] = "v_dra_keterisian_saluran_prov";
$nonAdminTablesArr[] = "v_dra_keterisian_genangan_kabkota_Report";
$nonAdminTablesArr[] = "v_dra_keterisian_saluran_kabkota_Report";
$nonAdminTablesArr[] = "v_iplt_terverifikasi_Report";
$nonAdminTablesArr[] = "v_iplt_belum_terverifikasi_Report";
$nonAdminTablesArr[] = "v_al_dataumum_permukiman_terverifikasi";
$nonAdminTablesArr[] = "v_al_dataumum_permukiman_belum_terverifikasi";
$nonAdminTablesArr[] = "v_al_dataumum_perkotaan_terverifikasi";
$nonAdminTablesArr[] = "v_al_dataumum_perkotaan_belum_terverifikasi";
$nonAdminTablesArr[] = "v_al_dataumum_tertetu_terverifikasi";
$nonAdminTablesArr[] = "v_al_dataumum_tertentu_belum_terverifikasi";
$nonAdminTablesArr[] = "al_dataumum_prov";
$nonAdminTablesArr[] = "al_dataumum_tertentu_prov";
$nonAdminTablesArr[] = "al_dataumum_permukiman_prov";
$nonAdminTablesArr[] = "al_iplt_prov";
$nonAdminTablesArr[] = "sp_pemrosesanakhir_prov";
$nonAdminTablesArr[] = "sp_tparegional_prov";
$nonAdminTablesArr[] = "sp_pengolahan_prov";
$nonAdminTablesArr[] = "sigdrainase_saluran_drainase_prov";
$nonAdminTablesArr[] = "dra_data_genangan_prov";
$nonAdminTablesArr[] = "al_spalds";
$nonAdminTablesArr[] = "al_t_suplai_air";
$nonAdminTablesArr[] = "ibm_lpk";
$nonAdminTablesArr[] = "ibm_padatkarya_ver";
$nonAdminTablesArr[] = "ibm_sanimas_ver";
$nonAdminTablesArr[] = "ibm_tps3r_ver";
$nonAdminTablesArr[] = "psn_capaian";
$nonAdminTablesArr[] = "psn_komposisi";
$nonAdminTablesArr[] = "al_spalds_prov";
$nonAdminTablesArr[] = "Dashboard Kabupaten/Kota";
$nonAdminTablesArr[] = "dash_persampahan_kabkota";
$nonAdminTablesArr[] = "Dashboard Provinsi";
$nonAdminTablesArr[] = "dash_airlimbah_kabkota";
$nonAdminTablesArr[] = "dash_airlimbah_nas";
$nonAdminTablesArr[] = "dash_airlimbah_prov";
$nonAdminTablesArr[] = "dash_persampahan_nas";
$nonAdminTablesArr[] = "dash_persampahan_prov";
$nonAdminTablesArr[] = "dash_saluran_drainase_kabkota";
$nonAdminTablesArr[] = "dash_saluran_drainase_nas";
$nonAdminTablesArr[] = "dash_saluran_drainase_prov";
$nonAdminTablesArr[] = "t_al_skalapelayanan";
$nonAdminTablesArr[] = "dash_spaldt_infra";
$nonAdminTablesArr[] = "dash_verifikasi_prov_kabkota";
$nonAdminTablesArr[] = "dash_verifikasi_prov_nas";
$nonAdminTablesArr[] = "dash_verifikasi_prov_prov";
$nonAdminTablesArr[] = "dash_spaldt_infra_prov";
$nonAdminTablesArr[] = "dash_spaldt_infra_kabkota";
$nonAdminTablesArr[] = "v_tpa_prov";
$nonAdminTablesArr[] = "v_tparegional_prov";
$nonAdminTablesArr[] = "dra_data_genangan_ver_prov";
$nonAdminTablesArr[] = "dra_data_genangan_ver";
$nonAdminTablesArr[] = "sigdrainase_saluran_drainase_ver_prov";
$nonAdminTablesArr[] = "sigdrainase_saluran_drainase_ver";
$nonAdminTablesArr[] = "dash_verifikasi_pusat_kabkota";
$nonAdminTablesArr[] = "dash_verifikasi_pusat_nas";
$nonAdminTablesArr[] = "dash_verifikasi_pusat_prov";
$nonAdminTablesArr[] = "sippa_renja1_2018";
$nonAdminTablesArr[] = "sippa_renja1_2019";
$nonAdminTablesArr[] = "sippa_renja1_2020";
$nonAdminTablesArr[] = "sippa_renja1_2021";
$nonAdminTablesArr[] = "sippa_renja1_2022";
$nonAdminTablesArr[] = "simdak_v_rekap_dak";
$nonAdminTablesArr[] = "simdak_v_rekap_drainase";
$nonAdminTablesArr[] = "simdak_v_rekap_ipal";
$nonAdminTablesArr[] = "simdak_v_rekap_ipal_mck";
$nonAdminTablesArr[] = "mne_umum_sippa";
$nonAdminTablesArr[] = "sippa_renja1";

$ug_connection = $cman->getForUserGroups();

$cbxNames = array('add' => array('mask' => 'A', 'rightName' => 'add')
	, 'edt' => array('mask' => 'E', 'rightName' => 'edit')
	, 'del' => array('mask' => 'D', 'rightName' => 'delete')
	, 'lst' => array('mask' => 'S', 'rightName' => 'list')
	, 'exp' => array('mask' => 'P', 'rightName' => 'export')
	, 'imp' => array('mask' => 'I', 'rightName' => 'import')
	, 'adm' => array('mask' => 'M'));

$wGroupTableName = $ug_connection->addTableWrappers( "siinsan_uggroups" );
	
switch(postvalue("a"))
{
	case "add":
		$sql = "insert into ". $wGroupTableName ." (". $ug_connection->addFieldWrappers( "Label" ) .")"
			." values (". $ug_connection->prepareString( postvalue("name") ). ")";		
		$ug_connection->exec( $sql );

		$sql = "select max(". $ug_connection->addFieldWrappers( "GroupID") .") from ". $wGroupTableName;
		$data = $ug_connection->query( $sql )->fetchNumeric();
		
		echo printJSON( array('success' => true, 'id' => $data[0]) );
		break;
		
	case "del":
		$sql = "delete from ". $wGroupTableName ." where ". $ug_connection->addFieldWrappers("GroupID") ."=".(postvalue_number("id"));
		$ug_connection->exec( $sql );
		
		$sql = "delete from ". $ug_connection->addTableWrappers( "siinsan_ugrights" ) 
			." where ". $ug_connection->addFieldWrappers( "GroupID" ) ."=".(postvalue_number("id"));
		$ug_connection->exec( $sql );

		// delete records from ugmembers table	
		$dataSource = Security::getUgMembersDatasource();
		$dc = new DsCommand();
		$dc->filter = DataCondition::FieldEquals( "GroupID", postvalue_number("id") ); 
		$dataSource->deleteSingle( $dc, false );
		
		echo printJSON( array('success' => true) );
		break;
		
	case "rename":
		$sql = "update ". $wGroupTableName  
			." set ". $ug_connection->addFieldWrappers( "Label" ) ."=". $ug_connection->prepareString( postvalue("name") )
			." where ". $ug_connection->addFieldWrappers( "GroupID" ) ."=".(postvalue_number("id"));
		$ug_connection->exec( $sql );
		
		echo printJSON( array('success' => true) );
		break;
	
	// @deprecated 
	// see ug_rights
	case 'saveRights':
		$error = '';
		if( postvalue('state') )
		{	
			$allRights = array();
			$sql = "select ". $ug_connection->addFieldWrappers( "GroupID" ) 
				.", ". $ug_connection->addFieldWrappers( "TableName" ) 
				.", ". $ug_connection->addFieldWrappers( "AccessMask" ) ." from ". $wGroupTableName;
			
			$qResult = $ug_connection->query( $sql );
			// don't use fetchAssoc! because of ORACLE and PostgreSQL
			while( $rightsRow = $qResult->fetchNumeric() )
			{
				$allRights[] = $rightsRow;
			}
			
			$wRightsTableName = $ug_connection->addTableWrappers( "siinsan_ugrights" );
			
			$delGroupId = 0;
			$state = my_json_decode( postvalue('state') );
			// delete all extra permissions from db
			foreach($allRights as $i => $rightValue)
			{
				$groupIDInt = (int) $rightValue[0];
				
				if($groupIDInt == $delGroupId)
					continue;
					
				//delete all extra permissions for group
				if( !array_key_exists($groupIDInt, $state) )
				{
					$sql = "delete from ". $wRightsTableName 
						." where ". $ug_connection->addFieldWrappers( "GroupID" ) ."=". $groupIDInt;
					$ug_connection->exec( $sql );
				}
				//delete all extra permissions for table in group
				else if(!array_key_exists(GetTableId($data[1]), $state[$groupIDInt]))
				{
					$sql = "delete from ". $wRightsTableName 
						." where ". $ug_connection->addFieldWrappers( "GroupID" ) ."=". $groupIDInt 
						." and ". $ug_connection->addFieldWrappers( "TableName" ) ."=".$ug_connection->prepareString( html_special_decode($data[1]) );				
					$ug_connection->exec( $sql );
				}
			}
			
			$realTables = GetRealValues();
			foreach ($state as $groupId => $groupRights)
			{
				foreach ($groupRights as $table => $mask)
				{
					if( !array_key_exists($table, $realTables) )
						continue;
					
					$ins = true;
					foreach($allRights as $i => $rightValue)
					{	
						if($rightValue[0] == $groupId && $rightValue[1] == $realTables[$table])	
						{
							$ins = false;
							if($data[2]!= $mask)
							{
								$sql ="update". $wRightsTableName 
									." set ". $ug_connection->addFieldWrappers( "AccessMask" ) ."=". $ug_connection->prepareString( $mask )
									." where ". $ug_connection->addFieldWrappers( "GroupID" ) ."=". $groupId 
									." and ". $ug_connection->addFieldWrappers( "TableName" ) ."=". $ug_connection->prepareString( html_special_decode($realTables[$table]) );
								$ug_connection->exec( $sql );
							}
						}
					}
					if($ins)
					{
						$sql = "insert into ". $wRightsTableName
							." (". $ug_connection->addFieldWrappers( "TableName" ) 
							.", ". $ug_connection->addFieldWrappers( "GroupID" ) 
							.", ". $ug_connection->addFieldWrappers( "AccessMask" ) .") " 
							."values (". $ug_connection->prepareString(html_special_decode($realTables[$table])) .", ". $groupId .", ". $ug_connection->prepareString($mask)  .")";
						$ug_connection->exec( $sql );
					}
					
					$error = $ug_connection->lastError();
				}
			}
		}
		
		getJSONResult($error);
		break;
}

function GetTableId($name)
{
	$tbls = GetRealValues();
	for($i = 0;$i < count($tbls); $i++)
	{
		if($tbls[$i] == $name)
			return $i;
	}
	return -1;
}

/**
 * GetRealValues
 * Form array with real users or tables names
 * @return {array} array of reaf names
 */
function GetRealValues()
{
	$result = array();
	if(postvalue('realValues'))
		$realValues = my_json_decode(postvalue('realValues'));
		foreach ($realValues as $key =>$value)
			$result[$key] = $value;
	return $result;
}

/**
 * getJSONResult
 * Form result as a JSON object according of errors
 * @param {string} list of errors
 */
function getJSONResult($error)
{
	$result['success'] = $error == '';
	$result['error'] = $error;	
	echo printJSON($result);
}