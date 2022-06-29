<?php
class ConnectionManager_Base
{
	/**
	 * Cached Connection objects
	 * @type Array
	 */
	protected $cache = array();

	/**
	 * Project connections data
	 * @type Array
	 */
	protected $_connectionsData;

	/**
	 * Project connections data
	 * @type Array
	 */
	protected $_connectionsIdByName = array();


	/**
	 * An array storing the correspondence between project
	 * datasource tables names and connections ids
	 * @type Array
	 */
	protected $_tablesConnectionIds;


	/**
	 * @constructor
	 */
	function __construct()
	{
		$this->_setConnectionsData();
		$this->_setTablesConnectionIds();
	}

	/**
	 * Get connection id by the table name
	 * @param String tName
	 * @return Connection
	 */
	public function getTableConnId( $tName )
	{
		return $this->_tablesConnectionIds[ $tName ];
	}


	/**
	 * Get connection object by the table name
	 * @param String tName
	 * @return Connection
	 */
	public function byTable( $tName )
	{
		$connId = $this->_tablesConnectionIds[ $tName ];
		if( !$connId )
			return $this->getDefault();
		return $this->byId( $connId );
	}

	/**
	 * Get connection object by the connection name
	 * @param String connName
	 * @return Connection
	 */
	public function byName( $connName )
	{
		$connId = $this->getIdByName( $connName );
		if( !$connId )
			return $this->getDefault();
		return $this->byId( $connId );
	}

	/**
	 * Get connection id by the connection name
	 * @param String connName
	 * @return String
	 */
	protected function getIdByName( $connName )
	{
		return $this->_connectionsIdByName[ $connName ];
	}

	/**
	 * Get connection object by the connection id
	 * @param String connId
	 * @return Connection
	 */
	public function byId( $connId )
	{
		if( !isset( $this->cache[ $connId ] ) ) {
			$conn = $this->getConnection( $connId );
			if( !$conn ) {
				global $restApis;
				$conn = $restApis->getConnection( $connId );
			}
			if( !$conn ) {
				$conn = $this->getDefault();
			}
			$this->cache[ $connId ] = $conn;
		}

		return $this->cache[ $connId ];
	}

	/**
	 * Get the default db connection class
	 * @return Connection
	 */
	public function getDefault()
	{
		return $this->byId( "siinsan_at_localhost" );
	}

	/**
	 * Get the default db connection id
	 * @return String
	 */
	public function getDefaultConnId()
	{
		return "siinsan_at_localhost";
	}



	/**
	 * Get the users table db connection
	 * @return Connection
	 */
	public function getForLogin() {
		return $this->byId( $this->getLoginConnId() );
	}

	public function getLoginConnId() {
		$db = &Security::dbProvider();		
		if( $db ) {
			return $db["table"]["connId"];
		}
		return "";
	}


	/**
	 * Get the log table db connection
	 * @return Connection
	 */
	public function getForAudit()
	{
		return $this->byId( "siinsan_at_localhost" );
	}

	/**
	 * Get the locking table db connection
	 * @return Connection
	 */
	public function getForLocking()
	{
		return $this->byId( "siinsan_at_localhost" );
	}

	/**
	 * Get the 'ug_groups' table db connection
	 * @return Connection
	 */
	public function getForUserGroups() {
		return $this->byId( $this->getUserGroupsConnId() );
	}

	public function getUserGroupsConnId() {
		return "siinsan_at_localhost";
	}

	/**
	 * Get the saved searches table db connection
	 * @return Connection
	 */
	public function getForSavedSearches()
	{
		return $this->byId( $this->getSavedSearchesConnId() );
	}
	
	/**
	 * Get the saved searches table db connection
	 * @return Connection
	 */
	public function getSavedSearchesConnId()
	{
		return "siinsan_at_localhost";
	}	

	/**
	 * Get the webreports tables db connection
	 * @return Connection
	 */
	public function getForWebReports() 
	{
		return $this->byId( $this->getSavedSearchesConnId() );
	}

