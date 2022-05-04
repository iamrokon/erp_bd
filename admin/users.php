<?php
/**********************************************************************
    Copyright (C) www.ngicon.com
	 
	 
	
    
    
      
     
***********************************************************************/
$page_security = 'SA_USERS';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");

page(_($help_context = "Users"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/admin/db/users_db.inc");

simple_page_mode(true);
//-------------------------------------------------------------------------------------------------

$sql_check_coulmn = "select user_location from 0_users";
$result_check_coulmn = $db->query($sql_check_coulmn);
if(!$result_check_coulmn){
	$db->query("ALTER TABLE `0_users` ADD `user_location` VARCHAR(50) NOT NULL AFTER `bank_accounts_number`");
}
function can_process($new) 
{

	if (strlen($_POST['user_id']) < 4)
	{
		display_error( _("The user login entered must be at least 4 characters long."));
		set_focus('user_id');
		return false;
	}

	if (!$new && ($_POST['password'] != ""))
	{
    	if (strlen($_POST['password']) < 4)
    	{
    		display_error( _("The password entered must be at least 4 characters long."));
			set_focus('password');
    		return false;
    	}

    	if (strstr($_POST['password'], $_POST['user_id']) != false)
    	{
    		display_error( _("The password cannot contain the user login."));
			set_focus('password');
    		return false;
    	}
	}

	return true;
}

//-------------------------------------------------------------------------------------------------

if (($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') && check_csrf_token())
{
	if (can_process($Mode == 'ADD_ITEM'))
	{
		
		if($_POST['sales_type']==""){
			display_error(_("Sales Type Must Be Selectd!"));
		}
		else{
		    $checkClmn = "SHOW COLUMNS FROM 0_users LIKE 'sales_type';";
            $excCheckClmn = $db->query($checkClmn);
            $numRows = mysqli_num_rows($excCheckClmn);
             if (empty($numRows)) {
                 $createClmn="ALTER TABLE `0_users` ADD `sales_type` VARCHAR(10) NULL DEFAULT NULL AFTER `user_location`;";
                 $excCreateClmn=$db->query($createClmn);
             }
             
             $updateUserTbl="UPDATE 0_users SET sales_type='".$_POST['sales_type']."' WHERE user_id='".$_POST['user_id']."'";
             $db->query($updateUserTbl);
            
		}
		
		
		
		if($_POST['user_location']==""){
			display_error(_("User Location Must Be Selectd!"));
		}
		else{
			if ($selected_id != -1) 
			{
				$sql_bank_account = "SELECT * FROM `0_bank_accounts`";
				$result_bank_account = $db->query($sql_bank_account);
				$bank_account_number = "";
				while($data_bank_account = mysqli_fetch_array($result_bank_account)){
					if(isset($_POST["$data_bank_account[3]"]) && $_POST["$data_bank_account[3]"]==1){
						if($bank_account_number==""){
							$bank_account_number = $data_bank_account[8];
						}
						else{
							$bank_account_number = $bank_account_number.",".$data_bank_account[8];
						}
					}
				}
				update_user_prefs($selected_id,
					get_post(array('user_id', 'real_name','phone', 'email', 'role_id', 'language',
						'print_profile', 'rep_popup' => 0,'pos','def_print_orientation','bank_accounts_number'=>$bank_account_number,'user_location')));

				if ($_POST['password'] != "")
					update_user_password($selected_id, $_POST['user_id'], md5($_POST['password']));

				display_notification_centered(_("The selected user has been updated."));
			} 
			else 
			{
					$sql_bank_account = "SELECT * FROM `0_bank_accounts`";
					$result_bank_account = $db->query($sql_bank_account);
					$bank_account_number = "";
					while($data_bank_account = mysqli_fetch_array($result_bank_account)){
						if(isset($_POST["$data_bank_account[3]"])){
							if($bank_account_number==""){
								$bank_account_number = $_POST["$data_bank_account[3]"];
							}
							else{
								$bank_account_number = $bank_account_number.",".$_POST["$data_bank_account[3]"];
							}
						}
					}
					add_user($_POST['user_id'], $_POST['real_name'], md5($_POST['password']),
						$_POST['phone'], $_POST['email'], $_POST['role_id'], $_POST['language'],
						$_POST['print_profile'], check_value('rep_popup'), $_POST['pos'],$_POST['def_print_orientation'],$bank_account_number,$_POST['user_location']);
					$id = db_insert_id();
					// use current user display preferences as start point for new user
					$prefs = $_SESSION['wa_current_user']->prefs->get_all();
					
					update_user_prefs($id, array_merge($prefs, get_post(array('print_profile',
						'rep_popup' => 0, 'language'))));

					display_notification_centered(_("A new user has been added."));
			}
		$Mode = 'RESET';
		}
	}
}

//-------------------------------------------------------------------------------------------------

if ($Mode == 'Delete' && check_csrf_token())
{
	$cancel_delete = 0;
    if (key_in_foreign_table($selected_id, 'audit_trail', 'user'))
    {
        $cancel_delete = 1;
        display_error(_("Cannot delete this user because entries are associated with this user."));
    }
    if ($cancel_delete == 0) 
    {
    	delete_user($selected_id);
    	display_notification_centered(_("User has been deleted."));
    } //end if Delete group
    $Mode = 'RESET';
}

//-------------------------------------------------------------------------------------------------
if ($Mode == 'RESET')
{
 	$selected_id = -1;
	$sav = get_post('show_inactive', null);
	unset($_POST);	// clean all input fields
	$_POST['show_inactive'] = $sav;
}

$result = get_users(check_value('show_inactive'));
start_form();
start_table(TABLESTYLE);

$th = array(_("User login"), _("Full Name"),_("Sms Name"),_("Bank Acc.Id"),_("Location"),_("Sales Type"), _("Phone"),_("Address"),
	_("E-mail"), _("Last Visit"), _("Access Level"), "", "");

inactive_control_column($th);
table_header($th);	

$k = 0; //row colour counter

while ($myrow = db_fetch($result)) 
{
    
    $salesTypeId= $myrow["sales_type"];
    if($salesTypeId=='all'){
       $salesTypeId='ALL';
    }
    else{
    $getsalesTypeName= "SELECT * FROM  0_sales_types WHERE id='$salesTypeId'";
    $getsalesTypeName = $db->query($getsalesTypeName);
    $saleTypeName=mysqli_fetch_array($getsalesTypeName);
    
    $salesTypeId=$saleTypeName['sales_type'];
}
	alt_table_row_color($k);

	$last_visit_date = sql2date($myrow["last_visit_date"]);

	/*The security_headings array is defined in config.php */
	$not_me = strcasecmp($myrow["user_id"], $_SESSION["wa_current_user"]->username);

	label_cell($myrow["user_id"]);
	label_cell($myrow["real_name"]);
	label_cell($myrow["def_print_orientation"]);
	label_cell($myrow["bank_accounts_number"]);
	label_cell($myrow["user_location"]);
	label_cell($salesTypeId);
	label_cell($myrow["phone"]);
	label_cell($myrow["print_profile"]);
	email_cell($myrow["email"]);
	label_cell($last_visit_date, "nowrap");
	label_cell($myrow["role"]);
	
    if ($not_me)
		inactive_control_cell($myrow["id"], $myrow["inactive"], 'users', 'id');
	elseif (check_value('show_inactive'))
		label_cell('');

	edit_button_cell("Edit".$myrow["id"], _("Edit"));
    if ($not_me)
 		delete_button_cell("Delete".$myrow["id"], _("Delete"));
	else
		label_cell('');
	end_row();

} //END WHILE LIST LOOP

inactive_control_row($th);
end_table(1);
//-------------------------------------------------------------------------------------------------
start_table(TABLESTYLE2);

$_POST['email'] = "";
if ($selected_id != -1) 
{
  	if ($Mode == 'Edit') {
		//editing an existing User
		$myrow = get_user($selected_id);

		$_POST['id'] = $myrow["id"];
		$_POST['user_id'] = $myrow["user_id"];
		$_POST['real_name'] = $myrow["real_name"];
		$_POST['phone'] = $myrow["phone"];
		$_POST['email'] = $myrow["email"];
		$_POST['role_id'] = $myrow["role_id"];
		$_POST['language'] = $myrow["language"];
		$_POST['print_profile'] = $myrow["print_profile"];
		$_POST['rep_popup'] = $myrow["rep_popup"];
		$_POST['pos'] = $myrow["pos"];
		$_POST['def_print_orientation'] = $myrow["def_print_orientation"];
		$_POST['bank_accounts_number'] = $myrow["bank_accounts_number"];
		$_POST['user_location'] = $myrow["user_location"];
	}
	hidden('selected_id', $selected_id);
	hidden('user_id');

	start_row();
	label_row(_("User login:"), $_POST['user_id']);
} 
else 
{ //end of if $selected_id only do the else when a new record is being entered
	text_row(_("User Login:"), "user_id",  null, 22, 20);
	$_POST['language'] = user_language();
	$_POST['print_profile'] = user_print_profile();
	$_POST['rep_popup'] = user_rep_popup();
	$_POST['pos'] = user_pos();
}
$_POST['password'] = "";
password_row(_("Password:"), 'password', $_POST['password']);

if ($selected_id != -1) 
{
	table_section_title(_("Enter a new password to change, leave empty to keep current."));
}
text_row_ex(_("Full Name").":", 'real_name',  50);
text_row_ex(_("Sms Name").":", 'def_print_orientation',  50);
echo "
	</td>
	</tr>
";
$sql_bank_account = "SELECT * FROM `0_bank_accounts`";
$result_bank_account = $db->query($sql_bank_account);
echo "
	<tr>
		<td>Bank Acc.Id :</td>
		<td>
		<div class='bank_account_number' style='width:400px;height:100px;overflow-x:auto'>
";
		$bank_data = $myrow["bank_accounts_number"];
		$strpos = strpos($bank_data,",");
		if($strpos!=false){
			$pds = explode(",", $bank_data);
			$pdscount = count($pds);
			$pdstrue = 1;
		}
		else{
			$pdstrue = 0;
		}
		while($data_bank_account = mysqli_fetch_array($result_bank_account)){
			if($pdstrue==1){
				$true = 0;
				for($i=0;$i<$pdscount;$i++){
					if($pds[$i]==$data_bank_account[8]){
						echo "<input type='checkbox' name='$data_bank_account[3]' value='$data_bank_account[8]' checked='checked'/>$data_bank_account[2]";
						$true = 1;
					}
				}
				if($true==0){
					echo "<input type='checkbox' name='$data_bank_account[3]' value='$data_bank_account[8]'/>$data_bank_account[2]";
				}
			}
			else{
				if($bank_data==$data_bank_account[8]){
					echo "<input type='checkbox' name='$data_bank_account[3]' value='$data_bank_account[8]' checked='checked'/>$data_bank_account[2]";
				}
				else{
					echo "<input type='checkbox' name='$data_bank_account[3]' value='$data_bank_account[8]'/>$data_bank_account[2]";
				}
			}
		}
echo "
		</div>
		</td>
	</tr>
";
$sql_location = "select * from 0_locations";
$result_location = $db->query($sql_location);
echo "<tr><td class='label'>User Location: </td>
<td><select style='width:23%' name='user_location' required='required'>
	<option value=''>Select Location</option>";
	while($data_location = mysqli_fetch_array($result_location)){
		if($myrow["user_location"]==$data_location[0]){
			echo "<option selected='selected' value='$data_location[0]'>$data_location[1]</option>";
		}else{
			echo "<option value='$data_location[0]'>$data_location[1]</option>";
		}
	}
echo "</select></td>
</tr>";

//ngicon : add for sales type drop down add
echo "<tr><td class='label'>Sales Type Ngicon: </td>
<td><select style='width:23%' name='sales_type' required='required'>
	<option value=''>Select Sales Type</option>";
	$getId = "SELECT * FROM  0_sales_types";
    $excGetId = $db->query($getId);
    $numRows = mysqli_num_rows($excGetId);
	echo "<option selected='selected' value='all'>All</option>";
	while ($row = mysqli_fetch_array($excGetId)) {
			echo "<option value='$row[id]'>$row[sales_type]</option>";
	}
echo "</select></td>
</tr>";
//engicon : end sales type drop down.





text_row_ex(_("Telephone No.:"), 'phone', 30);
text_row_ex(_("Address").":", 'print_profile',  100);

email_row_ex(_("Email Address:"), 'email', 50);

security_roles_list_row(_("Access Level:"), 'role_id', null); 

languages_list_row(_("Language:"), 'language', null);

pos_list_row(_("User's POS"). ':', 'pos', null);


check_row(_("Use popup window for reports:"), 'rep_popup', $_POST['rep_popup'],
	false, _('Set this option to on if your browser directly supports pdf files'));

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();
end_page();