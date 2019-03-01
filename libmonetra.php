<?php
/*
Copyright 2016 Main Street Softworks, Inc. All rights reserved.

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
if (!function_exists("m_initengine")) {

//error_reporting(E_ALL);

define('LIBMONETRA_VERSION', '0.9.7');

define('M_CONN_SSL', 1);
define('M_CONN_IP',  2);

define('M_TRAN_STATUS_NEW', 1);
define('M_TRAN_STATUS_SENT', 2);
define('M_TRAN_STATUS_DONE', 3);

define('M_ERROR', -1);
define('M_FAIL', 0);
define('M_SUCCESS', 1);

define('M_DONE', 2);
define('M_PENDING', 1);

$init_cafile = "";
function M_InitEngine($cafile = "")
{
	global $init_cafile;
	$init_cafile = $cafile;
	return 1;
}

function M_DestroyEngine()
{
	/* Do nothing */
}

function M_InitConn()
{
	global $init_cafile;
	$conn = array();
	$conn['blocking']        = false;
	$conn['conn_error']      = "";
	$conn['conn_timeout']    = 10;
	$conn['host']            = "";
	$conn['last_id']         = 0;
	$conn['method']          = M_CONN_IP;
	$conn['port']            = 0;
	$conn['readbuf']         = null;
	$conn['ssl_cafile']      = $init_cafile;
	$conn['ssl_verify']      = false;
	$conn['ssl_cert']        = null;
	$conn['ssl_key']         = null;

	/* Secure cipher list that prefers forward secrecy and AEAD ciphers.
	 * Roughly taken from https://blog.qualys.com/ssllabs/2013/08/05/configuring-apache-nginx-and-openssl-for-forward-secrecy
	 * without RC4, but added in AES without forward secrecy for legacy servers */
	$conn['ssl_ciphers']     = 'EECDH+ECDSA+AESGCM:EECDH+aRSA+AESGCM:EECDH+ECDSA+SHA384:EECDH+ECDSA+SHA256:EECDH+aRSA+SHA384:EECDH+aRSA+SHA256:EECDH:EDH+aRSA:AES256-GCM-SHA384:AES256-SHA256:AES256-SHA:AES128-SHA:!aNULL:!eNULL:!LOW:!3DES:!RC4:!MD5:!EXP:!PSK:!SRP:!DSS';
	if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
		/* Prefer only TLSv1.1 or TLSv1.2 if the system is capable */
		$conn['ssl_protocols'] = STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT|STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
	} else {
		/* Otherwise SSLv23 does *autonegotiation* to the highest the client and server allow, this may
		 * even be up to TLSv1.2 even though the name doesn't sound like it */
		$conn['ssl_protocols']   = STREAM_CRYPTO_METHOD_SSLv23_CLIENT;
	}
	$conn['timeout']         = 0;
	$conn['tran_array']      = array();
	$conn['verify_conn']     = true;
	$conn['writebuf']        = null;
	return $conn;
}

function M_SetIP(&$conn, $host, $port)
{
	/* Monetra only supports IPv4, but PHP on Windows7 seems to
	 * default to resolving 'localhost' as an IPv6 address and
	 * the connection fails */
	if ($host == "localhost")
		$conn['host'] = "127.0.0.1";
	else
		$conn['host']   = $host;
	$conn['port']   = $port;
	$conn['method'] = M_CONN_IP;
	return true;
}

function M_SetSSL(&$conn, $host, $port)
{
	/* Monetra only supports IPv4, but PHP on Windows7 seems to
	 * default to resolving 'localhost' as an IPv6 address and
	 * the connection fails */
	if ($host == "localhost")
		$conn['host'] = "127.0.0.1";
	else
		$conn['host']   = $host;
	$conn['port']   = $port;
	$conn['method'] = M_CONN_SSL;
	return true;
}

function M_SetDropFile(&$conn, $directory)
{
	/* NOT SUPPORTED! */
	return false;
}

