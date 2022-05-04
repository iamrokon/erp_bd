<?php
/**********************************************************************
    Copyright (C) NGICON (Next Generation icon) ERP.
	




   
 All Rights Reserved By www.ngicon.com
***********************************************************************/
class setup_app extends application
{
	function __construct()
	{
		parent::__construct("system", _($this->help_context = "S&etup"));

		$this->add_module(_("Company Setup"));
if (get_company_pref('SA_SETUPCOMPANY'))
		$this->add_lapp_function(0, _("&Company Setup"),
			"admin/company_preferences.php?", 'SA_SETUPCOMPANY', MENU_SETTINGS);
if (get_company_pref('SA_SETUPCOMPANY'))
			$this->add_lapp_function(0, _("&Menu Setup"),
			"admin/menu_preferences.php?", 'SA_SETUPCOMPANY', MENU_SETTINGS);			
if (get_company_pref('SA_USERS'))
			$this->add_lapp_function(0, _("&User Accounts Setup"),
			"admin/users.php?", 'SA_USERS', MENU_SETTINGS);
if (get_company_pref('SA_SECROLES'))
			$this->add_lapp_function(0, _("&Access Setup"),
			"admin/security_roles.php?", 'SA_SECROLES', MENU_SETTINGS);
if (get_company_pref('SA_SETUPDISPLAY'))
			$this->add_lapp_function(0, _("&Display Setup"),
			"admin/display_prefs.php?", 'SA_SETUPDISPLAY', MENU_SETTINGS);
if (get_company_pref('SA_FORMSETUP'))
			$this->add_lapp_function(0, _("Transaction &References"),
			"admin/forms_setup.php?", 'SA_FORMSETUP', MENU_SETTINGS);
if (get_company_pref('SA_TAXRATES'))
			$this->add_rapp_function(0, _("&Taxes"),
			"taxes/tax_types.php?", 'SA_TAXRATES', MENU_MAINTENANCE);
if (get_company_pref('SA_TAXGROUPS'))
			$this->add_rapp_function(0, _("Tax &Groups"),
			"taxes/tax_groups.php?", 'SA_TAXGROUPS', MENU_MAINTENANCE);
if (get_company_pref('SA_ITEMTAXTYPE'))
			$this->add_rapp_function(0, _("Item Ta&x Types"),
			"taxes/item_tax_types.php?", 'SA_ITEMTAXTYPE', MENU_MAINTENANCE);
if (get_company_pref('SA_SETUPCOMPANY'))
			$this->add_rapp_function(0, _("System Setup"),
			"admin/gl_setup.php?", 'SA_SETUPCOMPANY', MENU_SETTINGS);
if (get_company_pref('SA_FISCALYEARS'))
			$this->add_rapp_function(0, _("&Fiscal Years"),
			"admin/fiscalyears.php?", 'SA_FISCALYEARS', MENU_MAINTENANCE);
if (get_company_pref('SA_PRINTPROFILE'))
			$this->add_rapp_function(0, _("&Print Profiles"),
			"admin/print_profiles.php?", 'SA_PRINTPROFILE', MENU_MAINTENANCE);

		$this->add_module(_("Miscellaneous"));
if (get_company_pref('SA_PAYTERMS'))
		$this->add_lapp_function(1, _("Pa&yment Terms"),
			"admin/payment_terms.php?", 'SA_PAYTERMS', MENU_MAINTENANCE);
if (get_company_pref('SA_SHIPPING'))
			$this->add_lapp_function(1, _("Shi&pping Company"),
			"admin/shipping_companies.php?", 'SA_SHIPPING', MENU_MAINTENANCE);
if (get_company_pref('SA_POSSETUP'))
			$this->add_rapp_function(1, _("&Points of Sale"),
			"sales/manage/sales_points.php?", 'SA_POSSETUP', MENU_MAINTENANCE);
if (get_company_pref('SA_PRINTERS'))
			$this->add_rapp_function(1, _("&Printers"),
			"admin/printers.php?", 'SA_PRINTERS', MENU_MAINTENANCE);
if (get_company_pref('SA_CRMCATEGORY'))
			$this->add_rapp_function(1, _("Contact &Categories"),
			"admin/crm_categories.php?", 'SA_CRMCATEGORY', MENU_MAINTENANCE);

		$this->add_module(_("Maintenance"));
if (get_company_pref('SA_VOIDTRANSACTION'))
		$this->add_lapp_function(2, _("&Void a Transaction"),
			"admin/void_transaction.php?", 'SA_VOIDTRANSACTION', MENU_MAINTENANCE);
if (get_company_pref('SA_VIEWPRINTTRANSACTION'))
			$this->add_lapp_function(2, _("View or &Print Transactions"),
			"admin/view_print_transaction.php?", 'SA_VIEWPRINTTRANSACTION', MENU_MAINTENANCE);
if (get_company_pref('SA_ATTACHDOCUMENT'))
			$this->add_lapp_function(2, _("&Attach Documents"),
			"admin/attachments.php?filterType=20", 'SA_ATTACHDOCUMENT', MENU_MAINTENANCE);
if (get_company_pref('SA_SOFTWAREUPGRADE'))
			$this->add_lapp_function(2, _("System &Diagnostics"),
			"admin/system_diagnostics.php?", 'SA_SOFTWAREUPGRADE', MENU_SYSTEM);
if (get_company_pref('SA_BACKUP'))
		$this->add_rapp_function(2, _("&Backup and Restore"),
			"admin/backups.php?", 'SA_BACKUP', MENU_SYSTEM);
if (get_company_pref('SA_CREATECOMPANY'))
			$this->add_rapp_function(2, _("Create/Update &Companies"),
			"admin/create_coy.php?", 'SA_CREATECOMPANY', MENU_UPDATE);
if (get_company_pref('SA_CREATELANGUAGE'))
			$this->add_rapp_function(2, _("Install/Update &Languages"),
			"admin/inst_lang.php?", 'SA_CREATELANGUAGE', MENU_UPDATE);
if (get_company_pref('SA_CREATEMODULES'))
			$this->add_rapp_function(2, _("Install/Activate &Extensions"),
			"admin/inst_module.php?", 'SA_CREATEMODULES', MENU_UPDATE);
if (get_company_pref('SA_CREATEMODULES'))
			$this->add_rapp_function(2, _("Install/Activate &Themes"),
			"admin/inst_theme.php?", 'SA_CREATEMODULES', MENU_UPDATE);
if (get_company_pref('SA_CREATEMODULES'))
			$this->add_rapp_function(2, _("Install Chart of Accounts"),
			"admin/inst_chart.php?", 'SA_CREATEMODULES', MENU_UPDATE);
if (get_company_pref('SA_SOFTWAREUPGRADE'))
			$this->add_rapp_function(2, _("Software &Upgrade"),
			"admin/inst_upgrade.php?", 'SA_SOFTWAREUPGRADE', MENU_UPDATE);

		$this->add_extensions();
	}
}


