<?php
/*
Copyright 2010 Main Street Softworks, Inc. All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are
permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, this list of
      conditions and the following disclaimer.

   2. Redistributions in binary form must reproduce the above copyright notice, this list
      of conditions and the following disclaimer in the documentation and/or other materials
      provided with the distribution.

THIS SOFTWARE IS PROVIDED BY MAIN STREET SOFTWORKS INC ``AS IS'' AND ANY EXPRESS OR IMPLIED
WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL MAIN STREET SOFTWORKS INC OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

The views and conclusions contained in the software and documentation are those of the
authors and should not be interpreted as representing official policies, either expressed
or implied, of Main Street Softworks, Inc.
*/


/* Lets make it so this may always be included, even if
 * they have the module loaded for php_mcve.  Prefer the
 * module routines over the pure-php ones */
if (!function_exists("mcve_initengine")) {

//error_reporting(E_ALL);

require_once("libmonetra.php");

/* ---------- MAPPINGS for MCVE_ -> M_ -------------- */
define('MCVE_ERROR',   M_ERROR);
define('MCVE_FAIL',    M_FAIL);
define('MCVE_SUCCESS', M_SUCCESS);

define('MCVE_DONE',    M_DONE);
define('MCVE_PENDING', M_PENDING);

function MCVE_InitEngine($cafile=null)
{
	return M_InitEngine($cafile);
}

function MCVE_DestroyEngine()
{
	return M_DestroyEngine();
}

function MCVE_InitConn()
{
	return M_InitConn();
}

function MCVE_SetIP(&$conn, $host, $port)
{
	return M_SetIP($conn, $host, $port);
}

function MCVE_SetSSL(&$conn, $host, $port)
{
	return M_SetSSL($conn, $host, $port);
}

function MCVE_SetDropFile(&$conn, $directory)
{
	return M_SetDropFile($conn, $directory);
}



function MCVE_Connect(&$conn)
{
	return M_Connect($conn);
}

function MCVE_ConnectionError(&$conn)
{
	return M_ConnectionError($conn);
}

function MCVE_DestroyConn(&$conn)
{
	return M_DestroyConn($conn);
}

function MCVE_MaxConnTimeout(&$conn, $secs)
{
	return M_MaxConnTimeout($conn, $secs);
}

function MCVE_SetBlocking(&$conn, $tf)
{
	return M_SetBlocking($conn, $tf);
}

function MCVE_ValidateIdentifier(&$conn, $tf)
{
	return M_ValidateIdentifier($conn, $tf);
}

function MCVE_VerifyConnection(&$conn, $tf)
{
	return M_VerifyConnection($conn, $tf);
}

function MCVE_VerifySSLCert(&$conn, $tf)
{
	return M_VerifySSLCert($conn, $tf);
}

function MCVE_SetSSL_CAfile(&$conn, $cafile)
{
	return M_SetSSL_CAfile($conn, $cafile);
}

function MCVE_SetSSL_Files(&$conn, $sslkeyfile, $sslcertfile)
{
	return M_SetSSL_Files($conn, $sslkeyfile, $sslcertfile);
}

function MCVE_SetTimeout(&$conn, $secs)
{
	return M_SetTimeout($conn, $secs);
}

function MCVE_TransNew(&$conn)
{
	return M_TransNew($conn);
}

function MCVE_TransKeyVal(&$conn, $id, $key, $val)
{
	return M_TransKeyVal($conn, $id, $key, $val);
}

function MCVE_TransBinaryKeyVal(&$conn, $id, $key, $val, $val_len)
{
	return M_TransBinaryKeyVal($conn, $id, $key, $val, $val_len);
}

function MCVE_TransSend(&$conn, $id)
{
	return M_TransSend($conn, $id);
}

function MCVE_CheckStatus(&$conn, $id)
{
	return M_CheckStatus($conn, $id);
}

function MCVE_CompleteAuthorizations(&$conn, &$id_array)
{
	return M_CompleteAuthorizations($conn, $id_array);
}

function MCVE_DeleteTrans(&$conn, $id)
{
	return M_DeleteTrans($conn, $id);
}

function MCVE_Monitor(&$conn)
{
	return M_Monitor($conn);
}

function MCVE_TransInQueue(&$conn)
{
	return M_TransInQueue($conn);
}

function MCVE_TransactionsSent(&$conn)
{
	return M_TransactionsSent($conn);
}

function MCVE_GetCell(&$conn, $id, $col, $row)
{
	return M_GetCell($conn, $id, $col, $row);
}

function MCVE_GetBinaryCell(&$conn, $id, $col, $row, &$outlen)
{
	return M_GetBinaryCell($conn, $id, $col, $row, $outlen);
}

function MCVE_GetCellByNum(&$conn, $id, $col, $row)
{
	return M_GetCellByNum($conn, $id, $col, $row);
}

function MCVE_GetCommaDelimited(&$conn, $id)
{
	return M_GetCommaDelimited($conn, $id);
}

function MCVE_GetHeader(&$conn, $id, $col)
{
	return M_GetHeader($conn, $id, $col);
}

function MCVE_IsCommaDelimited(&$conn, $id)
{
	return M_IsCommaDelimited($conn, $id);
}

function MCVE_NumColumns(&$conn, $id)
{
	return M_NumColumns($conn, $id);
}

function MCVE_NumRows(&$conn, $id)
{
	return M_NumRows($conn, $id);
}

function MCVE_ParseCommaDelimited(&$conn, $id)
{
	return M_ParseCommaDelimited($conn, $id);
}

function MCVE_ResponseKeys(&$conn, $id)
{
	return M_ResponseKeys($conn, $id);
}

function MCVE_ResponseParam(&$conn, $id, $key)
{
	return M_ResponseParam($conn, $id, $key);
}

function MCVE_ReturnStatus(&$conn, $id)
{
	return M_ReturnStatus($conn, $id);
}

function MCVE_uwait($usec)
{
	return M_uwait($usec);
}

/* ------------- Other deprecated functions --------------- */
define("M_GOOD", "GOOD");
define("M_BAD", "BAD");
define("M_STREET", "STREET");
define("M_ZIP", "ZIP");
define("M_UNKNOWN", null);

define("M_AUTH", "AUTH");
define("M_DENY", "DENY");
define("M_CALL", "CALL");
define("M_DUPL", "DUPL");
define("M_PKUP", "PKUP");
define("M_RETRY", "RETRY");
define("M_SETUP", "SETUP");
define("M_TIMEOUT", "TIMEOUT");

define("MCVE_GOOD", M_GOOD);
define("MCVE_BAD", M_BAD);
define("MCVE_STREET", M_STREET);
define("MCVE_ZIP", M_ZIP);
define("MCVE_UNKNOWN", M_UNKNOWN);

define("MCVE_AUTH", M_AUTH);
define("MCVE_DENY", M_DENY);
define("MCVE_CALL", M_CALL);
define("MCVE_DUPL", M_DUPL);
define("MCVE_PKUP", M_PKUP);
define("MCVE_RETRY", M_RETRY);
define("MCVE_SETUP", M_SETUP);
define("MCVE_TIMEOUT", M_TIMEOUT);

function M_DeleteResponse(&$conn, $id)
{
	return M_DeleteTrans($conn, $id);
}

function MCVE_DeleteResponse(&$conn, $id)
{
	return M_DeleteResponse($conn, $id);
}

function M_ReturnCode(&$conn, $id)
{
	return M_ResponseParam($conn, $id, "code");
}

function MCVE_ReturnCode(&$conn, $id)
{
	return M_ReturnCode($conn, $id);
}

function M_TransactionItem(&$conn, $id)
{
	return M_ResponseParam($conn, $id, "item");
}

function MCVE_TransactionItem(&$conn, $id)
{
	return M_TransactionItem($conn, $id);
}

function M_TransactionBatch(&$conn, $id)
{
	return M_ResponseParam($conn, $id, "batch");
}

function MCVE_TransactionBatch(&$conn, $id)
{
	return M_TransactionBatch($conn, $id);
}

function M_TransactionID(&$conn, $id)
{
	return M_ResponseParam($conn, $id, "ttid");
}

function MCVE_TransactionID(&$conn, $id)
{
	return M_TransactionID($conn, $id);
}

function M_TransactionAuth(&$conn, $id)
{
	return M_ResponseParam($conn, $id, "auth");
}

function MCVE_TransactionAuth(&$conn, $id)
{
	return M_TransactionAuth($conn, $id);
}

function M_TransactionText(&$conn, $id)
{
	return M_ResponseParam($conn, $id, "verbiage");
}

function MCVE_TransactionText(&$conn, $id)
{
	return M_TransactionText($conn, $id);
}

function M_TransactionAVS(&$conn, $id)
{
	return M_ResponseParam($conn, $id, "avs");
}

function MCVE_TransactionAVS(&$conn, $id)
{
	return M_TransactionAVS($conn, $id);
}

function M_TransactionCV(&$conn, $id)
{
	return M_ResponseParam($conn, $id, "cv");
}

function MCVE_TransactionCV(&$conn, $id)
{
	return M_TransactionCV($conn, $id);
}

function M_TEXT_Code($code)
{
	if ($code == "DUPL")
		return "DUPLICATE TRANS";

	if ($code == "CALL")
		return "CALL PROCESSOR";

	if ($code == "PKUP")
		return "PICKUP CARD";

	return $code;
}

function MCVE_TEXT_Code($code)
{
	return M_TEXT_Code($code);
}

function M_TEXT_AVS($code)
{
	if ($code == "STREET")
		return "STREET FAILED";
	if ($code == "ZIP")
		return "ZIP FAILED";
	if ($code == "")
		return "UNKNOWN";

	return $code;
}

function MCVE_TEXT_AVS($code)
{
	return M_TEXT_AVS($code);
}

function M_TEXT_CV($code)
{
	return M_TEXT_AVS($code);
}

function MCVE_TEXT_CV($code)
{
	return M_TEXT_CV($code);
}

/* ------------------- Legacy TransParam Implementation ---------------- */
	
/* Key definitions for Transaction Parameters */
define("MC_TRANTYPE",  "action");
define("MC_USERNAME",  "username");
define("MC_PASSWORD",  "password");
define("MC_ACCOUNT",   "account");
define("MC_TRACKDATA", "trackdata");
define("MC_EXPDATE",   "expdate");
define("MC_STREET",    "street");
define("MC_ZIP",       "zip");
define("MC_CV",        "cvv2");
define("MC_COMMENTS",  "comments");
define("MC_CLERKID",   "clerkid");
define("MC_STATIONID", "stationid");
define("MC_APPRCODE",  "apprcode");
define("MC_AMOUNT",    "amount");
define("MC_PTRANNUM",  "ptrannum");
define("MC_TTID",      "ttid");
define("MC_USER",      "user");
define("MC_PWD",       "pwd");
define("MC_ACCT",      "acct");
define("MC_BDATE",     "bdate");
define("MC_EDATE",     "edate");
define("MC_BATCH",     "batch");
define("MC_FILE",      "file");
define("MC_ADMIN",     "admin");
define("MC_AUDITTYPE", "admin");
define("MC_CUSTOM",    "custom");
define("MC_EXAMOUNT",  "examount");
define("MC_EXCHARGES", "excharges");
define("MC_RATE",      "rate");
define("MC_PRIORITY",  "priority");
define("MC_CARDTYPES", "cardtypes");
define("MC_SUB",       "sub");
define("MC_NEWBATCH",  "newbatch");
define("MC_CURR",      "curr");
define("MC_DESCMERCH", "descmerch");
define("MC_DESCLOC",   "descloc");
define("MC_ORIGTYPE",  "origtype");
define("MC_PIN",       "pin");
define("MC_VOIDORIGTYPE", "voidorigtype");

/* Args for priorities */
define("MC_PRIO_HIGH", "high");
define("MC_PRIO_NORMAL", "normal");
define("MC_PRIO_LOW", "low");

/* Args for cardtype */
define("MC_CARD_VISA",  "visa");
define("MC_CARD_MC",    "mc");
define("MC_CARD_AMEX",  "amex");
define("MC_CARD_DISC",  "disc");
define("MC_CARD_JCB",   "jcb");
define("MC_CARD_CB",    "cb");
define("MC_CARD_DC",    "diners");
define("MC_CARD_GIFT",  "gift");
define("MC_CARD_OTHER", "other");
define("MC_CARD_ALL",   "all");

/* Value definitions for Transaction Types */
define("MC_TRAN_SALE", "sale");
define("MC_TRAN_REDEMPTION", "sale");
define("MC_TRAN_PREAUTH", "preauth");
define("MC_TRAN_VOID", "void");
define("MC_TRAN_PREAUTHCOMPLETE", "force");
define("MC_TRAN_FORCE", "force");
define("MC_TRAN_RETURN", "return");
define("MC_TRAN_RELOAD", "return");
define("MC_TRAN_CREDIT", "return");
define("MC_TRAN_SETTLE", "settle");
define("MC_TRAN_INCREMENTAL", "incremental");
define("MC_TRAN_REVERSAL", "reversal");
define("MC_TRAN_ACTIVATE", "activate");
define("MC_TRAN_BALANCEINQ", "balanceinq");
define("MC_TRAN_CASHOUT", "cashout");
define("MC_TRAN_TOREVERSAL", "toreversal");
define("MC_TRAN_SETTLERFR", "settlerfr");
define("MC_TRAN_ISSUE", "issue");
define("MC_TRAN_TIP", "tip");
define("MC_TRAN_MERCHRETURN", "merchreturn");
define("MC_TRAN_IVRREQ", "ivrreq");
define("MC_TRAN_IVRRESP", "ivrresp");
define("MC_TRAN_ADMIN", "admin");
define("MC_TRAN_PING", "ping");
define("MC_TRAN_CHKPWD", "chkpwd");

/* Engine Admin Transaction Types */
define("MC_TRAN_CHNGPWD", "chngpwd");
define("MC_TRAN_LISTSTATS","liststats");
define("MC_TRAN_LISTUSERS", "listusers");
define("MC_TRAN_GETUSERINFO", "getuserinfo");
define("MC_TRAN_ADDUSER", "adduser");
define("MC_TRAN_EDITUSER", "edituser");
define("MC_TRAN_DELUSER", "deluser");
define("MC_TRAN_ENABLEUSER", "enableuser");
define("MC_TRAN_DISABLEUSER", "disableuser");
define("MC_TRAN_IMPORT", "import");
define("MC_TRAN_EXPORT", "export");
define("MC_TRAN_ERRORLOG", "errorlog");
define("MC_TRAN_CLEARERRORLOG", "clearerrorlog");
define("MC_TRAN_GETSUBACCTS", "getsubaccts");

/* Value definitions for Admin Types */
define("MC_ADMIN_GUT", "gut");
define("MC_ADMIN_GL", "gl");
define("MC_ADMIN_GFT", "gft");
define("MC_ADMIN_BT", "bt");
define("MC_ADMIN_UB", "bt");
define("MC_ADMIN_QC", "qc");
define("MC_ADMIN_CTH", "cth");
define("MC_ADMIN_CFH", "cfh");
define("MC_ADMIN_FORCESETTLE", "forcesettle");
define("MC_ADMIN_SETBATCHNUM", "setbatchnum");
define("MC_ADMIN_RENUMBERBATCH", "renumberbatch");
define("MC_ADMIN_FIELDEDIT", "fieldedit");
define("MC_ADMIN_CLOSEBATCH", "closebatch");
	
function M_TransParam(&$conn, $id, $key, $val, $cust_val = NULL)
{
	if (strcasecmp($key, "custom") == 0)
		return M_TransKeyVal($conn, $id, $val, $cust_val);
	return M_TransKeyVal($conn, $id, $key, $val);
}
	
function MCVE_TransParam(&$conn, $id, $key, $val, $cust_val = NULL)
{
	return M_TransParam($conn, $id, $key, $val, $cust_val);
}
	
} /* function_exists("mcve_initengine") */


