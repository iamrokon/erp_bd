<?php
/**********************************************************************
    Copyright (C) NGICON (Next Generation icon) ERP.
               All Rights Reserved By www.ngicon.com. 






***********************************************************************/
/**********************************************************************
  Page for searching customer branch list and select it to customer
  branch selection in pages that have the customer branch dropdown lists.
  Author: bogeyman2007 from Discussion Forum. Modified by Joe Hunt
***********************************************************************/
$page_security = "SA_SALESORDER";
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/sales/includes/db/branches_db.inc");

$js = get_js_select_combo_item();

page(_($help_context = "Customer Branches"), true, false, "", $js);

if(get_post("search")) {
  $Ajax->activate("customer_branch_tbl");
}

start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

start_table(TABLESTYLE_NOBORDER);

start_row();

text_cells(_("Branch"), "branch");
submit_cells("search", _("Search"), "", _("Search branches"), "default");

end_row();

end_table();

end_form();

div_start("customer_branch_tbl");
start_table(TABLESTYLE);

$th = array("", _("Ref"), _("Branch"), _("Contact"), _("Phone"));

table_header($th);

$k = 0;
$name = $_GET["client_id"];
$result = get_branches_search($_GET["customer_id"], get_post("branch"));
while ($myrow = db_fetch_assoc($result))
{
  	alt_table_row_color($k);
	$value = $myrow['branch_code'];
	ahref_cell(_("Select"), 'javascript:void(0)', '', 'selectComboItem(window.opener.document, "'.$name.'", "'.$value.'")');
  	label_cell($myrow["branch_ref"]);
  	label_cell($myrow["br_name"]);
  	label_cell($myrow["contact_name"]);
  	label_cell($myrow["phone"]);
	end_row();
}

end_table(1);

div_end();
end_page(true);
