<?php 
/** **************************************************************************
 *	MYREPORTS/LAB_ORDERS.PHP
 *
 *	Copyright (c)2013 - Williams Medical Technology, Inc.
 *
 *	This program is free software: you can redistribute it and/or modify it 
 *	under the terms of the GNU General Public License as published by the Free 
 *	Software Foundation, either version 3 of the License, or (at your option) 
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 *	FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for 
 *	more details.
 *
 *	You should have received a copy of the GNU General Public License along with 
 *	this program.  If not, see <http://www.gnu.org/licenses/>.	This program is 
 *	free software; you can redistribute it and/or modify it under the terms of 
 *	the GNU Library General Public License as published by the Free Software 
 *	Foundation; either version 2 of the License, or (at your option) any 
 *	later version.
 *
 *  @package laboratory
 *  @subpackage myreports
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <info@keyfocusmedia.com>
 *  @uses quest_order/common.php
 * 
 *************************************************************************** */
require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/wmt/wmt.include.php";

// report defaults
$report_title = 'Lab Order Report';
$result_name = 'labcorp_result';
$item_name = 'labcorp_result_item';
$lab_name = 'labcorp_result_lab';
$order_name = 'labcorp_order';

// which interfaces are active?
$quest = ($GLOBALS['lab_quest_enable'])? TRUE : FALSE;
$labcorp = ($GLOBALS['lab_corp_enable'])? TRUE : FALSE;

// For each sorting option, specify the ORDER BY argument.
$ORDERHASH = array(
	'doctor'  => 'lower(u.lname), lower(u.fname), lab.order_datetime',
	'patient' => 'lower(lab.pat_last), lower(lab.pat_first), lab.order_datetime',
	'pubpid'  => 'lower(lab.pubpid), lab.order_datetime',
	'time'    => 'lab.order_datetime, lower(u.lname), lower(u.fname)',
);

// get date range
$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
if ($_POST['form_from_date'] || $_POST['form_to_date']) {
	$form_from_date = ($_POST['form_from_date']) ? formData('form_from_date') : date('Y-m-d', $last_month);
	$form_to_date = ($_POST['form_to_date']) ? formData('form_to_date') : date('Y-m-d');
}
else {
	$form_from_date = '';
	$form_to_date = '';
}

// get remaining report parameters
$form_provider  = formData('form_provider');
$form_facility  = formData('form_facility');
$form_status  	= formData('form_status');
$form_name      = formData('form_name');
$form_lab		= formData('form_lab');
$form_special 	= formData('form_special');
$form_details   = "1";

// get sort order
$form_orderby 	= $ORDERHASH[formData('form_orderby')] ? formData('form_orderby') : 'doctor';
$orderby 		= $ORDERHASH[$form_orderby];