	/**
	 * Get the webreports tables db connection id
	 * @return String
	 */
	public function getWebReportsConnId() {
		return "siinsan_at_localhost";
	}	
	
	/**
	 * @param String connId
	 * @return Connection
	 */
	protected function getConnection( $connId )
	{
		return false;
	}

	public function getConectionsIds()
	{
		$connectionsIds = array();
		foreach ($this->_connectionsData as $connId => $data) {
			$connectionsIds[] = $connId;
		}

		return $connectionsIds;
	}

	/**
	 * Set the data representing the project's
	 * db connection properties
	 */
	protected function _setConnectionsData()
	{
        return null;
	}

	/**
	 * Set the data representing the correspondence between
	 * the project's table names and db connections
	 */
	protected function _setTablesConnectionIds()
	{
		$connectionsIds = array();
		$connectionsIds["t_kabupatenkota"] = "siinsan_at_localhost";
		$connectionsIds["t_provinsi"] = "siinsan_at_localhost";
		$connectionsIds["user"] = "siinsan_at_localhost";
		$connectionsIds["admin_rights"] = "siinsan_at_localhost";
		$connectionsIds["admin_members"] = "siinsan_at_localhost";
		$connectionsIds["sp_pemrosesanakhir"] = "siinsan_at_localhost";
		$connectionsIds["sp_pemrosesanakhir_jenis"] = "siinsan_at_localhost";
		$connectionsIds["sp_pemrosesanakhir_kondisi"] = "siinsan_at_localhost";
		$connectionsIds["sp_pemrosesanakhir_kategoripelayanan"] = "siinsan_at_localhost";
		$connectionsIds["sp_pemrosesanakhir_jenispengelola"] = "siinsan_at_localhost";
		$connectionsIds["t_sumber_dana_pembangunan"] = "siinsan_at_localhost";
		$connectionsIds["t_ada_tidak"] = "siinsan_at_localhost";
		$connectionsIds["t_ba_status_aset"] = "siinsan_at_localhost";
		$connectionsIds["sp_pengangkutan"] = "siinsan_at_localhost";
		$connectionsIds["sp_pengolahan"] = "siinsan_at_localhost";
		$connectionsIds["sp_profil"] = "siinsan_at_localhost";
		$connectionsIds["san_profil"] = "siinsan_at_localhost";
		$connectionsIds["t_ada_rusak_tidak"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum"] = "siinsan_at_localhost";
		$connectionsIds["t_al_provinsi"] = "siinsan_at_localhost";
		$connectionsIds["t_al_kabupatenkota"] = "siinsan_at_localhost";
		$connectionsIds["t_al_kecamatan"] = "siinsan_at_localhost";
		$connectionsIds["t_al_kelurahan"] = "siinsan_at_localhost";
		$connectionsIds["dra_data_umum"] = "siinsan_at_localhost";
		$connectionsIds["t_dra_propinsi"] = "siinsan_at_localhost";
		$connectionsIds["t_dra_kota"] = "siinsan_at_localhost";
		$connectionsIds["t_dra_kecamatan"] = "siinsan_at_localhost";
		$connectionsIds["t_dra_kelurahan"] = "siinsan_at_localhost";
		$connectionsIds["al_cakupanairlimbah"] = "siinsan_at_localhost";
		$connectionsIds["al_sdm_institusi"] = "siinsan_at_localhost";
		$connectionsIds["al_sdm_pengelola"] = "siinsan_at_localhost";
		$connectionsIds["al_iplt"] = "siinsan_at_localhost";
		$connectionsIds["al_kualitasair"] = "siinsan_at_localhost";
		$connectionsIds["al_mck"] = "siinsan_at_localhost";
		$connectionsIds["al_babs_penderita"] = "siinsan_at_localhost";
		$connectionsIds["al_penggunasaranabab"] = "siinsan_at_localhost";
		$connectionsIds["t_al_sektor"] = "siinsan_at_localhost";
		$connectionsIds["al_programkegiatan"] = "siinsan_at_localhost";
		$connectionsIds["al_setempat"] = "siinsan_at_localhost";
		$connectionsIds["al_peranserta"] = "siinsan_at_localhost";
		$connectionsIds["dra_t_jenis_bahan"] = "siinsan_at_localhost";
		$connectionsIds["dra_data_genangan"] = "siinsan_at_localhost";
		$connectionsIds["dra_data_pembiayaan"] = "siinsan_at_localhost";
		$connectionsIds["dra_t_bentuk_saluran"] = "siinsan_at_localhost";
		$connectionsIds["dra_t_jenis_bangunan"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_sektor"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_jenis_infrastruktur"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_sumberdana"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_statususulan"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_kategori_perencanaan"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_tahap_perencanaan"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_sub_tahap_perencanaan"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_param_sub_tahap_perencanaan"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_pelaksanaan_konstruksi"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_sub_pelaksanaan_konstruksi"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_param_sub_pelaksanaan"] = "siinsan_at_localhost";
		$connectionsIds["san_spm_stbm"] = "siinsan_at_localhost";
		$connectionsIds["ssk_file"] = "siinsan_at_localhost";
		$connectionsIds["ssk_dokumen"] = "siinsan_at_localhost";
		$connectionsIds["ssk_tahun"] = "siinsan_at_localhost";
		$connectionsIds["ssk_pokja"] = "siinsan_at_localhost";
		$connectionsIds["san_profil_ver"] = "siinsan_at_localhost";
		$connectionsIds["t_verifikasi"] = "siinsan_at_localhost";
		$connectionsIds["san_spm_stbm_ver"] = "siinsan_at_localhost";
		$connectionsIds["sp_pemrosesanakhir_sistem"] = "siinsan_at_localhost";
		$connectionsIds["t_dimanfaatkan"] = "siinsan_at_localhost";
		$connectionsIds["sp_tparegional"] = "siinsan_at_localhost";
		$connectionsIds["sp_profil_prov"] = "siinsan_at_localhost";
		$connectionsIds["sp_banksampah"] = "siinsan_at_localhost";
		$connectionsIds["san_spm_ald"] = "siinsan_at_localhost";
		$connectionsIds["t_kecamatan"] = "siinsan_at_localhost";
		$connectionsIds["t_kelurahan"] = "siinsan_at_localhost";
		$connectionsIds["san_spm_ald_ver"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum_tertentu"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum_pemukiman"] = "siinsan_at_localhost";
		$connectionsIds["v_tpa_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_tpa_stat_Report"] = "siinsan_at_localhost";
		$connectionsIds["usertype"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_pasca_konstruksi"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_sub_pasca_konstruksi"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_param_sub_pasca_konstruksi"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_kriteria_evaluasi"] = "siinsan_at_localhost";
		$connectionsIds["t_aktif"] = "siinsan_at_localhost";
		$connectionsIds["san_rencanainduk"] = "siinsan_at_localhost";
		$connectionsIds["v_tpa_jumlah_bytahun_Chart"] = "siinsan_at_localhost";
		$connectionsIds["v_tparegional_jumlah_bytahun_Chart"] = "siinsan_at_localhost";
		$connectionsIds["v_tps3r_jumlah_bytahun_Chart"] = "siinsan_at_localhost";
		$connectionsIds["v_tpst_jumlah_bytahun_Chart"] = "siinsan_at_localhost";
		$connectionsIds["v_iplt_jumlah_bytahun_Chart"] = "siinsan_at_localhost";
		$connectionsIds["v_sanimas_jumlah_by_tahun_Chart"] = "siinsan_at_localhost";
		$connectionsIds["v_spaldt_pemukiman_jumlah_bytahun_Chart"] = "siinsan_at_localhost";
		$connectionsIds["v_spaldt_perkotaan_jumlah_bytahun_Chart"] = "siinsan_at_localhost";
		$connectionsIds["v_spaldt_tertentu_jumlah_bytahun_Chart"] = "siinsan_at_localhost";
		$connectionsIds["v_banksampah_jumlah_bytahun_Chart"] = "siinsan_at_localhost";
		$connectionsIds["v_dra_lokasi_genangan_Report"] = "siinsan_at_localhost";
		$connectionsIds["san_peraturan"] = "siinsan_at_localhost";
		$connectionsIds["t_jenis_peraturan"] = "siinsan_at_localhost";
		$connectionsIds["sp_tpa_alatberat"] = "siinsan_at_localhost";
		$connectionsIds["sp_tpa_saranapengangkutan"] = "siinsan_at_localhost";
		$connectionsIds["sp_t_jenis_alatberat"] = "siinsan_at_localhost";
		$connectionsIds["t_kondisi"] = "siinsan_at_localhost";
		$connectionsIds["sp_t_jenis_saranaangkut"] = "siinsan_at_localhost";
		$connectionsIds["t_status_lahan"] = "siinsan_at_localhost";
		$connectionsIds["al_t_jenis_infrastruktur"] = "siinsan_at_localhost";
		$connectionsIds["al_cakupanairlimbah_pemukiman"] = "siinsan_at_localhost";
		$connectionsIds["al_cakupanairlimbah_tertentu"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum_ver_prov"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum_pemukiman_ver_prov"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum_tertentu_ver_prov"] = "siinsan_at_localhost";
		$connectionsIds["al_iplt_rev_prov"] = "siinsan_at_localhost";
		$connectionsIds["al_iplt_rev_pusat"] = "siinsan_at_localhost";
		$connectionsIds["san_rencanainduk_ver_prov"] = "siinsan_at_localhost";
		$connectionsIds["san_rencanainduk_ver"] = "siinsan_at_localhost";
		$connectionsIds["mne_umum"] = "siinsan_at_localhost";
		$connectionsIds["mne_umum_kesiapan"] = "siinsan_at_localhost";
		$connectionsIds["t_mne_tahap"] = "siinsan_at_localhost";
		$connectionsIds["mne_umum_transisi_pelaksanaan"] = "siinsan_at_localhost";
		$connectionsIds["mne_umum_pelaksanaan"] = "siinsan_at_localhost";
		$connectionsIds["mne_t_jenis_kontrak"] = "siinsan_at_localhost";
		$connectionsIds["mne_umum_transisi_pasca"] = "siinsan_at_localhost";
		$connectionsIds["mne_umum_pasca"] = "siinsan_at_localhost";
		$connectionsIds["mne_umum_transisi_kesiapan"] = "siinsan_at_localhost";
		$connectionsIds["t_mne_tahap_kekersiapan"] = "siinsan_at_localhost";
		$connectionsIds["t_mne_tahap_kepasca"] = "siinsan_at_localhost";
		$connectionsIds["t_mne_tahap_kepelaksanaan"] = "siinsan_at_localhost";
		$connectionsIds["sp_pemrosesanakhir_ver_prov"] = "siinsan_at_localhost";
		$connectionsIds["sp_pemrosesanakhir_ver"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum_pemukiman_ver"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum_ver"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum_tertentu_ver"] = "siinsan_at_localhost";
		$connectionsIds["TPS3R"] = "rest";
		$connectionsIds["Sanimas"] = "rest";
		$connectionsIds["PadatKarya"] = "rest";
		$connectionsIds["san_spm_ald_ver_prov"] = "siinsan_at_localhost";
		$connectionsIds["sp_pengolahan_ver_prov"] = "siinsan_at_localhost";
		$connectionsIds["sp_pengolahan_ver"] = "siinsan_at_localhost";
		$connectionsIds["Sanimas_prov"] = "rest";
		$connectionsIds["PadatKarya_prov"] = "rest";
		$connectionsIds["TPS3R_prov"] = "rest";
		$connectionsIds["mne_umum_kabkota"] = "siinsan_at_localhost";
		$connectionsIds["tr_pengumuman"] = "siinsan_at_localhost";
		$connectionsIds["sp_tparegional_ver_prov"] = "siinsan_at_localhost";
		$connectionsIds["sp_tparegional_ver"] = "siinsan_at_localhost";
		$connectionsIds["sp_banksampah_ver"] = "siinsan_at_localhost";
		$connectionsIds["sp_banksampah_ver_prov"] = "siinsan_at_localhost";
		$connectionsIds["t_status_keberfungsian"] = "siinsan_at_localhost";
		$connectionsIds["t_kualitas_keberfungsian"] = "siinsan_at_localhost";
		$connectionsIds["t_kondisi_fasilitas"] = "siinsan_at_localhost";
		$connectionsIds["t_kategori_pelayanan"] = "siinsan_at_localhost";
		$connectionsIds["t_jenis_pengelola"] = "siinsan_at_localhost";
		$connectionsIds["mne_umum_verifikasi_perencanaan"] = "siinsan_at_localhost";
		$connectionsIds["mne_kesiapan_eval"] = "siinsan_at_localhost";
		$connectionsIds["mne_pelaksanaan_eval"] = "siinsan_at_localhost";
		$connectionsIds["mne_pasca_eval"] = "siinsan_at_localhost";
		$connectionsIds["v_spaldt_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_spaldt_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_iplt_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_kesiapan_eval_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_pelaksanaan_eval_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_pasca_eval_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_kesiapan_eval_total_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_pelaksanaan_eval_total_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_pasca_eval_total_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_kesiapan_eval_total_kategori_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_pelaksanaan_eval_total_kategori_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_pasca_eval_total_kategori_Report"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum_admin"] = "siinsan_at_localhost";
		$connectionsIds["v_kesiapan_eval_prov_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_al_iplt_keterisian_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_sp_pemrosesan_keterisian_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_al_iplt_keterisian_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_sp_pemrosesan_keterisian_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["mne_umum_renonly"] = "siinsan_at_localhost";
		$connectionsIds["mne_umum_verifiedonly"] = "siinsan_at_localhost";
		$connectionsIds["mne_kesiapan_penentu"] = "siinsan_at_localhost";
		$connectionsIds["t_yatidak"] = "siinsan_at_localhost";
		$connectionsIds["sigdrainase_saluran_drainase"] = "siinsan_at_localhost";
		$connectionsIds["sigdrainase_kontruksi_drainase"] = "siinsan_at_localhost";
		$connectionsIds["al_iplt_admin"] = "siinsan_at_localhost";
		$connectionsIds["sp_pemrosesanakhir_admin"] = "siinsan_at_localhost";
		$connectionsIds["sp_banksampah_admin"] = "siinsan_at_localhost";
		$connectionsIds["sp_pengolahan_admin"] = "siinsan_at_localhost";
		$connectionsIds["al_t_jenis_pengolahan_akhir"] = "siinsan_at_localhost";
		$connectionsIds["al_t_opsi_teknologi"] = "siinsan_at_localhost";
		$connectionsIds["al_t_sistem_pengolahan"] = "siinsan_at_localhost";
		$connectionsIds["sigdrainase_sumur"] = "siinsan_at_localhost";
		$connectionsIds["sigdrainase_kolam_retensi"] = "siinsan_at_localhost";
		$connectionsIds["v_spaldt_perkotaan_keterisian_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_spaldt_permukiman_keterisian_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_spaldt_tertentu_keterisian_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_sp_pengolahan_keterisian_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_sp_pengolahan_keterisian_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_spaldt_perkotaan_keterisian_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_spaldt_permukiman_keterisian_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_spaldt_tertentu_keterisian_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["dra_data_genangan_manual"] = "siinsan_at_localhost";
		$connectionsIds["sp_t_opsi_teknologi_organik"] = "siinsan_at_localhost";
		$connectionsIds["v_mne_keterisian_kesiapan"] = "siinsan_at_localhost";
		$connectionsIds["v_mne_keterisian_kesiapan_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_mne_keterisian_pasca"] = "siinsan_at_localhost";
		$connectionsIds["v_mne_keterisian_pasca_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_mne_keterisian_pelaksanaan"] = "siinsan_at_localhost";
		$connectionsIds["v_mne_keterisian_pelaksanaan_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_mne_keterisian_perencanaan"] = "siinsan_at_localhost";
		$connectionsIds["v_mne_keterisian_perencanaan_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_mne_keterisian_kesiapan_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_mne_keterisian_pasca_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_mne_keterisian_pelaksanaan_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_mne_keterisian_perencanaan_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["sp_pemrosesanakhir_pengembangan"] = "siinsan_at_localhost";
		$connectionsIds["sp_t_opsi_teknologi_anorganik"] = "siinsan_at_localhost";
		$connectionsIds["v_dra_keterisian_genangan_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_dra_keterisian_saluran_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_dra_keterisian_genangan_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_dra_keterisian_saluran_kabkota_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_iplt_terverifikasi_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_iplt_belum_terverifikasi_Report"] = "siinsan_at_localhost";
		$connectionsIds["v_al_dataumum_permukiman_terverifikasi"] = "siinsan_at_localhost";
		$connectionsIds["v_al_dataumum_permukiman_belum_terverifikasi"] = "siinsan_at_localhost";
		$connectionsIds["v_al_dataumum_perkotaan_terverifikasi"] = "siinsan_at_localhost";
		$connectionsIds["v_al_dataumum_perkotaan_belum_terverifikasi"] = "siinsan_at_localhost";
		$connectionsIds["v_al_dataumum_tertetu_terverifikasi"] = "siinsan_at_localhost";
		$connectionsIds["v_al_dataumum_tertentu_belum_terverifikasi"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum_prov"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum_tertentu_prov"] = "siinsan_at_localhost";
		$connectionsIds["al_dataumum_permukiman_prov"] = "siinsan_at_localhost";
		$connectionsIds["al_iplt_prov"] = "siinsan_at_localhost";
		$connectionsIds["sp_pemrosesanakhir_prov"] = "siinsan_at_localhost";
		$connectionsIds["sp_tparegional_prov"] = "siinsan_at_localhost";
		$connectionsIds["sp_pengolahan_prov"] = "siinsan_at_localhost";
		$connectionsIds["sigdrainase_saluran_drainase_prov"] = "siinsan_at_localhost";
		$connectionsIds["dra_data_genangan_prov"] = "siinsan_at_localhost";
		$connectionsIds["al_spalds"] = "siinsan_at_localhost";
		$connectionsIds["al_t_suplai_air"] = "siinsan_at_localhost";
		$connectionsIds["ibm_lpk"] = "siinsan_at_localhost";
		$connectionsIds["ibm_padatkarya_ver"] = "siinsan_at_localhost";
		$connectionsIds["ibm_sanimas_ver"] = "siinsan_at_localhost";
		$connectionsIds["ibm_tps3r_ver"] = "siinsan_at_localhost";
		$connectionsIds["psn_capaian"] = "siinsan_at_localhost";
		$connectionsIds["psn_komposisi"] = "siinsan_at_localhost";
		$connectionsIds["al_spalds_prov"] = "siinsan_at_localhost";
		$connectionsIds["dash_persampahan_kabkota"] = "siinsan_at_localhost";
		$connectionsIds["dash_airlimbah_kabkota"] = "siinsan_at_localhost";
		$connectionsIds["dash_airlimbah_nas"] = "siinsan_at_localhost";
		$connectionsIds["dash_airlimbah_prov"] = "siinsan_at_localhost";
		$connectionsIds["dash_persampahan_nas"] = "siinsan_at_localhost";
		$connectionsIds["dash_persampahan_prov"] = "siinsan_at_localhost";
		$connectionsIds["dash_saluran_drainase_kabkota"] = "siinsan_at_localhost";
		$connectionsIds["dash_saluran_drainase_nas"] = "siinsan_at_localhost";
		$connectionsIds["dash_saluran_drainase_prov"] = "siinsan_at_localhost";
		$connectionsIds["t_al_skalapelayanan"] = "siinsan_at_localhost";
		$connectionsIds["dash_spaldt_infra"] = "siinsan_at_localhost";
		$connectionsIds["dash_verifikasi_prov_kabkota"] = "siinsan_at_localhost";
		$connectionsIds["dash_verifikasi_prov_nas"] = "siinsan_at_localhost";
		$connectionsIds["dash_verifikasi_prov_prov"] = "siinsan_at_localhost";
		$connectionsIds["dash_spaldt_infra_prov"] = "siinsan_at_localhost";
		$connectionsIds["dash_spaldt_infra_kabkota"] = "siinsan_at_localhost";
		$connectionsIds["v_tpa_prov"] = "siinsan_at_localhost";
		$connectionsIds["v_tparegional_prov"] = "siinsan_at_localhost";
		$connectionsIds["dra_data_genangan_ver_prov"] = "siinsan_at_localhost";
		$connectionsIds["dra_data_genangan_ver"] = "siinsan_at_localhost";
		$connectionsIds["sigdrainase_saluran_drainase_ver_prov"] = "siinsan_at_localhost";
		$connectionsIds["sigdrainase_saluran_drainase_ver"] = "siinsan_at_localhost";
		$connectionsIds["dash_verifikasi_pusat_kabkota"] = "siinsan_at_localhost";
		$connectionsIds["dash_verifikasi_pusat_nas"] = "siinsan_at_localhost";
		$connectionsIds["dash_verifikasi_pusat_prov"] = "siinsan_at_localhost";
		$connectionsIds["sippa_renja1_2018"] = "siinsan_at_localhost";
		$connectionsIds["sippa_renja1_2019"] = "siinsan_at_localhost";
		$connectionsIds["sippa_renja1_2020"] = "siinsan_at_localhost";
		$connectionsIds["sippa_renja1_2021"] = "siinsan_at_localhost";
		$connectionsIds["sippa_renja1_2022"] = "siinsan_at_localhost";
		$connectionsIds["simdak_v_rekap_dak"] = "siinsan_at_localhost";
		$connectionsIds["simdak_v_rekap_drainase"] = "siinsan_at_localhost";
		$connectionsIds["simdak_v_rekap_ipal"] = "siinsan_at_localhost";
		$connectionsIds["simdak_v_rekap_ipal_mck"] = "siinsan_at_localhost";
		$connectionsIds["admin_users"] = "siinsan_at_localhost";
		$connectionsIds["mne_umum_sippa"] = "siinsan_at_localhost";
		$connectionsIds["sippa_renja1"] = "siinsan_at_localhost";

		$this->_tablesConnectionIds = &$connectionsIds;
	}

	/**
	 * Check if It's possible to add to one table's sql query
	 * an sql subquery to another table.
	 * Access doesn't support subqueries from the same table as main.
	 * @param String dataSourceTName1
	 * @param String dataSourceTName2
	 * @return Boolean
	 */
	public function checkTablesSubqueriesSupport( $dataSourceTName1, $dataSourceTName2 )
	{
		$connId1 = $this->_tablesConnectionIds[ $dataSourceTName1 ];
		$connId2 = $this->_tablesConnectionIds[ $dataSourceTName2 ];

		if( $connId1 != $connId2 )
			return false;

		if( $this->_connectionsData[ $connId1 ]["dbType"] == nDATABASE_Access && $dataSourceTName1 == $dataSourceTName2 )
			return false;

		return true;
	}

	/**
	 * Close db connections
    */
	function CloseConnections()
	{
		foreach( $this->cache as $connection )
		{
			if( $connection )
				$connection->close();
		}
	}
}
?>