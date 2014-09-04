<?php 
/** **************************************************************************
 *	LabCorpDocument.PHP
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

if (!class_exists("LabCorpRequest")) {
	/**
	 * The class LabCorpDocument is used to generate the lab documents for
	 * the LabCorp interface. It utilizes the TCPDF library routines to 
	 * generate the PDF documents.
	 *
	 */
	class LabCorpRequest extends TCPDF {
		/**
		 * Overrides the default header method to produce a custom document header.
		 * @return null
		 * 
		 */
		public function Header() {
			global $order_data;
			
			
			$pageNo = $this->PageNo();
			if ($pageNo > 1) { // starting on second page
				$acct = $order_data->request_account;
				$reqno = $order_data->order_number;
				$date = 'COR EDI';
				if ($order_data->order_datetime > 0)
					$date = date('m/d/Y',strtotime($order_data->order_datetime));
				$pubpid = $order_data->pid;
				if ($order_data->pubpid != $pid) $pubpid .= " ( ".$order_data->pid." )";
				$pat = $order_data->pat_last . ", ";
				$pat .= $order_data->pat_first . " ";
				$pat .= $order_data->pat_middle;
				
				$header = <<<EOD
<table style="width:80%;border:3px solid black">
	<tr>
		<td style="font-weight:bold;text-align:right">
			Account #:
		</td>
		<td style="text-align:left">
			&nbsp;$acct
		</td>
		<td style="font-weight:bold;text-align:right">
			Patient Name:
		</td>
		<td style="text-align:left">
			&nbsp;$pat
		</td>
	</tr>
	<tr>
		<td style="font-weight:bold;text-align:right">
			Requisition #:
		</td>
		<td style="text-align:left">
			&nbsp;$reqno
		</td>
		<td style="font-weight:bold;text-align:right">
			Patient ID:
		</td>
		<td style="text-align:left">
			&nbsp;$pubpid
		</td>
	</tr>
	<tr>
		<td style="font-weight:bold;text-align:right">
			Specimen Date:
		</td>
		<td style="text-align:left">
			&nbsp;$date
		</td>
		<td style="font-weight:bold;text-align:right">
			Page:
		</td>
		<td style="text-align:left">
EOD;
				$header .= "&nbsp;". $this->getAliasNumPage() ." of ". $this->getAliasNbPages();
				$header .= <<<EOD
		</td>
	</tr>
</table>
EOD;
				// add the header to the document
				$this->writeHTMLCell(0,0,120,'',$header,0,1,0,1,'C');
			} // end if second page
		} // end header

		
		/**
		 * Overrides the default footer method to produce a custom document footer.
		 * @return null
		 * 
		 */
		public function Footer() {
			global $order_data;
			
			$pageNo = $this->PageNo();
			if ($pageNo == 1) { // first page only
				$acct = $order_data->request_account;
				$reqno = $order_data->order_number;
				$date = 'COR EDI';
				if ($order_data->order_datetime > 0)
					$date = date('m/d/Y',strtotime($order_data->order_datetime));
				$dob = '';
				if ($order_data->pat_DOB > 0)
					$dob = date('m/d/Y',strtotime($order_data->pat_DOB));
				$pat = $order_data->pat_last . ", ";
				$pat .= $order_data->pat_first . " ";
				$pat .= $order_data->pat_middle;
			
				$footer = <<<EOD
<table style="width:100%;font-size:7px">
	<tr>
		<td>
			<table>
				<tr>
					<td colspan="2"><b>$pat</b></td>
				</tr>
				<tr>
					<td>$dob</td>
					<td>$date</td>
				</tr>
				<tr>
					<td>$acct</td>
					<td>$reqno</td>
				</tr>
			</table>
		</td>
		<td>
			<table>
				<tr>
					<td colspan="2"><b>$pat</b></td>
				</tr>
				<tr>
					<td>$dob</td>
					<td>$date</td>
				</tr>
				<tr>
					<td>$acct</td>
					<td>$reqno</td>
				</tr>
			</table>
				</td>
		<td>
			<table>
				<tr>
					<td colspan="2"><b>$pat</b></td>
				</tr>
				<tr>
					<td>$dob</td>
					<td>$date</td>
				</tr>
				<tr>
					<td>$acct</td>
					<td>$reqno</td>
				</tr>
			</table>
				</td>
		<td>
			<table>
				<tr>
					<td colspan="2"><b>$pat</b></td>
				</tr>
				<tr>
					<td>$dob</td>
					<td>$date</td>
				</tr>
				<tr>
					<td>$acct</td>
					<td>$reqno</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
EOD;
				$this->writeHTMLCell(0,0,50,'',$footer,0,1,0,1);
				$this->ln(5);
				$this->writeHTMLCell(0,0,50,'',$footer,0,1,0,1);
			} // end if first page
		} // end footer
	} // end LabCorpRequest
} // end if exists