function M_verifyping(&$conn)
{
	$max_ping_time = 5;

	$blocking = $conn['blocking'];
	M_SetBlocking($conn, false);
	$id = M_TransNew($conn);
	M_TransKeyVal($conn, $id, "action", "ping");
	if (!M_TransSend($conn, $id)) {
		M_DeleteTrans($conn, $id);
		return false;
	}
	$lasttime=time();
	while (M_CheckStatus($conn, $id) == M_PENDING && time()-$lasttime <= $max_ping_time) {
		$wait_time_ms = ($max_ping_time - (time() - $lasttime)) * 1000;
		if ($wait_time_ms < 0)
			$wait_time_ms = 0;
		if ($wait_time_ms > $max_ping_time * 1000)
			$wait_time_ms = $max_ping_time * 1000;

		if (!M_Monitor($conn, $wait_time_ms))
			break;
	}
	M_SetBlocking($conn, $blocking);
	$status = M_CheckStatus($conn, $id);
	M_DeleteTrans($conn, $id);
	if ($status != M_DONE)
		return false;
	return true;
}

function M_Connect(&$conn)
{
	$ctx = stream_context_create();

	if ($conn['method'] == M_CONN_SSL) {
		stream_context_set_option($ctx, 'ssl', 'cafile', $conn['ssl_cafile']);
		stream_context_set_option($ctx, 'ssl', 'verify_peer', $conn['ssl_verify']);
		stream_context_set_option($ctx, 'ssl', 'verify_peer_name', $conn['ssl_verify']);
		stream_context_set_option($ctx, 'ssl', 'allow_self_signed', !$conn['ssl_verify']);
		stream_context_set_option($ctx, 'ssl', 'disable_compression', true);
		stream_context_set_option($ctx, 'ssl', 'ciphers', $conn['ssl_ciphers']);
		stream_context_set_option($ctx, 'ssl', 'SNI_enabled', true);
		if ($conn['ssl_cert'] != NULL) {
			stream_context_set_option($ctx, 'ssl', 'local_cert', $conn['ssl_cert']);
		}
		if ($conn['ssl_key'] != NULL) {
			stream_context_set_option($ctx, 'ssl', 'local_pk', $conn['ssl_key']);
		}
	}

	/* Always use TCP, not ssl:// as we need to upgrade it later after we set socket
	 * options for best compatibility */
	$url = "tcp://" . $conn['host'] . ":" . $conn['port'];

	$error = "";
	$errorString = "";
	$conn['fd'] = @stream_socket_client($url, $error, $errorString, $conn['conn_timeout'], STREAM_CLIENT_CONNECT, $ctx);
	if (!$conn['fd']) {
		$conn['conn_error'] = "Failed to connect to $url: $error: $errorString";
		return false;
	}

	/* Use blocking reads, we'll set timeouts later */
	stream_set_blocking($conn['fd'], TRUE);

	/* Disable the nagle algorithm, should reduce latency */
	if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
		$socket = socket_import_stream($conn['fd']);
		socket_set_option($socket, SOL_TCP, TCP_NODELAY, (int)1);
	}

	/* Upgrade the connection to use SSL/TLS.  We need to do this *after* setting Nagle due to
	 * bug https://bugs.php.net/bug.php?id=70939 */
	if (!stream_socket_enable_crypto($conn['fd'], true, $conn['ssl_protocols'])) {
		$conn['conn_error'] = "Failed to negotiate SSL/TLS";
		fclose($conn['fd']);
		$conn['fd'] = false;
		return false;
	}

	if ($conn['verify_conn'] && !M_verifyping($conn)) {
		$conn['conn_error'] = "PING request failed";
		fclose($conn['fd']);
		$conn['fd'] = false;
		return false;
	}
	return true;
}

function M_ConnectionError(&$conn)
{
	return $conn['conn_error'];
}

function M_DestroyConn(&$conn)
{
	if(is_resource($conn['fd']))
		fclose($conn['fd']);
	$conn['fd'] = false;
	unset($conn);
}

