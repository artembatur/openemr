<?php 
/** **************************************************************************
 *	LabCorpObservation.PHP
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
//require_once("{$GLOBALS['srcdir']}/tcpdf/config/lang/eng.php");
require_once("{$GLOBALS['srcdir']}/tcpdf/tcpdf.php");
require_once("{$GLOBALS['srcdir']}/tcpdf/fpdi/fpdi.php");

$client_address = '';

if (!class_exists("LabCorpConcat")) {
	class LabCorpConcat extends FPDI {
		public function Footer() {}
		public function Header() {}
	}
}


if (!class_exists("LabCorpResult")) {
	/**
	 * The class LabCorpResult is used to generate the lab documents for
	 * the LabCorp interface. It utilizes the TCPDF library routines to 
	 * generate the PDF documents.
	 *
	 */
	class LabCorpResult extends TCPDF {
		/**
		 * Overrides the default header method to produce a custom document header.
		 * @return null
		 * 
		 */
		public function Header() {
			global $message, $client_address;
			
			ob_start();
?>
<table style="width:100%">
	<tr>
		<td style="text-align:center;font-size:20px;font-weight:bold">
			LabCorp Observations
		</td>
	</tr>
	<tr>
		<td style="text-align:center;font-weight:bold">
			Williams Medical Technologies, Inc.
		</td>
	</tr>
</table>
<?php 
			$output = ob_get_clean(); 
			$this->writeHTMLCell(0,0,'','',$output,0,1);
			$this->ln(15);

			ob_start();
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:150px"><span style="font-size:1.3em;font-weight:bold">LabCorp</span><sup>TM</sup></td>
		<td style="width:475px;text-align:center"></td>
		<td style="width:150px;text-align:right"><span style="font-size:1.3em;font-weight:bold">&nbsp;</span>Page <?php echo $this->getAliasNumPage() ?> of <?php echo $this->getAliasNbPages() ?></td>
	</tr>
</table>
<?php 
			$output = ob_get_clean(); 
			$this->writeHTMLCell(0,0,'','',$output,0,1);

			$pageNo = $this->PageNo();
			if ($pageNo > 1) { // starting on second page
				// reset header height for page 2+
				$this->SetMargins(PDF_MARGIN_LEFT, 150, PDF_MARGIN_RIGHT);
					
				ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;padding:0 5px">
	<tr>
		<td style="width:60%;border:1px solid black">
			<small>Patient Name</small><br/>
			<b><?php echo $message->name[0].", ".$message->name[1]." ".$message->name[2] ?></b>
		</td>
		<td style="width:20%;border:1px solid black">
			<small>Account Number</small><br/><b><?php echo $message->account ?></b>
		</td>
		<td style="width:20%;border:1px solid black">
			<small>Specimen Number</small><br/><b><?php echo $message->lab_number; ?></b>
		</td>
	</tr>
</table>
<table nobr="true" style="width:100%;border:1px solid black;padding:0 5px">
	<tr>
		<td style="width:12%;border:1px solid black">
			<small>Patient ID</small><br/>
			<b><?php echo $message->pubpid ?></b>
		</td>
		<td style="width:12%;border:1px solid black">
			<small>Date of Birth</small><br/>
			<b><?php if ($message->dob) echo ( date('m/d/Y',strtotime($message->dob)) ) ?></b>
		</td>
		<td style="width:12%;border:1px solid black">
			<small>Patient Age</small><br/>
			<b><?php if ($message->dob) echo ( floor((time() - strtotime($message->dob)) / 31556926) ) ?></b>
		</td>
		<td style="width:12%;border:1px solid black">
			<small>Gender</small><br/>
			<b><?php echo $message->sex ?></b>
		</td>
		<td style="width:12%;border:1px solid black">
			<small>Control Number</small><br/>
			<b><?php echo $message->order_number ?></b>
		</td>
		<td style="width:20%;border:1px solid black">
			<small>Date/Time Collected</small><br/>
			<b><?php echo date('m/d/Y h:i A',strtotime($message->specimen_datetime)) ?></b>
		</td>
		<td style="width:20%;border:1px solid black">
			<small>Date/Time Reported</small><br/>
			<b><?php echo date('m/d/Y h:i A',strtotime($message->reported_datetime)) ?></b>
		</td>
	</tr>
</table>
<?php 
				$output = ob_get_clean(); 
				$this->writeHTMLCell(0,0,'','',$output,0,1);
				$this->ln(5);
				
				ob_start();
?>
<table style="width:100%;border:1px solid black">
	<tr style="font-size:9px;font-weight:bold">
		<td style="width:20px"></td>
		<td style="text-align:center;width:240px">
			TEST
		</td>
		<td style="text-align:center;width:110px">
			RESULT
		</td>
		<td style="text-align:center;width:80px">
			FLAG
		</td>
		<td style="text-align:center;width:80px">
			UNITS
		</td>
		<td style="text-align:center;width:125px">
			REFERENCE
		</td>
		<td style="text-align:center;width:73px">
			LAB
		</td>
	</tr>
</table>
<?php 
				$output = ob_get_clean(); 
				$this->writeHTMLCell(0,0,'','',$output,0,1);
				$this->ln(5);
				
			} // end page 2+ header
			
		} // end header

		
		/**
		 * Overrides the default footer method to produce a custom document footer.
		 * @return null
		 * 
		 */
		public function Footer() {
			global $message;
			
			ob_start();
?>
			<table nobr="true" style="width:100%;border:1px solid black;padding:0 5px">
				<tr>
					<td style="border:1px solid black">
						<small>Specimen Number</small><br/><b><?php echo $message->lab_number ?></b>
					</td>
					<td style="border:1px solid black">
						<small>Patient ID</small><br/>
						<b><?php echo $message->pubpid ?></b>
					</td>
					<td style="border:1px solid black">
						<small>Control Number</small><br/>
						<b><?php echo $message->order_number ?></b>
					</td>
					<td style="border:1px solid black">
						<small>Account Number</small><br/>
						<b><?php echo $message->account ?></b>
					</td>
					<td style="border:1px solid black">
						<small>Observation Status</small><br/>
						<b><?php echo ($message->lab_status == 'F')? "FINAL REPORT" : "PRELIMINARY" ?></b>
					</td>
				</tr>
			</table>
<?php 
			$output = ob_get_clean(); 
			$this->writeHTMLCell(0,0,'','',$output,0,1);
					
			ob_start();
?>
			<table nobr="true" style="width:100%;padding:10px 0;font-size:1.2em">
				<tr>
					<td style="width:5%">&nbsp;</td>
					<td style="text-align:left;width:45%">
						<?php echo date('m/d/Y h:i A') ?>
					</td>
					<td style="text-align:right;width:50%">
						Page <?php echo $this->getAliasNumPage() ?> of <?php echo $this->getAliasNbPages() ?>
					</td>
				</tr>
			</table>
<?php 
			$output = ob_get_clean(); 
			$this->writeHTMLCell(0,0,'','',$output,0,1);
									
		} // end footer
	} // end LabCorpResult
} // end if exists

