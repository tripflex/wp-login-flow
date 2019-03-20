<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This file is part of the array_column library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey (http://benramsey.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */

if ( ! function_exists( 'array_column' ) ) {
	/**
	 * Returns the values from a single column of the input array, identified by
	 * the $columnKey.
	 *
	 * Optionally, you may provide an $indexKey to index the values in the returned
	 * array by the values from the $indexKey column in the input array.
	 *
	 * @param array $input     A multi-dimensional array (record set) from which to pull
	 *                         a column of values.
	 * @param mixed $columnKey The column of values to return. This value may be the
	 *                         integer key of the column you wish to retrieve, or it
	 *                         may be the string key name for an associative array.
	 * @param mixed $indexKey  (Optional.) The column to use as the index/keys for
	 *                         the returned array. This value may be the integer key
	 *                         of the column, or it may be the string key name.
	 *
	 * @return array
	 */
	function array_column( $input = null, $columnKey = null, $indexKey = null ) {

		// Using func_get_args() in order to check for proper number of
		// parameters and trigger errors exactly as the built-in array_column()
		// does in PHP 5.5.
		$argc   = func_num_args();
		$params = func_get_args();

		if ( $argc < 2 ) {
			trigger_error( "array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING );

			return null;
		}

		if ( ! is_array( $params[0] ) ) {
			trigger_error(
				'array_column() expects parameter 1 to be array, ' . gettype( $params[0] ) . ' given',
				E_USER_WARNING
			);

			return null;
		}

		if ( ! is_int( $params[1] )
		     && ! is_float( $params[1] )
		     && ! is_string( $params[1] )
		     && $params[1] !== null
		     && ! ( is_object( $params[1] ) && method_exists( $params[1], '__toString' ) )
		) {
			trigger_error( 'array_column(): The column key should be either a string or an integer', E_USER_WARNING );

			return false;
		}

		if ( isset( $params[2] )
		     && ! is_int( $params[2] )
		     && ! is_float( $params[2] )
		     && ! is_string( $params[2] )
		     && ! ( is_object( $params[2] ) && method_exists( $params[2], '__toString' ) )
		) {
			trigger_error( 'array_column(): The index key should be either a string or an integer', E_USER_WARNING );

			return false;
		}

		$paramsInput     = $params[0];
		$paramsColumnKey = ( $params[1] !== null ) ? (string) $params[1] : null;

		$paramsIndexKey = null;
		if ( isset( $params[2] ) ) {
			if ( is_float( $params[2] ) || is_int( $params[2] ) ) {
				$paramsIndexKey = (int) $params[2];
			} else {
				$paramsIndexKey = (string) $params[2];
			}
		}

		$resultArray = array();

		foreach ( $paramsInput as $row ) {
			$key    = $value = null;
			$keySet = $valueSet = false;

			if ( $paramsIndexKey !== null && array_key_exists( $paramsIndexKey, $row ) ) {
				$keySet = true;
				$key    = (string) $row[ $paramsIndexKey ];
			}

			if ( $paramsColumnKey === null ) {
				$valueSet = true;
				$value    = $row;
			} elseif ( is_array( $row ) && array_key_exists( $paramsColumnKey, $row ) ) {
				$valueSet = true;
				$value    = $row[ $paramsColumnKey ];
			}

			if ( $valueSet ) {
				if ( $keySet ) {
					$resultArray[ $key ] = $value;
				} else {
					$resultArray[] = $value;
				}
			}

		}

		return $resultArray;
	}

}

/**
 * @see https://gist.github.com/tripflex/2818993b85db39a1f89a
 */
if ( ! function_exists( 'array_column_recursive' ) ) {
	/**
	 * Returns the values recursively from columns of the input array, identified by
	 * the $columnKey.
	 *
	 * Optionally, you may provide an $indexKey to index the values in the returned
	 * array by the values from the $indexKey column in the input array.
	 *
	 * @param array $input     A multi-dimensional array (record set) from which to pull
	 *                         a column of values.
	 * @param mixed $columnKey The column of values to return. This value may be the
	 *                         integer key of the column you wish to retrieve, or it
	 *                         may be the string key name for an associative array.
	 * @param mixed $indexKey  (Optional.) The column to use as the index/keys for
	 *                         the returned array. This value may be the integer key
	 *                         of the column, or it may be the string key name.
	 *
	 * @return array
	 */
	function array_column_recursive( $input = null, $columnKey = null, $indexKey = null ) {

		// Using func_get_args() in order to check for proper number of
		// parameters and trigger errors exactly as the built-in array_column()
		// does in PHP 5.5.
		$argc   = func_num_args();
		$params = func_get_args();
		if ( $argc < 2 ) {
			trigger_error( "array_column_recursive() expects at least 2 parameters, {$argc} given", E_USER_WARNING );

			return null;
		}
		if ( ! is_array( $params[0] ) ) {
			// Because we call back to this function, check if call was made by self to
			// prevent debug/error output for recursiveness :)
			$callers = debug_backtrace();
			if ( $callers[1]['function'] != 'array_column_recursive' ) {
				trigger_error( 'array_column_recursive() expects parameter 1 to be array, ' . gettype( $params[0] ) . ' given', E_USER_WARNING );
			}

			return null;
		}
		if ( ! is_int( $params[1] )
		     && ! is_float( $params[1] )
		     && ! is_string( $params[1] )
		     && $params[1] !== null
		     && ! ( is_object( $params[1] ) && method_exists( $params[1], '__toString' ) )
		) {
			trigger_error( 'array_column_recursive(): The column key should be either a string or an integer', E_USER_WARNING );

			return false;
		}
		if ( isset( $params[2] )
		     && ! is_int( $params[2] )
		     && ! is_float( $params[2] )
		     && ! is_string( $params[2] )
		     && ! ( is_object( $params[2] ) && method_exists( $params[2], '__toString' ) )
		) {
			trigger_error( 'array_column_recursive(): The index key should be either a string or an integer', E_USER_WARNING );

			return false;
		}
		$paramsInput     = $params[0];
		$paramsColumnKey = ( $params[1] !== null ) ? (string) $params[1] : null;
		$paramsIndexKey  = null;
		if ( isset( $params[2] ) ) {
			if ( is_float( $params[2] ) || is_int( $params[2] ) ) {
				$paramsIndexKey = (int) $params[2];
			} else {
				$paramsIndexKey = (string) $params[2];
			}
		}
		$resultArray = array();
		foreach ( $paramsInput as $row ) {
			$key    = $value = null;
			$keySet = $valueSet = false;
			if ( $paramsIndexKey !== null && is_array( $row ) && array_key_exists( $paramsIndexKey, $row ) ) {
				$keySet = true;
				$key    = (string) $row[ $paramsIndexKey ];
			}
			if ( $paramsColumnKey === null ) {
				$valueSet = true;
				$value    = $row;
			} elseif ( is_array( $row ) && array_key_exists( $paramsColumnKey, $row ) ) {
				$valueSet = true;
				$value    = $row[ $paramsColumnKey ];
			}
			$possibleValue = array_column_recursive( $row, $paramsColumnKey, $paramsIndexKey );
			if ( $possibleValue ) {
				$resultArray = array_merge( $possibleValue, $resultArray );
			}
			if ( $valueSet ) {
				if ( $keySet ) {
					$resultArray[ $key ] = $value;
				} else {
					$resultArray[] = $value;
				}
			}
		}

		return $resultArray;
	}
}

if( ! function_exists( 'array_unique_assoc' ) ){
	/**
	 * Return only unique array values based on key in the array
	 *
	 *
	 * @param array  $array
	 * @param string $key
	 *
	 * @return array
	 *
	 */
	function array_unique_assoc( $array, $key ) {

		$unique = array();

		foreach ( $array as $value ) {
			$unique[ $value[ $key ] ] = $value;
		}

		$data = array_values( $unique );

		return $data;
	}
}