/**
 *
 * The makeOrderDocuments() creates a PDF requisition.
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
if (!function_exists("makeOrderDocument")) {
	/**
	 * The makeOrderDocument function is used to generate the requisition for
	 * the LabCorp interface. It utilizes the TCPDF library routines to 
	 * generate the PDF document.
	 *
	 * @param Order $order object containing original input data
	 * @param Request $request object containing prepared request data
	 * 
	 */
	function makeOrderDocument(&$order_data,&$test_list,&$zseg_list) {
		// retrieve insurance information
		$ins_primary = new wmtInsurance($order_data->ins_primary_id);
		$ins_secondary = new wmtInsurance($order_data->ins_secondary_id);
		
		// create new PDF document
		$pdf = new LabCorpRequest('P', 'pt', 'letter', true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator('OpenEMR');
		$pdf->SetAuthor('Williams Medical Technologies, Inc.');
		$pdf->SetTitle('LabCorp Requisition');
		$pdf->SetSubject('LabCorp Order '.$order_data->order_number);
		$pdf->SetKeywords('LabCorp, WMT, order, '.$order_data->order_number);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, 65, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(15);
		$pdf->SetFooterMargin(98); // orig 60
		//$pdf->setPrintHeader(false);
		//$pdf->setPrintFooter(false);
	
		// set auto page breaks / bottom margin
		$pdf->SetAutoPageBreak(TRUE, 90);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setJPEGQuality ( 90 );

		$pdf->setLanguageArray($l);

		// set font
		$pdf->SetFont('helvetica', '', 10);

		// start page
		$pdf->AddPage();

		ob_start(); 
?>
<table style="width:100%">
	<tr>
		<td style="text-align:center;font-size:20px;font-weight:bold">
			LabCorp - <?php echo ($order_data->order_psc)? "COR EDI": "EREQ" ?>
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
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(40);
		ob_start();
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:150px"><span style="font-size:1.3em;font-weight:bold">LabCorp</span><sup>TM</sup></td>
		<td style="width:400px;text-align:center"><span style="font-size:1.3em;font-weight:bold"><?php echo ($order_data->order_psc)? "COR EDI": "EREQ" ?></span> Williams Medical Technologies, Inc.</td>
		<td style="width:225px;text-align:right"><span style="font-size:1.3em;font-weight:bold">&nbsp;</span>Page <?php echo $pdf->getAliasNumPage() ?> of <?php echo $pdf->getAliasNbPages() ?></td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black">
	<tr>
		<td style="width:50%">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Account #:</td>
					<td><?php echo $order_data->request_account ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Requisition #:</td>
					<td><?php echo $order_data->order_number ?></td>
				</tr>
			</table>
		</td>
		<td style="width:50%">
			<table style="width:100%">
<?php 
	if ($order_data->order_psc) {
?>
				<tr><td><!-- filler --></td></tr>
<?php 
	}
	else {
		$coll_date = date('m/d/Y',strtotime($order_data->order_datetime));
		$coll_time = date('h:i A',strtotime($order_data->order_datetime));
?>
		 
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Collection Date:</td>
					<td><?php echo $coll_date ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Collection Time:</td>
					<td><?php echo $coll_time ?></td>
				</tr>
<?php 
	}
	
	if ($order_data->copy_acct || $order_data->copy_fax || $order_data->copy_pat) {
		$copies = '';
		if ($order_data->copy_pat) {
			$copies = '<tr><td style="width:120px;font-weight:bold;text-align:right;vertical-align:top">Courtesy Copy:</td>';
			$copies .= "<td>Patient</td></tr>\n"; 
		}
		
		if ($order_data->copy_acct) {
			$copies .= '<tr><td style="width:120px;font-weight:bold;text-align:right;vertical-align:top">Copy Account:</td>';
			$copies .= "<td>".$order_data->copy_acct; 
			if ($order_data->copy_acctname) $copies .= "<br/>". $order_data->copy_acctname;
			$copies .= "</td></tr>\n";
		}
	
			if ($order_data->copy_fax) {
			$copies .= '<tr><td style="width:120px;font-weight:bold;text-align:right;vertical-align:top">Send Fax:</td>';
			$copies .= "<td>".substr($order_data->copy_fax, 0, 3) . '-' . substr($order_data->copy_fax, 3, 3) . '-' . substr($order_data->copy_fax, 6);; 
			if ($order_data->copy_faxname) $copies .= "<br/>". $order_data->copy_faxname;
			$copies .= "</td></tr>\n";
		}
		if ($copies) echo $copies;
	} // end copy to
?>
			</table>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(5);
		
		if ($order_data->facility_id)
			$facility = sqlQuery("SELECT * FROM facility WHERE id = $order_data->facility_id LIMIT 1");

		if ($order_data->request_provider)
			$provider = sqlQuery("SELECT * FROM users WHERE id = $order_data->request_provider LIMIT 1");
		
		ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td style="font-size:.8em;width:50%;font-weight:bold;border:1px solid black">&nbsp;CLIENT / ORDERING SITE INFORMATION:</td>
		<td style="font-size:.8em;width:50%;font-weight:bold;border:1px solid black">&nbsp;ORDERING PHYSICIAN:</td>
	</tr>
	<tr>
		<td style="border-right:1px solid black;vertical-align:top;padding:5px">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Account Name:</td>
					<td><?php echo $facility['name'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Client Address:</td>
					<td><?php echo $facility['street'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($facility['city'])? $facility['city'].", ": "" ?><?php echo $facility['state'] ?>  <?php echo $facility['postal_code'] ?></td>
				</tr>
<?php if ($facility['phone']) { ?>
				<tr>
					<td style="font-weight:bold;text-align:right">Phone:</td>
					<td><?php echo $facility['phone'] ?></td>
				</tr>
<?php } ?>
			</table>
		</td>
		<td>
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Physician Name:</td>
					<td><?php echo $provider['lname'] ?>, <?php echo $provider['fname'] ?> <?php echo $provider['mname'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">NPI:</td>
					<td><?php echo $provider['npi'] ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>		
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(5);
		
		$self_guarantor = false;
		if ($order_data->pat_first == $order_data->guarantor_first &&
				$order_data->pat_last == $order_data->guarantor_last)
			$self_guarantor = true;
		
		
		ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse;margin-bottom:5px">
	<tr style="border:1px solid black;">
		<td colspan="2" style="font-size:.8em;font-weight:bold">
			&nbsp;PATIENT <?php if ($self_guarantor && $order_data->request_billing != 'C') echo "/ GUARANTOR "?>INFORMATION:
		</td>
	</tr>
	<tr>
		<td style="width:50%;border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Patient Name:</td>
					<td><?php echo $order_data->pat_last ?>, <?php echo $order_data->pat_first ?> <?php echo $order_data->pat_middle ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Patient Address:</td>
					<td><?php echo $order_data->pat_street ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($order_data->pat_city)? $order_data->pat_city.", ": "" ?><?php echo $order_data->pat_state ?> <?php echo $order_data->pat_zip ?></td>
				</tr>
<?php if ($order_data->pat_phone) { ?>
				<tr>
					<td style="font-weight:bold;text-align:right">Phone:</td>
					<td><?php echo $order_data->pat_phone ?></td>
				</tr>
<?php } ?>
<?php if ($self_guarantor && $order_data->request_billing != 'C') { ?>
				<tr>
					<td style="font-weight:bold;text-align:right">Guarantor:</td>
					<td><?php echo ($order_data->work_flag)? "Work Comp": "Self" ?></td>
				</tr>
<?php } ?>
</table>
		</td>
		<td style="width:50%;border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Patient ID:</td>
					<td>
						<?php echo ($order_data->pubpid)? $order_data->pubpid: $order_data->pid ?>
					</td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Gender:</td>
					<td><?php echo $order_data->pat_sex ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Date of Birth:</td>
					<td>
						<?php echo ($order_data->pat_DOB)? date('m/d/Y',strtotime($order_data->pat_DOB)): '' ?>
						<?php echo ($order_data->pat_age)? ' ( '.$order_data->pat_age.' years )': '' ?>
					</td>
				</tr>
<?php if ($order_data->pat_race) { ?>				
				<tr>
					<td style="font-weight:bold;text-align:right">Race:</td>
					<td>
						<?php echo ListLook($order_data->pat_race,'Race') ?>
						<?php echo ($order_data->pat_ethnicity)? ' ( '.ListLook($order_data->pat_ethnicity,'Ethnicity').' )': '' ?>
					</td>
				</tr>
<?php } ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Alt Patient ID:</td>
					<td>
						<?php echo $order_data->pid ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(5);
		
		ob_start();
		
		$adtl_done = false; // done additional data section
		if (count($test_list) < 5) { // one section only
			$adtl_done = true;
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr style="border:1px solid black">
		<td style="width:10%;font-size:.8em;font-weight:bold">&nbsp;TEST ID</td>
		<td style="width:40%;border-right:1px solid black;font-size:.8em;font-weight:bold">TEST DESCRIPTION&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(total:<?php echo count($test_list) ?>)</td>
		<td style="width:50%;font-size:.8em;font-weight:bold">&nbsp;ADDITIONAL INFORMATION:</td>
	</tr>
	<tr>
		<td colspan="2" style="width:50%;border:1px solid black">
			<table style="width:100%">
<?php 
			foreach ($test_list AS $test_data) {
?>
				<tr>
					<td style="width:70px;text-align:left"><?php echo $test_data->test_code ?></td>
					<td style="width:330px"><?php echo $test_data->test_text ?></td>
				</tr>
<?php 
			} // end foreach test
?>			
			</table>
		</td>
		<td style="border:1px solid black">
			<table style="width:100%">
<?php if ($order_data->order_fasting) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Patient Fasting:</td>
					<td><?php echo ($order_data->order_fasting == 'Y')? "Yes" : "No" ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->pat_height > 0) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Height (in):</td>
					<td><?php printf('%03s',intval($order_data->pat_height)) ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->pat_weight > 0) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Weight (lbs):</td>
					<td><?php printf('%03s',intval($order_data->pat_weight)) ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->order_volume) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Volume (mls):</td>
					<td><?php printf('%04s',intval($order_data->order_volume)) ?></td>
				</tr>
<?php } ?>
				<tr><td>&nbsp;</td></tr>
			</table>
		</td>
<?php 
		} else { // two sections
			$half = round(count($test_list) / 2);
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr style="border:1px solid black">
		<td style="width:10%;font-size:.8em;font-weight:bold">&nbsp;TEST ID</td>
		<td style="width:40%;border-right:1px solid black;font-size:.8em;font-weight:bold">TEST DESCRIPTION&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(total:<?php echo count($test_list) ?>)</td>
		<td style="width:10%;font-size:.8em;font-weight:bold">&nbsp;TEST ID</td>
		<td style="width:40%;font-size:.8em;font-weight:bold">TEST DESCRIPTION</td>
	</tr>
	<tr style="padding-top:5px">
<?php 
			$test = 99;
			foreach ($test_list AS $test_data) {
				if ($test > $half) {
					if ($test != 99) {
?>
			</table>
		</td>
<?php 
					} // end if first split
?>
		<td colspan="2" style="width:50%;border:1px solid black">
			<table style="width:100%">
<?php 
					$test = 0;
				} // end new column
				$test++;
?>
				<tr>
					<td style="width:70px"><?php echo $test_data->test_code ?></td>
					<td style="width:330px"><?php echo $test_data->test_text ?></td>
				</tr>
<?php 
			} // end foreach test
?>			
			</table>
		</td>
<?php 
		} // end section selection
?>
	</tr>
</table>		
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(5);
		
		ob_start();
		$do_section = false;
		if ($order_data->order_notes && $adtl_done) { // do we need this section?
			$do_section = true;
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td style="font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;ORDER INFORMATION:</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			&nbsp;<?php echo $order_data->order_notes ?>
		</td>
	</tr>
</table>		
<?php 
		} // end if
		
		if (!$adtl_done) { // need this section
			$do_section = true;
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;ORDER INFORMATION:</td>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;ADDITIONAL INFORMATION:</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			&nbsp;<?php echo $order_data->order_notes ?>
		</td>
		<td style="border:1px solid black">
			<table style="width:100%">
<?php if ($order_data->order_fasting) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Patient Fasting:</td>
					<td><?php echo ($order_data->order_fasting == 'Y')? 'Yes': 'No' ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->pat_height > 0) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Height (in):</td>
					<td><?php echo $order_data->pat_height ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->pat_weight > 0) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Weight (lbs):</td>
					<td><?php echo $order_data->pat_weight ?></td>
				</tr>
