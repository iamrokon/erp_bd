<?php
/**********************************************************************
    Copyright (C) NGICON (Next Generation icon) ERP.
               All Rights Reserved By www.ngicon.com. 






***********************************************************************/

if (isset($_GET['sel_app']))
{
	$page_security = 'SA_SETUPDISPLAY'; // A very low access level. The real access level is inside the routines.
	$path_to_root = "..";

	include_once($path_to_root . "/includes/session.inc");
	include_once($path_to_root . "/includes/ui.inc");
	include_once($path_to_root . "/reporting/includes/class.graphic.inc");
	include_once($path_to_root . "/includes/dashboard.inc"); // here are all the dashboard routines.

	$js = "";
	if ($SysPrefs->use_popup_windows)
		$js .= get_js_open_window(800, 500);

	page(_($help_context = "Dashboard"), false, false, "", $js);
	dashboard($_GET['sel_app']);
	end_page();
	exit;
}
