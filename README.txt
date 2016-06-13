LIBMONETRA PHP 0.9.6

ChangeLog
=========

* 0.9.0 Initial Release
* 0.9.1 M_InitEngine() should return 1 or 0 instead of nothing
* 0.9.2 Simplify CSV parse code
* 0.9.3 Win7 defaults to IPv6, lets hardcode 'localhost' to IPv4
          127.0.0.1 to prevent issues.
        Prevent warning output from M_ResponseParam()
        Properly detect IP disconnects and don't allow PHP to emit
          its own warnings
        Reading large responses via SSL didn't work because of limitations
          in PHP's select() statement.  Move to non-blocking reads to
          work around this limitation.
        Additional sanity checks for response parsing to prevent PHP
          from emitting warnings.
* 0.9.4 Only use non-blocking reads for SSL.  Attempt to be smarter
        about when dead waits have to be used for SSL.  No dead waits
        for IP at all now, can't do the same for SSL because 
        stream_set_timeout() doesn't work for SSL.
* 0.9.5 Additional legacy function implementation for existing
        customer scripts.
* 0.9.6 For large reports, its better to free memory as early as possible to
          make sure we do not hit the php limit.
        Disable the nagle algorithm to lower latency.

Notes
=====

This is a reimplementation of the php_mcve (PECL module) in pure
PHP and designed as a full drop-in replacement. 

It does not have any dependencies outside of what PHP natively
provides making this deployable across all systems supported
by PHP.

Simply include 'libmonetra.php', or if using legacy functions,
'libmonetra_legacy.php' into any page which requires use of
libmonetra functions.  