<?php } ?>
<?php if ($order_data->order_volume) { ?>
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Volume (mls):</td>
					<td><?php echo $order_data->order_volume ?></td>
				</tr>
<?php } ?>
			</table>
		</td>
	</tr>
</table>		
<?php
		} // end if section needed 

		$output = ob_get_clean(); // clean buffer regardless
		if ($do_section) { 
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
			$pdf->ln(5);
		}
			
		$aoe_list = $zseg_list['SOURCE']; // get aoe data
		if (count($aoe_list) > 0) { // do we need this section?
			ob_start();
			$aoe = $aoe_list[0]; // only one response this type
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td style="font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;<?php echo strtoupper($aoe->display_label) ?>:</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			<?php echo $aoe->display_text ?>
		</td>
	</tr>
</table>		
<?php 
			$output = ob_get_clean(); 
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
			$pdf->ln(5);
		} // end if
				
		foreach ($zseg_list AS $zseg => $aoe_list) {
			if ($zseg == 'SOURCE') continue;

			switch ($zseg) {
				case 'PAP': $title = 'GYNECOLOGIC CYTOLOGY'; break;
				case 'LCMBLD': $title = 'BLOOD LEAD'; break;
				case 'SOURCE': $title = 'SOURCE'; break;
				case 'AFAFP': $title = 'AMNIOTIC FLUID AFP'; break;
				case 'MSONLY': $title = 'MATERNAL SCREEN ONLY'; break;
				case 'MSSNT': $title = 'MATERNAL SCREEN WITH NT'; break;
				case 'SERIN': $title = 'SERUM INTEGRATED AFP'; break;
				default: $title = 'UNKNOWN SECTION';
			}
		
			$aoe_count = 0;
			foreach ($aoe_list AS $aoe_data)
				if ($aoe_data->display_text) $aoe_count++;
		
			if ($aoe_count > 0) { // do we need this section?
				ob_start();
				$half = round($aoe_count / 2);
?>
		<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
			<tr>
				<td colspan="2" style="width:100%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;<?php echo $title ?></td>
			</tr>
			<tr>
<?php 
				$aoe = 99;
				$section = '';
				foreach ($aoe_list AS $aoe_data) {
					if ($aoe_data->display_text == '') continue;
				
					if ($aoe > $half) { // check for new column
						if ($aoe != 99) {
?>
					</table>
				</td>
<?php 
						} // end if first split
?>
				<td style="width:50%">
					<table style="width:100%">
<?php 
						$aoe = 1;
					} // end new column
					
					if ($aoe_data->section != $section) {
						$section = $aoe_data->section;
						switch ($section) {
							case 'age': $label = 'GESTATIONAL AGE'; break;
							case 'calc': $label = 'CALCULATION INFORMATION'; break;
							case 'patient': $label = 'PATIENT INFORMATION'; break;
							case 'other': $label = 'OTHER INFORMATION'; break;
							case 'prior': $label = 'PRIOR INFORMATION'; break;
							case 'fetal': $label = 'FETAL INFORMATION'; break;
							case 'creds': $label = 'CREDENTIAL INFORMATION'; break;
							case 'bodysite': $label = 'GYNOLOGICAL BODY SITE'; break;
							case 'collection': $label = 'COLLECTION INFORMATION'; break;
							case 'cytology': $label = 'CYTOLOGY INFORMATION'; break;
							case 'lmp': $label = 'LMP INFORMATION'; break;
							case 'previous': $label = 'PREVIOUS TREATMENTS'; break;
							default: $label = '';
						}
						
						if ($label) {
?>							
						<tr>
							<td colspan="2" style="font-weight:normal;text-align:left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u><?php echo $label ?></u></td>
						</tr>
<?php 						
						}
					}
					$aoe++;
?>
						<tr>
							<td style="font-weight:bold;text-align:right"><?php echo $aoe_data->display_label ?>:</td>
							<td style="width:120px"><?php echo $aoe_data->display_text ?></td>
						</tr>
<?php 
				} // end foreach test
?>			
					</table>
				</td>
			</tr>
		</table>		
<?php 
				$output = ob_get_clean(); 
				$pdf->writeHTMLCell(0,0,'','',$output,0,1);
				$pdf->ln(5);
			} // end if
								
		} // end foreach zseg					
				
		
		ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td colspan="8" style="font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;DIAGNOSIS CODES:</td>
	</tr>
	<tr>
