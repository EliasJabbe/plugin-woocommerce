<?php
/**
 * All components for toolbox
 *
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Check Class.
 */
class Lengow_Check {

	/**
	 * @var Lengow_Translation Lengow translation instance.
	 */
	private $_locale;

	/**
	 * Construct new Lengow check.
	 */
	public function __construct() {
		$this->_locale                      = new Lengow_Translation();
		Lengow_Translation::$force_iso_code = Lengow_Translation::DEFAULT_ISO_CODE;
	}

	/**
	 * Check if PHP Curl is activated.
	 *
	 * @return boolean
	 */
	public static function is_curl_activated() {
		return function_exists( 'curl_version' );
	}

	/**
	 * Check if SimpleXML Extension is activated.
	 *
	 * @return boolean
	 */
	public static function is_simple_xml_activated() {
		return function_exists( 'simplexml_load_file' );
	}

	/**
	 * Check if json Extension is activated.
	 *
	 * @return boolean
	 */
	public static function is_json_activated() {
		return function_exists( 'json_decode' );
	}

	/**
	 * Get array of requirements and their status.
	 *
	 * @return string
	 */
	public function get_check_list() {
		$checklist   = array();
		$checklist[] = array(
			'title'      => $this->_locale->t( 'toolbox.index.curl_message' ),
			'help'       => $this->_locale->t( 'toolbox.index.curl_help' ),
			'help_link'  => $this->_locale->t( 'toolbox.index.curl_help_link' ),
			'help_label' => $this->_locale->t( 'toolbox.index.curl_help_label' ),
			'state'      => (int) self::is_curl_activated(),
		);
		$checklist[] = array(
			'title'      => $this->_locale->t( 'toolbox.index.simple_xml_message' ),
			'help'       => $this->_locale->t( 'toolbox.index.simple_xml_help' ),
			'help_link'  => $this->_locale->t( 'toolbox.index.simple_xml_help_link' ),
			'help_label' => $this->_locale->t( 'toolbox.index.simple_xml_help_label' ),
			'state'      => (int) self::is_simple_xml_activated(),
		);
		$checklist[] = array(
			'title'      => $this->_locale->t( 'toolbox.index.json_php_message' ),
			'help'       => $this->_locale->t( 'toolbox.index.json_php_help' ),
			'help_link'  => $this->_locale->t( 'toolbox.index.json_php_help_link' ),
			'help_label' => $this->_locale->t( 'toolbox.index.json_php_help_label' ),
			'state'      => (int) self::is_json_activated(),
		);
		$checklist[] = array(
			'title'      => $this->_locale->t( 'toolbox.index.checksum_message' ),
			'help'       => $this->_locale->t( 'toolbox.index.checksum_help' ),
			'help_link'  => '/wp-content/plugins/lengow-woocommerce/toolbox/checksum.php',
			'help_label' => $this->_locale->t( 'toolbox.index.checksum_help_label' ),
			'state'      => (int) self::get_file_modified(),
		);

		return $this->get_admin_content( $checklist );
	}