// retrieve records
$orders = array();
$results = FALSE;
if ($_POST['form_refresh'] || $_POST['form_orderby']) {
	$query = "SELECT f.*, fe.encounter, fe.date AS enc_date, fe.facility_id, fe.reason, u.fname, u.mname, u.lname, ";
	$query .= "lab.pid, lab.pubpid, lab.status, lab.pat_last, lab.pat_first, lab.pat_middle, lab.order_number, lab.order_datetime, lab.request_processed ";
	$query .= "FROM forms f, form_encounter fe, ";
	$subs = array();
	if ($quest && (!$form_lab || $form_lab == 'q')) {
		$sub = "SELECT id, pid, pubpid, status, pat_last, pat_first, pat_middle, order_number, order_datetime, request_processed, request_provider FROM form_quest_order ";
		$sub .= "WHERE activity = 1 ";
		if ($form_from_date) $sub .= "AND order_datetime >= '$form_from_date 00:00:00' AND order_datetime <= '$form_to_date 23:59:59' ";
		if ($form_provider) $sub .= "AND request_provider = '$form_provider' ";
		$subs[] = $sub;
	}
	if ($labcorp && (!$form_lab || $form_lab == 'l')) { 
		$sub = "SELECT id, pid, pubpid, status, pat_last, pat_first, pat_middle, order_number, order_datetime, request_processed, request_provider FROM form_labcorp_order ";
		$sub .= "WHERE activity = 1 ";
		if ($form_from_date) $sub .= "AND order_datetime >= '$form_from_date 00:00:00' AND order_datetime <= '$form_to_date 23:59:59' ";
		if ($form_provider) $sub .= "AND request_provider = '$form_provider' ";
		$subs[] = $sub;
	}
	$subquery = '';
	foreach ($subs AS $sub) {
		if ($subquery) $subquery .= " UNION ";			
		$subquery .= $sub;
	}
	$query .= "( ".$subquery." ) lab ";
	$query .= "LEFT JOIN users u ON u.id = lab.request_provider ";
	$query .= "WHERE f.deleted != '1' AND f.formdir LIKE '%_order' AND f.encounter = fe.encounter ";
	$query .= "AND f.form_id = lab.id ";
	if ($form_facility) $query .= "AND fe.facility_id = '$form_facility' ";
	$query .= "ORDER BY $orderby";

//	echo $query."<br />\n";
	$results = sqlStatement($query);
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php html_header_show();?>
		<title><?php echo $result_title; ?></title>

		<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
		<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/interface/forms/labcorp_order/style_wmt.css" media="screen" />
		
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.7.2.min.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui-1.10.0.custom.min.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.pack.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/overlib_mini.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
		<!-- script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmtstandard.js"></script -->
		
		<!-- pop up calendar -->
		<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
		<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>

		<style type="text/css">
			/* specifically include & exclude from printing */
			@media print {
			    #report_parameters {
    			    visibility: hidden;
    			    display: none;
    			}
    			#report_parameters_daterange {
        			visibility: visible;
					display: inline;
				}
				#report_results table {
					margin-top: 0px;
				}
			}

			/* specifically exclude some from the screen */
			@media screen {
				#report_parameters_daterange {
					visibility: hidden;
					display: none;
				}
			}
		</style>

		<script>

			var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

			function dosort(orderby) {
				var f = document.forms[0];
				f.form_orderby.value = orderby;
				f.submit();
				return false;
			}

			function refreshme() {
				document.forms[0].submit();
			}

		</script>
	</head>
	
	
	<body class="body_top">
		<!-- Required for the popup date selectors -->
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

		<span class='title'><?php xl('Report','e'); ?> - <?php xl('Lab Orders','e'); ?></span>

		<div id="report_parameters_daterange">
			<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
		</div>

		<form method='post' name='theform' id='theform' action='lab_orders.php'>
			<div id="report_parameters">
				<table>
					<tr>
						<td width='900px'>
							<div style='float:left'>
								<table class='text'>
									<tr>
										<td class='label'><?php xl('Facility','e'); ?>: </td>
										<td>
											<?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', false, true); ?></td>
										<td class='label'><?php xl('Provider','e'); ?>: </td>
										<td>
<?php
	// Build a drop-down list of providers.
	$query = "SELECT id, username, lname, fname FROM users WHERE authorized = 1 $provider_facility_filter ORDER BY lname, fname";
	$ures = sqlStatement($query);

	echo "   <select name='form_provider'>\n";
	echo "    <option value=''>-- " . xl('All') . " --\n";

	while ($urow = sqlFetchArray($ures)) {
		$provid = $urow['id'];
		echo "    <option value='$provid'";
		if ($provid == $_POST['form_provider']) echo " selected";
		echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
	}
	
	echo "   </select>\n";
?>
										</td>
           								<td class='label'><?php xl('Laboratory','e'); ?>: </td>
          								<td>
