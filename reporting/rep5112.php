<?php
/**********************************************************************
    Copyright (C) NGICON (Next Generation icon) ERP.






   All Rights Reserved By www.ngicon.com
***********************************************************************/

$page_security = $_POST['PARAM_0'] == $_POST['PARAM_1'] ?
	'SA_SALESTRANSVIEW' : 'SA_SALESBULKREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Receipts
// ----------------------------------------------------------------
$path_to_root="..";
$rep_id = $_POST['REP_ID'];
$trans_no_info = explode("-",$_POST['PARAM_0']);
$trans_no = $trans_no_info[0];
include_once "../../config_sms.php";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

//----------------------------------------------------------------------------------------------------

print_receipts();

//----------------------------------------------------------------------------------------------------
function get_receipt($type, $trans_no)
{
    $sql = "SELECT trans.*,
				(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax) AS Total,
				trans.ov_discount,
				debtor.name AS DebtorName,
				debtor.debtor_ref,
   				debtor.curr_code,
   				debtor.payment_terms,
   				debtor.tax_id AS tax_id,
   				debtor.address
    			FROM ".TB_PREF."debtor_trans trans,"
    				.TB_PREF."debtors_master debtor
				WHERE trans.debtor_no = debtor.debtor_no
				AND trans.type = ".db_escape($type)."
				AND trans.trans_no = ".db_escape($trans_no);
   	$result = db_query($sql, "The remittance cannot be retrieved");
   	if (db_num_rows($result) == 0)
   		return false;
    return db_fetch($result);
}

function get_creator()
{
	global $trans_no;
	$sql = "SELECT 0_users.real_name,
					0_users.id,
					0_audit_trail.stamp
					FROM `0_audit_trail`
					LEFT JOIN `0_users`
					ON 0_audit_trail.user=0_users.id
					WHERE 0_audit_trail.type=12
					AND 0_audit_trail.trans_no='$trans_no'";

	return db_query($sql, "Cant retrieve invoice range");
}

// function get_due()
// {
// 	$sql = "SELECT value FROM 0_sys_prefs where name = 'receipt_due_status'";
//
// 	return db_query($sql, "Cant retrieve invoice range");
// }

