<?php
/**********************************************************************
    Copyright (C) NGICON (Next Generation icon) ERP.
	




   
 All Rights Reserved By www.ngicon.com
***********************************************************************/
//--------------------------------------------------------------------------------------------------

function get_comments($type, $type_no)
{
	$sql = "SELECT * FROM ".TB_PREF."comments WHERE type="
		.db_escape($type)." AND id=".db_escape($type_no);

	return db_query($sql, "could not query comments transaction table");
}

//--------------------------------------------------------------------------------------------------

function add_comments($type, $type_no, $date_, $memo_)
{
	if ($memo_ != null && $memo_ != "")
	{
    	$date = date2sql($date_);
    	$sql = "INSERT INTO ".TB_PREF."comments (type, id, date_, memo_)
    		VALUES (".db_escape($type).", ".db_escape($type_no)
			.", '$date', ".db_escape($memo_).")";

    	db_query($sql, "could not add comments transaction entry");
	}
}

//--------------------------------------------------------------------------------------------------

function update_comments($type, $id, $date_, $memo_)
{
	if ($date_ == null)
	{
    	delete_comments($type, $id);
    	add_comments($type, $id, Today(), $memo_);
	}
	else
	{
		$date = date2sql($date_);
    	$sql = "UPDATE ".TB_PREF."comments SET memo_=".db_escape($memo_)
			." WHERE type=".db_escape($type)." AND id=".db_escape($id)
			." AND date_='$date'";
    	db_query($sql, "could not update comments");
	}
}

//--------------------------------------------------------------------------------------------------

function delete_comments($type, $type_no)
{
	$sql = "DELETE FROM ".TB_PREF."comments WHERE type=".db_escape($type)
		." AND id=".db_escape($type_no);

	db_query($sql, "could not delete from comments transaction table");
}

//--------------------------------------------------------------------------------------------------