function M_MaxConnTimeout(&$conn, $secs)
{
	$conn['conn_timeout'] = $secs;
	return true;
}

function M_SetBlocking(&$conn, $tf)
{
	if ($tf)
		$conn['blocking'] = true;
	else
		$conn['blocking'] = false;

	return true;
}

function M_ValidateIdentifier(&$conn, $tf)
{
	/* Always validated, stub for compatibility */
	return true;
}

function M_VerifyConnection(&$conn, $tf)
{
	if ($tf)
		$conn['verify_conn'] = true;
	else
		$conn['verify_conn'] = false;

	return true;
}

function M_VerifySSLCert(&$conn, $tf)
{
	if ($tf)
		$conn['ssl_verify'] = true;
	else
		$conn['ssl_verify'] = false;

	return true;
}

function M_SetSSL_CAfile(&$conn, $cafile)
{
	$conn['cafile'] = $cafile;
	return true;
}

function M_SetSSL_Files(&$conn, $sslkeyfile, $sslcertfile)
{
	$conn['ssl_cert'] = $sslcertfile;
	$conn['ssl_key']  = $sslkeyfile;
	return true;
}

function M_SetSSL_Ciphers(&$conn, $ciphers)
{
	$conn['ssl_ciphers'] = $ciphers;
	return true;
}

/* One or more crypto_type's as defined in http://php.net/manual/en/function.stream-socket-enable-crypto.php */
function M_SetSSL_Protocols(&$conn, $protocols)
{
	$conn['ssl_protocols'] = $protocols;
	return true;
}

function M_SetTimeout(&$conn, $secs)
{
	$conn['timeout'] = $secs;
	return true;
}

function M_TransNew(&$conn)
{
	$tran = array();
	$tran['id']              = ++$conn['last_id'];
	$tran['status']          = M_TRAN_STATUS_NEW;
	$tran['comma_delimited'] = false;
	$tran['in_params']       = array();
	$tran['out_params']      = array();
	$tran['raw_response']    = null;
	$tran['csv']             = null;
	$conn['tran_array'][$tran['id']] = &$tran;
	return $tran['id'];
}

function &M_findtranbyid(&$conn, $id)
{
	if (!isset($conn['tran_array'][$id])) {
		$error = false;
		return $error;
	}
	return $conn['tran_array'][$id];
}

function M_TransKeyVal(&$conn, $id, $key, $val)
{
	$tran =& M_findtranbyid($conn, $id);
	/* Invalid ptr, or transaction has already been sent out */
	if ($tran === false || $tran['status'] != M_TRAN_STATUS_NEW)
		return false;

	$tran['in_params'][$key] = $val;

	return true;
}

function M_TransBinaryKeyVal(&$conn, $id, $key, $val, $val_len)
{
	return M_TransKeyVal($conn, $id, $key, base64_encode($val));
}

function M_TransSend(&$conn, $id)
{
	$tran =& M_findtranbyid($conn, $id);
	/* Invalid ptr, or transaction has already been sent out */
	if ($tran === false || $tran['status'] != M_TRAN_STATUS_NEW)
		return false;

	$tran['status'] = M_TRAN_STATUS_SENT;
	/* Structure Transaction */

	/* STX, identifier, FS */
	$tran_str = "\x02" . $tran['id'] . "\x1c";

	/* PING is specially formed */
	if (isset($tran['in_params']['action']) &&
	    strcasecmp($tran['in_params']['action'], "ping") == 0) {
		$tran_str .= "PING";
	} else {
		/* Each key/value pair in array as key="value" */
		foreach ($tran['in_params'] as $key => $value) {
			$tran_str .= $key . '="' . str_replace('"', '""', $value) . '"' . "\r\n";
		}
		/* Add timeout if necessary */
		if ($conn['timeout'] != 0) {
			$tran_str .= 'timeout="' . $conn['timeout'] . '"' . "\r\n";
		}
	}

	/* ETX */
	$tran_str .= "\x03";

	$conn['writebuf'] .= $tran_str;

	if ($conn['blocking']) {
		while (M_CheckStatus($conn, $id) == M_PENDING) {
			if (!M_Monitor($conn, -1))
				return false;
		}
	}
	return true;
}