<?php
	// Build a drop-down list of lab names.
	$query = "SELECT option_id, title FROM list_options WHERE list_id = 'Lab_Form_Labs' ORDER BY seq";
	$ures = sqlStatement($query);

	echo "   <select name='form_lab'>\n";
	echo "    <option value=''>-- " . xl('All') . " --\n";

	while ($urow = sqlFetchArray($ures)) {
		$labid = $urow['option_id'];
		echo "    <option value='$labid'";
		if ($labid == $_POST['form_lab']) echo " selected";
		echo ">" . $urow['title'] . "\n";
	}

	echo "   </select>\n";
  ?>
  										</td>
									</tr>
								</table>

								<table class='text'>
									<tr>
										<td class='label'><?php xl('From','e'); ?>: </td>
										<td>
											<input type='text' name='form_from_date' id="form_from_date" size='10' 
												value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' 
												onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
											<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' 
												id='img_from_date' border='0' alt='[?]' style='cursor:pointer' 
												title='<?php xl('Click here to choose a date','e'); ?>'>
										</td>
										<td class='label'><?php xl('To','e'); ?>: </td>
										<td>
											<input type='text' name='form_to_date' id="form_to_date" size='10' 
												value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' 
												onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
											<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' 
												id='img_to_date' border='0' alt='[?]' style='cursor:pointer' 
												title='<?php xl('Click here to choose a date','e'); ?>'>
										</td>
										<td class='label'><?php xl('Form Status','e'); ?>: </td>
										<td>
<?php
	// Build a drop-down list of form statuses.
	$query = "SELECT option_id, title FROM list_options WHERE list_id = 'Lab_Form_Status' AND title LIKE 'Order_%' ORDER BY seq";
	$ures = sqlStatement($query);

	echo "   <select name='form_status'>\n";
	echo "    <option value=''>-- " . xl('All') . " --\n";

	while ($urow = sqlFetchArray($ures)) {
		$statid = $urow['option_id'];
		echo "    <option value='$statid'";
		if ($statid == $_POST['form_status']) echo " selected";
		echo ">" . $urow['title'] . "\n";
	}
              
	echo "   </select>\n";
?>
										</td>
              
										<td class='label'><?php xl('Special Handling','e'); ?>: </td>
										<td>
<?php
	// Build a drop-down list of form statuses.
 	$query = "SELECT option_id, title FROM list_options WHERE list_id = 'LabCorp_Handling' ORDER BY seq";
	$ures = sqlStatement($query);

	echo "   <select name='form_special'>\n";
	echo "    <option value=''>-- " . xl('All') . " --\n";

	while ($urow = sqlFetchArray($ures)) {
		$statid = $urow['option_id'];
		echo "    <option value='$statid'";
		if ($statid == $_POST['form_special']) echo " selected";
		echo ">" . $urow['title'] . "\n";
	}
              
	echo "   </select>\n";
?>
										</td>
									</tr>
								</table>
							</div>
						</td>
						<td align='left' valign='middle' height="100%">
							<table style='border-left:1px solid; width:100%; height:100%' >
								<tr>
									<td>
										<div style='margin-left:15px'>
											<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
												<span><?php xl('Submit','e'); ?></span>
											</a>

<?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
            								<a href='#' class='css_button' onclick='window.print()'>
												<span><?php xl('Print','e'); ?></span>
											</a>
<?php } ?>
										</div>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>

			</div> <!-- end report_parameters -->

