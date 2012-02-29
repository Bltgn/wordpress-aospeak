<?php
/**
 * AOSPeak for Wordpress
 * cache.php - Handles a bsic file based cache for external requests.
 * If caching is not possible (filesystem error) it will attempt to work
 * 
 * @author Guillaume Olivetti
 * @version 0.1
 * @package AOSpeak 
 */

/**
 * File cache class
 *  
 * @var int $timeout The cache's lifetime in seconds.
 */
class aospeak_cache {
	
	// Config
	const CACHE_DIR = AO_SPEAK_CACHE_DIR;
	const TIMEOUT = 300;
	
	/**
	 * @var bool Activates the cache. 
	 */
	protected $cacheActive = FALSE;
	
	public function __construct() {
		
		$this->cacheActive = $this->isCachePossible();
		
	}
	
	/**
	 * Performs checks to determine if caching is possible.
	 * 
	 * @return boolean 
	 */
	protected function isCachePossible() {
		
		// The folder is available and writeable ?
		
	}
	
	/**
	 * Create a md5 from the key, used afterwards as a filename.
	 * The key is cut at 7 characters as it is unique enough for this purpose.
	 * 
	 * @param string $key The original cache key.
	 * @return string The hashed string
	 */
	protected function processKey($key) {
		
		// Build a md5 from the key string
		
		// Return the first 7 chars
		
	}
	
	/**
	 * Save the data to the cache.
	 * The file name will be an md5 based on the entered key.
	 * 
	 * @param string $key The name to identify the cache with.
	 * @param mixed $data The date to save, booleans are not accepted.
	 * @throws aospeak_cache_Exception If you attempt to save a boolean.
	 * @return boolean TRUE in case of success.
	 */
	public function set($key, $data) {
		
		// Verify the data
		
		// Create (overwrite) the cache file
		
		// Encode the data and save it
		
		// Return
		
	}
	
	/**
	 * Load the data from the cache.
	 * If the data is not found or timed out FALSE is returned.
	 * 
	 * @param string $key 
	 */
	public function get($key) {
		
		// Look for the file
		
		// If the file is timed out, delete it and return FALSE.
		
		// Read the file and decode the JSON
		
		// In case of success return the data
		
	}
	
	/**
	 * Remove the data from the cache.
	 * 
	 * 
	 * @param type $key 
	 */
	public function delete($key) {
		
		// Delete the file
		
	}
	
}

/**
 * Exception handler
 *  
 */
class aospeak_cache_Exception extends Exception {
	// Error during set
	const CODE_BOOLEAN_DATA = 20;
}
?>