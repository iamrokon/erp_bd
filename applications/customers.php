<?php
/**********************************************************************
    Copyright (C) NGICON (Next Generation icon) ERP.
	




   
 All Rights Reserved By www.ngicon.com
***********************************************************************/
class customers_app extends application 
{
	function __construct() 
	{
		parent::__construct("orders", _($this->help_context = "&Sales"));
	
		$this->add_module(_("Transactions"));
	if (get_company_pref('SA_SALESQUOTE'))			
		$this->add_lapp_function(0, _("Sales &Quotation Entry"),
			"sales/sales_order_entry.php?NewQuotation=Yes", 'SA_SALESQUOTE', MENU_TRANSACTION);
	if (get_company_pref('SA_SALESORDER'))				
		$this->add_lapp_function(0, _("Sales &Order Entry"),
			"sales/sales_order_entry.php?NewOrder=Yes", 'SA_SALESORDER', MENU_TRANSACTION);
	if (get_company_pref('SA_SALESDELIVERY'))				
		$this->add_lapp_function(0, _("Direct &Delivery"),
			"sales/sales_order_entry.php?NewDelivery=0", 'SA_SALESDELIVERY', MENU_TRANSACTION);
	if (get_company_pref('SA_SALESINVOICE'))			
		$this->add_lapp_function(0, _("Billing | Invoice"),
			"sales/sales_order_entry.php?NewInvoice=0", 'SA_SALESINVOICE', MENU_TRANSACTION);	
	if (get_company_pref('SA_SALESINVOICE'))			
		$this->add_lapp_function(0, _("Billing | Invoice (A5)"),
			"sales/sales_order_entrya5.php?NewInvoice=0", 'SA_SALESINVOICE', MENU_TRANSACTION);
		$this->add_lapp_function(0, "","");
	if (get_company_pref('SA_SALESDELIVERY'))		
		$this->add_lapp_function(0, _("&Delivery Against  Orders"),
			"sales/inquiry/sales_orders_view.php?OutstandingOnly=1", 'SA_SALESDELIVERY', MENU_TRANSACTION);
	if (get_company_pref('SA_SALESINVOICE'))						
		$this->add_lapp_function(0, _("&Invoice Against Delivery"),
			"sales/inquiry/sales_deliveries_view.php?OutstandingOnly=1", 'SA_SALESINVOICE', MENU_TRANSACTION);

	if (get_company_pref('SA_SALESALLOC'))			
		$this->add_rapp_function(0, _("&Template Delivery"),
			"sales/inquiry/sales_orders_view.php?DeliveryTemplates=Yes", 'SA_SALESALLOC', MENU_TRANSACTION);
	if (get_company_pref('SA_SALESALLOC'))						
		$this->add_rapp_function(0, _("&Template Invoice"),
			"sales/inquiry/sales_orders_view.php?InvoiceTemplates=Yes", 'SA_SALESALLOC', MENU_TRANSACTION);
	if (get_company_pref('SA_SALESALLOC'))						
		$this->add_rapp_function(0, _("Cyclic |  Recurrent Invoices"),
			"sales/create_recurrent_invoices.php?", 'SA_SALESALLOC', MENU_TRANSACTION);
		$this->add_rapp_function(0, "","");
	if (get_company_pref('SA_SALESPAYMNT'))		
		$this->add_rapp_function(0, _("Customer &Payments"),
			"sales/customer_payments.php?", 'SA_SALESPAYMNT', MENU_TRANSACTION);
	if (get_company_pref('SA_SALESINVOICE'))						
		$this->add_lapp_function(0, _("Invoice &Prepaid Orders"),
			"sales/inquiry/sales_orders_view.php?PrepaidOrders=Yes", 'SA_SALESINVOICE', MENU_TRANSACTION);
			
	if (get_company_pref('SA_SALESCREDIT'))			
		$this->add_rapp_function(0, _("Customer Return"),
			"sales/credit_note_entry.php?NewCredit=Yes", 'SA_SALESCREDIT', MENU_TRANSACTION);
	if (get_company_pref('SA_SALESALLOC'))				
		$this->add_rapp_function(0, _("&Allocate Payments | Return"),
			"sales/allocations/customer_allocation_main.php?", 'SA_SALESALLOC', MENU_TRANSACTION);

		$this->add_module(_("Inquiries and Reports"));
	if (get_company_pref('SA_SALESTRANSVIEW'))			
		$this->add_lapp_function(1, _("Sales Quotation I&nquiry"),
			"sales/inquiry/sales_orders_view.php?type=32", 'SA_SALESTRANSVIEW', MENU_INQUIRY);
	if (get_company_pref('SA_SALESTRANSVIEW'))				
		$this->add_lapp_function(1, _("Sales Order &Inquiry"),
			"sales/inquiry/sales_orders_view.php?type=30", 'SA_SALESTRANSVIEW', MENU_INQUIRY);
	if (get_company_pref('SA_SALESTRANSVIEW'))				
		$this->add_lapp_function(1, _("Customer Transaction &Inquiry"),
			"sales/inquiry/customer_inquiry.php?", 'SA_SALESTRANSVIEW', MENU_INQUIRY);
	if (get_company_pref('SA_SALESALLOC'))				
		$this->add_lapp_function(1, _("Customer Allocation &Inquiry"),
			"sales/inquiry/customer_allocation_inquiry.php?", 'SA_SALESALLOC', MENU_INQUIRY);
	if (get_company_pref('SA_SALESTRANSVIEW'))	
		$this->add_rapp_function(1, _("Customer and Sales &Reports"),
			"reporting/reports_main.php?Class=0", 'SA_SALESTRANSVIEW', MENU_REPORT);

		$this->add_module(_("Maintenance"));
	if (get_company_pref('SA_CUSTOMER'))			
		$this->add_lapp_function(2, _("Add & Manage Customers"),
			"sales/manage/customers.php?", 'SA_CUSTOMER', MENU_ENTRY);
	if (get_company_pref('SA_SALESAREA'))				
		$this->add_lapp_function(2, _("Customer &Branches"),
			"sales/manage/customer_branches.php?", 'SA_SALESAREA', MENU_ENTRY);
	if (get_company_pref('SA_SALESGROUP'))				
		$this->add_lapp_function(2, _("Sales &Groups"),
			"sales/manage/sales_groups.php?", 'SA_SALESGROUP', MENU_MAINTENANCE);
	if (get_company_pref('SA_SRECURRENT'))				
		$this->add_lapp_function(2, _("Manage Cyclic Invoices"),
			"sales/manage/recurrent_invoices.php?", 'SA_SRECURRENT', MENU_MAINTENANCE);
	if (get_company_pref('SA_SALESTYPES'))				
		$this->add_rapp_function(2, _("Sales T&ypes"),
			"sales/manage/sales_types.php?", 'SA_SALESTYPES', MENU_MAINTENANCE);
	if (get_company_pref('SA_SALESMAN'))				
		$this->add_rapp_function(2, _("Sales &Persons"),
			"sales/manage/sales_people.php?", 'SA_SALESMAN', MENU_MAINTENANCE);
	if (get_company_pref('SA_SALESAREA'))				
		$this->add_rapp_function(2, _("Sales &Areas"),
			"sales/manage/sales_areas.php?", 'SA_SALESAREA', MENU_MAINTENANCE);
	if (get_company_pref('SA_CRSTATUS'))				
		$this->add_rapp_function(2, _("Credit &Status Setup"),
			"sales/manage/credit_status.php?", 'SA_CRSTATUS', MENU_MAINTENANCE);

		$this->add_extensions();
	}
}


