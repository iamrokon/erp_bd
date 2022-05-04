<?php
/**********************************************************************
    Copyright (C) NGICON (Next Generation icon) ERP.
	Released under the terms of the GNU General Public License, GPL,
	as published by the Free Software Foundation, either version 3



    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 All Rights Reserved By www.ngicon.com
***********************************************************************/
class general_ledger_app extends application
{
	function __construct()
	{
		parent::__construct("GL", _($this->help_context = "&Banking & Accounts"));

		$this->add_module(_("Transactions"));
if (get_company_pref('SA_PAYMENT'))
		$this->add_lapp_function(0, _("&Payments"),
			"gl/gl_bank.php?NewPayment=Yes", 'SA_PAYMENT', MENU_TRANSACTION);
if (get_company_pref('SA_DEPOSIT'))
			$this->add_lapp_function(0, _("&Deposits"),
			"gl/gl_bank.php?NewDeposit=Yes", 'SA_DEPOSIT', MENU_TRANSACTION);
if (get_company_pref('SA_BANKTRANSFER'))
			$this->add_lapp_function(0, _("Bank Account &Transfers"),
			"gl/bank_transfer.php?", 'SA_BANKTRANSFER', MENU_TRANSACTION);
if (get_company_pref('SA_JOURNALENTRY'))
			$this->add_rapp_function(0, _("&Journal Entry"),
			"gl/gl_journal.php?NewJournal=Yes", 'SA_JOURNALENTRY', MENU_TRANSACTION);
if (get_company_pref('SA_BUDGETENTRY'))
			$this->add_rapp_function(0, _("&Budget Entry"),
			"gl/gl_budget.php?", 'SA_BUDGETENTRY', MENU_TRANSACTION);
if (get_company_pref('SA_RECONCILE'))
			$this->add_rapp_function(0, _("&Reconcile Bank Account"),
			"gl/bank_account_reconcile.php?", 'SA_RECONCILE', MENU_TRANSACTION);
if (get_company_pref('SA_ACCRUALS'))
			$this->add_rapp_function(0, _("Revenue / &Costs Accruals"),
			"gl/accruals.php?", 'SA_ACCRUALS', MENU_TRANSACTION);

		$this->add_module(_("Inquiries and Reports"));
if (get_company_pref('SA_GLANALYTIC'))
		$this->add_lapp_function(1, _("&Journal Inquiry"),
			"gl/inquiry/journal_inquiry.php?", 'SA_GLANALYTIC', MENU_INQUIRY);
if (get_company_pref('SA_GLTRANSVIEW'))
			$this->add_lapp_function(1, _("GL| General Ledger Inquiry"),
			"gl/inquiry/gl_account_inquiry.php?", 'SA_GLTRANSVIEW', MENU_INQUIRY);
if (get_company_pref('SA_BANKTRANSVIEW'))
			$this->add_lapp_function(1, _("Bank Account &Inquiry"),
			"gl/inquiry/bank_inquiry.php?", 'SA_BANKTRANSVIEW', MENU_INQUIRY);
if (get_company_pref('SA_TAXREP'))
			$this->add_lapp_function(1, _("Ta&x Inquiry"),
			"gl/inquiry/tax_inquiry.php?", 'SA_TAXREP', MENU_INQUIRY);
if (get_company_pref('SA_GLANALYTIC'))
		$this->add_rapp_function(1, _("Trial &Balance"),
			"gl/inquiry/gl_trial_balance.php?", 'SA_GLANALYTIC', MENU_INQUIRY);
if (get_company_pref('SA_GLANALYTIC'))
			$this->add_rapp_function(1, _("Balance &Sheet Drilldown"),
			"gl/inquiry/balance_sheet.php?", 'SA_GLANALYTIC', MENU_INQUIRY);
if (get_company_pref('SA_GLANALYTIC'))
			$this->add_rapp_function(1, _("&Profit and Loss Drilldown"),
			"gl/inquiry/profit_loss.php?", 'SA_GLANALYTIC', MENU_INQUIRY);
if (get_company_pref('SA_BANKREP'))
			$this->add_rapp_function(1, _("Banking &Reports"),
			"reporting/reports_main.php?Class=5", 'SA_BANKREP', MENU_REPORT);
if (get_company_pref('SA_GLREP'))
			$this->add_rapp_function(1, _("General Ledger &Reports"),
			"reporting/reports_main.php?Class=6", 'SA_GLREP', MENU_REPORT);

		$this->add_module(_("Maintenance"));
if (get_company_pref('SA_BANKACCOUNT'))
		$this->add_lapp_function(2, _("Bank &Accounts"),
			"gl/manage/bank_accounts.php?", 'SA_BANKACCOUNT', MENU_MAINTENANCE);
if (get_company_pref('SA_QUICKENTRY'))
			$this->add_lapp_function(2, _("&Quick Entries"),
			"gl/manage/gl_quick_entries.php?", 'SA_QUICKENTRY', MENU_MAINTENANCE);
if (get_company_pref('SA_GLACCOUNTTAGS'))
			$this->add_lapp_function(2, _("Account &Tags"),
			"admin/tags.php?type=account", 'SA_GLACCOUNTTAGS', MENU_MAINTENANCE);
		$this->add_lapp_function(2, "","");
if (get_company_pref('SA_CURRENCY'))
		$this->add_lapp_function(2, _("&Currencies"),
			"gl/manage/currencies.php?", 'SA_CURRENCY', MENU_MAINTENANCE);
if (get_company_pref('SA_EXCHANGERATE'))
			$this->add_lapp_function(2, _("&Exchange Rates"),
			"gl/manage/exchange_rates.php?", 'SA_EXCHANGERATE', MENU_MAINTENANCE);
if (get_company_pref('SA_GLACCOUNT'))
		$this->add_rapp_function(2, _("GL| General Ledger Accounts"),
			"gl/manage/gl_accounts.php?", 'SA_GLACCOUNT', MENU_ENTRY);
if (get_company_pref('SA_GLACCOUNTGROUP'))
			$this->add_rapp_function(2, _("GL Account &Groups"),
			"gl/manage/gl_account_types.php?", 'SA_GLACCOUNTGROUP', MENU_MAINTENANCE);
if (get_company_pref('SA_GLACCOUNTCLASS'))
			$this->add_rapp_function(2, _("GL Account &Classes"),
			"gl/manage/gl_account_classes.php?", 'SA_GLACCOUNTCLASS', MENU_MAINTENANCE);
if (get_company_pref('SA_GLSETUP'))
			$this->add_rapp_function(2, _("&Closing GL Transactions"),
			"gl/manage/close_period.php?", 'SA_GLSETUP', MENU_MAINTENANCE);
if (get_company_pref('SA_EXCHANGERATE'))
			$this->add_rapp_function(2, _("Revaluation of Currency"),
			"gl/manage/revaluate_currencies.php?", 'SA_EXCHANGERATE', MENU_MAINTENANCE);

		$this->add_extensions();
	}
}


