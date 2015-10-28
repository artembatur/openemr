<?php
/** **************************************************************************
 *	LabsAjax.PHP
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
 *  @package labs
 *  @subpackage library
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <info@keyfocusmedia.com>
 * 
 *************************************************************************** */

// SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

// STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once("../../interface/globals.php");
require_once("{$GLOBALS['srcdir']}/classes/Document.class.php");

// Get request type
$type = $_REQUEST['type'];

if ($type == 'icd9') {
	$code = strtoupper($_REQUEST['code']);

	$query = "SELECT formatted_dx_code AS code, short_desc, long_desc FROM icd9_dx_code ";
	$query .= "WHERE formatted_dx_code LIKE '".$code."%' ";
	if (!is_numeric($code)) $query .= "OR short_desc LIKE '%".$code."%' ";
	$query .= "ORDER BY dx_code";
	$result = sqlStatement($query);

	$count = 1;
	$data = array();
	while ($record = sqlFetchArray($result)) {
		$data[$count++] = array('code'=>$record['code'],'short_desc'=>$record['short_desc'],'long_desc'=>$record['long_desc']);		
	}
	
	echo json_encode($data);
}

if ($type == 'lab') {
	$code = strtoupper($_REQUEST['code']);

//	$query = "SELECT DISTINCT test_type AS type, test_cd AS code, test_text AS description, specimen, storage FROM labcorp_codes codes";
	$query = "SELECT test_type AS type, test_cd AS code, test_text AS description, specimen, storage FROM labcorp_codes ";
	$query .= "WHERE active = 'Y' AND (test_cd LIKE '%".$code."%' ";
	if (!is_numeric($code)) $query .= "OR test_text LIKE '%".$code."%'";
//	$query .= ") GROUP BY test_type, test_cd ORDER BY test_cd "; 
	$query .= ") GROUP BY test_cd ORDER BY test_cd "; 
	$result = sqlStatement($query);

	$count = 1;
	$data = array();
	while ($record = sqlFetchArray($result)) {
		$type = ($record['type'] == 'T') ? 'T' : 'P';
		$data[$count++] = array('code'=>$record['code'],'type'=>$type,'description'=>$record['description'],'specimen'=>$record['specimen'],'storage'=>$record['storage']);
	}

	echo json_encode($data);
}

if ($type == 'details') {
	$code = strtoupper($_REQUEST['code']);

	// determine the type of test
	$query = "SELECT DISTINCT test_cd AS code, test_type AS type, test_class AS class, zseg FROM labcorp_codes ";
	$query .= "WHERE active = 'Y' AND test_cd = '".$code."' ";
	$query .= "LIMIT 1 ";
	$result = sqlStatement($query);

	$zseg = 'STD';
	$flag = FALSE;
	if ($record = sqlFetchArray($result)) {
		if ($record['type'] == 'P' || $record['type'] == 'S') $flag = TRUE; // this is a profile
		$zseg = $record['zseg'];
		$type = $record['class'];
	}
	
	
/*
	$profile = array();
	if ($type == 'P') { // profile found
		$query = "SELECT lct.test_cd AS component, lct.test_text AS description FROM labcorp_codes lcr ";
		$query .= "JOIN labcorp_codes lct ON lcr.result_cd = lct.test_cd ";
		$query .= "WHERE lct.active = 'Y' AND lct.test_type = 'T' AND lcr.active = 'Y' AND lcr.test_cd = '".$code."' ";
		$query .= "ORDER BY lct.test_cd";
		$result = sqlStatement($query);

		while ($record = sqlFetchArray($result)) {
			$profile[$record['component']] = array('code'=>$code,'component'=>$record['component'],'description'=>$record['description']);
		}
	}
 */		
	$profile = array();
	if ($flag) { // profile found
		$query = "SELECT result_cd AS component, result_text AS description FROM labcorp_codes ";
		$query .= "WHERE active = 'Y' AND test_cd = '".$code."' AND result_loinc NOT LIKE '%INC' AND result_units != '' ";
		$query .= "ORDER BY result_cd ";
		$result = sqlStatement($query);

		while ($record = sqlFetchArray($result)) {
			$profile[$record['component']] = array('code'=>$code,'component'=>$record['component'],'description'=>$record['description']);
		}
	}

	$data = array('profile'=>$profile,'zseg'=>$zseg,'type'=>$type);
	echo json_encode($data);
}