/* ------------------- Super Legacy Functions ---------------- */

define("MC_USER_PROC", "proc");
define("MC_USER_USER", "user");
define("MC_USER_PWD",  "pwd");
define("MC_USER_INDCODE", "indcode");
define("MC_USER_MERCHID", "merchid");
define("MC_USER_BANKID",  "bankid");
define("MC_USER_TERMID",  "termid");
define("MC_USER_CLIENTNUM", "clientnum");
define("MC_USER_STOREID", "storeid");
define("MC_USER_AGENTID", "agentid");
define("MC_USER_CHAINID", "chainid");
define("MC_USER_ZIPCODE", "zipcode");
define("MC_USER_TIMEZONE", "timezone");
define("MC_USER_MERCHCAT", "merchcat");
define("MC_USER_MERNAME", "mername");
define("MC_USER_MERCHLOC", "merchloc");
define("MC_USER_STATECODE", "statecode");
define("MC_USER_PHONE", "phone");
define("MC_USER_SUB", "sub");
define("MC_USER_CARDTYPES", "cardtypes");
define("MC_USER_MODE", "mode");
define("MC_USER_VNUMBER", "vnumber");
define("MC_USER_ROUTINGID", "routingid");
define("MC_USER_PPROPERTY", "pproperty");


function MCVE_GetUserParam(&$conn, $id, $key)
{
	return M_ResponseParam($conn, $id, $key);
}