function M_CheckStatus(&$conn, $id)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false ||
            ($tran['status'] != M_TRAN_STATUS_SENT && $tran['status'] != M_TRAN_STATUS_DONE))
		return M_ERROR;

	if ($tran['status'] == M_TRAN_STATUS_SENT)
		return M_PENDING;

	return M_DONE;
}

function M_CompleteAuthorizations(&$conn, &$id_array)
{
	$id_array = array();
	foreach ($conn['tran_array'] as $id => $val) {
		if ($val['status'] == M_TRAN_STATUS_DONE)
			$id_array[] = $id;
	}
	return count($id_array);
}

function M_DeleteTrans(&$conn, $id)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false)
		return false;
	unset($conn['tran_array'][$id]);
	return true;
}

function M_verify_comma_delimited($data)
{
	for ($i=0; $i<strlen($data); $i++) {
		/* If hit a new line or a comma before an equal sign, must
		 * be comma delimited */
		if (ord($data[$i]) == 0x0A ||
		    ord($data[$i]) == 0x0D ||
		    $data[$i] == ',')
			return true;
		/* If hit an equal sign before a new line or a comma, must be
		 * key/val */
		if ($data[$i] == '=')
			return false;
	}
	/* Who knows?  Should never get here */
	return true;
}

function M_explode_quoted($delim, $data, $quote_char, $max_sects)
{
	if ($data === null || strlen($data) === 0) {
		return null;
	}

	$data_length = strlen($data);
	$num_sects = 1;
	$on_quote = false;

	/* We need to first count how many lines we have */
	for ($i=0; $i<$data_length && ($max_sects == 0 || $num_sects < $max_sects); $i++) {
		if ($quote_char !== 0 && $data[$i] === $quote_char) {
			/* Doubling the quote char acts as escaping if in a quoted string */
			if ($on_quote && $data_length - $i > 1 && $data[$i+1] === $quote_char) {
				$i++;
				continue;
			} elseif ($on_quote) {
				$on_quote = false;
			} else {
				$on_quote = true;
			}
		}
		if ($data[$i] == $delim && !$on_quote) {
			$num_sects++;
		}
	}
	
	$ret = array();
	$beginsect = 0;
	$cnt = 1;
	$on_quote = false;
	for ($i=0; $i<$data_length && $cnt < $num_sects; $i++) {
		if ($quote_char !== 0 && $data[$i] === $quote_char) {
			/* Doubling the quote char acts as escaping */
			if ($on_quote && $data_length - $i > 1 && $data[$i+1] === $quote_char) {
				$i++;
				continue;
			} elseif ($on_quote) {
				$on_quote = false;
			} else {
				$on_quote = true;
			}
		}
		if ($data[$i] == $delim && !$on_quote) {
			$ret[$cnt-1] = substr($data, $beginsect, $i - $beginsect);
			$beginsect  = $i + 1;
			$cnt++;
		}
	}
	/* Capture last segment */
	$ret[$cnt-1] = substr($data, $beginsect, $data_length - $beginsect);

	return $ret;
}

function M_remove_dupe_quotes($data)
{

	$i = 0;
	$len = strlen($data);

	/* No quotes */
	if (strpos($data, '"') === false)
		return $data;

	/* Surrounding quotes, remove */
	if ($data[0] == '"' && $data[$len-1] == '"') {
			$i += 1;
			$len -= 1;
		}

	$ret = "";
	for (; $i<$len; $i++) {
		if ($data[$i] == '"' && $i < $len - 1 && $data[$i+1] == '"') {
			$ret .= '"';
			$i++;
		} else if ($data[$i] != '"') {
			$ret .= $data[$i];
		}
	}
	return $ret;
}