/**
 *
 * The makeResultDocuments() creates a PDF requisition.
 *
 * 1. Create a PDF requisition document
 * 2. Store the document in the repository
 * 4. Return a reference to the document
 *
 * @access public
 * @param Request $request object
 * @return string $document PDF document as string
 * 
 */
if (!function_exists("makeResultDocument")) {
	/**
	 * The makeResultDocument function is used to generate the requisition for
	 * the LabCorp interface. It utilizes the TCPDF library routines to 
	 * generate the PDF document.
	 *
	 * @param Order $order object containing original input data
	 * @param Request $request object containing prepared request data
	 * 
	 */
	function makeResultDocument(&$message) {
		// get client information
		global $client_address;
		
		$client_address = "Unknown Site Identifier:<br/>";
		$client_address .= ($message->facility)? $message->facility: "NONE"; // in case we can't find it
		
		if ($message->facility) {
			$query = "SELECT f.* FROM list_options l ";
			$query .= "LEFT JOIN facility f ON l.option_id = f.id ";
			$query .= "WHERE l.list_id = 'LabCorp_Site_Identifiers' AND l.title = '".$message->facility."' LIMIT 1";
			$facility = sqlQuery($query);
			$client_address = $facility['name']."<br/>";
			$client_address .= $facility['street']."<br/>";
			$client_address .= $facility['city'].",  ".$facility['state']."  ".$facility['postal_code']."<br/>";
			$client_address .= $facility['phone'];
		}
		
		// create new PDF document
		$pdf = new LabCorpResult('P', 'pt', 'letter', true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator('OpenEMR');
		$pdf->SetAuthor('Williams Medical Technologies, Inc.');
		$pdf->SetTitle('LabCorp Observations');
		$pdf->SetSubject('LabCorp Results '.$result_data->lab_number);
		$pdf->SetKeywords('LabCorp, WMT, results, observation, '.$result_data->lab_number);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set initial margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, 80, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(15);
		$pdf->SetFooterMargin(60);
		//$pdf->setPrintHeader(false);
		//$pdf->setPrintFooter(false);
	
		// set auto page breaks / bottom margin
		$pdf->SetAutoPageBreak(TRUE, 65);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setJPEGQuality ( 90 );

		$pdf->setLanguageArray($l);

		// set font
		$pdf->SetFont('helvetica', '', 10);

		// start page
		$pdf->AddPage();

		// result image storage
		$images = array();
		
		ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;padding:0 5px">
	<tr>
		<td style="border:1px solid black">
			<small>Specimen Number</small><br/><b><?php echo $message->lab_number; ?></b>
		</td>
		<td style="border:1px solid black">
			<small>Patient ID</small><br/>
			<b><?php echo $message->pubpid ?></b>
		</td>
		<td style="border:1px solid black">
			<small>Control Number</small><br/>
			<b><?php echo $message->order_number ?></b>
		</td>
		<td style="border:1px solid black">
			<small>Account Number</small><br/>
			<b><?php echo $message->account ?></b>
		</td>
		<td style="border:1px solid black">
			<small>Observation Status</small><br/>
			<b><?php echo ($message->lab_status == 'F')? "FINAL REPORT" : "PRELIMINARY" ?></b>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;padding:0 5px">
	<tr>
		<td colspan="3" width="50%" style="border:1px solid black">
			<small>Patient Name</small><br/>
			<b><?php echo $message->name[0].", ".$message->name[1]." ".$message->name[2] ?></b>
		</td>
		<td rowspan="3" colspan="2" width="50%">
			<small>Client Address</small><br/>
			<b><?php echo $client_address ?></b>
		</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			<small>Date of Birth</small><br/>
			<b><?php if ($message->dob) echo ( date('m/d/Y',strtotime($message->dob)) ) ?></b>
		</td>
		<td style="border:1px solid black">
			<small>Patient Age</small><br/>
			<b><?php if ($message->dob) echo ( floor((time() - strtotime($message->dob)) / 31556926) ) ?></b>
		</td>
		<td style="border:1px solid black">
			<small>Gender</small><br/>
			<b><?php echo $message->sex ?></b>
		</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			<small>Alt Patient Id</small><br/>
			<b><?php echo $message->pid ?></b>
		</td>
		<td style="border:1px solid black">
			<small>Patient SS#</small><br/>
			<b><?php echo $message->ss ?></b>
		</td>
		<td style="border:1px solid black">
			<small>Patient Phone</small><br/>
			<b><?php echo $message->phone ?></b>
		</td>
	</tr>
	<tr>
		<td colspan="3" style="border:1px solid black">
			<small>Patient Address</small><br/>
			<b><?php echo $message->address[0] ?>&nbsp;<br/>
			<?php if ($message->address[2]) echo $message->address[2].", " ?><?php echo $message->address[3] ?>  <?php echo $message->address[4] ?></b>&nbsp;<br/>
		</td>
		<td width="50%"  style="border:1px solid black">
			<small>Additional Information</small><br/><b><?php echo $message->additional_data; ?></b>
<?php 
//		$notes = 0;
//		if (count($message->notes) > 0) {
//			foreach ($message->notes AS $note) {
//				echo "<b>".$note ."</b><br/>\n";
//				if ($notes++ == 3) break;
//			}
//		}
?>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;padding:0 5px">
	<tr>
		<td style="border:1px solid black">
			<small>Date/Time Collected</small><br/>
			<b><?php echo date('m/d/Y h:i A',strtotime($message->specimen_datetime)) ?></b>
		</td>
		<td style="border:1px solid black">
			<small>Date/Time Received</small><br/>
			<b><?php echo date('m/d/Y h:i A',strtotime($message->received_datetime)) ?></b>
		</td>
		<td style="border:1px solid black">
			<small>Date/Time Reported</small><br/>
			<b><?php echo date('m/d/Y h:i A',strtotime($message->reported_datetime)) ?></b>
		</td>
		<td style="border:1px solid black">
			<small>Physician Name</small><br/>
			<b><?php echo substr($message->provider[1].", ".$message->provider[2]." ".$message->provider[3], 0, 21) ?></b>
		</td>
		<td style="border:1px solid black">
			<small>NPI Number</small><br/>
			<b><?php echo $message->provider[0] ?></b>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(5);
					
		$count = 0;
		$tests = "";
		$last_id = "";
		foreach ($message->orders AS $order) {
			if ($order->action_type == '' && $order->service_id[0] != $last_id) {
				$last_id = $order->service_id[0];
				if ($tests) $tests .= "; ";
				$tests .= $order->service_id[1];
				$count++;
			}
		}
			
		ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;padding:0 5px">
	<tr>
		<td style="border:1px solid black">
			<small>Tests Ordered  (<?php echo $count ?>)</small><br/>
			<b><?php echo $tests ?></b>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);

		$note_text = '';
		if (count($message->notes) > 0) {
			foreach ($message->notes AS $note) {
				$note_text .= trim($note->comment)." ";
			}
		}
		
		if ($note_text) { // only print section when there is text
			$pdf->ln(5);
			ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;padding:0 5px">
	<tr>
		<td style="border:1px solid black">
			<small>Laboratory Comments</small>
			<b><pre><?php
			$first = true;
			foreach ($message->notes AS $note) {
				if (!$first) echo "<br/>";
				echo $note->comment;
				$first = false;
			}
			?></pre></b>
		</td>
	</tr>
</table>
<?php 
			$output = ob_get_clean(); 
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		} // end if lab comments
		
		$pdf->ln(5);
		ob_start();
?>
<table style="width:100%;border:1px solid black">
	<tr style="font-size:9px;font-weight:bold">
		<td style="text-align:center;width:220px">
			TEST
		</td>
		<td style="text-align:center;width:150px">
			RESULT
		</td>
		<td style="text-align:center;width:90px">
			FLAG
		</td>
		<td style="text-align:center;width:90px">
			UNITS
		</td>
		<td style="text-align:center;width:100px">
			REFERENCE
		</td>
		<td style="text-align:center;width:73px">
			LAB
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(5);

		ob_start();
?>
<table style="width:100%">
	<tr>
		<td style="width:20px"></td>
		<td style="width:30px"></td>
		<td style="width:170px"></td>
		<td style="width:150px"></td>
		<td style="width:90px"></td>
		<td style="width:90px"></td>
		<td style="width:100px"></td>
		<td style="width:73px"></td>
	</tr>
<?php 
		// loop through all of the results
		$last_id = '';
		foreach ($message->orders AS $order) {
			if ($order->action_type == '' && $order->service_id[0] != $last_id) { // skip reflex result
				$last_id = $order->service_id[0];
?>
	<tr>
		<td colspan="8" style="text-align:left;font-weight:bold">
			<?php echo htmlspecialchars($order->service_id[0]) ?> - <?php echo htmlspecialchars($order->service_id[1]) ?>
		</td>
	</tr>
<?php 
			}

			foreach ($order->results AS $result) {
				if ($result->observation_id[1] == ".") $result->observation_id[1] = '';
				
				$abnormal = $result->observation_abnormal; // in case they sneak in a new status
				if ($result->observation_abnormal == 'H') $abnormal = 'High';
				if ($result->observation_abnormal == 'L') $abnormal = 'Low';
				if ($result->observation_abnormal == 'HH') $abnormal = 'Alert High';
				if ($result->observation_abnormal == 'LL') $abnormal = 'Alert Low';
				if ($result->observation_abnormal == '>') $abnormal = 'Panic High';
				if ($result->observation_abnormal == '<') $abnormal = 'Panic Low';
				if ($result->observation_abnormal == 'A') $abnormal = 'Abnormal';
				if ($result->observation_abnormal == 'AA') $abnormal = 'Critical';
				if ($result->observation_abnormal == 'S') $abnormal = 'Susceptible';
				if ($result->observation_abnormal == 'R') $abnormal = 'Resistant';
				if ($result->observation_abnormal == 'I') $abnormal = 'Intermediate';
				if ($result->observation_abnormal == 'NEG') $abnormal = 'Negative';
				if ($result->observation_abnormal == 'POS') $abnormal = 'Positive';

				$facilities[$result->producer_id] = $result->producer_id; // store lab identifier (only once)
?>
	<tr <?php if ($abnormal) echo 'style="font-weight:bold;color:#bb0000"'?>>
		<td>&nbsp;</td>
		<td colspan="2" class="wmtLabel" style="text-align:left">
			<?php echo htmlspecialchars($result->observation_id[1]) ?>
		</td>
<?php 
				$obvalue = $result->observation_value;
				if (is_array($obvalue))
					$obvalue = ($obvalue[1] == 'Image')? "SEE ATTACHED" : $obvalue[0];
				if ( ($result->value_type == 'TX') && $obvalue && $obvalue != ".") { // put TEXT on next line
?>
		<td colspan="5"></td>
	</tr>
	<tr <?php if ($abnormal) echo 'style="font-weight:bold;color:#bb0000"' ?>>
		<td colspan="2"></td>
		<td colspan="2" style="font-family:monospace;text-align:left">
			<?php if ($obvalue != ".") echo htmlspecialchars($obvalue) ?>
		</td>
<?php 
				}
				else {
?>
		<td style="font-family:monospace;text-align:center">
			<?php if ($obvalue != ".") echo htmlspecialchars($obvalue) ?>
		</td>
<?php 		
				} // end if TX type
?>
		<td style="font-family:monospace;text-align:center">
			<?php echo htmlspecialchars($abnormal) ?>
		</td>
		<td style="font-family:monospace;text-align:center">
			<?php echo htmlspecialchars($result->observation_units) ?>
		</td>
		<td style="font-family:monospace;text-align:center">
			<?php echo htmlspecialchars($result->observation_range) ?>
		</td>
		<td style="font-family:monospace;text-align:center">
			<?php echo htmlspecialchars($result->producer_id) ?>
		</td>
	</tr>
<?php
				if ($result->notes) { // put comments below test line
?>
	<tr <?php if ($abnormal) echo 'style="font-weight:bold;color:#bb0000"'?>>
		<td colspan="2">&nbsp;</td>
		<td colspan="6" style="text-align:left">
			<pre>
<?php 
					$first = true;
					foreach ($result->notes AS $note) {
						if (!$first) echo "<br/>";
						echo htmlspecialchars($note->comment);
						$first = false;
					} // end note foreach
?>
			</pre>	
		</td>
	</tr>
<?php 
				} // end if notes
				
				// store any result images (processed below)
				if (is_array($result->images))
					$images = array_merge($images, $result->images);
				
			} // end result foreach
		} // end order foreach
?>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(15);
		ob_start();
?>
<table style="width:100%;border:1px solid black;padding:0 10px;font-size:0.8em">
<?php 
		// loop through all of the labs
		$first = true;
		foreach ($message->labs AS $lab) {
			if ($lab->phone) {
				$phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', '($1) $2-$3', $lab->phone);
			}

			if ($lab->director) {
				$director = "";
				$director .= $lab->director[2]." "; // first
				if ($lab->director[3]) $director .= $lab->director[3]." "; // middle
				$director .= $lab->director[1]." "; // last
				if ($lab->director[0]) $director .= $lab->director[0]." "; // title
			}
			
			if ($lab->address[4]) {
				if (strlen($lab->address[4] > 5)) $zip = preg_replace('~.*(\d{5})(\d{4}).*~', '$1-$2', $lab->address[4]);
				else $zip = $lab->address[4];				
			}
?>
	<tr nobr="true">
		<td style="width:70px">
			<b><?php echo $lab->code ?></b>
		</td>
		<td style="width:400px">
<?php 
				echo $lab->name."<br/>";
				echo $lab->address[0].", ";
				if ($lab->addres[1]) echo $lab->address[1].", ";
				echo $lab->address[2].", ";
				echo $lab->address[3]." ";
				echo $zip."<br/>";
?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<b>For inquiries, please contact the lab at: <?php echo $phone ?></b> 
		</td>
		<td style="width:255px">
			Director: <?php echo $director ?>
		</td>
	</tr>
<?php
		} // end foreach lab 
?>
</table>
<?php 		
		$output = ob_get_clean();
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		
		// finish page
		$pdf->lastPage();

		// generate the PDF document
		$document = $pdf->Output('result.pdf','S'); // return as variable
		
		/* ************************************************************************************************* *
		 *   CAPTURE AND ATTACH IMAGES TO PDF OUTPUT FILE                                                    *
		 * ************************************************************************************************* */
		if (count($images) > 0) { // we have image attachments
			$pdfc = new LabCorpConcat('P', 'pt', 'letter', true, 'UTF-8', false);
				
			$pdfc->setPrintHeader(false);
			$pdfc->setPrintFooter(false);
				
			$thefile = tempnam(sys_get_temp_dir(),'PDF'); // work file
			file_put_contents($thefile, $document);

			// add generated result document
			$pagecount = $pdfc->setSourceFile($thefile);
			for ($i = 1; $i <= $pagecount; $i++) {
				$tplidx = $pdfc->ImportPage($i);
				$s = $pdfc->getTemplatesize($tplidx);
				$pdfc->AddPage('P', array($s['w'], $s['h']));
				$pdfc->useTemplate($tplidx);
			}
		
			// add embedded documents
			foreach ($images AS $image) {
				// write raw file
				$thedoc = base64_decode($image);
				$thefile = tempnam(sys_get_temp_dir(),'PDF'); // work file
				file_put_contents($thefile, $thedoc);
				
				$pagecount = $pdfc->setSourceFile($thefile);
				for ($i = 1; $i <= $pagecount; $i++) {
					$tplidx = $pdfc->ImportPage($i);
					$s = $pdfc->getTemplatesize($tplidx);
					$pdfc->AddPage('P', array($s['w'], $s['h']));
					$pdfc->useTemplate($tplidx);
				}
			}
	
			// generate merged document
			$document = $pdfc->Output('total.pdf','S'); // return as variable
		}
			
		return $document;
	} // end makeResultDocument
} // end if exists
