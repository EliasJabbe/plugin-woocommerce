<?php
/**
 * All components to generate logs
 *
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * at your option) any later version.
 * 
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0
 *
 * @category   	Lengow
 * @package    	lengow-woocommerce
 * @subpackage 	includes
 * @author     	Team module <team-module@lengow.com>
 * @copyright  	2017 Lengow SAS
 * @license    	https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Log Class.
 */
class Lengow_Log extends Lengow_File {

	/**
	 * @var string name of logs folder.
	 */
	public static $lengow_log_folder = 'logs';

	/**
	 * @var Lengow_File Lengow file instance.
	 */
	protected $_file;

	/**
	 * Construct a new Lengow log.
	 *
	 * @param string $file_name log file name
	 */
	public function __construct( $file_name = null ) {
		if ( empty( $file_name ) ) {
			$this->file_name = 'logs-' . date( 'Y-m-d' ) . '.txt';
		} else {
			$this->file_name = $file_name;
		}
		$this->_file = new Lengow_File( self::$lengow_log_folder, $this->file_name );
	}

	/**
	 * Write log.
	 *
	 * @param string $category Category
	 * @param string $message log message
	 * @param boolean $display display on screen
	 * @param string $marketplace_sku lengow order id
	 */
	public function write( $category, $message = "", $display = false, $marketplace_sku = null ) {
		$decoded_message = Lengow_Main::decode_log_message( $message, 'en_GB' );
		$log             = date( 'Y-m-d H:i:s' );
		$log .= ' - ' . ( empty( $category ) ? '' : '[' . $category . '] ' );
		$log .= '' . ( empty( $marketplace_sku ) ? '' : 'order ' . $marketplace_sku . ' : ' );
		$log .= $decoded_message . "\r\n";
		if ( $display ) {
			echo $log . '<br />';
			flush();
		}
		$this->_file->write( $log );
	}

	/**
	 * Get log files.
	 *
	 * @return array
	 */
	public static function get_files() {
		return Lengow_File::get_files_from_folder( self::$lengow_log_folder );
	}

	/**
	 * Get log files path.
	 *
	 * @return array|false
	 */
	public static function get_paths() {
		$files = self::get_files();
		if ( empty( $files ) ) {
			return false;
		}
		$logs = array();
		foreach ( $files as $file ) {
			preg_match(
				'/\/lengow-woocommerce\/logs\/logs-([0-9]{4}-[0-9]{2}-[0-9]{2})\.txt/',
				$file->get_path(),
				$match
			);
			$logs[] = array(
				'full_path'  => $file->get_path(),
				'short_path' => 'logs-' . $match[1] . '.txt',
				'name'       => $match[1] . '.txt'
			);
		}

		return $logs;
	}

	/**
	 * Download log file.
	 *
	 * @param string $file log file name
	 */
	public static function download( $file = null ) {
		if ( $file && preg_match( '/^logs-([0-9]{4}-[0-9]{2}-[0-9]{2})\.txt$/', $file, $match ) ) {
			$filename = LENGOW_PLUGIN_PATH . '/' . self::$lengow_log_folder . '/' . $file;
			$handle   = fopen( $filename, "r" );
			$contents = fread( $handle, filesize( $filename ) );
			header( 'Content-type: text/plain' );
			header( 'Content-Disposition: attachment; filename="' . $match[1] . '.txt"' );
			echo $contents;
			exit();
		} else {
			$files = self::get_paths();
			header( 'Content-type: text/plain; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename="logs.txt"' );
			foreach ( $files as $file ) {
				$handle   = fopen( $file['full_path'], "r" );
				$contents = fread( $handle, filesize( $file['full_path'] ) );
				echo $contents;
			}
			exit();
		}
	}
}
