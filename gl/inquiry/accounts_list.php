<?php
/**********************************************************************
    Copyright (C) NGICON (Next Generation icon) ERP.
               All Rights Reserved By www.ngicon.com. 






***********************************************************************/
/**********************************************************************
  Page for searching GL account list and select it to GL account
  selection in pages that have GL account dropdown lists.
  Author: bogeyman2007 from Discussion Forum. Modified by Joe Hunt
***********************************************************************/
$page_security = "SA_GLACCOUNT";
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/gl/includes/db/gl_db_accounts.inc");

$js = get_js_select_combo_item();

page(_($help_context = "GL Accounts"), true, false, "", $js);

if(get_post("search")) {
  	$Ajax->activate("account_tbl");
}

// Filter form. Use query string so the client_id will not disappear
// after ajax form post.
start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

start_table(TABLESTYLE_NOBORDER);

start_row();

text_cells(_("Description"), "description");
submit_cells("search", _("Search"), "", _("Search GL accounts"), "default");

end_row();

end_table();

end_form();

div_start("account_tbl");

start_table(TABLESTYLE);

$th = array("", _("Account Code"), _("Description"), _("Category"));

table_header($th);

$k = 0;
$name = $_GET["client_id"];
$skip = $_GET["skip"];

$result = get_chart_accounts_search(get_post("description"), $skip);
while ($myrow = db_fetch_assoc($result)) {
	alt_table_row_color($k);
	$value = $myrow['account_code'];
	ahref_cell(_("Select"), 'javascript:void(0)', '', 'selectComboItem(window.opener.document, "'.$name.'", "'.$value.'")');
  	label_cell($myrow["account_code"]);
	label_cell($myrow["account_name"]);
  	label_cell($myrow["name"]);
	end_row();
}

end_table(1);

div_end();
end_page(true);