<?php 
	for ($d = 0; $d < 8; $d++) {
		$key = "dx".$d."_code";
		$dx_code = $order_data->$key;
?>
		<td style="border:1px solid black">&nbsp;<?php echo $dx_code ?></td>
<?php 
	} // end for loop
	if ($order_data->dx8_code) { // are there more than 8 codes?
		echo "</tr><tr>\n";
		for ($d = 8; $d < 16; $d++) {
			$key = "dx".$d."_code";
			$dx_code = ($d < 10) ? $order_data->$key : ""; // openemr only stores 10 dx codes
?>
		<td style="border:1px solid black">&nbsp;<?php echo $dx_code ?></td>
<?php 
		} // end loop
	} // end line 2
?>
	</tr>
	<tr>
		<td colspan="4" style="font-weight:bold;border:1px solid black">&nbsp;Bill Type:&nbsp;&nbsp;<span style="font-weight:normal"><?php if ($order_data->request_billing) echo ListLook($order_data->request_billing,'LabCorp_Billing') ?></span></td>
		<td colspan="4" style="font-weight:bold;border:1px solid black">
			&nbsp;LCA Ins Code:&nbsp;&nbsp;
			<span style="font-weight:normal">
<?php 
		if ($order_data->request_billing == 'T') {
			if ($order_data->work_insurance) {
				echo ListLook($order_data->work_insurance,'LabCorp_Insurance');
			}
			else { 
				if ($order_data->ins_primary_id) echo ListLook($ins_primary->company_id,'LabCorp_Insurance');
			}
		}		
