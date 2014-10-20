<?php
namespace Skeleton;

/**
 * Request class to bundle up some common functionality
 */
class SkeletonRequest {
	public function __construct($config) {
		$this->_server = $_SERVER;
		$this->_post = $_POST;
		$this->_get = $_GET;

		$this->_config = $config;
		$this->_db = NULL;
	}

	/**
	 * Creates a PDO object to do queries with. Only creates one
	 * per request.
	 */
	public function db() {
		if($this->_db === NULL) {
			$this->_db = new \PDO(
				'mysql:host=localhost;dbname='.$this->_config['DBNAME'],
				$this->_config['DBUSER'],
				$this->_config['DBPASS'],
				array(\PDO::ATTR_EMULATE_PREPARES => FALSE,
				      \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
		}

		return $this->_db;
	}

	/**
	 * If no arguments are given, get the whole post array. If
	 * $key is given, get the value for that from the post
	 * array. If it is not in the array, return $default.
	 */
	public function post($key=NULL, $default=NULL) {
		return static::_get_from_array($this->_post, $key, $default);
	}

	/**
	 * If no arguments are given, get the whole get array. If
	 * $key is given, get the value for that from the get
	 * array. If it is not in the array, return $default.
	 */
	public function get($key=NULL, $default=NULL) {
		return static::_get_from_array($this->_get, $key, $default);
	}

	/**
	 * If no arguments are given, get the whole server array. If
	 * $key is given, get the value for that from the server
	 * array. If it is not in the array, return $default.
	 */
	public function server($key=NULL, $default=NULL) {
		return static::_get_from_array($this->_server, $key, $default);
	}

	/**
	 * Gets just the path section of the request, which we use for
	 * routing.
	 */
	public function path() {
		$uri = $this->server('REQUEST_URI');
		return parse_url($uri, \PHP_URL_PATH);
	}

	/**
	 * Look at the config and decide where our views live.
	 */
	public function _view_dir() {
		return static::_get_from_array($this->_config, 'VIEW_DIR', 'views');
	}

	/**
	 * Utility function for getting data from an array.
	 */
	public static function _get_from_array($array, $key=NULL, $default=NULL) {
		if($key === NULL) {
			return $array;
		}

		if(array_key_exists($key, $array)) {
			return $array[$key];
		}

		return $default;
	}

	/**
	 * Redirect and exit.
	 */
	public function redirect($to='/') {
		header( 'Location: '.$to ) ;
		exit();
	}
}
