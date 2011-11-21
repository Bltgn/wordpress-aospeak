<?php
/**
 * Contains the view class for the widget's display.
 *
 * @package AOSpeak
 */

/**
 * Basic view class
 * 
 * @todo Table() method
 */
abstract class Ao_Speak_View {

	public $data;

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
	protected function output($output) {
		return htmlentities($output, NULL, NULL, FALSE);
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
		if( FALSE === isset($this->data['egResult']) ) {
			$this->data['egResult'] = array();
		}
	}

	/**
	 * Output
	 * 
	 */
	public function __toString() {
		$this->dataCheck();

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

		foreach( $this->data['egResult'] as $eResult) {
			$ingame = ($eResult['ingame']) ? 'Yes' : 'No';

			$html .= '<tr>
				<td>' . $this->output( $eResult['name'] ) . '</td>
				<td>' . $this->output( $eResult['country'] ) . '</td>
				<td>' . $ingame . '</td>
				<td>' . $this->output( $eResult['idleTime']) . '</td>
			</tr>';
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
		if( FALSE === isset($this->data['egResult']) ) {
			$this->data['egResult'] = array();
		}
	}

	/**
	 * Output
	 *
	 */
	public function __toString() {
		$this->dataCheck();

		// Header
		$html = '<table class="ao-speak online">
			<thead>
				<tr>
					<th>Name</th>
					<th>Country</th>
					<th>Ingame</th>
					<th>Channel</th>
					<th>Idle time</th>
				</tr>
			</thead>
			<tbody>';

		foreach( $this->data['egResult'] as $eResult) {
			$ingame = ($eResult['ingame']) ? 'Yes' : 'No';

			$html .= '<tr>
				<td>' . $this->output( $eResult['name'] ) . '</td>
				<td>' . $this->output( $eResult['country'] ) . '</td>
				<td>' . $ingame . '</td>
				<td>' . $this->output( $eResult['channelName'] ) . '</td>
				<td>' . $this->output( $eResult['idleTime']) . '</td>
			</tr>';
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