/*! -1 = wait indefinitely/block, 0 = do not wait at all, return immediately if no bytes, >0 number of ms to wait */
function M_socket_set_timeout(&$conn, $timeout_ms)
{
	if ($timeout_ms == -1) {
		$sec  = 999;
		$usec = 0;
	} else {
		$sec  = $timeout_ms / 1000;
		$usec = ($timeout_ms % 1000) * 1000;
	}
	stream_set_timeout($conn['fd'], $sec, $usec);
	//socket_set_option($conn['fd'], SOL_SOCKET, SO_RCVTIMEO, array('sec'=>$sec, 'usec'=>$usec));
}

function M_Monitor_read(&$conn, $timeout_ms=-1)
{
	if (!$conn['fd'])
		return false;

	M_socket_set_timeout($conn, $timeout_ms);
	/* NOTE: stream_set_timeout() doesn't appear to actually work on SSL,
	 *       lots of bugs reported on this */
	if ($conn['method'] == M_CONN_SSL && $timeout_ms != -1) {
		stream_set_blocking($conn['fd'], FALSE);
	}

	/* Read Data */
	$data_read = false;
	while (1) {
		$buf = fread($conn['fd'], 8192);
		$info = stream_get_meta_data($conn['fd']);
		if ($buf === false || $info['eof']) {
			fclose($conn['fd']);
			$conn['fd'] = false;
			$conn['conn_error'] = "fread failure";
			return false;
		}

		if ($info['timed_out'] || strlen($buf) == 0) {
			/* No data, read.  We're non-blocking, remember? */
			break;
		}

		$conn['readbuf'] .= $buf;
		$data_read        = true;

		/* Only loop if buffer was full on last read */
		if (strlen($buf) < 8192)
			break;

		/* Make timeout 0 (or non-blocking for SSL) incase there are no more bytes */
		M_socket_set_timeout($conn, 0);
		if ($conn['method'] == M_CONN_SSL) {
			stream_set_blocking($conn['fd'], FALSE);
		}
	}

	if ($conn['method'] == M_CONN_SSL) {
		stream_set_blocking($conn['fd'], TRUE);

		/* With SSL, let's not loop too fast since we're using
		 * non-blocking reads */
		if (!$data_read && $timeout_ms != 0)
			M_uwait(20000);
	}

	return true;
}

function M_Monitor_write(&$conn)
{
	if (!$conn['fd'])
		return false;

	/* Write Data */
	if (strlen($conn['writebuf'])) {
		$retval = fwrite($conn['fd'], $conn['writebuf'], strlen($conn['writebuf']));
		if ($retval === false) {
			fclose($conn['fd']);
			$conn['fd'] = false;
			$conn['conn_error'] = "fwrite failure";
			return false;
		} else if ($retval == strlen($conn['writebuf'])) {
			$conn['writebuf'] = null;
		} else {
			$oldlen = strlen($conn['writebuf']);
			$conn['writebuf'] = substr($conn['writebuf'], $retval, $oldlen-$retval);
		}
	}

	return true;
}

