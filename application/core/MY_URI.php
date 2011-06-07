<?php
class MY_URI extends CI_URI
{
	/**
	 *  To make this override work I had to change the _detect_uri() function
     *  in the core URI class protected instead of private
     */
	function _fetch_uri_string()
	{
		if (strtoupper($this->config->item('uri_protocol')) == 'AUTO')
		{
			
			/*
			We handle our own CLI stuff with the /cli/CliBootstrap so CI and bugger off             

			// Arguments exist, it must be a command line request
			if ( ! empty($_SERVER['argv']))
			{
				$this->uri_string = $this->_parse_cli_args();
				return;
			}
			*/

			// Let's try the REQUEST_URI first, this will work in most situations
			if ($uri = $this->_detect_uri())
			{
				$this->uri_string = $uri;
				return;
			}

			// Is there a PATH_INFO variable?
			// Note: some servers seem to have trouble with getenv() so we'll test it two ways
			$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
			if (trim($path, '/') != '' && $path != "/".SELF)
			{
				$this->uri_string = $path;
				return;
			}

			// No PATH_INFO?... What about QUERY_STRING?
			$path =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
			if (trim($path, '/') != '')
			{
				$this->uri_string = $path;
				return;
			}

			// As a last ditch effort lets try using the $_GET array
			if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '')
			{
				$this->uri_string = key($_GET);
				return;
			}

			// We've exhausted all our options...
			$this->uri_string = '';
		}
		else
		{
			$uri = strtoupper($this->config->item('uri_protocol'));

			if ($uri == 'REQUEST_URI')
			{
				$this->uri_string = $this->_detect_uri();
				return;
			}
			elseif ($uri == 'CLI')
			{
				$this->uri_string = $this->_parse_cli_args();
				return;
			}

			$this->uri_string = (isset($_SERVER[$uri])) ? $_SERVER[$uri] : @getenv($uri);
		}

		// If the URI contains only a slash we'll kill it
		if ($this->uri_string == '/')
		{
			$this->uri_string = '';
		}
	}
}