<?php if ($_POST['form_refresh'] || $_POST['form_orderby']) { ?>

			<div id="report_results">
				<table>
					<thead>
<?php if ($form_details) { ?>
						<th>
							<a href="nojs.php" onclick="return dosort('doctor')"
								<?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php  xl('Provider','e'); ?> 
							</a>
						</th>
						<th>
							<a href="nojs.php" onclick="return dosort('time')"
								<?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  xl('Date','e'); ?>
							</a>
  						</th>
						<th>
							<a href="nojs.php" onclick="return dosort('patient')"
								<?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  xl('Patient','e'); ?>
							</a>
						</th>
						<th>
							<a href="nojs.php" onclick="return dosort('pubpid')"
								<?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php  xl('ID','e'); ?>
							</a>
						</th>
						<th>
							<?php  xl('Status','e'); ?>
						</th>
						<th>
							<?php  xl('Encounter','e'); ?>
						</th>
						<th>
							<?php  xl('Form','e'); ?>
						</th>
<?php } else { ?>
						<th><?php  xl('Provider','e'); ?></td>
						<th><?php  xl('Encounters','e'); ?></td>
<?php } ?>
					</thead>
					<tbody>
<?php
	if (mysql_num_rows($results) < 1) {
?>
						<tr>
							<td colspan="7" style="font-weight:bold;text-align:center;padding:25px">
								NO ORDERS FOUND
							</td>
						</tr>
<?php 
	} 
	else {
		$lastdocname = "";
		$doc_encounters = 0;
		while ($row = sqlFetchArray($results)) {
			$docname = '';
			if (!empty($row['lname']) || !empty($row['fname'])) {
				$docname = $row['lname'];
				if (!empty($row['fname']) || !empty($row['mname']))
					$docname .= ', ' . $row['fname'] . ' ' . $row['mname'];
    		}

		    $errmsg  = "";
   			$fstatus = $row['status'];
   			if ($fstatus == 'u' || $fstatus == 'h') continue; // orphan results
	    	if ($form_status == 'f') { // abnormal results
    			if ($fstatus != 'x' && $fstatus != 'z') continue; // only keep results
    			if ($row['result_abnormal'] == 0) continue; // only keep abnormal results 
    		}
    		else {
	    		if ($form_status && $form_status != $fstatus) continue;
    		}
    		if ($form_special > 0) {
    			if ($row['request_handling'] != $form_special) continue;
	    	}
     
			if ($form_lab) {
				if (($row['formdir'] == 'quest_result' || $row['formdir'] == 'quest_order') && $form_lab != 'q') continue;
		    	if (($row['formdir'] == 'labcorp_result' || $row['formdir'] == 'labcorp_order') && $form_lab != 'l') continue;
		    }
    
		    $status = ListLook($fstatus, 'Lab_Form_Status');
    		if ($status == 'Error' || $status == '') { $status = 'Unassigned'; }

    		if ($row['formdir'] == 'quest_order')
    			$link_ref="$rootdir/forms/quest_order/update.php?id=".$row['form_id']."&pid=".$row['pid']."&enc=".$row['encounter'];
    		if ($row['formdir'] == 'quest_result')
    			$link_ref="$rootdir/forms/quest_result/update.php?id=".$row['form_id']."&pid=".$row['pid']."&enc=".$row['encounter'];
    		if ($row['formdir'] == 'labcorp_order')
    			$link_ref="$rootdir/forms/labcorp_order/update.php?id=".$row['form_id']."&pid=".$row['pid']."&enc=".$row['encounter'];
    		if ($row['formdir'] == 'labcorp_result')
    			$link_ref="$rootdir/forms/labcorp_result/update.php?id=".$row['form_id']."&pid=".$row['pid']."&enc=".$row['encounter'];
?>
						<tr bgcolor='<?php echo $bgcolor ?>'>
							<td class="nowrap">
								<?php echo $docname; ?>&nbsp;
							</td>
							<td>
								<?php echo oeFormatShortDate(substr($row['date'], 0, 10)) ?>&nbsp;
							</td>
							<td>
								<?php echo $row['pat_last'] . ', ' . $row['pat_first'] . ' ' . $row['pat_middle']; ?>&nbsp;
							</td>
							<td>
								<?php echo $row['pubpid']; ?>&nbsp;
							</td>
							<td>
								<?php echo $status; ?>&nbsp;
							</td>
							<td>
								<?php echo ($row['reason'])? $row['reason'] : "REASON FOR ENCOUNTER NOT PROVIDED"; ?>&nbsp;
							</td>
							<td style="min-width:130px">
								<a href="<?php echo $link_ref; ?>" target="_blank" class="link_submit" 
									onclick="top.restoreSession()"><?php echo $row['form_name']; ?></a>&nbsp;
							</td>
						</tr>
<?php
			$lastdocname = $docname;
		}
	}
?>
					</tbody>
				</table>
			</div>  <!-- end encresults -->
<?php 
	} 
	else { 
?>
			<div class='text'>
				<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
			</div>
<?php 
	} 
?>

			<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
			<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

		</form>
	</body>

	<script language='JavaScript'>
		Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
		Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
		<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>
	</script>

</html>