function M_Monitor_parse(&$conn)
{
	/* Parse */
	while(strlen($conn['readbuf'])) {
		if ($conn['readbuf'][0] != chr(0x02)) {
			fclose($conn['fd']);
			$conn['fd'] = false;
			$conn['conn_error'] = "protocol error, responses must start with STX";
			return false;
		}
		$etx = strpos($conn['readbuf'], chr(0x03));
		if ($etx === false) {
			/* Not enough data */
			break;
		}

		/* Chop off txn from readbuf */
		$readbuf_len = strlen($conn['readbuf']);
		$txndata = substr($conn['readbuf'], 0, $etx);
		if ($etx+1 == $readbuf_len) {
			$conn['readbuf'] = null;
		} else {
			$temp = substr($conn['readbuf'], $etx+1, $readbuf_len-($etx+1));
			$conn['readbuf'] = $temp;
		}

		$fs = strpos($txndata, chr(0x1c));
		if ($fs === false) {
			fclose($conn['fd']);
			$conn['fd'] = false;
			$conn['conn_error'] = "protocol error, responses must contain a FS";
			return false;
		}

		$id = substr($txndata, 1, $fs - 1);
		$data = substr($txndata, $fs+1, strlen($txndata)-$fs-1);

		$txn = &M_findtranbyid($conn, intval($id));
		if ($txn === false) {
			echo "Unrecognized identifier in response: '$id'\n";
			/* Discarding data */
			continue;
		}

		$txn['raw_response']    = $data;
		$txn['comma_delimited'] = M_verify_comma_delimited($data);
		if (!$txn['comma_delimited']) {
			$lines = M_explode_quoted("\n", $data, '"', 0);

			if ($lines === false || count($lines) == 0) {
				fclose($conn['fd']);
				$conn['fd'] = false;
				$conn['conn_error'] = "protocol error, no lines in response";
				return false;
			}

			for ($i=0; $i<count($lines); $i++) {
				$lines[$i] = trim($lines[$i]);
				if (!strlen($lines[$i]))
					continue;

				$keyval = explode("=", $lines[$i], 2);
				if ($keyval === false || count($keyval) != 2)
					continue;

				if ($keyval[0] == null || !strlen($keyval[0]))
					continue;

				/* Array key needs to be lowercase as it is case-sensitive in php */
				$txn['out_params'][strtolower($keyval[0])] = M_remove_dupe_quotes(trim($keyval[1]));
			}
			$tran['raw_response'] = NULL; /* Free memory */
		}
		$txn['status']          = M_TRAN_STATUS_DONE;
	}

	return true;
}

function M_Monitor(&$conn, $timeout_ms = 0)
{
	if (!M_Monitor_write($conn))
		return false;

	if (!M_Monitor_read($conn, $timeout_ms))
		return false;

	if (!M_Monitor_parse($conn))
		return false;

	return true;
}

function M_TransInQueue(&$conn)
{
	return count($conn['tran_array']);
}

function M_TransactionsSent(&$conn)
{
	if (strlen($conn['writebuf']))
		return false;
	return true;
}

function M_GetCell(&$conn, $id, $col, $row)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false)
		return false;

	/* Invalid ptr, or transaction has not returned */
	if ($tran['status'] != M_TRAN_STATUS_DONE)
		return false;
	if (!$tran['comma_delimited'])
		return false;
	if (!isset($tran['csv'][$row+1]))
		return false;

	$colname = strtolower($col);
	if (!isset($tran['csv_header_idx'][$colname]))
		return false;

	$idx = $tran['csv_header_idx'][$colname];
	return $tran['csv'][$row+1][$idx];
}

function M_GetBinaryCell(&$conn, $id, $col, $row, &$outlen)
{
	$outlen = 0;
	$out = null;
	$cell = M_GetCell($conn, $id, $col, $row);
	if ($cell)
		$out = base64_decode($cell);
	if ($out)
		$outlen = strlen($out);
	return $out;
}

function M_GetCellByNum(&$conn, $id, $col, $row)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false)
		return false;

	/* Invalid ptr, or transaction has not returned */
	if ($tran['status'] != M_TRAN_STATUS_DONE)
		return false;
	if (!$tran['comma_delimited'])
		return false;
	if (!isset($tran['csv'][$row+1][$col]))
		return false;
	return $tran['csv'][$row+1][$col];
}

function M_GetCommaDelimited(&$conn, $id)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false)
		return false;

	/* Invalid ptr, or transaction has not returned */
	if ($tran['status'] != M_TRAN_STATUS_DONE)
		return false;
	return $tran['raw_response'];
}

function M_GetHeader(&$conn, $id, $col)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false)
		return false;

	/* Invalid ptr, or transaction has not returned */
	if ($tran['status'] != M_TRAN_STATUS_DONE)
		return false;
	if (!$tran['comma_delimited'])
		return false;
	if (!isset($tran['csv'][0][$col]))
		return false;
	return $tran['csv'][0][$col];
}

