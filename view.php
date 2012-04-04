<?php
/**
 * Contains the view class for the widget's display.
 * @todo support for idle time, converted in time
 * @todo support for a optional fields
 * @todo externalize the table and header for translation
 *
 * @package AOSpeak
 */

/**
 * Basic view class
 * 
 */
abstract class Ao_Speak_View {
	
	// The fields
	const FIELD_NAME = 1;
	const FIELD_COUNTRY = 2;
	const FIELD_IDLE_TIME = 4;
	const FIELD_INGAME = 8;

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
	abstract protected function dataCheck();

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
	public function plural($singular, $plural, $number) {
		return ($number === 1) ? $singular : $plural;
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
		$cTime = (int)floor($timeSecond / 86400);
		if( $cTime > 0 ) {
			$timeField[] = $cTime . ' ' . $this->plural( 'day', 'days', $cTime );
			$timeSecond -= $cTime * 86400;
		}
		
		// Hours
		$cTime = (int)floor($timeSecond / 3600);
		if( $cTime > 0 ) {
			$timeField[] = $cTime . ' ' . $this->plural( 'hour', 'hours', $cTime );
			$timeSecond -= $cTime * 3600;
		}
		
		// Minutes
		$cTime = (int)floor($timeSecond / 60);
		if( $cTime > 0 ) {
			$timeField[] = $cTime . ' ' . $this->plural( 'minute', 'minutes', $cTime );
			$timeSecond -= $cTime * 60;
		}
		
		// Seconds
		if( count($timeField) < 3 ) {
			$timeField[] = $cTime . ' ' . $this->plural( 'second', 'seconds', $cTime );
		}
		
		return implode( ' ', $timeField );
		
	}

}

/**
 * Displays a result table for the organisation
 */
class Ao_Speak_View_Organisation extends Ao_Speak_View {

	/**
	 * Defaults the results to an empty array
	 * 
	 */
	protected function dataCheck() {
		if( FALSE === isset( $this->data['egResult'] ) ) {
			$this->data['egResult'] = array();
		}
	}

	/**
	 * Output
	 * 
	 */
	public function __toString() {
		$this->dataCheck();
		$even = FALSE;

		// Header
		$html = '<table class="ao-speak organization">
			<thead>
				<tr>
					<th>Name</th>
					<th>Country</th>
					<th>Ingame</th>
					<th>Idle time</th>
				</tr>
			</thead>
			<tbody>';

		foreach( $this->data['egResult'] as $eResult ) {
			$ingame = ($eResult['ingame']) ? 'Yes' : 'No';
			$attributes = $even ? ' class="even"' : ' class="odd"';

			$html .= '<tr'.$attributes.'>
				<td>' . $this->output( $eResult['name'] ) . '</td>
				<td>' . $this->output( $eResult['country'] ) . '</td>
				<td>' . $ingame . '</td>
				<td>' . $this->idleTime( $eResult['idleTime'] ) . '</td>
			</tr>';
			
			$even = ($even === FALSE);
		}

		// Closing
		$html .= '</tbody>
			</table>';

		return $html;
	}

}

/**
 * Displays a result table for the online mode
 */
class Ao_Speak_View_Online extends Ao_Speak_View {

	/**
	 * Defaults the results to an empty array
	 *
	 */
	protected function dataCheck() {
		if( FALSE === isset( $this->data['egResult'] ) ) {
			$this->data['egResult'] = array();
		}
	}

	/**
	 * Output
	 *
	 */
	public function __toString() {
		$this->dataCheck();
		$even = FALSE;

		// Header
		$html = '<table class="ao-speak online">
			<thead>
				<tr>
					<th>Name</th>
					<th>Country</th>
					<th>Ingame</th>
					<th>Channel</th>
				</tr>
			</thead>
			<tbody>';

		foreach( $this->data['egResult'] as $eResult ) {
			$ingame = ($eResult['ingame']) ? 'Yes' : 'No';
			$attributes = $even ? ' class="even"' : ' class="odd"';

			$html .= '<tr'.$attributes.'>
				<td>' . $this->output( $eResult['name'] ) . '</td>
				<td>' . $this->output( $eResult['country'] ) . '</td>
				<td>' . $ingame . '</td>
				<td>' . $this->output( $eResult['channelName'] ) . '</td>
			</tr>';
			
			$even = ($even === FALSE);
		}

		// Closing
		$html .= '</tbody>
			</table>';

		return $html;
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
		
		extract( $this->data['instance'] );
		
		// Empty table
		$html = '<div class="aospeak"></div>';
		
		
		// Javascript call
		$jsParams = '"' . $this->data['widget_id'] . '", ' . $this->data['instance']['mode'] . ', ' . $this->data['instance']['dim'] . ', ';
		$jsParams .= ( FALSE === empty( $this->data['instance']['org'] ) ) ? $this->data['instance']['org'] : 0;
		
		$html .= '<script type="text/javascript">
			jQuery( aospeak_request(' . $jsParams . ') );
		</script>';	
		
		return $html;
	}
	
}
