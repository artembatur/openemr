<?php
/** **************************************************************************
 *	LabCorpParserHL7v2.PHP
 *
 *	Copyright (c)2013 - Williams Medical Technology, Inc.
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package labcorp
 *  @subpackage library
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <info@keyfocusmedia.com>
 * 
 *************************************************************************** */
require_once 'LabCorpModelHL7v2.php';

class ParseException extends Exception {
}

class Parser_HL7v2 {

	var $field_separator;
	var $map;
	var $message;
	var $message_type;

	var $MSH;
	var $PID;
	var $IN1;
	var $GT1;
	var $DG1;
	var $ORC;
	var $OBR;
	var $OBX;
	var $NTE;
	var $ZBL;
	var $ZCI;
	var $ZCC;
	var $ZCY;
	var $ZSA;
	var $ZRE;
	var $ZON;
	var $ZAP;
	var $ZEF;
	var $ZPS;
	var $OTHER;

	function Parser_HL7v2( $message, $_options = NULL ) {
		$this->message = $message;
		$this->field_separator = '|'; // default
		if (is_array($_options)) {
			$this->options = $_options;
		}
	}
	
	function parse() {
		// reference to message
		$message = &$this->message;
		
		// Split HL7v2 message into lines
		$segments = explode("\r", $message);
		
		// Fail if there are no or one segments
		if (count($segments) <= 1) {
			throw new ParseException('\nNo segments found in HL7 message');
		}

		// Loop through messages
		$count = 0;
		foreach ($segments AS $segment) {
			$segment = trim($segment); // strip garbage
			$pos = 0;
			$count++;

			// Determine segment ID
			$type = substr($segment, 0, 3);
			switch ($type) {
				case 'MSH':
				case 'PID':
				case 'ORC':
					$this->message_type = trim($type);
					$pos = call_user_func_array(
						array(&$this, '_'.$type),
						array($segment)
					);
					$this->map[$count]['type'] = $type;
					$this->map[$count]['position'] = 0;
					break;

				case 'IN1':
				case 'OBR':
				case 'OBX':
				case 'NTE':
				case 'EVN':
				case 'ZEF':
				case 'ZPS':
					$this->message_type = trim($type);
					$pos = call_user_func_array(
						array(&$this, '_'.$type),
						array($segment)
					);
					$this->map[$count]['type'] = $type;
					$this->map[$count]['position'] = $pos;
					break;

				default:
					$this->message_type = trim($type);
					$this->__default_segment_parser($segment);
					$this->map[$count]['type'] = $type;
					$this->map[$count]['position'] = count($this->OTHER[$type]);
					break;
					
			} // end switch type
		}
	}