function M_IsCommaDelimited(&$conn, $id)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false)
		return false;

	/* Invalid ptr, or transaction has not returned */
	if ($tran['status'] != M_TRAN_STATUS_DONE)
		return false;
	return $tran['comma_delimited'];
}

function M_NumColumns(&$conn, $id)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false)
		return false;

	/* Invalid ptr, or transaction has not returned */
	if ($tran['status'] != M_TRAN_STATUS_DONE)
		return false;
	if (!$tran['comma_delimited'])
		return false;
	return count($tran['csv'][0]);
}

function M_NumRows(&$conn, $id)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false)
		return false;

	/* Invalid ptr, or transaction has not returned */
	if ($tran['status'] != M_TRAN_STATUS_DONE)
		return false;
	if (!$tran['comma_delimited'])
		return false;
	return count($tran['csv'])-1;
}

/**
* Create a 2D array from a CSV string
*
* @param mixed $data 2D array
* @param string $delimiter Field delimiter
* @param string $enclosure quote character
* @return
*/
function M_parsecsv($data, $delimiter = ',', $enclosure = '"')
{
	$lines = M_explode_quoted("\n", $data, $enclosure, 0);
	$csv = array();

	/* Strip any trailing blank lines */
	for ($line_cnt = count($lines); $line_cnt > 0 && strlen($lines[$line_cnt-1]) == 0; $line_cnt--) {
		/* Do nothing */
	}
	
	for ($i = 0; $i<$line_cnt; $i++) {
		$cells     = M_explode_quoted($delimiter, $lines[$i], $enclosure, 0);
		$lines[$i] = NULL; /* Free memory sooner */
		for ($j = 0; $j<count($cells); $j++) {
			$csv[$i][$j] = M_remove_dupe_quotes(trim($cells[$j]));
			$cells[$j]   = NULL; /* Free memory sooner */
		}
	}

	return $csv;
}

function M_ParseCommaDelimited(&$conn, $id)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false)
		return false;

	/* Invalid ptr, or transaction has not returned */
	if ($tran['status'] != M_TRAN_STATUS_DONE)
		return false;

	$tran['csv']          = M_parsecsv($tran['raw_response'], ",", '"');
	$tran['raw_response'] = NULL; /* Free memory */

	/* Convert headers into a hash lookup table */
	for ($i=0; $i<count($tran['csv'][0]); $i++) {
		$tran['csv_header_idx'][strtolower($tran['csv'][0][$i])] = $i;
	}
	return true;
}

function M_ResponseKeys(&$conn, $id)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false)
		return false;

	/* Invalid ptr, or transaction has not returned */
	if ($tran['status'] != M_TRAN_STATUS_DONE)
		return false;

	$ret = array();
	foreach ($tran['out_params'] as $key => $value)
		$ret[] = $key;

	return $ret;
}

function M_ResponseParam(&$conn, $id, $key)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false)
		return false;

	/* Invalid ptr, or transaction has not returned */
	if ($tran['status'] != M_TRAN_STATUS_DONE)
		return false;

	/* Need this lowercase as array keys are case-sensitive in PHP */
	$mykey = strtolower($key);

	if (!isset($tran['out_params'][$mykey]))
		return false;

	return $tran['out_params'][$mykey];
}

function M_ReturnStatus(&$conn, $id)
{
	$tran =& M_findtranbyid($conn, $id);
	if ($tran === false)
		return false;

	/* Invalid ptr, or transaction has not returned */
	if ($tran['status'] != M_TRAN_STATUS_DONE)
		return M_ERROR;

	if ($tran['comma_delimited'] == true)
		return M_SUCCESS;

	$code = M_ResponseParam($conn, $id, "code");
	if (strcasecmp($code, "AUTH") == 0 || strcasecmp($code, "SUCCESS") == 0)
		return M_SUCCESS;

	return M_FAIL;
}

function M_uwait($usec)
{
	usleep($usec);
	return true;
}

} /* function_exists("m_initengine") */
?>
