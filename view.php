<?php
/**
 * Contains the view class for the widget's display.
 * @todo externalize the table and header for translation
 * @todo Handling of empty results (no one online)
 *
 * @package AOSpeak
 */

/**
 * Common values for the widget and the views
 *  
 */
class AO_Speak_View_Setup {
	
	/**
	 * The fields returned by the AO Speak API.
	 * - The keys are the fields names as returned by the API.
	 * - The values will be displayed through the __ and _e functions.
	 * 
	 * @var array Fields returned by AO Speak : ['fieldname' => 'Label']
	 */
	public static $fields = array(
		'fieldName' => 'Name',
		'fieldCountry' => 'Country',
		'fieldIngame' => 'Ingame',
		'fieldChannelName' => 'Channel',
		'fieldIdleTime' => 'Idle Time'
	);
	
}

/**
 * Basic view class
 * 
 */
abstract class Ao_Speak_View {
	
	// By default we show every fields
	const DEFAULT_FIELDS = 32;
	protected $class = '';

	/**
	 * @var array Will contain the variables to display.
	 */
	public $data = array();

	/**
	 * Allow a direct data addition during class creation.
	 * 
	 * @param array $data 
	 */
	public function __construct( array $data = array() ) {
		$this->data = $data;
	}

	/**
	 * Takes appropriate actions if some values are not set.
	 * 
	 */
	protected function dataCheck() {
		if( FALSE === isset( $this->data['egResult'] ) ) {
			$this->data['egResult'] = array();
		}
		if( FALSE === isset( $this->data['fields'] ) ) {
			$this->data['fields'] = self::DEFAULT_FIELDS;
		}
	}

	/**
	 * Filter the provided value.
	 *
	 * @param mixed $output
	 * @return string
	 */
	protected function output( $output ) {
		return htmlentities( $output, NULL, NULL, FALSE );
	}

	/**
	 * Add the variable to the view's data.
	 *
	 * @param atring $key
	 * @param mixed $data
	 * @return Ao_Speak_View
	 */
	public function setData( $key, $value ) {
		$this->data[$key] = $value;
		return $this;
	}
	
	/**
	 * Returns the singular or plural form depending of the entered number.
	 * @todo I18N - russian support (11, 21, etc uses singular)
	 * 
	 * @param string $singular The singuler version of the string.
	 * @param string $plural The plural version of the string.
	 * @param int $number The number defining what we need
	 */
	protected function plural($singular, $plural, $number) {
		return ($number === 1) ? $singular : $plural;
	}
	
	/**
	 * Select the fields to display.
	 * 
	 * @return array
	 * @see AO_Speak_View_Setup
	 */
	protected function fields() {
		// init
		$fields = array();
		$counter = 0;
		
		// For each field, check if the correct bit is set
		foreach( array_keys( AO_Speak_View_Setup::$fields ) as $fieldKey ) {
			$test = $this->data['fields'] & pow( 2, $counter );
			if( 0 !== $test ) {
				$fieldName = lcfirst( substr( $fieldKey, 5 ) );
				$fields[$fieldName] = AO_Speak_View_Setup::$fields[$fieldKey];
			}
			$counter++;
		}
		
		return $fields;
	}
	
	/**
	 * Convert the idle time to a understandable string.
	 * Will add "X day(s), X hours..."
	 * Ommit fields equals to 0.
	 * Doesn't add more then 3 fields.
	 *  
	 * @param int $timeSecond The number of seconds sent by the AO Speak API
	 * @return string
	 */
	protected function idleTime( $timeSecond ) {
		
		$timeField = array();
		
		// Days
		$cTime = (int)floor( $timeSecond / 86400 );
		if( $cTime > 0 ) {
			$timeField[] = $cTime . ' ' . $this->plural( 'day', 'days', $cTime );
			$timeSecond -= $cTime * 86400;
		}
		
		// Hours
		$cTime = (int)floor( $timeSecond / 3600 );
		if( $cTime > 0 ) {
			$timeField[] = $cTime . ' ' . $this->plural( 'hour', 'hours', $cTime );
			$timeSecond -= $cTime * 3600;
		}
		
		// Minutes
		$cTime = (int)floor( $timeSecond / 60 );
		if( $cTime > 0 ) {
			$timeField[] = $cTime . ' ' . $this->plural( 'minute', 'minutes', $cTime );
			$timeSecond -= $cTime * 60;
		}
		
		// Seconds
		if( count( $timeField ) < 3 ) {
			$timeField[] = $cTime . ' ' . $this->plural( 'second', 'seconds', $cTime );
		}
		
		return implode( ' ', $timeField );
		
	}
	
