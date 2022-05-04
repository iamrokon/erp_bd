<?php
/**********************************************************************
    Copyright (C) NGICON (Next Generation icon) ERP.






   All Rights Reserved By www.ngicon.com
***********************************************************************/
$page_security = $_POST['PARAM_0'] == $_POST['PARAM_1'] ?
	'SA_SALESTRANSVIEW' : 'SA_SALESBULKREP';

$rep_id = $_POST['REP_ID'];
$trans_no_info = explode("-",$_POST['PARAM_0']);
$trans_no = $trans_no_info[0];
include_once "../../config_sms.php";

// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Print Invoices
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");


//----------------------------------------------------------------------------------------------------
function get_invoice_range($from, $to)
{
	global $SysPrefs;

	$ref = ($SysPrefs->print_invoice_no() == 1 ? "trans_no" : "reference");

	$sql = "SELECT trans.trans_no, trans.reference
		FROM ".TB_PREF."debtor_trans trans
			LEFT JOIN ".TB_PREF."voided voided ON trans.type=voided.type AND trans.trans_no=voided.id
		WHERE trans.type=".ST_SALESINVOICE
			." AND ISNULL(voided.id)"
 			." AND trans.trans_no BETWEEN ".db_escape($from)." AND ".db_escape($to)
		." ORDER BY trans.tran_date, trans.$ref";

	return db_query($sql, "Cant retrieve invoice range");
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
					WHERE 0_audit_trail.type=10
					AND 0_audit_trail.trans_no='$trans_no'";

	return db_query($sql, "Cant retrieve invoice range");
}

// function get_footer()
// {
// 	$sql = "SELECT value FROM 0_sys_prefs where name = 'invoice_footer_info'";
//
// 	return db_query($sql, "Cant retrieve invoice range");
// }

print_invoices();

//----------------------------------------------------------------------------------------------------

