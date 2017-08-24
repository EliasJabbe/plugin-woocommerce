<?php
/**
 * Import process to synchronise stock
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
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team module <team-module@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Import Class.
 */
class Lengow_Import {

	/**
	 * @var array valid states lengow to create a Lengow order.
	 */
	public static $lengow_states = array(
		'waiting_shipment',
		'shipped',
		'closed',
	);

	/**
	 * @var boolean import is processing.
	 */
	public static $processing;

	/**
	 * @var string marketplace order sku.
	 */
	private $_marketplace_sku = null;

	/**
	 * @var string marketplace name.
	 */
	private $_marketplace_name = null;

	/**
	 * @var integer delivery address id.
	 */
	private $_delivery_address_id = null;

	/**
	 * @var integer number of orders to import.
	 */
	private $_limit = 0;

	/**
	 * @var string start import date.
	 */
	private $_date_from = null;

	/**
	 * @var string end import date.
	 */
	private $_date_to = null;

	/**
	 * @var boolean import one order.
	 */
	private $_import_one_order = false;

	/**
	 * @var boolean use preprod mode.
	 */
	private $_preprod_mode = false;

	/**
	 * @var boolean display log messages.
	 */
	private $_log_output = false;

	/**
	 * @var string type import (manual or cron).
	 */
	private $_type_import;

	/**
	 * @var string account ID.
	 */
	private $_account_id;

	/**
	 * @var string access token.
	 */
	private $_access_token;

	/**
	 * @var string access secret.
	 */
	private $_secret_token;

	/**
	 * @var Lengow_Connector Lengow connector instance
	 */
	private $_connector;

	/**
	 * @var array shop catalog ids for import
	 */
	private $_shop_catalog_ids = array();

	/**
	 * Construct the import manager.
	 *
	 * @param $params array Optional options
	 * string  marketplace_sku     lengow marketplace order id to import
	 * string  marketplace_name    lengow marketplace name to import
	 * string  type                type of current import
	 * integer delivery_address_id Lengow delivery address id to import
	 * integer shop_id             shop id for current import
	 * integer days                import period
	 * integer limit               number of orders to import
	 * boolean log_output          display log messages
	 * boolean preprod_mode        preprod mode
	 */
	public function __construct( $params = array() ) {
		// params for re-import order.
		if ( isset( $params['marketplace_sku'] ) && isset( $params['marketplace_name'] ) ) {
			$this->_marketplace_sku  = $params['marketplace_sku'];
			$this->_marketplace_name = $params['marketplace_name'];
			$this->_limit            = 1;
			$this->_import_one_order = true;
			if ( isset( $params['delivery_address_id'] ) && $params['delivery_address_id'] != '' ) {
				$this->_delivery_address_id = $params['delivery_address_id'];
			}
		} else {
			// recovering the time interval.
			$days             = (
			isset( $params['days'] )
				? $params['days']
				: (int) Lengow_Configuration::get( 'lengow_import_days' )
			);
			$this->_date_from = date( 'c', strtotime( date( 'Y-m-d' ) . ' -' . $days . 'days' ) );
			$this->_date_to   = date( 'c' );
			$this->_limit     = ( isset( $params['limit'] ) ? $params['limit'] : 0 );
		}
		// get other params.
		$this->_preprod_mode = (
		isset( $params['preprod_mode'] )
			? $params['preprod_mode']
			: (bool) Lengow_Configuration::get( 'lengow_preprod_enabled' )
		);
		$this->_type_import  = ( isset( $params['type'] ) ? $params['type'] : 'manual' );
		$this->_log_output   = ( isset( $params['log_output'] ) ? $params['log_output'] : false );
	}

	/**
	 * Execute import: fetch orders and import them.
	 *
	 * @throws Lengow_Exception order not found
	 *
	 * @return array|false
	 */
	public function exec() {
		if ( ! (bool) Lengow_Configuration::get( 'lengow_import_enabled' ) ) {
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message( 'log.import.import_not_active' ),
				$this->_log_output
			);

			return false;
		}

		$order_new   = 0;
		$order_error = 0;
		$error       = false;