if ($type == 'overview') {
	$code = strtoupper($_REQUEST['code']);

	$dos = array();
	
	$query = "SELECT * FROM labcorp_dos ";
	$query .= "WHERE test_cd = '".$code."' ";
	$query .= "LIMIT 1 ";
	$data = sqlQuery($query);

	echo "<div style='width:480px;text-align:center;padding:10px;font-weight:bold;font-size:16px;background-color:orange;color:white'>DIRECTORY OF SERVICE INFORMATION</div>\n";
	echo "<div style='overflow-y:auto;overflow-x:hidden;height:350px;width:450p;margin-top:10px'>\n";
	
	if ($data['collection']) {
		echo "<h4 style='margin-bottom:0'>PREFERRED COLLECTION METHOD</h4>\n";
		echo "<div class='wmtOutput' style='padding-right:10px;white-space:pre-wrap'>\n";
		echo "<br/>Specimen Type: <b>".$data['type']."</b>\n";
		echo "<br/><b>".$data['collection']."</b><br/>\n";
		echo "</div>\n";
	}

	if ($data['special']) {
		echo "<h4 style='margin-bottom:0'>SPECIAL INSTRUCTIONS</h4>\n";
		echo "<div class='wmtOutput' style='padding-right:10px'>\n";
		echo "<br/><b>".$data['special']."</b><br/>\n";
		echo "</div>\n";
	}

	if ($data['volume'] || $data['minimum']) {
		echo "<h4 style='margin-bottom:0'>VOLUME REQUIREMENT</h4>\n";
		echo "<div class='wmtOutput' style='padding-right:10px'>\n";
		echo "<br/>Normal: <b>".$data['volume']."</b>\n";
		echo "<br/>Minimum: <b>".$data['minimum']."</b><br/>\n";
		echo "</div>\n";
	}

	if ($data['container']) {
		echo "<h4 style='margin-bottom:0'>TRANSPORT CONTAINER</h4>\n";
		echo "<div class='wmtOutput' style='padding-right:10px'>\n";
		echo "<br/><b>".$data['container']."</b><br/>\n";
		echo "</div>\n";
	}

	if ($data['storage']) {
		echo "<h4 style='margin-bottom:0'>SPECIMEN STORAGE</h4>\n";
		echo "<div class='wmtOutput' style='padding-right:10px'>\n";
		echo "<br/><b>".$data['storage']."</b><br/>\n";
		echo "</div>\n";
	}

	if ($data['method']) {
		echo "<h4 style='margin-bottom:0'>TESTING METHODOLOGY</h4>\n";
		echo "<div class='wmtOutput' style='padding-right:10px'>\n";
		echo "<br/><b>".$data['method']."</b><br/>\n";
		echo "</div>\n";
	}

	if ($data['frequency']) {
		echo "<h4 style='margin-bottom:0'>TESTING FREQUENCY</h4>\n";
		echo "<div class='wmtOutput' style='padding-right:10px'>\n";
		echo "<br/><b>".$data['frequency']."</b><br/>\n";
		echo "</div>\n";
	}

	echo "<br/><br/></div>";
}