	/**
	 * Get array of requirements and their status.
	 *
	 * @return string
	 */
	public function get_global_information() {
		global $woocommerce, $wp_version;
		$checklist   = array();
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.wordpress_version' ),
			'message' => $wp_version,
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.woocommerce_version' ),
			'message' => $woocommerce->version,
		);

		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.plugin_version' ),
			'message' => LENGOW_VERSION,
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.ip_server' ),
			'message' => $_SERVER['SERVER_ADDR'],
		);
		$checklist[] = array(
			'title' => $this->_locale->t( 'toolbox.index.ip_enabled' ),
			'state' => (bool) Lengow_Configuration::get( 'lengow_ip_enabled' ) ? 1 : 0,
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.ip_authorized' ),
			'message' => Lengow_Configuration::get( 'lengow_authorized_ip' ),
		);
		$checklist[] = array(
			'title' => $this->_locale->t( 'toolbox.index.debug_disabled' ),
			'state' => Lengow_Configuration::debug_mode_is_active() ? 0 : 1,
		);

		return $this->get_admin_content( $checklist );
	}

	/**
	 * Get array of requirements and their status.
	 *
	 * @return string
	 */
	public function get_import_information() {
		$last_import      = Lengow_Main::get_last_import();
		$last_import_date = 'none' === $last_import['timestamp']
			? $this->_locale->t( 'toolbox.index.last_import_none' )
			: Lengow_Main::get_date_in_correct_format( $last_import['timestamp'], true );
		if ( 'none' === $last_import['type'] ) {
			$last_import_type = $this->_locale->t( 'toolbox.index.last_import_none' );
		} elseif ( Lengow_Import::TYPE_CRON === $last_import['type'] ) {
			$last_import_type = $this->_locale->t( 'toolbox.index.last_import_cron' );
		} else {
			$last_import_type = $this->_locale->t( 'toolbox.index.last_import_manual' );
		}

		if ( Lengow_Import::is_in_process() ) {
			$import_in_progress = Lengow_Main::decode_log_message(
				'toolbox.index.rest_time_to_import',
				null,
				array( 'rest_time' => Lengow_Import::rest_time_to_import() )
			);
		} else {
			$import_in_progress = $this->_locale->t( 'toolbox.index.no_import' );
		}
		$checklist   = array();
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.global_token' ),
			'message' => Lengow_Configuration::get( 'lengow_token' ),
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.url_import' ),
			'message' => Lengow_Main::get_cron_url(),
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.import_in_progress' ),
			'message' => $import_in_progress,
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.shop_last_import' ),
			'message' => $last_import_date,
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.shop_type_import' ),
			'message' => $last_import_type,
		);

		return $this->get_admin_content( $checklist );
	}


	/**
	 * Get array of requirements and their status, no multi-store on wordpress.
	 *
	 * @return string
	 */
	public function get_information_by_store() {
		$lengowExport = new Lengow_Export;
		if ( null !== Lengow_Configuration::get( 'lengow_last_export' )
		     && '' !== Lengow_Configuration::get( 'lengow_last_export' )
		) {
			$last_export_timestamp = (int) Lengow_Configuration::get( 'lengow_last_export' );
			$last_export           = Lengow_Main::get_date_in_correct_format( $last_export_timestamp, true );
		} else {
			$last_export = $this->_locale->t( 'toolbox.index.last_import_none' );
		}
		$checklist   = array();
		$checklist[] = array(
			'header' => get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'wpurl' ),
		);
		$checklist[] = array(
			'title' => $this->_locale->t( 'toolbox.index.shop_active' ),
			'state' => (int) Lengow_Configuration::get( 'lengow_store_enabled' ),
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.shop_catalogs_id' ),
			'message' => Lengow_Configuration::get( 'lengow_catalog_id' ),
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.shop_product_total' ),
			'message' => $lengowExport->get_total_product(),
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.shop_product_exported' ),
			'message' => $lengowExport->get_total_export_product(),
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.shop_export_token' ),
			'message' => Lengow_Configuration::get( 'lengow_token' ),
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.url_export' ),
			'message' => Lengow_Main::get_export_url(),
		);
		$checklist[] = array(
			'title'   => $this->_locale->t( 'toolbox.index.shop_last_export' ),
			'message' => $last_export,
		);

		return $this->get_admin_content( $checklist );
	}

	/**
	 * Get files checksum.
	 *
	 * @return string
	 */
	public function check_file_md5() {
		$checklist    = array();
		$file_name    = LENGOW_PLUGIN_PATH . '/toolbox' . DIRECTORY_SEPARATOR . 'checkmd5.csv';
		$html         = '<h3><i class="fa fa-commenting"></i> '
		                . $this->_locale->t( 'toolbox.checksum.summary' ) . '</h3>';
		$file_counter = 0;
		if ( file_exists( $file_name ) ) {
			$file_errors  = array();
			$file_deletes = array();
			if ( false !== ( $file = fopen( $file_name, 'r' ) ) ) {
				while ( false !== ( $data = fgetcsv( $file, 1000, '|' ) ) ) {
					$file_counter ++;
					$file_path = LENGOW_PLUGIN_PATH . $data[0];
					if ( file_exists( $file_path ) ) {
						$file_md5 = md5_file( $file_path );
						if ( $file_md5 !== $data[1] ) {
							$file_errors[] = array(
								'title' => $file_path,
								'state' => 0,
							);
						}
					} else {
						$file_deletes[] = array(
							'title' => $file_path,
							'state' => 0,
						);
					}
				}
				fclose( $file );
			}
			$checklist[] = array(
				'title' => $this->_locale->t(
					'toolbox.checksum.file_checked',
					array( 'nb_file' => $file_counter )
				),
				'state' => 1,
			);
			$checklist[] = array(
				'title' => $this->_locale->t(
					'toolbox.checksum.file_modified',
					array( 'nb_file' => count( $file_errors ) )
				),
				'state' => ! empty( $file_errors ) ? 0 : 1,
			);
			$checklist[] = array(
				'title' => $this->_locale->t(
					'toolbox.checksum.file_deleted',
					array( 'nb_file' => count( $file_deletes ) )
				),
				'state' => ! empty( $file_deletes ) ? 0 : 1,
			);
			$html        .= $this->get_admin_content( $checklist );
			if ( ! empty( $file_errors ) ) {
				$html .= '<h3><i class="fa fa-list"></i> '
				         . $this->_locale->t( 'toolbox.checksum.list_modified_file' ) . '</h3>';
				$html .= $this->get_admin_content( $file_errors );
			}
			if ( ! empty( $file_deletes ) ) {
				$html .= '<h3><i class="fa fa-list"></i> '
				         . $this->_locale->t( 'toolbox.checksum.list_deleted_file' ) . '</h3>';
				$html .= $this->get_admin_content( $file_deletes );
			}
		} else {
			$checklist[] = array(
				'title' => $this->_locale->t( 'toolbox.checksum.file_not_exists' ),
				'state' => 0,
			);
			$html        .= $this->get_admin_content( $checklist );
		}

		return $html;
	}

	/**
	 * Get checksum errors.
	 *
	 * @return boolean
	 */
	public static function get_file_modified() {
		$file_name = LENGOW_PLUGIN_PATH . '/toolbox' . DIRECTORY_SEPARATOR . 'checkmd5.csv';
		if ( file_exists( $file_name ) ) {
			if ( false !== ( $file = fopen( $file_name, 'r' ) ) ) {
				while ( false !== ( $data = fgetcsv( $file, 1000, '|' ) ) ) {
					$file_path = LENGOW_PLUGIN_PATH . $data[0];
					$file_md5  = md5_file( $file_path );
					if ( $file_md5 !== $data[1] ) {
						return false;
					}
				}
				fclose( $file );

				return true;
			}
		}

		return false;
	}

	/**
	 * Get HTML Table content of checklist.
	 *
	 * @param array $checklist
	 *
	 * @return string
	 */
	private function get_admin_content( $checklist = array() ) {
		if ( empty( $checklist ) ) {
			return null;
		}
		$out = '<table class="table" cellpadding="0" cellspacing="0">';
		foreach ( $checklist as $check ) {
			$out .= '<tr>';
			if ( isset( $check['header'] ) ) {
				$out .= '<td colspan="2" align="center" style="border:0"><h4>' . $check['header'] . '</h4></td>';
			} else {
				$out .= '<td><b>' . $check['title'] . '</b></td>';
				if ( isset( $check['state'] ) ) {
					if ( 1 === $check['state'] ) {
						$out .= '<td align="right"><i class="fa fa-check lengow-green"></i></td>';
					} else {
						$out .= '<td align="right"><i class="fa fa-times lengow-red"></i></td>';
					}
					if ( 0 === $check['state'] ) {
						if ( isset( $check['help'] )
						     && isset( $check['help_link'] )
						     && isset( $check['help_label'] )
						) {
							$out .= '<tr><td colspan="2"><p>' . $check['help'];
							if ( array_key_exists( 'help_link', $check ) && '' !== $check['help_link'] ) {
								$out .= '<br /><a target="_blank" href="'
								        . $check['help_link'] . '">' . $check['help_label'] . '</a>';
							}
							$out .= '</p></td></tr>';
						}
					}
				} else {
					$out .= '<td align="right"><b>' . $check['message'] . '</b></td>';
				}
			}
			$out .= '</tr>';
		}
		$out .= '</table>';

		return $out;
	}
}