?>
			</span>
		</td>
	</tr>
</table>		
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(5);
		
		if (!$self_guarantor && $order_data->request_billing != 'C') { // only needed when not patient
			ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td colspan="2" style="font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;RESPONSIBLE PARTY / GUARANTOR INFORMATION:</td>
	</tr>
	<tr>
		<td>
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Guarantor:</td>
					<td>
						<?php echo $order_data->guarantor_last ?>, <?php echo $order_data->guarantor_first ?> <?php echo $order_data->guarantor_middle ?>
					</td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Address:</td>
					<td><?php echo $order_data->guarantor_street ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td>
						<?php if ($order_data->guarantor_city) echo $order_data->guarantor_city.", " ?><?php echo $order_data->guarantor_state ?> <?php echo $order_data->guarantor_zip ?>
					</td>
				</tr>
			</table>
		</td>
		<td>
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Phone:</td>
					<td><?php echo $order_data->guarantor_phone ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Relationship:</td>
					<td><?php echo $order_data->guarantor_relation ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right"></td>
					<td></td>
				</tr>
			</table>
		</td>
	</tr>
</table>		
<?php 
			$output = ob_get_clean(); 
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
			$pdf->ln(5);
		} // end self guaranteed
		
		if ($order_data->order_abn_signed || $order_data->work_flag ) {
			ob_start();
?>
<table nobr="true" style="width:100%;border:1px solid black;border-collapse:collapse">
	<tr>
		<td style="text-align:right;font-weight:bold">ABN Signed: </td>
		<td><?php echo ($order_data->order_abn_signed)? ListLook($order_data->order_abn_signed,'LabCorp_Yes_No'): '' ?></td>
		<td style="text-align:right;font-weight:bold">Worker's Comp: </td>
		<td><?php echo ($order_data->work_flag)? ListLook($order_data->work_flag,'LabCorp_Yes_No'): '' ?></td>
		<td style="text-align:right;font-weight:bold">Date of Injury: </td>
		<td><?php echo ($order_data->work_flag)? date('m/d/Y',strtotime($order_data->work_date)): '' ?></td>
	</tr>
</table>		
<?php 
			$output = ob_get_clean(); 
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
			$pdf->ln(5);
		} // end extra bar
		
		if ($order_data->request_billing == 'T') { // third-party so need insurance
			ob_start();
		
			if ($order_data->work_flag) { // workers comp insurance
				$ins_work = wmtInsurance::getCompany($order_data->work_insurance);
?>
<table nobr="true" style="width:100%">
	<tr>
		<td colspan="2" style="font-size:1.3em;font-weight:bold">Insurance Information</td>
	</tr>
	<tr>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;WORKERS COMP INSURANCE:</td>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;INSURED EMPLOYEE:</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">LCA Ins Code:</td>
					<td><?php echo ListLook($ins_work['company_id'], 'LabCorp_Insurance') ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Company Name:</td>
					<td><?php echo $ins_work['company_name'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Ins Address:</td>
					<td><?php echo $ins_work['line1'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_work['city'])? $ins_work['city'].', ': '' ?><?php echo $ins_work['state'] ?> <?php echo $ins_work['zip'] ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Case Number:</td>
					<td><?php echo $order_data->work_case ?></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</table>
		</td>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Insured Name:</td>
					<td><?php echo $order_data->pat_last ?>, <?php echo $order_data->pat_first ?> <?php echo $order_data->pat_middle ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Insured Address:</td>
					<td><?php echo $order_data->pat_street ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($order_data->pat_city)? $order_data->pat_city.', ': '' ?><?php echo $order_data->pat_state ?> <?php echo $order_data->pat_zip ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Employer:</td>
					<td><?php echo $order_data->work_employer ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>		
<?php 
				$output = ob_get_clean(); 
				$pdf->writeHTMLCell(0,0,'','',$output,0,1);
				$pdf->ln(15);
			} // end workers comp insurance
			elseif ($order_data->ins_primary_id && $order_data->ins_secondary_id) { // two insurance plans
?>
<table nobr="true" style="width:100%">
	<tr>
		<td colspan="2" style="font-size:1.3em;font-weight:bold">Insurance Information</td>
	</tr>
	<tr>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;PRIMARY INSURANCE:</td>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;SECONDARY INSURANCE:</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">LCA Ins Code:</td>
					<td><?php echo ListLook($ins_primary->company_id, 'LabCorp_Insurance') ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Company Name:</td>
					<td><?php echo $ins_primary->company_name ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Ins Address:</td>
					<td><?php echo $ins_primary->line1 ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_primary->city)? $ins_primary->city.', ': '' ?><?php echo $ins_primary->state ?> <?php echo $ins_primary->zip ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Policy Number:</td>
					<td><?php echo $order_data->ins_primary_policy ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Group Number:</td>
					<td><?php echo $order_data->ins_primary_group ?></td>
				</tr>
			</table>
		</td>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">LCA Ins Code:</td>
					<td><?php echo ListLook($ins_secondary->company_id, 'LabCorp_Insurance') ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Company Name:</td>
					<td><?php echo $ins_secondary->company_name ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Ins Address:</td>
					<td><?php echo $ins_secondary->line1?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_secondary->city)? $ins_secondary->city.', ': '' ?><?php echo $ins_secondary->state ?> <?php echo $ins_secondary->zip ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Policy Number:</td>
					<td><?php echo $order_data->ins_secondary_policy ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Group Number:</td>
					<td><?php echo $order_data->ins_secondary_group ?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;PRIMARY POLICY HOLDER / INSURED:</td>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;SECONDARY POLICY HOLDER / INSURED:</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Insured Name:</td>
					<td><?php echo $ins_primary->subscriber_lname ?>, <?php echo $ins_primary->subscriber_fname ?> <?php echo $ins_primary->subscriber_mname ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Insured Address:</td>
					<td><?php echo $ins_primary->subscriber_street ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_primary->subscriber_city)? $ins_primary->subscriber_city.', ': '' ?><?php echo $ins_primary->subscriber_state ?> <?php echo $ins_primary->subscriber_postal_code ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Relationship:</td>
					<td><?php echo ListLook($ins_primary->subscriber_relationship,'sub_relation') ?></td>
				</tr>
			</table>
		</td>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Insured Name:</td>
					<td><?php echo $ins_secondary->subscriber_lname ?>, <?php echo $ins_secondary->subscriber_fname ?> <?php echo $ins_secondary->subscriber_mname ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Insured Address:</td>
					<td><?php echo $ins_secondary->subscriber_street ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_secondary->subscriber_city)? $ins_secondary->subscriber_city.', ': '' ?><?php echo $ins_secondary->subscriber_state ?> <?php echo $ins_secondary->subscriber_postal_code ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Relationship:</td>
					<td><?php echo ListLook($ins_secondary->subscriber_relationship,'sub_relation') ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>		