function MCVE_ChkPwd(&$conn, $username, $password)
{
	$id = MCVE_TransNew($conn);
	MCVE_TransKeyVal($conn, $id, "username", $username);
	MCVE_TransKeyVal($conn, $id, "password", $password);
	MCVE_TransKeyVal($conn, $id, "action", "chkpwd");
	if (!MCVE_TransSend($conn, $id))
		return -1;
	return $id;
}


function MCVE_ListUsers(&$conn, $password)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", "MADMIN");
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "listusers");
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

function MCVE_DelUser(&$conn, $password, $user)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", "MADMIN");
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "deluser");
	M_TransKeyVal($conn, $id, "user", $user);
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

function MCVE_EnableUser(&$conn, $password, $user)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", "MADMIN");
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "enableuser");
	M_TransKeyVal($conn, $id, "user", $user);
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

function MCVE_DisableUser(&$conn, $password, $user)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", "MADMIN");
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "disableuser");
	M_TransKeyVal($conn, $id, "user", $user);
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

function MCVE_ChngPwd(&$conn, $password, $newpassword)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", "MADMIN");
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "chngpwd");
	M_TransKeyVal($conn, $id, "pwd", $newpassword);
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

function MCVE_Sale(&$conn, $username, $password, $trackdata, $account, $expdate, $amount, $street, $zip, $cv, $comments, $clerkid, $stationid, $ptrannum)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", $username);
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "sale");
	M_TransKeyVal($conn, $id, "trackdata", $trackdata);
	M_TransKeyVal($conn, $id, "account", $account);
	M_TransKeyVal($conn, $id, "expdate", $expdate);
	M_TransKeyVal($conn, $id, "amount", $amount);
	M_TransKeyVal($conn, $id, "street", $street);
	M_TransKeyVal($conn, $id, "zip", $zip);
	M_TransKeyVal($conn, $id, "cv", $cv);
	M_TransKeyVal($conn, $id, "comments", $comments);
	M_TransKeyVal($conn, $id, "clerkid", $clerkid);
	M_TransKeyVal($conn, $id, "stationid", $stationid);
	M_TransKeyVal($conn, $id, "ptrannum", $ptrannum);
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

