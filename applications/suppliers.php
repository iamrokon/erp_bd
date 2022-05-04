<?php
/**********************************************************************
    Copyright (C) NGICON (Next Generation icon) ERP.
	




   
 All Rights Reserved By www.ngicon.com
***********************************************************************/
class suppliers_app extends application 
{
	function __construct() 
	{
		parent::__construct("AP", _($this->help_context = "&Purchases"));

		$this->add_module(_("Transactions"));
if (get_company_pref('SA_PURCHASEORDER'))	
		$this->add_lapp_function(0, _("Purchase &Order Entry"),
			"purchasing/po_entry_items.php?NewOrder=Yes", 'SA_PURCHASEORDER', MENU_TRANSACTION);
if (get_company_pref('SA_GRN'))	
		$this->add_lapp_function(0, _("Pending Purchase Orders"),
			"purchasing/inquiry/po_search.php?", 'SA_GRN', MENU_TRANSACTION);
if (get_company_pref('SA_GRN'))	
			$this->add_lapp_function(0, _("Direct Goods Receive Note"),
			"purchasing/po_entry_items.php?NewGRN=Yes", 'SA_GRN', MENU_TRANSACTION);
if (get_company_pref('SA_SUPPLIERINVOICE'))
    	$this->add_lapp_function(0, _("Supplier Billing | Invoice"),
			"purchasing/po_entry_items.php?NewInvoice=Yes", 'SA_SUPPLIERINVOICE', MENU_TRANSACTION);
if (get_company_pref('SA_SUPPLIERPAYMNT'))
		$this->add_rapp_function(0, _("&Payments to Suppliers"),
			"purchasing/supplier_payment.php?", 'SA_SUPPLIERPAYMNT', MENU_TRANSACTION);
		$this->add_rapp_function(0, "","");
if (get_company_pref('SA_SUPPLIERINVOICE'))
		$this->add_rapp_function(0, _("Supplier &Invoices"),
			"purchasing/supplier_invoice.php?New=1", 'SA_SUPPLIERINVOICE', MENU_TRANSACTION);
if (get_company_pref('SA_SUPPLIERCREDIT'))
			$this->add_rapp_function(0, _("Supplier Return| Credit Notes"),
			"purchasing/supplier_credit.php?New=1", 'SA_SUPPLIERCREDIT', MENU_TRANSACTION);
if (get_company_pref('SA_SUPPLIERALLOC'))
			$this->add_rapp_function(0, _("Allocate Payments | Return"),
			"purchasing/allocations/supplier_allocation_main.php?", 'SA_SUPPLIERALLOC', MENU_TRANSACTION);

		$this->add_module(_("Inquiries and Reports"));
if (get_company_pref('SA_SUPPTRANSVIEW'))
		$this->add_lapp_function(1, _("Purchase Orders &Inquiry"),
			"purchasing/inquiry/po_search_completed.php?", 'SA_SUPPTRANSVIEW', MENU_INQUIRY);
if (get_company_pref('SA_SUPPTRANSVIEW'))
			$this->add_lapp_function(1, _("Supplier Transaction &Inquiry"),
			"purchasing/inquiry/supplier_inquiry.php?", 'SA_SUPPTRANSVIEW', MENU_INQUIRY);
if (get_company_pref('SA_SUPPLIERALLOC'))
			$this->add_lapp_function(1, _("Supplier Allocation &Inquiry"),
			"purchasing/inquiry/supplier_allocation_inquiry.php?", 'SA_SUPPLIERALLOC', MENU_INQUIRY);
if (get_company_pref('SA_SUPPTRANSVIEW'))
		$this->add_rapp_function(1, _("Supplier & Purchase Reports"),
			"reporting/reports_main.php?Class=1", 'SA_SUPPTRANSVIEW', MENU_REPORT);

		$this->add_module(_("Maintenance"));
if (get_company_pref('SA_SUPPLIER'))
		$this->add_lapp_function(2, _("&Suppliers"),
			"purchasing/manage/suppliers.php?", 'SA_SUPPLIER', MENU_ENTRY);

		$this->add_extensions();
	}
}