<?php 
				$output = ob_get_clean(); 
				$pdf->writeHTMLCell(0,0,'','',$output,0,1);
				$pdf->ln(15);
			} 
			elseif ($order_data->ins_primary_id) { // only one insurance plan
				$ins_primary = new wmtInsurance($order_data->ins_primary_id);
?>
<table nobr="true" style="width:100%">
	<tr>
		<td colspan="2" style="font-size:1.3em;font-weight:bold">Insurance Information</td>
	</tr>
	<tr>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;PRIMARY INSURANCE:</td>
		<td style="width:50%;font-size:.8em;font-weight:bold;border:1px solid black">&nbsp;PRIMARY POLICY HOLDER / INSURED:</td>
	</tr>
	<tr>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">LCA Ins Code:</td>
					<td><?php echo ListLook($ins_primary->company_id, 'LabCorp_Insurance') ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Company Name:</td>
					<td><?php echo $ins_primary->company_name ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Ins Address:</td>
					<td><?php echo $ins_primary->line1?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_primary->city)? $ins_primary->city.', ': '' ?><?php echo $ins_primary->state ?> <?php echo $ins_primary->zip ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Policy Number:</td>
					<td><?php echo $order_data->ins_primary_policy ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Group Number:</td>
					<td><?php echo $order_data->ins_primary_group ?></td>
				</tr>
			</table>
		</td>
		<td style="border:1px solid black">
			<table style="width:100%">
				<tr>
					<td style="width:120px;font-weight:bold;text-align:right">Insured Name:</td>
					<td><?php echo $ins_primary->subscriber_lname ?>, <?php echo $ins_primary->subscriber_fname ?> <?php echo $ins_primary->subscriber_mname ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Insured Address:</td>
					<td><?php echo $ins_primary->subscriber_street ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">City, State Zip:</td>
					<td><?php echo ($ins_primary->subscriber_city)? $ins_primary->subscriber_city.', ': '' ?><?php echo $ins_primary->subscriber_state ?> <?php echo $ins_primary->subscriber_postal_code ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold;text-align:right">Relationship:</td>
					<td><?php echo ListLook($ins_primary->subscriber_relationship,'sub_relation') ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>		