function MCVE_Preauth(&$conn, $username, $password, $trackdata, $account, $expdate, $amount, $street, $zip, $cv, $comments, $clerkid, $stationid, $ptrannum)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", $username);
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "preauth");
	M_TransKeyVal($conn, $id, "trackdata", $trackdata);
	M_TransKeyVal($conn, $id, "account", $account);
	M_TransKeyVal($conn, $id, "expdate", $expdate);
	M_TransKeyVal($conn, $id, "amount", $amount);
	M_TransKeyVal($conn, $id, "street", $street);
	M_TransKeyVal($conn, $id, "zip", $zip);
	M_TransKeyVal($conn, $id, "cv", $cv);
	M_TransKeyVal($conn, $id, "comments", $comments);
	M_TransKeyVal($conn, $id, "clerkid", $clerkid);
	M_TransKeyVal($conn, $id, "stationid", $stationid);
	M_TransKeyVal($conn, $id, "ptrannum", $ptrannum);
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

function MCVE_Void(&$conn, $username, $password, $ttid, $ptrannum)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", $username);
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "void");
	M_TransKeyVal($conn, $id, "ttid", $ttid);
	M_TransKeyVal($conn, $id, "ptrannum", $ptrannum);
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

function MCVE_PreAuthCompletion(&$conn, $username, $password, $finalamount, $ttid, $ptrannum)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", $username);
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "preauthcomplete");
	M_TransKeyVal($conn, $id, "amount", $finalamount);
	M_TransKeyVal($conn, $id, "ttid", $ttid);
	M_TransKeyVal($conn, $id, "ptrannum", $ptrannum);
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

