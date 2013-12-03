<?php

class FC_Smart_Client_Cache {

	const APC_TTL = 3600;

	/** @var bool */
	protected static $_apcEnabled = null;

	/** @var array */
	protected static $_runtimeCache = array();

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function get($key) {
		$key = (string) $key;
		$value = self::_getRuntime($key);
		if(false !== $value) {
			return $value;
		}
		$value = self::_getApc($key);
		if(false !== $value) {
			self::_setRuntime($key, $value);
		}
		return $value;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function set($key, $value) {
		$key = (string) $key;
		self::_setRuntime($key, $value);
		self::_setApc($key, $value);
	}

	/**
	 * @param string $key
	 */
	public static function delete($key) {
		self::_deleteRuntime($key);
		self::_deleteApc($key);
	}

	public static function flush() {
		self::_flushRuntime();
		self::_flushApc();
	}

	/**
	 * @param string $key
	 */
	protected static function _deleteRuntime($key) {
		unset(self::$_runtimeCache[$key]);
	}

	protected static function _flushRuntime() {
		self::$_runtimeCache = array();
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected static function _getRuntime($key) {
		if(!array_key_exists($key, self::$_runtimeCache)) {
			return false;
		}
		return self::$_runtimeCache[$key];
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	protected static function _setRuntime($key, $value) {
		self::$_runtimeCache[$key] = $value;
	}

	/**
	 * @return bool
	 */
	protected static function _isApcEnabled() {
		if(null === self::$_apcEnabled) {
			self::$_apcEnabled = extension_loaded('apc');
		}
		return self::$_apcEnabled;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	protected static function _deleteApc($key) {
		if(!self::_isApcEnabled()) {
			return false;
		}
		return apc_delete($key);
	}

	/**
	 * @return bool
	 */
	protected static function _flushApc() {
		if(!self::_isApcEnabled()) {
			return false;
		}
		return apc_clear_cache('user');
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected static function _getApc($key) {
		if(!self::_isApcEnabled()) {
			return false;
		}
		return apc_fetch($key);
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	protected static function _setApc($key, $value) {
		if(!self::_isApcEnabled()) {
			return false;
		}
		return apc_store($key, $value, self::APC_TTL);
	}
}