function print_invoices()
{
	global $path_to_root, $SysPrefs, $rep_id, $db5, $db;

	$show_this_payment = true; // include payments invoiced here in summary

	include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$totalQTY = 0;
	$from = $_POST['PARAM_0'];
	$to = $_POST['PARAM_1'];
	$currency = $_POST['PARAM_2'];
	$email = $_POST['PARAM_3'];
	$pay_service = $_POST['PARAM_4'];
	$comments = $_POST['PARAM_5'];
	$customer = $_POST['PARAM_6'];
	$orientation = $_POST['PARAM_7'];

	if (!$from || !$to) return;

	$orientation = ($orientation ? 'L' : 'P');
	$dec = user_price_dec();

 	$fno = explode("-", $from);
	$tno = explode("-", $to);
	$from = min($fno[0], $tno[0]);
	$to = max($fno[0], $tno[0]);
	$rangex = get_invoice_range($from, $to);
	$have_discount = 0;
	while($rowx = db_fetch($rangex))
	{
		$resultx = get_customer_trans_details(ST_SALESINVOICE, $rowx['trans_no']);
		while ($myrow2x=db_fetch($resultx))
		{
			if ($myrow2x["discount_percent"]!=0){
				$have_discount = 1;
			}
		}
	}
	//-------------code-Descr-Qty--uom--tax--prc--Disc-Tot--//
	if($have_discount == 0){
		$cols = array(4, 20, 65, 350, 380, 400, 450, 515);
			// $headers in doctext.inc
		$aligns = array('left',	'left',	'center', 'center', 'left', 'center', 'right');

		$bottomSectionPosition1 = 4;
		$bottomSectionPosition2 = 7;
		$bottomSectionPosition3 = 6;
		$bottomSectionPosition4 = 7;
	}else {
		$cols = array(4, 20, 65, 250, 280, 300, 350, 390, 450, 515);
			// $headers in doctext.inc
		$aligns = array('left',	'left',	'center', 'center', 'left', 'center', 'center', 'right', 'right');

		$bottomSectionPosition1 = 5;
		$bottomSectionPosition2 = 9;
		$bottomSectionPosition3 = 8;
		$bottomSectionPosition4 = 9;
	}


	$params = array('comments' => $comments);

	$cur = get_company_Pref('curr_default');

	if ($email == 0)
		$rep = new FrontReport(_('INVOICE'), "InvoiceBulk", user_pagesize(), 9, $orientation);
	if ($orientation == 'L')
		recalculate_cols($cols);

	$range = get_invoice_range($from, $to);
	while($row = db_fetch($range))
	{
			if (!exists_customer_trans(ST_SALESINVOICE, $row['trans_no']))
				continue;
			$sign = 1;
			$myrow = get_customer_trans($row['trans_no'], ST_SALESINVOICE);

			if ($customer && $myrow['debtor_no'] != $customer) {
				continue;
			}
			if ($currency != ALL_TEXT && $myrow['curr_code'] != $currency) {
				continue;
			}
			$baccount = get_default_bank_account($myrow['curr_code']);
			$params['bankaccount'] = $baccount['id'];

			$branch = get_branch($myrow["branch_code"]);
			$sales_order = get_sales_order_header($myrow["order_"], ST_SALESORDER);

//                        echo '<pre/>';
//                        print_r($sales_order);
//                        exit();

			$result_prev_due1 = get_due_to_cust_transactions($myrow['debtor_no']);
			$result_prev_due2 = get_due_to_cust_transactions2($myrow['debtor_no']);
			$result_prev_due3 = get_due_to_cust_transactions3($myrow['debtor_no']);

			$result_prev_due = get_due_to_cust_transactions_new($myrow['debtor_no']);
			$t_due = $result_prev_due["Balance"];
			$column_d = "invoice_due_status";
			$due_status = getOneColNew('value','0_sys_prefs','name',$column_d);
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


			if ($email == 1)
			{
				$rep = new FrontReport("", "", user_pagesize(), 9, $orientation);
				$rep->title = _('INVOICE');
				$rep->filename = "Invoice" . $myrow['reference'] . ".pdf";
			}

			if ($email == 2)
			{
				$rep = new FrontReport("", "", user_pagesize(), 9, $orientation);
				$rep->title = _('INVOICE');
				$rep->filename = "Invoice" . $myrow['reference'] . ".pdf";

			}

			$rep->currency = $cur;
			$rep->Font();
			$rep->Info($params, $cols, null, $aligns);

			$contacts = get_branch_contacts($branch['branch_code'], 'invoice', $branch['debtor_no'], true);
			$baccount['payment_service'] = $pay_service;
			$rep->SetCommonData($myrow, $branch, $sales_order, $baccount, ST_SALESINVOICE, $contacts);



			$rep->SetHeaderType('Header2');
			$rep->NewPage();
			// calculate summary start row for later use
			$summary_start_row = $rep->bottomMargin + (20* $rep->lineHeight);

			if ($rep->formData['prepaid'])
			{
				$result = get_sales_order_invoices($myrow['order_']);
				$prepayments = array();
				while($inv = db_fetch($result))
				{
					$prepayments[] = $inv;
					if ($inv['trans_no'] == $row['trans_no'])
					break;
				}

				if (count($prepayments) > ($show_this_payment ? 0 : 1))
					$summary_start_row += (count($prepayments)) * $rep->lineHeight;
				else
					unset($prepayments);
			}

   			$result = get_customer_trans_details(ST_SALESINVOICE, $row['trans_no']);

			$SubTotal = 0;
			$z=1;
			while ($myrow2=db_fetch($result))
			{
				if ($myrow2["quantity"] == 0)
					continue;

				$Net = round2($sign * ((1 - $myrow2["discount_percent"]) * $myrow2["unit_price"] * $myrow2["quantity"]),
				   user_price_dec());
				$SubTotal += $Net;
	    		$DisplayPrice = number_format2($myrow2["unit_price"],$dec);
	    		$DisplayQty = number_format2($sign*$myrow2["quantity"],get_qty_dec($myrow2['stock_id']));
					$totalQTY += $DisplayQty;
	    		$DisplayNet = number_format2($Net,$dec);
				$afterDiscount = number_format2(($myrow2["unit_price"] - ($myrow2["discount_percent"] * $myrow2["unit_price"])),$dec);
	    		if ($myrow2["discount_percent"]==0)
		  			$DisplayDiscount ="0";
	    		else
		  			$DisplayDiscount = number_format2($myrow2["discount_percent"]*100,user_percent_dec()) . "%";
				$c=0;
				$rep->TextCol($c++, $c,	$z, -2);
				$rep->TextCol($c++, $c,	$myrow2['stock_id'], -2);

				$oldrow = $rep->row;
				$rep->TextColLines($c++, $c, $myrow2['StockDescription'], -2);
				$newrow = $rep->row;
				$rep->row = $oldrow;
				if ($Net != 0.0 || !is_service($myrow2['mb_flag']) || !$SysPrefs->no_zero_lines_amount())
				{
					$rep->TextCol($c++, $c,	$DisplayQty, -2);
					$rep->TextCol($c++, $c,	$myrow2['units'], -2);
					$rep->TextCol($c++, $c,	$DisplayPrice, -2);
					if ($have_discount == 1){
						$rep->TextCol($c++, $c,	$DisplayDiscount, -2);
						$rep->TextCol($c++, $c,	$afterDiscount, -2);
					}
					// $rep->TextCol($c++, $c,	$DisplayDiscount, -2);
					// $rep->TextCol($c++, $c,	$afterDiscount, -2);
					$rep->TextCol($c++, $c,	$DisplayNet, -2);
				}
				$rep->row = $newrow;
				//$rep->NewLine(1);
				if ($rep->row < $summary_start_row)
					$rep->NewPage();
				$z++;
			}


			$sqlquery = "SELECT `ov_amount`,`trans_no` FROM `0_debtor_trans` WHERE `debtor_no`='".$myrow['debtor_no']."' AND `tran_date`='".$myrow['tran_date']."' AND type=12";

			$dbresult = db_query($sqlquery);
			$todaypay = 0;
			$today_trno = "";

			while ($result1 = db_fetch($dbresult)){
				$todaypay += $result1['ov_amount'];
				$today_trno.=$result1['trans_no'].',';
			}

			$today_trno = rtrim($today_trno,',');

			$sqlquery11 = "SELECT `ov_amount`,`trans_no` FROM `0_debtor_trans` WHERE `debtor_no`='".$myrow['debtor_no']."' AND `tran_date`='".$myrow['tran_date']."' AND type=11";

			$dbresult11 = db_query($sqlquery11);
			$todaypay11 = 0;
			$today_trno11 = "";

			while ($result1 = db_fetch($dbresult11)){
				$todaypay11 += $result1['ov_amount'];
				$today_trno11.=$result1['trans_no'].',';
			}

			$today_trno11 = rtrim($today_trno11,',');

			$exploadtrno11 = explode(',',$today_trno11);

            $DisplaySubTot = number_format2($SubTotal,$dec);

            $rep->NewLine();
            $rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("Total QTY"), -2);
			$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4,	$totalQTY, -2);

            $rep->NewLine();
            $rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("Sub-total"), -2);
			$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4,	$DisplaySubTot, -2);
            $rep->NewLine();

            $rep->NewLine();
			$DisplayTotal = number_format2($sign*($myrow["ov_freight"] + $myrow["ov_gst"] +
				$myrow["ov_amount"]+$myrow["ov_freight_tax"]),$dec);
			$rep->Font('bold');
			if (!$myrow['prepaid']) $rep->Font('bold');
				$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, $rep->formData['prepaid'] ? _("TOTAL ORDER VAT INCL.") : _("TOTAL INVOICE"), - 2);
			$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4, $DisplayTotal, -2);
			if ($rep->formData['prepaid'])
			{
				$rep->NewLine();
				$rep->Font('bold');
				$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, $rep->formData['prepaid']=='final' ? _("THIS INVOICE") : _("TOTAL INVOICE"), - 2);
				$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4, number_format2($myrow['prep_amount'], $dec), -2);
			}

            $rep->NewLine();
            $rep->TextCol(0, 0, _(""), - 2);

            $rep->NewLine();
            $rep->TextCol(0, 0, _("RETURN PAYMENTS"), - 2);

            $rep->NewLine();
            $rep->Font();

            $z = 1;
			foreach($exploadtrno11 as $key => $value){

				$sqltr11_details = "SELECT *  FROM `0_debtor_trans_details` WHERE `debtor_trans_no` = '".$value."' AND `debtor_trans_type` = 11";
				$rtproductdt 	 = db_query($sqltr11_details);//Return product details

				while ($myrow3=db_fetch($rtproductdt))
				{
					if ($myrow3["quantity"] == 0)
						continue;

					$Net = round2($sign * ((1 - $myrow3["discount_percent"]) * (-$myrow3["unit_price"]) * $myrow3["quantity"]),
					   user_price_dec());
					// $SubTotal += $Net;
					$DisplayPrice = number_format($myrow3["unit_price"],2,'.','');
					//$DisplayPrice = 0;
					$DisplayQty = number_format2($sign*$myrow3["quantity"],get_qty_dec($myrow3['stock_id']));
					$DisplayNet = number_format2($Net,$dec);
					$afterDiscount = number_format2(($myrow3["unit_price"] - ($myrow3["discount_percent"] * $myrow3["unit_price"])),$dec);
					if ($myrow3["discount_percent"]==0)
						$DisplayDiscount ="0";
					else
						$DisplayDiscount = number_format2($myrow3["discount_percent"]*100,user_percent_dec()) . "%";
					$c=0;
					$rep->TextCol($c++, $c,	$z, -2);
					$rep->TextCol($c++, $c,	$myrow3['stock_id'], -2);

					$oldrow = $rep->row;
					$rep->TextColLines($c++, $c, $myrow3['description'], -2);
					$newrow = $rep->row;
					$rep->row = $oldrow;

                    if ($Net != 0.0 || !$SysPrefs->no_zero_lines_amount())
					{
						$rep->TextCol($c++, $c,	$DisplayQty, -2);
						$rep->TextCol($c++, $c,	$myrow3['units'], -2);
						$rep->TextCol($c++, $c,	-$DisplayPrice, -2);
						if ($have_discount == 1){
							$rep->TextCol($c++, $c,	$DisplayDiscount, -2);
							$rep->TextCol($c++, $c,	$afterDiscount, -2);
						}
						// $rep->TextCol($c++, $c,	$DisplayDiscount, -2);
						// $rep->TextCol($c++, $c,	$afterDiscount, -2);
						$rep->TextCol($c++, $c,	$DisplayNet, -2);
					}

					$rep->row = $newrow;
					//$rep->NewLine(1);
					if ($rep->row < $summary_start_row)
						$rep->NewPage();
					$z++;
				}

			}




			$memo = get_comments_string(ST_SALESINVOICE, $row['trans_no']);
			if ($memo != "")
			{
				$rep->NewLine();
				$rep->TextColLines(1, 3, $memo, -2);
			}

			// set to start of summary line:
    		$rep->row = $summary_start_row;
			if (isset($prepayments))
			{
				// Partial invoices table
				$rep->TextCol(0, 3,_("Prepayments invoiced to this order up to day:"));
				$rep->TextCol(0, 3,	str_pad('', 150, '_'));
				$rep->cols[2] -= 20;
				$rep->aligns[2] = 'right';
				$rep->NewLine(); $c = 0; $tot_pym=0;
				$rep->TextCol(0, 3,	str_pad('', 150, '_'));
				$rep->TextCol($c++, $c, _("Date"));
				$rep->TextCol($c++, $c,	_("Invoice reference"));
				$rep->TextCol($c++, $c,	_("Amount"));

				foreach ($prepayments as $invoice)
				{
					if ($show_this_payment || ($invoice['reference'] != $myrow['reference']))
					{
						$rep->NewLine();
						$c = 0; $tot_pym += $invoice['prep_amount'];
						$rep->TextCol($c++, $c,	sql2date($invoice['tran_date']));
						$rep->TextCol($c++, $c,	$invoice['reference']);
						$rep->TextCol($c++, $c, number_format2($invoice['prep_amount'], $dec));
					}
					if ($invoice['reference']==$myrow['reference']) break;
				}
				$rep->TextCol(0, 3,	str_pad('', 150, '_'));
				$rep->NewLine();
				$rep->TextCol(1, 2,	_("Total payments:"));
				$rep->TextCol(2, 3,	number_format2($tot_pym, $dec));
			}


			$doctype = ST_SALESINVOICE;
    		$rep->row = $summary_start_row;
			$rep->cols[2] += 20;
			$rep->cols[3] += 20;
			$rep->aligns[3] = 'left';

			// $rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("Sub-total"), -2);
			// $rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4,	$DisplaySubTot, -2);
			// $rep->NewLine();

			if ($myrow['ov_freight'] != 0.0)
			{
   				$DisplayFreight = number_format2($sign*$myrow["ov_freight"],$dec);
				$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("Shipping"), -2);
				$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4,	$DisplayFreight, -2);
				$rep->NewLine();
			}
			$tax_items = get_trans_tax_details(ST_SALESINVOICE, $row['trans_no']);
			$first = true;
    		while ($tax_item = db_fetch($tax_items))
    		{
    			if ($tax_item['amount'] == 0)
    				continue;
    			$DisplayTax = number_format2($sign*$tax_item['amount'], $dec);

    			if ($SysPrefs->suppress_tax_rates() == 1)
    				$tax_type_name = $tax_item['tax_type_name'];
    			else
    				$tax_type_name = $tax_item['tax_type_name']." (".$tax_item['rate']."%) ";

    			if ($myrow['tax_included'])
    			{
    				if ($SysPrefs->alternative_tax_include_on_docs() == 1)
    				{
    					if ($first)
    					{
							$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("Total Tax Excluded"), -2);
							$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4,	number_format2($sign*$tax_item['net_amount'], $dec), -2);
							$rep->NewLine();
    					}
						$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, $tax_type_name, -2);
						$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4,	$DisplayTax, -2);
						$first = false;
    				}
    				else
						$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("Included") . " " . $tax_type_name . _("Amount") . ": " . $DisplayTax, -2);
				}
    			else
    			{
					$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, $tax_type_name, -2);
					$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4,	$DisplayTax, -2);
				}
				$rep->NewLine();
    		}





				$rep->NewLine();
				$rep->Font('bold');
	//			$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("TR_NO"), - 2);
	//			$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4, $today_trno, -2);
				//$rep->NewLine();

				$rep->NewLine();
				$rep->Font('bold');
				$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("TODAY Return"), - 2);
				$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4, -$todaypay11, -2);

				$newDisplayTotal = str_replace(',', '', $DisplayTotal);
				$newDisplayTotal = (int)$newDisplayTotal;
				$thisTxDues	 	 = $newDisplayTotal-$todaypay11;

				$rep->NewLine();
				$rep->Font('bold');
				$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("This Transaction Due"), - 2);
				$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4, $thisTxDues, -2);

				$rep->NewLine();
				$rep->Font('bold');
	//			$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("TR_NO"), - 2);
	//			$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4, $today_trno11, -2);
				//$rep->NewLine();


			$prev_due = number_format2($t_due - $sign*($myrow["ov_freight"] + $myrow["ov_gst"] +
			$myrow["ov_amount"]+$myrow["ov_freight_tax"]),$dec);
			if($due_status == 1){

				$totalBalance = $todaypay+$t_due;

				$rep->NewLine();
				$rep->Font('bold');
				$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("PREVIOUS DUE"), - 2);
				$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4, $totalBalance-$thisTxDues, -2);


				$rep->NewLine();
				$rep->Font('bold');
				$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("Total Balance"), - 2);
				$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4, $totalBalance, -2);

				$rep->NewLine();
				$rep->Font('bold');
				$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("TODAY PAYMENTS"), - 2);
				$rep->TextCol($bottomSectionPosition3, $bottomSectionPosition4, $todaypay, -2);

				$rep->NewLine();
				$rep->Font('bold');
				$rep->TextCol($bottomSectionPosition1, $bottomSectionPosition2, _("TOTAL DUE"), - 2);
				$rep->AmountCol($bottomSectionPosition3, $bottomSectionPosition4, $t_due, $dec, -2);
			}


						if ($email == 2)
						{
							$user_id = $_SESSION['wa_current_user']->user;

							$sql_user="SELECT def_print_orientation FROM `0_users` where id='$user_id'";
							$result_user=$db->query($sql_user);
							$sms_permission_value = mysqli_fetch_assoc($result_user);
							$sms_permission_data = $sms_permission_value['def_print_orientation'];

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
							// print_r($user_id);
							// exit();
							$customer_name = $rep->formData["customer_ref"];
							$sms = "Hi, ".$customer_name.". Thanks for your purchase from ".$SysPrefs->prefs['coy_name']." BDT ".$DisplayTotal."Tk for bill no ".$bill_no.". For any query call ".$phone.".";
							if($prev_due){
								$sms = "Hi, ".$customer_name.". Thanks for your purchase from ".$SysPrefs->prefs['coy_name']." BDT ".$DisplayTotal."Tk for bill no ".$bill_no.". For any query call ".$phone.". Your previous due ".$prev_due.", total due ".$t_due.".";
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

								$client_mobile = $rep->formData["contact_phone"];
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
										$message = "<font color='green' size='+1'>Message Send Successfully!</font>";
										include_once "sms_sending.php";
									}
									else{
										$message = "<font color='red' size='+1'>Sorry please try again!</font>";
										//include_once "sms_sending.php";
									}
								}
						}

			$words = price_in_words($rep->formData['prepaid'] ? $myrow['prep_amount'] : $myrow['Total']
				, array( 'type' => ST_SALESINVOICE, 'currency' => $myrow['curr_code']));
			if ($words != "")
			{
				$rep->NewLine(1);
				$rep->TextCol(1, 7, $myrow['curr_code'] . ": " . $words, - 2);
			}
			$rep->Font();
			if ($email == 1)
			{
				$rep->End($email);
			}
	}
	if ($email == 0)
		$rep->End();
}