	//----- All handlers go below here

	
	function _EVN ($segment) {
		$composites = $this->__parse_segment ($segment);
		if ($this->options['debug']) {
			print "<b>EVN segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}

		list (
			$this->EVN['event_type_code'],
			$this->EVN['event_datetime'],
			$this->EVN['event_planned'],
			$this->EVN['event_reason'],
			$this->EVN['operator_id']
		) = $composites;
	} // end method _EVN

	function _MSH($segment) {
		// Get separator
		$this->field_separator = substr($segment, 3, 1);
		
		// decompose composite segments
		$composites = $this->__parse_segment($segment);
		if ($this->options['debug']) {
			print "<b>MSH segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}
		
		// Assign values
		list (
			$__garbage, // Skip index [0], it's the separator
			$this->MSH['encoding_characters'],
			$this->MSH['sending_application'],
			$this->MSH['sending_facility'] ,
			$this->MSH['receiving_application'],
			$this->MSH['receiving_facility'],
			$this->MSH['message_datetime'],
			$__garbage, // unsupported
			$this->MSH['message_type'],
			$this->MSH['message_control_id'],
			$this->MSH['processing_id'],
			$this->MSH['version_id']
		) = $composites;

	} // end method _MSH

	function _PID($segment) {
		$composites = $this->__parse_segment($segment);

		// try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
		
		if ($this->options['debug']) {
			print "<b>PID segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}
		
		// Assign values
		list (
			$__garbage, // Skip index [0], it's the type
			$this->PID['set_id'],
			$this->PID['alternate_id'],
			$this->PID['external_id'],
			$this->PID['patient_id'],
			$this->PID['patient_name'],
			$this->PID['maiden_name'],
			$this->PID['birth_datetime'],
			$this->PID['sex'],
			$this->PID['patient_alias'],
			$this->PID['race'],
			$this->PID['patient_address'],
			$this->PID['country_code'],
			$this->PID['phone_number'],
			$this->PID['phone_business'],
			$this->PID['primary_language'],
			$this->PID['marital_status'],
			$this->PID['religion'],
			$this->PID['accounting'],
			$this->PID['ssn']
		) = $composites;

	} // end method _PID

	function _IN1($segment) {
		$composites = $this->__parse_segment($segment);

		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
		
		// Debug
		if ($this->options['debug']) {
			print "<b>IN1 segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}

		// Find out where we are
		$pos = 0;
		if (is_array($this->IN1)) {
			$pos = count($this->IN1);
		}
		
		list (
			$__garbage, // Skip index [0], it's the type
			$this->IN1[$pos]['set_id'],
			$__garbage, // unsupported,
			$this->IN1[$pos]['ins_company_id'],
			$this->IN1[$pos]['ins_company_name'],
			$this->IN1[$pos]['ins_company_address'],
			$__garbage, // unsupported
			$this->IN1[$pos]['ins_phone_number'],
			$this->IN1[$pos]['group_number'],
			$__garbage, // unsupported
			$__garbage, // unsupported
			$this->IN1[$pos]['group_emp_name'],
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$this->IN1[$pos]['insured_name'],
			$this->IN1[$pos]['insured_relation'],
			$__garbage, // unsupported
			$this->IN1[$pos]['insured_address'],
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$__garbage, // unsupported
			$this->IN1[$pos]['company_plan_code'],
			$this->IN1[$pos]['policy_number']
		) = $composites;
		
		return $pos;

	} // end method _IN1

	function _ORC($segment) {
		$composites = $this->__parse_segment($segment);

		// Try to parse composites
		foreach ($composites as $key => $composite) {
					if (!(strpos($composite, '^LAB') === false)) {
				$composites[$key] = str_replace('^LAB', '', $composite);
			}
			elseif (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
		
		// Debug
		if ($this->options['debug']) {
			print "<b>ORC segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}

		list (
			$__garbage, // Skip index [0], it's the type
			$this->ORC['order_control'],
			$this->ORC['placer_order_number'],
			$this->ORC['filler_order_number'],
			$__garbage, // unsupported ORC-4
			$__garbage, // unsupported ORC-5
			$__garbage, // unsupported ORC-6
			$__garbage, // unsupported ORC-7
			$__garbage, // unsupported ORC-8
			$this->ORC['datetime_transaction'], 
			$__garbage, // unsupported
			$__garbage, // unsupported
			$this->ORC['ordering_provider']
		) = $composites;

	} // end method _ORC

	function _OBR($segment) {
		$composites = $this->__parse_segment($segment);
	
		// Try to parse composites
		foreach ($composites as $key => $composite) {
			if (!(strpos($composite, '^LAB') === false)) {
				$composites[$key] = str_replace('^LAB', '', $composite);
			}
			elseif (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
	
		// Debug
		if ($this->options['debug']) {
			print "<b>OBR segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}
	
		// Find out where we are
		$pos = 0;
		if (is_array($this->OBR)) {
			$pos = count($this->OBR);
		}
	
		list (
			$__garbage, // Skip index [0], it's the type
			$this->OBR[$pos]['set_id'],
			$this->OBR[$pos]['placer_order_number'],
			$this->OBR[$pos]['filler_order_number'],
			$this->OBR[$pos]['universal_service_id'],
			$__garbage, // unsupported OBR-5
			$__garbage, // unsupported OBR-6
			$this->OBR[$pos]['collected_datetime'],
			$__garbage, // unsupported OBR-8
			$__garbage, // unsupported OBR-9
			$__garbage, // unsupported OBR-10
			$this->OBR[$pos]['action_type'],
			$__garbage, // unsupported OBR-12
			$this->OBR[$pos]['additional_data'],
			$this->OBR[$pos]['received_datetime'],
			$__garbage, // unsupported OBR-15
			$this->OBR[$pos]['ordering_provider'],
			$__garbage, // unsupported OBR-17
			$this->OBR[$pos]['passthru_field1'],
			$__garbage, // unsupported OBR-19
			$this->OBR[$pos]['microbiology_field'],
			$__garbage, // unsupported OBR-21
			$this->OBR[$pos]['reported_datetime'],
			$__garbage, // unsupported OBR-23
			$this->OBR[$pos]['producer_id'],
			$this->OBR[$pos]['result_status'],
			$this->OBR[$pos]['parent_id'],
			$__garbage, // unsupported OBR-27
			$__garbage, // unsupported OBR-28
			$__garbage, // unsupported OBR-29
		) = $composites;
		
		return $pos;
	
	} // end method _OBR
	
	function _OBX($segment) {
		$composites = $this->__parse_segment($segment);
	
		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
	
		// Debug
		if ($this->options['debug']) {
			print "<b>OBX segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}
	
		// Find out where we are
		$pos = 0;
		if (is_array($this->OBX)) {
			$pos = count($this->OBX);
		}
	
		list (
			$__garbage, // Skip index [0], it's the type
			$this->OBX[$pos]['set_id'],
			$this->OBX[$pos]['value_type'],
			$this->OBX[$pos]['universal_service_id'],
			$__garbage, // unsupported OBX-4
			$this->OBX[$pos]['observation_value'],
			$this->OBX[$pos]['observation_units'],
			$this->OBX[$pos]['observation_range'],
			$this->OBX[$pos]['observation_abnormal'],
			$__garbage, // unsupported OBX-9
			$__garbage, // unsupported OBX-10
			$this->OBX[$pos]['observation_status'],
			$__garbage, // unsupported OBX-12
			$__garbage, // unsupported OBX-13
			$this->OBX[$pos]['observation_datetime'],
			$this->OBX[$pos]['producer_id']
		) = $composites;
		
		return $pos;
	
	} // end method _OBX
	
	function _NTE($segment) {
		$composites = $this->__parse_segment($segment);
	
		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
	
		// Debug
		if ($this->options['debug']) {
			print "<b>NTE segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}
	
		// Find out where we are
		$pos = 0;
		if (is_array($this->NTE)) {
			$pos = count($this->NTE);
		}
	
		list (
			$__garbage, // Skip index [0], it's the type
			$this->NTE[$pos]['set_id'],
			$this->NTE[$pos]['source'],
			$this->NTE[$pos]['comment']
		) = $composites;
	
		return $pos;
	
	} // end method _NTE
	
	function _ZEF($segment) {
		$composites = $this->__parse_segment($segment);
	
		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
	
		// Debug
		if ($this->options['debug']) {
			print "<b>ZEF segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}
	
		// Find out where we are
		$pos = 0;
		if (is_array($this->ZEF)) {
			$pos = count($this->ZEF);
		}
	
		list (
			$__garbage, // Skip index [0], it's the type
			$this->ZEF[$pos]['set_id'],
			$this->ZEF[$pos]['base64']
		) = $composites;
	
		return $pos;
	
	} // end method _NTE
	
	function _ZPS($segment) {
		$composites = $this->__parse_segment($segment);
	
		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
	
		// Debug
		if ($this->options['debug']) {
			print "<b>ZPS segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}
	
		// Find out where we are
		$pos = 0;
		if (is_array($this->ZPS)) {
		$pos = count($this->ZPS);
		}
	
		list (
		$__garbage, // Skip index [0], it's the type
		$this->ZPS[$pos]['set_id'],
		$this->ZPS[$pos]['lab_id'],
		$this->ZPS[$pos]['lab_name'],
		$this->ZPS[$pos]['lab_address'],
		$this->ZPS[$pos]['lab_phone'],
		$__garbage,
		$this->ZPS[$pos]['lab_director'],
		$__garbage,
		$this->ZPS[$pos]['lab_clia']
		) = $composites;
	
		return $pos;
	
	} // end method _NTE
	
	
	function getMessage() {
		$map = &$this->map;
		$message = new Message_HL7v2();
	
		// gather request information
		$message->datetime = $this->MSH['message_datetime'];
		$message->pid = $this->PID['patient_id'];
		$message->dob = $this->PID['birth_datetime'];
		$message->age = $this->PID['age'][0];
		$message->name = $this->PID['patient_name'];
		$message->sex = $this->PID['sex'];
		$message->ss = $this->PID['ss'];
		$message->pubpid = $this->PID['alternate_id'];
		$message->phone = $this->PID['phone_number'];
		$message->address = $this->PID['patient_address'];
		$message->application = $this->MSH['receiving_application'];
		$message->facility = $this->MSH['receiving_facility'];
		$message->order_control = $this->ORC['order_control'];
		$message->order_number = $this->ORC['placer_order_number'];
		$message->account = $this->PID['accounting'][0]; // first element
		$message->bill_type = $this->PID['accounting'][3];
		$message->lab_status = $this->PID['accounting'][5];
		$message->provider = $this->ORC['ordering_provider'];
		$message->lab_received = $this->ORC['datetime_transaction'];
		$message->lab_number = $this->ORC['filler_order_number'];
		if (strlen($message->lab_number) == 11 ) { // needs formatting
			$lab = $message->lab_number;
			$message->lab_number = substr($lab,0,3)."-".substr($lab,3,3)."-".substr($lab,6,4)."-".substr($lab,10,1);
		}
		
		for ($i = 0; $i < count($map); $i++) {
			$item = $map[$i];
			
			while ($item['type'] == 'NTE') {
				$nte_data = &$this->NTE[$item['position']];
				
				$note = new Note_HL7v2();
				$note->set_id = $nte_data['set_id'];
				$note->source = $nte_data['source'];
				$note->comment = $nte_data['comment'];
				
				$message->notes[] = $note;
				$item = $map[++$i];		
			}
			
			while ($item['type'] == 'OBR') {
				$obr_data = &$this->OBR[$item['position']];
				if ($obr_data['placer_order_number'] != $message->order_number)
						throw new ParseException("OBR Detail (".$obr_data['placer_order_number'].") does not match ORC order (".$message->order_number.").");
						
				$order = new Order_HL7v2();
				$order->set_id = $obr_data['set_id'];
				$order->order_control = $this->ORC['order_control'];
				$order->order_number = $this->ORC['placer_order_number'];
				$order->lab_number = $message->lab_number; // use formatted version
				$order->service_id = $obr_data['universal_service_id'];
				$order->parent_id = $obr_data['parent_id'];
				$order->component_id = null;
				$order->specimen_datetime = $obr_data['collected_datetime'];
				if (!$message->specimen_datetime) $message->specimen_datetime = $obr_data['collected_datetime']; 
				$order->received_datetime = $obr_data['received_datetime'];
				if (!$message->received_datetime) $message->received_datetime = $obr_data['received_datetime']; 
				$order->result_datetime = $obr_data['reported_datetime'];
				if (!$message->reported_datetime) $message->reported_datetime = $obr_data['reported_datetime']; 
				$order->service_section = $obr_data['producer_id'];
				$order->result_status = $obr_data['result_status'];
				$order->action_type = $obr_data['action_type'];
				if (!$message->additional_data) {
					if (strlen($obr_data['additional_data']) > 53) {
						$additional = substr($obr_data['additional_data'],0,26).substr($obr_data['additional_data'],27,26).substr($obr_data['additional_data'],54);
					}
					elseif (strlen($obr_data['additional_data']) > 26) {
						$additional = substr($obr_data['additional_data'],0,26).substr($obr_data['additional_data'],27);
					}
					else {
						$additional = $obr_data['additional_data'];
					}
					$message->additional_data = $additional;
				}
				
				
				$item = $map[++$i];		
				while ($item['type'] == 'NTE') {
					$nte_data = &$this->NTE[$item['position']];
				
					$note = new Note_HL7v2();
					$note->set_id = $nte_data['set_id'];
					$note->source = $nte_data['source'];
					$note->comment = $nte_data['comment'];
				
					$order->notes[] = $note;
					$item = $map[++$i];		
				}
			
				while ($item['type'] == 'OBX') {
					$obx_data = &$this->OBX[$item['position']];
			
					$result = new Result_HL7v2();
					$result->set_id = $obx_data['set_id'];
					$result->value_type = $obx_data['value_type'];
					$result->observation_id = $obx_data['universal_service_id'];
					$result->observation_value = $obx_data['observation_value'];
					$result->observation_units = $obx_data['observation_units'];
					$result->observation_range = $obx_data['observation_range'];
					$result->observation_abnormal = $obx_data['observation_abnormal'];
					$result->observation_status = $obx_data['observation_status'];
					$result->observation_datetime = $obx_data['observation_datetime'];
					$result->producer_id = $obx_data['producer_id'];

					// get next HL7 item
					$item = $map[++$i];

					$image = '';
					$last_id = 0;
					$zef_images = array();		
					while ($item['type'] == 'ZEF') {
						$zef_data = &$this->ZEF[$item['position']];
						$image .= $zef_data['base64'];
						if ($zef_data['set_id'] < $last_id) { // started new document
							$last_id = $zef_data['set_id']; 
							$zef_images[] = $image;
							$image = '';
						}
						$item = $map[++$i];		
					}

					// store empty array or base64 image files
					if ($image) {
						$zef_images[] = $image;
						$result->images = $zef_images;
					}
						
					while ($item['type'] == 'NTE') {
						$nte_data = &$this->NTE[$item['position']];
				
						$note = new Note_HL7v2();
						$note->set_id = $nte_data['set_id'];
						$note->source = $nte_data['source'];
						$note->comment = $nte_data['comment'];
				
						$result->notes[] = $note;
						$item = $map[++$i];		
					}
			
					$order->results[] = $result;
				}
				
				$message->orders[] = $order;
			}
			
			while ($item['type'] == 'ZPS') { // place of service
				$zps_data = &$this->ZPS[$item['position']];
			
				$lab = new Lab_HL7v2();
				$lab->set_id = $zps_data['set_id'];
				$lab->code = $zps_data['lab_id'];
				$lab->name = $zps_data['lab_name'];
				$lab->address = $zps_data['lab_address'];
				$lab->phone = $zps_data['lab_phone'];
				$lab->director = $zps_data['lab_director'];
				$lab->clia = $zps_data['lab_clia'];
				
				$message->labs[$zps_data['lab_id']] = $lab; // only need one instance
				$item = $map[++$i];		
			}
		}	
		
		return $message;
	}
	
	
	//----- Truly internal functions

	function __default_segment_parser ($segment) {
		$composites = $this->__parse_segment($segment);

		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->__parse_composite($composite);
			}
		}
		
		// The first composite is always the message type
		$type = $composites[0];

		// Debug
		if ($this->options['debug']) {
			print "<b>".$type." segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".$v."<br/>\n";
			}
		}

		$pos = 0;

		// Find out where we are
		if (is_array($this->OTHER[$type])) {
			$pos = count($this->OTHER[$type]);
		}
		
		$this->OTHER[$type][$pos] = $composites;

	} // end method __default_segment_parser

	function __parse_composite ($composite) {
		return explode('^', $composite);
	} // end method __parse_composite

	function __parse_segment ($segment) {
		return explode($this->field_separator, $segment);
	} // end method __parse_segment
	
} // end class Parser_HL7v2

?>
