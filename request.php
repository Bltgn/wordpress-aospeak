<?php
/**
 * This page receive get parameters and handle a request to the AOSpeak webservice.
 * Cache is checked for data first to prevent spamming.
 * A GET request is then sent according to the settings.
 * Resulting JSON is then applied to the proper view and displayed.
 *
 * Syntax request.php?mode=x&dim=y&org=z
 * - x : 1 for online, 2 for org
 * - y : 1 for Atlantean, 2 for Rimor, 0 for all
 * - z : the org ID, unused in online mode
 *
 * @package AOSpeak
 * @todo Online mode, only org mode is supported to far
 */
require_once dirname( __FILE__ ).'/view.php';

/**
 * Handles requests to the AO Speak API
 *
 * @todo Handling of connection and AO Speak errors
 */
class Ao_Speak_Request {

	const AOSPEAK_URL = 'http://api.aospeak.com';

	// The AO dimension
	const DIMENSION_ATLANTEAN = 1;
	const DIMENSION_RIMOR = 2;
	const DIMENSION_ALL = 0;

	// Request features
	const MODE_ONLINE = 1;
	const MODE_ORG = 2;

	/**
	 * @var int The target dimension, only valid values are the MODE_X constants
	 */
	protected $mode;

	/**
	 * @var int The dimension number, see the DIMENSION_X constants
	 */
	protected $dimension;

	/**
	 * @var array An array of parameters
	 */
	protected $param;

	/**
	 * Prepares a request to the AO Speak API
	 *
	 * @param int $mode
	 * @param int $dimension
	 * @param array $param
	 */
	public function __construct( $mode = 0, $dimension = 0, array $param = array() ) {
		// Class setup
		if( 0 !== $mode ) {
			$this->setMode( $mode );
		}

		if( 0 !== $dimension ) {
			$this->setDimension( $dimension );
		}

		if( FALSE === empty($param) and self::MODE_ORG === $mode ) {
			$this->setOrgParam( $param );
		}
	}

	/**
	 * Sets the kind of requests.
	 * Only work with online and org modes, see the class constants
	 *
	 * @param int $mode
	 * @return Ao_Speak_Request
	 */
	public function setMode( $mode ) {
		// Must be one of the web service's mode
		if( FALSE === in_array( $mode, array( self::MODE_ONLINE, self::MODE_ORG ), TRUE ) ) {
			throw new Ao_Speak_Exception("Invalid mode specified", Ao_Speak_Exception::CODE_MODE_INVALID);
		}

		$this->mode = $mode;
		return $this;
	}

	/**
	 * Sets the dimension.
	 * AO have only 2 dimensions, see the class constants.
	 *
	 * @param int $dimension
	 * @return Ao_Speak_Request
	 */
	public function setDimension( $dimension ) {
		$this->dimension = $dimension;
		return $this;
	}

	/**
	 * Sets the parameters, what parameters is needed depends on the request mode.
	 *
	 * @param array $param
	 * @return Ao_Speak_Request
	 */
	public function setOrgParam( array $param ) {
		$this->param = array();

		// The org id is required
		if( FALSE === empty($param['org']) ) {
			$this->param['org'] = $param['org'];
		}
		return $this;
	}

	/**
	 * Validate the set parameters and send a request to AOSpeak.
	 * mode and dimension must be set first.
	 *
	 * @return array One line per result.
	 */
	public function request() {
		// Selecting the mode's keyword
		// The parameters are checked at the same time
		switch( $this->mode ) {
			case self::MODE_ONLINE :
				$command = 'online';
				break;

			case self::MODE_ORG :
				// Org must be set, cannot be 0
				if( empty( $this->param['org'] ) ) {
					throw new Ao_Speak_Exception("Organisation is not set.", Ao_Speak_Exception::CODE_ORGANISATION_INVALID);
				}
				$command = 'org';
				break;

			default :
				throw new Ao_Speak_Exception("The command is not been set.", Ao_Speak_Exception::CODE_MODE_INVALID);
		}

		// Request building, mode params are added is needed
		$request = self::AOSPEAK_URL . '/' . $command . '/' . $this->dimension;
		if( self::MODE_ORG === $this->mode ) {
			$request .= '/' . $this->param['org'];
		}

		// Sending request with CURL
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		curl_close($curl);

		// Connection error handling

		// AOSpeak error handling

		// Returning the result in array form, or FALSE in case of failure
		return json_decode($result, TRUE);
	}

}

/**
 * Exception for the various errors that may happen.
 * 
 */
class Ao_Speak_Exception extends Exception {
	// Setup problems
	const CODE_MODE_INVALID = 10;
	const CODE_DIMENSION_INVALID = 10;
	const CODE_ORGANISATION_INVALID = 10;

	// AO Speak errors

	// Connection errors
}

/*
 * Functions
 */

/**
 * Grab the value in GET and type it in int.
 * If the value doesn't exists, returns 0.
 *
 * @param string $value
 * @return int
 */
function getGetInt( $value ) {
	if( isset( $_GET[$value] ) ) {
		return (int) $_GET[$value];
	} else {
		return 0;
	}
}

// Action
$mode = getGetInt( 'mode' );
$dimension = getGetInt( 'dim' );
$org = getGetInt( 'org' );
$data = array();

$request = new Ao_Speak_Request( $mode, $dimension, array( 'org' => $org ) );

try {
	$result = $request->request();

	if($mode === Ao_Speak_Request::MODE_ONLINE) {
		$view = new Ao_Speak_View_Online( array( 'egResult' => $result ) );
	} else {
		$view = new Ao_Speak_View_Organisation( array( 'egResult' => $result ) );
	}
	$data['html'] = (string) $view;

} catch( Ao_Speak_Exception $e ) {
	user_error( $e->getMessage(), E_USER_WARNING );
	// Displays a default empty message to the user

}

// Output the result
echo json_encode($data);