	/**
	 * Output
	 *
	 * @param array $fields
	 * @return string HTML markup
	 */
	public function displayResult(array $fields) {
		// init
		$even = FALSE;

		// Header
		$html = '<table class="aospeak ' . $this->class . '">
			<thead>
				<tr>';
		
		foreach($fields as $fieldLabel) {
			$html .= '<th>' . $fieldLabel . '</th>';
		}
		
		$html .= '</tr>
			</thead>
			<tbody>';

		// Table body
		foreach( $this->data['egResult'] as $eResult ) {
			
			// Filtering
			if(isset($fields['ingame'])) {
				$eResult['ingame'] = ($eResult['ingame']) ? 'Yes' : 'No';
			}
			if(isset($fields['idleTime'])) {
				$eResult['idleTime'] = $this->idleTime( $eResult['idleTime'] );
			}
			
			// Attributes
			$attributes = $even ? 'class="even"' : 'class="odd"';

			// Line generation
			$html .= '<tr '.$attributes.'>';
			foreach( array_keys( $fields ) as $fieldKey ) {
				$html .= '<td>' . $this->output( $eResult[$fieldKey] ) . '</td>';
			}
			$html .= '</tr>';
			
			$even = ($even === FALSE);
			
		}

		// Closing
		$html .= '</tbody>
			</table>';

		return $html;
	}

}

/**
 * Displays a result table for the organisation
 * 
 */
class Ao_Speak_View_Organisation extends Ao_Speak_View {
	
	protected $class = 'aospeak-org';

	/**
	 * Output
	 * 
	 */
	public function __toString() {
		$this->dataCheck();
		$fields = $this->fields();
		unset($fields['channelName']); // no channel is returned in this mode

		// Header
		return $this->displayResult( $fields );
	}

}

/**
 * Displays a result table for the online mode
 * 
 */
class Ao_Speak_View_Online extends Ao_Speak_View {
	
	protected $class = 'aospeak-online';

	/**
	 * Output
	 *
	 */
	public function __toString() {
		// init
		$this->dataCheck();
		return $this->displayResult( $this->fields() );
	}

}

/**
 * View displaying an empty space ready to receive the request data
 * @todo transfer the table and headers there so they can be translated
 * 
 */
class Ao_Speak_View_Request extends Ao_Speak_View {
	
	/**
	 * @todo Some actual checks against weird limit cases
	 */
	protected function dataCheck() {
	}
	
	/**
	 * Output
	 * 
	 * @return string 
	 */
	public function __toString() {
		// Empty table
		$html = '<div class="aospeak"></div>';
		
		// Fields to display
		$counter = $fields = 0;
		foreach( array_keys( AO_Speak_View_Setup::$fields ) as $fieldKey ) {
			if((bool)$this->data['instance'][$fieldKey]) {
				$fields |= pow(2, $counter);
			}
			
			$counter++;
		}
		
		// Javascript call
		$jsParams = array(
			'"' . $this->data['widget_id'] . '"',
			$this->data['instance']['mode'],
			$this->data['instance']['dim'],
			$fields,
			( FALSE === empty( $this->data['instance']['org'] ) ) ? $this->data['instance']['org'] : 0
		);
		
		$html .= '<script type="text/javascript">
			jQuery( aospeak_request(' . implode( ',', $jsParams ) . ') );
		</script>';	
		
		return $html;
	}
	
}