		// clean logs.
		Lengow_Main::clean_log();
		if ( self::is_in_process() && ! $this->_preprod_mode && ! $this->_import_one_order ) {
			$error = Lengow_Main::set_log_message(
				'lengow_log.error.rest_time_to_import',
				array( 'rest_time' => self::rest_time_to_import() )
			);
			Lengow_Main::log( 'Import', $error, $this->_log_output );
		} elseif ( ! $this->_check_credentials() ) {
			$error = Lengow_Main::set_log_message( 'lengow_log.error.credentials_not_valid' );
			Lengow_Main::log( 'Import', $error, $this->_log_output );
		} else {
			// check Lengow catalogs for order synchronisation
			if ( ! $this->_preprod_mode && ! $this->_import_one_order && $this->_type_import === 'manual' ) {
				Lengow_Sync::sync_catalog();
			}
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message( 'log.import.start', array( 'type' => $this->_type_import ) ),
				$this->_log_output
			);
			if ( $this->_preprod_mode ) {
				Lengow_Main::log(
					'Import',
					Lengow_Main::set_log_message( 'log.import.preprod_mode_active' ),
					$this->_log_output
				);
			}
			if ( ! $this->_import_one_order ) {
				self::set_in_process();
				// update last import date.
				Lengow_Main::update_date_import( $this->_type_import );
			}
			if ( Lengow_Configuration::get( 'lengow_store_enabled' ) ) {
				try {
					// check shop catalog ids.
					if ( ! $this->_check_catalog_ids() ) {
						$error_catalog_ids = Lengow_Main::set_log_message( 'lengow_log.error.no_catalog_for_shop' );
						Lengow_Main::log( 'Import', $error_catalog_ids, $this->_log_output );
						$error = $error_catalog_ids;
					} else {
						// get orders from Lengow API.
						$orders       = $this->_get_orders_from_api();
						$total_orders = count( $orders );
						if ( $this->_import_one_order ) {
							Lengow_Main::log(
								'Import',
								Lengow_Main::set_log_message(
									'log.import.find_one_order',
									array(
										'nb_order'         => $total_orders,
										'marketplace_sku'  => $this->_marketplace_sku,
										'marketplace_name' => $this->_marketplace_name,
										'account_id'       => $this->_account_id,
									)
								),
								$this->_log_output
							);
						} else {
							Lengow_Main::log(
								'Import',
								Lengow_Main::set_log_message(
									'log.import.find_all_orders',
									array(
										'nb_order'   => $total_orders,
										'account_id' => $this->_account_id,
									)
								),
								$this->_log_output
							);
						}
						if ( $total_orders <= 0 && $this->_import_one_order ) {
							throw new Lengow_Exception( 'lengow_log.exception.order_not_found' );
						} elseif ( $total_orders > 0 ) {
							$result = $this->_import_orders( $orders );
							if ( ! $this->_import_one_order ) {
								$order_new += $result['order_new'];
								$order_error += $result['order_error'];
							}
						}
					}
				} catch ( Lengow_Exception $e ) {
					$error_message = $e->getMessage();
				} catch ( Exception $e ) {
					$error_message = '[WooCommerce error] "' . $e->getMessage()
					                 . '" ' . $e->getFile() . ' | ' . $e->getLine();
				}
				if ( isset( $error_message ) ) {
					$decoded_message = Lengow_Main::decode_log_message( $error_message, 'en_GB' );
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message(
							'log.import.import_failed',
							array( 'decoded_message' => $decoded_message )
						),
						$this->_log_output
					);
					$error = $error_message;
					unset( $error_message );
				}
				if ( ! $this->_import_one_order ) {
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message(
							'lengow_log.error.nb_order_imported',
							array( 'nb_order' => $order_new )
						),
						$this->_log_output
					);
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message(
							'lengow_log.error.nb_order_with_error',
							array( 'nb_order' => $order_error )
						),
						$this->_log_output
					);
				}
			}

			// finish import process.
			self::set_end();
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message( 'log.import.end', array( 'type' => $this->_type_import ) ),
				$this->_log_output
			);
		}
		if ( $this->_import_one_order ) {
			$result['error'] = $error;

			return $result;
		} else {
			return array(
				'order_new'   => $order_new,
				'order_error' => $order_error,
				'error'       => $error,
			);
		}
	}

	/**
	 * Check credentials.
	 *
	 * @return boolean
	 */
	private function _check_credentials() {
		if ( Lengow_Connector::is_valid_auth() ) {
			list( $this->_account_id, $this->_access_token, $this->_secret_token ) = Lengow_Configuration::get_access_id();
			$this->_connector = new Lengow_Connector( $this->_access_token, $this->_secret_token );

			return true;
		}

		return false;
	}

	/**
	 * Check catalog ids
	 *
	 * @return boolean
	 */
	private function _check_catalog_ids() {
		if ( $this->_import_one_order ) {
			return true;
		}
		$catalog_ids = Lengow_Configuration::get_catalog_ids();
		if ( count( $catalog_ids ) > 0 ) {
			$this->_shop_catalog_ids = $catalog_ids;

			return true;
		}

		return false;
	}

	/**
	 * Call Lengow order API.
	 *
	 * @throws Lengow_Exception no connection with Lengow webservice / credentials not valid
	 *
	 * @return array
	 */
	private function _get_orders_from_api() {
		$page   = 1;
		$orders = array();

		if ( $this->_import_one_order ) {
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message(
					'log.import.connector_get_order',
					array(
						'marketplace_sku'  => $this->_marketplace_sku,
						'marketplace_name' => $this->_marketplace_name,
					)
				),
				$this->_log_output
			);
		} else {
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message(
					'log.import.connector_get_all_order',
					array(
						'date_from'  => date( 'Y-m-d', strtotime( (string) $this->_date_from ) ),
						'date_to'    => date( 'Y-m-d', strtotime( (string) $this->_date_to ) ),
						'catalog_id' => implode( ', ', $this->_shop_catalog_ids ),
					)
				),
				$this->_log_output
			);
		}
		do {
			if ( $this->_import_one_order ) {
				$results = $this->_connector->get(
					'/v3.0/orders',
					array(
						'marketplace_order_id' => $this->_marketplace_sku,
						'marketplace'          => $this->_marketplace_name,
						'account_id'           => $this->_account_id,
						'page'                 => $page,
					),
					'stream'
				);
			} else {
				$results = $this->_connector->get(
					'/v3.0/orders',
					array(
						'updated_from' => $this->_date_from,
						'updated_to'   => $this->_date_to,
						'catalog_ids'  => implode( ',', $this->_shop_catalog_ids ),
						'account_id'   => $this->_account_id,
						'page'         => $page,
					),
					'stream'
				);
			}
			if ( is_null( $results ) ) {
				throw new Lengow_Exception(
					Lengow_Main::set_log_message( 'lengow_log.exception.no_connection_webservice' )
				);
			}
			$results = json_decode( $results );
			if ( ! is_object( $results ) ) {
				throw new Lengow_Exception(
					Lengow_Main::set_log_message( 'lengow_log.exception.no_connection_webservice' )
				);
			}
			if ( isset( $results->error ) ) {
				throw new Lengow_Exception(
					Lengow_Main::set_log_message(
						'lengow_log.exception.error_lengow_webservice',
						array(
							'error_code'    => $results->error->code,
							'error_message' => $results->error->message,
						)
					)
				);
			}
			// Construct array orders.
			foreach ( $results->results as $order ) {
				$orders[] = $order;
			}
			$page ++;
			$finish = ( is_null( $results->next ) || $this->_import_one_order ) ? true : false;
		} while ( $finish != true );

		return $orders;
	}

	/**
	 * Create or update order in WooCommerce.
	 *
	 * @param mixed $orders API orders
	 *
	 * @return array|false
	 */
	protected function _import_orders( $orders ) {
		$order_new       = 0;
		$order_error     = 0;
		$import_finished = false;

		foreach ( $orders as $order_data ) {
			if ( ! $this->_import_one_order ) {
				self::set_in_process();
			}
			$nb_package      = 0;
			$marketplace_sku = (string) $order_data->marketplace_order_id;
			if ( $this->_preprod_mode ) {
				$marketplace_sku .= '--' . time();
			}
			// if order contains no package.
			if ( count( $order_data->packages ) == 0 ) {
				Lengow_Main::log(
					'Import',
					Lengow_Main::set_log_message( 'log.import.error_no_package' ),
					$this->_log_output,
					$marketplace_sku
				);
				continue;
			}
			// start import.
			foreach ( $order_data->packages as $package_data ) {
				$nb_package ++;
				// check whether the package contains a shipping address.
				if ( ! isset( $package_data->delivery->id ) ) {
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message( 'log.import.error_no_delivery_address' ),
						$this->_log_output,
						$marketplace_sku
					);
					continue;
				}
				$package_delivery_address_id = (int) $package_data->delivery->id;
				$first_package               = ( $nb_package > 1 ? false : true );
				// check the package for re-import order.
				if ( $this->_import_one_order ) {
					if ( ! is_null( $this->_delivery_address_id )
					     && $this->_delivery_address_id != $package_delivery_address_id
					) {
						Lengow_Main::log(
							'Import',
							Lengow_Main::set_log_message( 'log.import.error_wrong_package_number' ),
							$this->_log_output,
							$marketplace_sku
						);
						continue;
					}
				}
				try {
					// try to import or update order.
					$import_order = new Lengow_Import_Order(
						array(
							'preprod_mode'        => $this->_preprod_mode,
							'log_output'          => $this->_log_output,
							'marketplace_sku'     => $marketplace_sku,
							'delivery_address_id' => $package_delivery_address_id,
							'order_data'          => $order_data,
							'package_data'        => $package_data,
							'first_package'       => $first_package,
						)
					);
					$order        = $import_order->import_order();
				} catch ( Lengow_Exception $e ) {
					$error_message = $e->getMessage();
				} catch ( Exception $e ) {
					$error_message = '[WooCommerce error]: "' . $e->getMessage()
					                 . '" ' . $e->getFile() . ' | ' . $e->getLine();
				}
				if ( isset( $error_message ) ) {
					$decoded_message = Lengow_Main::decode_log_message( $error_message, 'en_GB' );
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message(
							'log.import.order_import_failed',
							array( 'decoded_message' => $decoded_message )
						),
						$this->_log_output,
						$marketplace_sku
					);
					unset( $error_message );
					continue;
				}
				// if re-import order -> return order information.
				if ( isset( $order ) && $this->_import_one_order ) {
					return $order;
				}
				if ( isset( $order ) ) {
					if ( isset( $order['order_new'] ) && $order['order_new'] == true ) {
						$order_new ++;
					} elseif ( isset( $order['order_error'] ) && $order['order_error'] == true ) {
						$order_error ++;
					}
				}
				// clean process.
				unset( $import_order );
				unset( $order );
				// if limit is set.
				if ( $this->_limit > 0 && $order_new == $this->_limit ) {
					$import_finished = true;
					break;
				}
			}
			if ( $import_finished ) {
				break;
			}
		}

		return array(
			'order_new'   => $order_new,
			'order_error' => $order_error,
		);
	}

	/**
	 * Check if import is already in process.
	 *
	 * @return boolean
	 */
	public static function is_in_process() {
		$timestamp = (int) Lengow_Configuration::get( 'lengow_import_in_progress' );
		if ( $timestamp > 0 ) {
			// security check: if last import is more than 60 seconds old => authorize new import to be launched.
			if ( ( $timestamp + ( 60 * 1 ) ) < time() ) {
				self::set_end();

				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Get Rest time to make re import order.
	 *
	 * @return boolean
	 */
	public static function rest_time_to_import() {
		$timestamp = (int) Lengow_Configuration::get( 'lengow_import_in_progress' );
		if ( $timestamp > 0 ) {
			return $timestamp + ( 60 * 1 ) - time();
		}

		return false;
	}

	/**
	 * Set import to "in process" state.
	 */
	public static function set_in_process() {
		self::$processing = true;
		Lengow_Configuration::update_value( 'lengow_import_in_progress', time() );
	}

	/**
	 * Set import to finished.
	 */
	public static function set_end() {
		self::$processing = false;
		Lengow_Configuration::update_value( 'lengow_import_in_progress', - 1 );
	}

	/**
	 * Check if order status is valid for import.
	 *
	 * @param string $order_state_marketplace order state
	 * @param Lengow_Marketplace $marketplace Lengow marketplace instance
	 *
	 * @return boolean
	 */
	public static function check_state( $order_state_marketplace, $marketplace ) {
		if ( empty( $order_state_marketplace ) ) {
			return false;
		}
		if ( ! in_array( $marketplace->get_state_lengow( $order_state_marketplace ), self::$lengow_states ) ) {
			return false;
		}

		return true;
	}
}