function print_receipts()
{
	global $path_to_root, $systypes_array, $db5, $db, $SysPrefs;

	include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$from = $_POST['PARAM_0'];
	$to = $_POST['PARAM_1'];
	$currency = $_POST['PARAM_2'];
	//$comments = $_POST['PARAM_3'];
	$email = $_POST['PARAM_3'];
	$orientation = $_POST['PARAM_4'];
	$z = 1;

	if (!$from || !$to) return;

	$orientation = ($orientation ? 'L' : 'P');
	$dec = user_price_dec();

 	$fno = explode("-", $from);
	$tno = explode("-", $to);
	$from = min($fno[0], $tno[0]);
	$to = max($fno[0], $tno[0]);

	$cols = array(4, 85, 150, 225, 275, 360, 450, 515);

	// $headers in doctext.inc
	$aligns = array('left',	'left',	'left', 'left', 'right', 'right', 'right');

	$params = array('comments' => $comments);

	$cur = get_company_Pref('curr_default');

	$rep = new FrontReport(_('MONEY RECEIPT'), "ReceiptBulk", user_pagesize(), 9, $orientation);
   	if ($orientation == 'L')
    	recalculate_cols($cols);
	$rep->currency = $cur;
	$rep->Font();
	$rep->Info($params, $cols, null, $aligns);

	for ($i = $from; $i <= $to; $i++)
	{
		if ($fno[0] == $tno[0])
			$types = array($fno[1]);
		else
			$types = array(ST_BANKDEPOSIT, ST_CUSTPAYMENT);
		foreach ($types as $j)
		{
			$myrow = get_receipt($j, $i);
			if (!$myrow)
				continue;
			if ($currency != ALL_TEXT && $myrow['curr_code'] != $currency) {
				continue;
			}
			$res = get_bank_trans($j, $i);
			$baccount = db_fetch($res);
			$params['bankaccount'] = $baccount['bank_act'];

			$contacts = get_branch_contacts($myrow['branch_code'], 'invoice', $myrow['debtor_no']);
			$rep->SetCommonData($myrow, null, $myrow, $baccount, ST_CUSTPAYMENT, $contacts);
 			$rep->SetHeaderType('Header2');
			$rep->NewPage();
			$result = get_allocatable_to_cust_transactions($myrow['debtor_no'], $myrow['trans_no'], $myrow['type']);
			$result_prev_due1 = get_due_to_cust_transactions($myrow['debtor_no']);
			$result_prev_due2 = get_due_to_cust_transactions2($myrow['debtor_no']);
			$result_prev_due3 = get_due_to_cust_transactions3($myrow['debtor_no']);
			$result_prev_due = get_due_to_cust_transactions_new($myrow['debtor_no']);
			$column_d = "receipt_due_status";
			$due_status = getOneColNew('value','0_sys_prefs','name',$column_d);
			$t_due = $result_prev_due["Balance"];
			// $myrow10=db_fetch($result_prev_due)
			// $t_due = $myrow10['due1'];
			while ($myrow10=db_fetch($result_prev_due1))
			{
				$due1 = $myrow10['due1'];
			}
			while ($myrow11=db_fetch($result_prev_due2))
			{
				$due2 = $myrow11['due2'];
			}
			while ($myrow12=db_fetch($result_prev_due3))
			{
				$due3 = $myrow12['due3'];
			}
			// $myrow11=mysqli_fetch_row($result_prev_due2)
			// $t_due = $myrow11['due2'];
			//$t_due = $due1 - ($due2 + $due3);

			$doctype = ST_CUSTPAYMENT;

			$total_allocated = 0;
			$rep->TextCol(0, 4,	_("As advance / full / part / payment towards:"), -2);
			$rep->NewLine(2);
			$this_transaction_due = 0;
			while ($myrow2=db_fetch($result))
			{
				$this_transaction_due = $this_transaction_due + $myrow2['Total'] - $myrow2['alloc'];
				$rep->TextCol(0, 1,	$systypes_array[$myrow2['type']], -2);
				$rep->TextCol(1, 2,	$myrow2['reference'], -2);
				$rep->TextCol(2, 3,	sql2date($myrow2['tran_date']), -2);
				$rep->TextCol(3, 4,	sql2date($myrow2['due_date']), -2);
				$rep->AmountCol(4, 5, $myrow2['Total'], $dec, -2);
				$rep->AmountCol(5, 6, $myrow2['Total'] - $myrow2['alloc'], $dec, -2);
				$rep->AmountCol(6, 7, $myrow2['amt'], $dec, -2);

				$total_allocated += $myrow2['amt'];
				$rep->NewLine(1);
				if ($rep->row < $rep->bottomMargin + (15 * $rep->lineHeight))
					$rep->NewPage();
			}

			$memo = get_comments_string($j, $i);
			if ($memo != "")
			{
				$rep->NewLine();
				$rep->TextColLines(1, 5, $memo, -2);
			}

			$rep->row = $rep->bottomMargin + (18 * $rep->lineHeight);

			$rep->TextCol(3, 6, _("Total Allocated"), -2);
			$rep->AmountCol(6, 7, $total_allocated, $dec, -2);
			if (floatcmp($myrow['ov_discount'], 0))
			{
				$rep->NewLine();
				$rep->TextCol(3, 6, _("Discount"), - 2);
				$rep->AmountCol(6, 7, -$myrow['ov_discount'], $dec, -2);
			}
			$rep->NewLine();
			$rep->Font('bold');
			$rep->TextCol(3, 6, _("TOTAL MONEY RECEIPT"), - 2);
			$rep->AmountCol(6, 7, $myrow['Total'], $dec, -2);
			$rep->NewLine();
			$rep->TextCol(3, 6, _("THIS TRANSACTION DUE"), -2);
			$rep->AmountCol(6, 7, $this_transaction_due, $dec, -2);
			if($due_status == 1){
				$rep->NewLine();
				$rep->Font('bold');
				$rep->TextCol(3, 6, _("PREVIOUS DUE"), - 2);
				$rep->AmountCol(6, 7, $t_due + $myrow['Total'], $dec, -2);
				$rep->NewLine();
				$rep->Font('bold');
				$rep->TextCol(3, 6, _("TOTAL DUE"), - 2);
				$rep->AmountCol(6, 7, $t_due, $dec, -2);
			}



									if ($email == 2)
									{

										$user_id = $_SESSION['wa_current_user']->user;
										$prev_due = $t_due + $myrow['Total'];


										$sql_user="SELECT def_print_orientation FROM `0_users` where id='$user_id'";
										$result_user=$db->query($sql_user);
										$sms_permission_value = mysqli_fetch_assoc($result_user);
										$sms_permission_data = $sms_permission_value['def_print_orientation'];

										$debtor_no = $myrow['debtor_no'];
										$sql_phone="SELECT contact_phone FROM `0_sales_orders` where debtor_no='$debtor_no' order by order_no desc LIMIT 1";
										$result_phone=$db->query($sql_phone);
										$phone_value = mysqli_fetch_assoc($result_phone);

																														// echo '<pre/>';
																														// print_r($phone_value['contact_phone']);
																														// exit();


										$sql_registered_user="SELECT * FROM `user_account` where id='$sms_permission_data'";
										$result_registered_user=mysqli_query($db5,$sql_registered_user);
										$data_registered_user = mysqli_fetch_assoc($result_registered_user);
										$user_exist = mysqli_num_rows($result_registered_user);
										$username = $data_registered_user['masking_user_name'];
										$password = $data_registered_user['masking_user_password'];
										$masking_name = $data_registered_user['masking_name'];
										$bill_no = $rep->formData['document_number'];
										$phone = $SysPrefs->prefs['phone'];

										// echo '<pre/>';
										// print_r($rep->formData);
										// exit();
										$customer_name = $rep->formData["debtor_ref"];
										$sms = "Hi, ".$customer_name.". Thanks for your payment to ".$SysPrefs->prefs['coy_name']." BDT ".$myrow['Total']."Tk for bill no ".$bill_no.". For any query call ".$phone.".";
										if($prev_due){
											$sms = "Hi, ".$customer_name.". Thanks for your payment to ".$SysPrefs->prefs['coy_name']." BDT ".$myrow['Total']."Tk for bill no ".$bill_no.". For any query call ".$phone.". Your previous due ".$prev_due.", total due ".$t_due.".";
										}
										// $username = "ngicon";
										// $password = "Appsit.org@1";
										// $masking_name = "NGICON";

										$character_count = strlen($sms);
									  $encoding = mb_detect_encoding($sms);
									  $multiplication_by = 0;
										if($encoding == "ASCII"){
									  $multiplication_by = (int)($character_count/160);
									  }else {
									    $multiplication_by = (int)($character_count/60);
									  }
									  $multiplication_by += 1;

											$client_mobile = $phone_value['contact_phone'];
											if($client_mobile){
												$used_sms = $data_registered_user['use_sms'];
												$prev_used_sms = $data_registered_user['use_sms'];
												$total_sms = $data_registered_user['sms_packege'];
												$real_count = 1*$multiplication_by;
												$used_sms += $real_count;
												$remaining_sms = $total_sms - $used_sms;
												$curl = curl_init();
												curl_setopt_array($curl, array(

														CURLOPT_URL => "https://api.mobireach.com.bd/SendTextMessage?Username=".$username."&Password=".$password."&From=".$masking_name."&To=88".$client_mobile."&Message=".urlencode($sms )."",


														CURLOPT_RETURNTRANSFER => true,
														CURLOPT_TIMEOUT => 30,
														CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
														CURLOPT_CUSTOMREQUEST => "GET",
														CURLOPT_HTTPHEADER => array(
																"cache-control: no-cache"
														),
												));


											$response = curl_exec($curl);
											$err = curl_error($curl);
											curl_close($curl);
											if($response){
												$sql = "INSERT INTO `send_sms`(`massege`, `mobile_no`, `from_name`, `remaing_sms`, `sms_status`, `failed_sms_status`) VALUES ('$sms','$client_mobile','$sms_permission_data','$prev_used_sms',1,'0')";
												mysqli_query($db5,$sql);

												$sql = "UPDATE `user_account` SET `use_sms`='$used_sms', `remaining_sms`='$remaining_sms' WHERE `id`='$sms_permission_data'";
												mysqli_query($db5,$sql);
												$_SESSION['sms_send_msg'] = "Message Send Successfully";

												exit();
											}
											else{
												$message = "<font color='red' size='+1'>Sorry please try again!</font>";
												//include_once "sms_sending.php";
											}
										}
									}

			$words = price_in_words($myrow['Total'], ST_CUSTPAYMENT);
			if ($words != "")
			{
				$rep->NewLine(1);
				$rep->TextCol(0, 7, $myrow['curr_code'] . ": " . $words, - 2);
			}
			$rep->Font();
			$rep->NewLine();
			$rep->TextCol(6, 7, _("Received / Sign"), - 2);
			$rep->NewLine();
			$rep->TextCol(0, 2, _("By Cash / Cheque* / Draft No."), - 2);
			$rep->TextCol(2, 4, "______________________________", - 2);
			$rep->TextCol(4, 5, _("Dated"), - 2);
			$rep->TextCol(5, 6, "__________________", - 2);
			$rep->NewLine(1);
			$rep->TextCol(0, 2, _("Drawn on Bank"), - 2);
			$rep->TextCol(2, 4, "______________________________", - 2);
			$rep->TextCol(4, 5, _("Branch"), - 2);
			$rep->TextCol(5, 6, "__________________", - 2);
			$rep->TextCol(6, 7, "__________________");
		}
	}
	// if ($email == 2)
	// {
	// 	$rep = new FrontReport("", "", user_pagesize(), 9, $orientation);
	// 	$rep->title = _('MONEY RECEIPT');
	// 	$rep->filename = "MONEY RECEIPT.pdf";
	//
	// }
	$rep->End();
}