if ($type == 'label') {
	require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");

	$address = $_REQUEST['printer'];
	$printer = ($address == 'file')? 'file' : ListLook($address, 'Quest_Label_Printers');
	$order = $_REQUEST['order'];
	$patient = strtoupper($_REQUEST['patient']);
	$client = $_REQUEST['siteid'];
	$pid = $_REQUEST['pid'];
	
	$count = 1;
	if ($_REQUEST['count']) $count = $_REQUEST['count'];
	
	require_once("{$GLOBALS['srcdir']}/tcpdf/config/lang/eng.php");
	require_once("{$GLOBALS['srcdir']}/tcpdf/tcpdf.php");
	
	// create new PDF document
	$pdf = new TCPDF('L', 'pt', array(54,144), true, 'UTF-8', false);
	
	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	
	//set margins
	$pdf->SetMargins(15,5,20);
	$pdf->SetAutoPageBreak(FALSE, 35);
	
	//set some language-dependent strings
	$pdf->setLanguageArray($l);
	
	// define barcode style
	$style = array(
		'position' => '',
		'align' => 'L',
		'stretch' => true,
		'fitwidth' => false,
		'cellfitalign' => '',
		'border' => false,
		'hpadding' => 4,
		'vpadding' => 2,
		'fgcolor' => array(0,0,0),
		'bgcolor' => false, //array(255,255,255),
		'text' => false,
		'font' => 'helvetica',
		'fontsize' => 8,
		'stretchtext' => 4
	);
	
	// ---------------------------------------------------------
	
	do {
		$pdf->AddPage();
	
		$pdf->SetFont('times', '', 7);
		$pdf->Cell(0,5,'Client #: '.$client,0,1);
		$pdf->Cell(0,5,'Order #: '.$order,0,1);
	
		$pdf->SetFont('times', 'B', 8);
		$pdf->Cell(0,0,$patient,0,1,'','','',1);
	
		$pdf->write1DBarcode($client.'-'.$order, 'C39', '', '', 110, 25, '', $style, 'N');
		
		$count--;
		
	} while ($count > 0);

	// ---------------------------------------------------------
	if ($printer == 'file') {
		$repository = $GLOBALS['oer_config']['documents']['repository'];
		$label_file = $repository . preg_replace("/[^A-Za-z0-9]/","_",$pid) . "/" . $order . "_LABEL.pdf";

		$pdf->Output($label_file, 'F'); // force display download
		
		// register the new document
		$d = new Document();
		$d->name = $order."_LABEL.pdf";
		$d->storagemethod = 0; // only hard disk sorage supported
		$d->url = "file://" .$label_file;
		$d->mimetype = "application/pdf";
		$d->size = filesize($label_file);
		$d->owner = 'quest';
		$d->hash = sha1_file( $label_file );
		$d->type = $d->type_array['file_url'];
		$d->set_foreign_id($pid);
		$d->persist();
		$d->populate();
		
		echo $GLOBALS['web_root'].'/controller.php?document&retrieve&patient_id='.$pid.'&document_id='.$d->get_id();
	}
	else {
		$label = $pdf->Output('label.pdf','S'); // return as variable
		$CMDLINE = "lpr -P $printer ";
		$pipe = popen("$CMDLINE" , 'w' );
		if (!$pipe) {
			echo "Label printing failed...";
		}
		else {
			fputs($pipe, $label);
			pclose($pipe);
			echo "Labels printing at $printer ...";
		}
	}
}

if ($type == 'print') {
	require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");

	$address = $_REQUEST['printer'];
	$printer = ($address == 'file')? 'file' : ListLook($address, 'LabCorp_Printers');
	$order = $_REQUEST['order'];
	$patient = strtoupper($_REQUEST['patient']);
	$client = $_REQUEST['siteid'];
	$pid = $_REQUEST['pid'];
	$reqid = $_REQUEST['reqid'];
	
	$d = new Document($reqid);
	$url =  $d->get_url();
	
	// strip url of protocol handler
	$url = preg_replace("|^(.*)://|","",$url);
	
	// first node is filename, second is patient id
	$from_all = explode("/",$url);
	$from_filename = array_pop($from_all);
	$from_patientid = array_pop($from_all);
	
	$url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_patientid . '/' . $from_filename;
	
	if (!file_exists($url)) {
		echo xl('The requested document is not present at the expected location on the filesystem or there are not sufficient permissions to access it.','','',' ') . $url;
		exit;
	}
	
	// read in the file
	$file = fopen($url,"r");
	$filetext = fread( $file, filesize($url) );
	
	$CMDLINE = "lpr -P $address ";
	$pipe = popen("$CMDLINE" , 'w' );
	if (!$pipe) {
		echo "Document printing failed ...";
	}
	else {
		fputs($pipe, $filetext);
		pclose($pipe);
		echo "Document printing at $printer ...";
	}
}

if ($type == 'insurance') {
	$ins1 = $_REQUEST['ins1'];
	$code1 = strtoupper($_REQUEST['code1']);

	if ($ins1 && $code1) {
		$query = "REPLACE INTO list_options SET option_id = '".$ins1."', title = '".$code1."', list_id = 'LabCorp_Insurance' ";
		sqlStatement($query);
	}

	$ins2 = $_REQUEST['ins2'];
	$code2 = strtoupper($_REQUEST['code2']);

	if ($ins2 && $code2) {
		$query = "REPLACE INTO list_options SET option_id = '".$ins2."', title = '".$code2."', list_id = 'LabCorp_Insurance' ";
		sqlStatement($query);
	}
}

?>