function MCVE_Force(&$conn, $username, $password, $trackdata, $account, $expdate, $amount, $authcode, $comments, $clerkid, $stationid, $ptrannum)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", $username);
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "force");
	M_TransKeyVal($conn, $id, "trackdata", $trackdata);
	M_TransKeyVal($conn, $id, "account", $account);
	M_TransKeyVal($conn, $id, "expdate", $expdate);
	M_TransKeyVal($conn, $id, "amount", $amount);
	M_TransKeyVal($conn, $id, "apprcode", $authcode);
	M_TransKeyVal($conn, $id, "comments", $comments);
	M_TransKeyVal($conn, $id, "clerkid", $clerkid);
	M_TransKeyVal($conn, $id, "stationid", $stationid);
	M_TransKeyVal($conn, $id, "ptrannum", $ptrannum);
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

function MCVE_Return(&$conn, $username, $password, $trackdata, $account, $expdate, $amount, $comments, $clerkid, $stationid, $ptrannum)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", $username);
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "return");
	M_TransKeyVal($conn, $id, "trackdata", $trackdata);
	M_TransKeyVal($conn, $id, "account", $account);
	M_TransKeyVal($conn, $id, "expdate", $expdate);
	M_TransKeyVal($conn, $id, "amount", $amount);
	M_TransKeyVal($conn, $id, "comments", $comments);
	M_TransKeyVal($conn, $id, "clerkid", $clerkid);
	M_TransKeyVal($conn, $id, "stationid", $stationid);
	M_TransKeyVal($conn, $id, "ptrannum", $ptrannum);
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

function MCVE_Settle(&$conn, $username, $password, $batch)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", $username);
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "settle");
	M_TransKeyVal($conn, $id, "batch", $batch);
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

function MCVE_Bt(&$conn, $username, $password)
{
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "username", $username);
	M_TransKeyVal($conn, $id, "password", $password);
	M_TransKeyVal($conn, $id, "action", "admin");
	M_TransKeyVal($conn, $id, "admin", "bt");
	if (!M_TransSend($conn, $id))
		return -1;
	return $id;
}

?>
