<?php
	error_reporting(E_ALL);
	require_once("libmonetra.php");
	$MYHOST="testbox.monetra.com";
	$MYPORT=8665;
	$MYUSER="test_ecomm:public";
	$MYPASS="publ1ct3st";
	$MYMETHOD="SSL";
	$MYCAFILE=NULL;
	$MYVERIFYSSL=0;

	/* Initialize Engine */
	if (!M_InitEngine(NULL)) {
		echo "Failed to initialize libmonetra\r\n";
		return;
	}
	/* Initialize Connection */
	$conn = M_InitConn();
	if ($conn === FALSE) {
		echo "Failed to initialize connection resource\r\n";
		return;
	}
	if (strcasecmp($MYMETHOD, "SSL") == 0) {
		/* Set up SSL Connection Location */
		if (!M_SetSSL($conn, $MYHOST, $MYPORT)) {
			echo "Could not set method to SSL\r\n";
			/* Free memory associated with conn */
			M_DestroyConn($conn);
			return;
		}
		/* Set up information required to verify certificates */
		if ($MYVERIFYSSL) {
			if (!M_SetSSL_CAfile($conn, $MYCAFILE)) {
				echo "Could not set SSL CAFile. " . "Does the file exist?\r\n";
				M_DestroyConn($conn);
				return;
			}
			M_VerifySSLCert($conn, 1);
		}
	} else if (strcasecmp($MYMETHOD, "IP") == 0) {
		/* Set up IP Connection Location */
		if (!M_SetIP($conn, $MYHOST, $MYPORT)) {
			echo "Could not set method to IP\r\n";
			/* Free memory associated with conn */
			M_DestroyConn($conn);
			return;
		}
	} else {
		echo "Invalid method '" . $MYMETHOD . "' specified!\r\n";
		/* Free memory associated with conn */
		M_DestroyConn($conn);
		return;
	}

	/* Set to blocking mode, means we do not have to
	 * do a M_Monitor() loop, M_TransSend() will do this for us */
	if (!M_SetBlocking($conn, 1)) {
		echo "Could not set non-blocking mode\r\n";
		/* Free memory associated with conn */
		M_DestroyConn($conn);
		return;
	}

	/* Set a timeout to be appended to each transaction
	 * sent to Monetra */
	if (!M_SetTimeout($conn, 30)) {
		echo "Could not set timeout\r\n";
		/* Free memory associated with conn */
		M_DestroyConn($conn);
		return;
	}

	echo "Connecting to $MYHOST:$MYPORT using $MYMETHOD...";

	/* Connect to Monetra */
	if (!M_Connect($conn)) {
		echo "Connection failed: " . M_ConnectionError($conn) . "\r\n";
		/* Free memory associated with conn */
		M_DestroyConn($conn); // free memory
		return;
	}

	echo "connected\r\n";

	/* Allocate new transaction */
	$identifier=M_TransNew($conn);

	/* User credentials */
	M_TransKeyVal($conn, $identifier, "username", $MYUSER);
	M_TransKeyVal($conn, $identifier, "password", $MYPASS);

	/* Transaction Type */
	M_TransKeyVal($conn, $identifier, "action", "admin");
	M_TransKeyVal($conn, $identifier, "admin", "GUT");

	echo "Sending Unsettled report request...\r\n";

	/* Additional Auditing parameters may be specified
	 * Please consult the Monetra Client Interface Protocol */
	if (!M_TransSend($conn, $identifier)) {
		echo "Communication Error: " . M_ConnectionError($conn) . "\r\n";
		/* Free memory associated with conn */
		M_DestroyConn($conn);
		return;
	}

	echo "Response received\r\n";

	/* We do not have to perform the M_Monitor() loop
	 * because we are in blocking mode */
	if (M_ReturnStatus($conn, $identifier) != M_SUCCESS) {
		echo "Audit failed\r\n";
		M_DestroyConn($conn);
		return;
	}

	if (!M_IsCommaDelimited($conn, $identifier)) {
		echo "Not a comma delimited response!\r\n";
		M_DestroyConn($conn);
		return;
	}

	/* Print the raw, unparsed data */
	echo "Raw Data:\r\n" . M_GetCommaDelimited($conn, $identifier) . "\r\n";

	/* Tell the API to parse the Data */
	if (!M_ParseCommaDelimited($conn, $identifier)) {
		echo "Parsing comma delimited data failed";
		M_DestroyConn($conn);
		return;
	}

	/* Retrieve each number of rows/columns */
	$rows=M_NumRows($conn, $identifier);
	$columns=M_NumColumns($conn, $identifier);

	/* Print all the headers separated by |'s */
	for ($i=0; $i<$columns; $i++) {
		if ($i != 0) echo "|";
		echo M_GetHeader($conn, $identifier, $i);
	}
	echo "\r\n";

	/* Print one row per line, each cell separated by |'s */
	for ($j=0; $j<$rows; $j++) {
		for ($i=0; $i<$columns; $i++) {
			if ($i != 0) echo "|";
			echo M_GetCellByNum($conn, $identifier, $i, $j);
		}
		echo "\r\n";
	}

	/*
	 * Use M_GetCell instead of M_GetCellByNum if you need a
	 * specific column, as the results will allow for position-
	 * independent searching of the results. The ordering of
	 * returned headers may be different between Monetra versions,
	 * so that is _highly_ recommended */

	/* Optionally free transaction, though M_DestroyConn() will
	 * do this for us */
	M_DeleteTrans($conn, $identifier);

	/* Clean up and close */
	M_DestroyConn($conn);
	M_DestroyEngine();
?>