<?php 
				$output = ob_get_clean(); 
				$pdf->writeHTMLCell(0,0,'','',$output,0,1);
				$pdf->ln(15);
			} // end single insurance
		} // end if insurance
		
		ob_start();
?>
<table nobr="true" style="width:100%;font-size:0.7em">
	<tr>
		<td colspan="2"><span style="font-size:1.3em;font-weight:bold">Authorization</span> - Please sign and date</td>
	</tr><tr>
		<td colspan="2">I hereby authorize the release of medical information related to the services described hereon and authorize payment directly to Laboratory Corporation of America.</td>
	</tr><tr>
		<td colspan="2">I agree to assume responsibility for payment of charges for laboratory services that are not covered by my healthcare insurer.</td>
	</tr><tr>
		<td><br/></td>
	</tr><tr>
		<td>
			<table style="width:100%">
				<tr><td colspan="3">&nbsp;</td></tr>
				<tr>
					<td style="width:400px;border-top:1px solid black">Patient Signature</td>
					<td style="width:40px"></td>
					<td style="width:100px;border-top:1px solid black">Date</td>
				</tr>
				<tr><td colspan="3">&nbsp;</td></tr>
				<tr><td colspan="3">&nbsp;</td></tr>
				<tr>
					<td style="width:400px;border-top:1px solid black">Physician Signature</td>
					<td style="width:40px"></td>
					<td style="width:100px;border-top:1px solid black">Date</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);

		// finish page
		$pdf->lastPage();

//		$TEST = true;
//		if ($TEST) {
//			$pdf->Output('label.pdf', 'I'); // force display download
//		}
//		else {
			$document = $pdf->Output('requisition.pdf','S'); // return as variable
			
//			$CMDLINE = "lpr -P $printer ";
//			$pipe = popen("$CMDLINE" , 'w' );
//			if (!$pipe) {
//				echo "Label printing failed...";
//			}
//			else {
//				fputs($pipe, $label);
//				pclose($pipe);
//				echo "Labels printing at $printer ...";
//			}
//		}

		return $document;
	} // end makeOrderDocument
} // end if exists
