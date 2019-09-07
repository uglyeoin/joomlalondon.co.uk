<?php
/**
 * ReDJ Enterprise plugin for Joomla
 *
 * @author selfget.com (info@selfget.com)
 * @package ReDJ
 * @copyright Copyright 2009 - 2017
 * @license GNU Public License
 * @link http://www.selfget.com
 * @version 1.9.0
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

defined("VARCHAR_SIZE") or define("VARCHAR_SIZE", 255);
defined("PLACEHOLDERS_MATCH_MASK") or define("PLACEHOLDERS_MATCH_MASK", '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/');
defined("PLACEHOLDERS_MATCH_ALL_MASK") or define("PLACEHOLDERS_MATCH_ALL_MASK", '/\$\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/U');

class plgSystemReDJ extends JPlugin
{
  public function __construct(& $subject, $config)
  {
    parent::__construct($subject, $config);
    $this->loadLanguage();

    $visited_request_uri = self::getRequestURI();
    $this->staticParams('visited_request_uri', $visited_request_uri);
    $this->staticParams('visited_full_url', self::getPrefix() . $visited_request_uri); // Better then self::getURL(); or self::getPrefix() . self::getRequestURI();
    $this->staticParams('siteurl', self::getSiteURL());
    $this->staticParams('baseonly', self::getBase(true));
    $this->staticParams('self', self::getSelf());
    $this->staticParams('error_only_matching_rule', 0);
    $this->staticParams('tourl', '');
    $this->staticParams('redirect', 301);
  }

  /**
   *
   * Build and return the (called) prefix (e.g. http://www.youdomain.com) from the current server variables
   *
   * We say 'called' 'cause we use HTTP_HOST (taken from client header) and not SERVER_NAME (taken from server config)
   *
   */
  private static function getPrefix()
  {
    if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
      $https = 's://';
    } else {
      $https = '://';
    }
    return 'http' . $https . $_SERVER['HTTP_HOST'];
  }

  /**
   *
   * Build and return the (called) base path for site (e.g. http://www.youdomain.com/path/to/site)
   *
   * @param  boolean  If true returns only the path part (e.g. /path/to/site)
   *
   */
  private static function getBase($pathonly = false)
  {
    if ( (strpos(php_sapi_name(), 'cgi') !== false) && (!ini_get('cgi.fix_pathinfo')) && (strlen($_SERVER['REQUEST_URI']) > 0) ) {
      // PHP-CGI on Apache with "cgi.fix_pathinfo = 0"

      // We use PHP_SELF
      if (@strlen(trim($_SERVER['PATH_INFO'])) > 0) {
        $p = strrpos($_SERVER['PHP_SELF'], $_SERVER['PATH_INFO']);
        if ($p !== false) { $s = substr($_SERVER['PHP_SELF'], 0, $p); }
      } else {
        $p = $_SERVER['PHP_SELF'];
      }
      $base_path = trim(rtrim(dirname(str_replace(array('"', '<', '>', "'"), '', $p)), '/\\'));
      // Check if base path was correctly detected, or use another method
      /*
         On some Apache servers (mainly using cgi-fcgi) it happens that the base path is not correctly detected.
         For URLs like http://www.site.com/index.php/content/view/123/5 the server returns a wrong PHP_SELF variable.

         WRONG:
         [REQUEST_URI] => /index.php/content/view/123/5
         [PHP_SELF] => /content/view/123/5

         CORRECT:
         [REQUEST_URI] => /index.php/content/view/123/5
         [PHP_SELF] => /index.php/content/view/123/5

         And this lead to a wrong result for JUri::base function.

         WRONG:
         JUri::base(true) => /content/view/123
         JUri::base(false) => http://www.site.com/content/view/123/

         CORRECT:
         getBase(true) =>
         getBase(false):http://www.site.com/
      */
      if (strlen($base_path) > 0) {
        if (strpos($_SERVER['REQUEST_URI'], $base_path) !== 0) {
          $base_path = trim(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
        }
      }
    } else {
      // We use SCRIPT_NAME
      $base_path = trim(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
    }

    return $pathonly === false ? self::getPrefix() . $base_path . '/' : $base_path;
  }

  /**
   *
   * Build and return the REQUEST_URI (e.g. /site/index.php?id=1&page=3)
   *
   */
  private static function getRequestURI($redirect_mode = 0)
  {
    if ( ($redirect_mode === 1) && ( (isset($_SERVER['REDIRECT_URL'])) || (isset($_SERVER['HTTP_X_REWRITE_URL'])) ) ) {
      $uri = (isset($_SERVER['HTTP_X_REWRITE_URL'])) ? $_SERVER['HTTP_X_REWRITE_URL'] : $_SERVER['REDIRECT_URL'];
    } else {
      $uri = $_SERVER['REQUEST_URI'];
    }
    return $uri;
  }

  /**
   *
   * Build and return the (called) {siteurl} macro value
   *
   */
  private static function getSiteURL()
  {
    $siteurl = str_replace( 'https://', '', self::getBase() );
    return rtrim(str_replace('http://', '', $siteurl), '/');
  }

  /**
   *
   * Build and return the (called) full URL (e.g. http://www.youdomain.com/site/index.php?id=12) from the current server variables
   *
   */
  private static function getURL($redirect_mode = 0)
  {
    return self::getPrefix() . self::getRequestURI($redirect_mode);
  }

  /**
   *
   * Return the host name from the given address
   *
   * Reference http://www.php.net/manual/en/function.parse-url.php#93983
   *
   */
  private static function getHost($address)
  {
    $parsedUrl = parse_url(trim($address));
    return @trim($parsedUrl['host'] ? $parsedUrl['host'] : array_shift(explode('/', $parsedUrl['path'], 2)));
  }

  /**
   *
   * Build and return the (called) {self} macro value
   *
   */
  private static function getSelf()
  {
    return $_SERVER['HTTP_HOST'];
  }

  /**
   *
   * Store and return static params
   *
   */
  static function staticParams($name, $value = NULL)
  {
    static $params = array();

    if (isset($name))
    {
      if (isset($value))
      {
        $params[$name] = $value;
      } else {
        if (array_key_exists($name, $params))
        {
          return $params[$name];
        }
      }
    }
    return NULL;
  }

  /**
   *
   * Rebuild an URL from the parsed array
   *
   * @param  array   $parsed_url  Array of URL parts
   *
   * @return  string  The result URL
   *
   */
  private static function unparse_url($parsed_url)
  {
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
  }

  /**
   *
   * Remove specified vars from URL query
   *
   * @param  string  $url   The URL
   * @param  array   $vars  List of variables to drop
   *
   * @return  string  The result URL
   *
   */
  private static function drop_query_vars($url, $vars)
  {
    $parsed = parse_url($url);
    if (isset($parsed['query']))
    {
      parse_str($parsed['query'], $query_vars);
      $query_vars = array_diff_key($query_vars, $vars);
      $parsed['query'] = http_build_query($query_vars, '', '&');
      if (strlen($parsed['query']) == 0) { unset($parsed['query']);}
      return self::unparse_url($parsed);
    }
    return $url;
  }

  /**
   *
   * Return the routed (relative) URL
   *
   * @param  string  $url   Visited full URL
   * @param  array   $vars  List of variables to use (if empty use all current variables)
   *
   * @return  string  The routed (relative) URL
   *
   */
  private static function route_url($url, $vars)
  {
    static $routed_urls = array();

    $checksum = @md5(json_encode($vars));
    if (isset($routed_urls[$url][$checksum]))
    {
      return $routed_urls[$url][$checksum];
    }

    JUri::reset();
    $uri_visited_full_url = JUri::getInstance($url);
    $router = JSite::getRouter();
    $parsed = $router->parse($uri_visited_full_url);
    $suffix = isset($parsed['format']) ? $parsed['format'] : '';
    $drop_vars = array();
    if (@count($vars)) // True if $vars is an array and with at least one element
    {
      // First drop unwanted vars
      foreach ($vars as $k => $v)
      {
        if (preg_match('/^!(.*)/', $k, $m) === 1)
        {
          $drop_vars[$m[1]] = $v;
          if (isset($parsed[$m[1]])) { unset($parsed[$m[1]]); }
          unset($vars[$k]);
        }
      }
      if (@count($vars)) // True if there is still at least one element
      {
        foreach ($vars as $k => $v)
        {
          if ($v === NULL)
          {
            if (isset($parsed[$k]))
            {
              $vars[$k] = $parsed[$k];
            } else {
              unset($vars[$k]);
            }
          }
        }
        $p = $vars;
      } else {
        $p = $parsed;
      }
    } else {
      // Variables to use/not use are not specified
      foreach ($parsed as $k => $v)
      {
        if ($v === NULL)
        {
          unset($parsed[$k]);
        }
      }
      $p = $parsed;
    }
    // Now $p contains the variable to use for routing
    if (isset($p['option']))
    {
      // For components search for a menu item
      $q = $p;
      unset($q['Itemid']);
      unset($q['format']);
      foreach ($q as $k => $v)
      {
        // Remove the slug part if present
        $parts = explode(":", $v);
        $q[$k] = $parts[0];
      }
      // Build the URL to route
      $link = 'index.php';
      foreach ($q as $k => $v)
      {
        $link .= (($link === 'index.php') ? '?' : '&') . $k . '=' . $v;
      }
      $routed_link = JRoute::_($link, false);
      $app = JFactory::getApplication();
      $menu = $app->getMenu();
      $items = $menu->getItems('component', $q['option']);
      $found = false;
      $menu_route = '';
      foreach ($items as $k => $v)
      {
        $current_link = (JRoute::_($v->link, false));
        if ($current_link == $routed_link)
        {
          $found = true;
          $menu_route = $v->route;
          break;
        }
      }
      if ($found)
      {
        $sef_prefix = ($app->getCfg('sef_rewrite')) ? '' : 'index.php/';
        $routed = '/' . $sef_prefix . trim($menu_route, '/') . ((strlen($suffix) > 0) ? '.' . $suffix : '');
        $routed_urls[$url][$checksum] = $routed;
        return $routed;
      }
    }
    // Build the URL to route
    $build = 'index.php';
    foreach ($p as $pk => $pv)
    {
      $build .= (($build === 'index.php') ? '?' : '&') . $pk . '=' . $pv;
    }
    $routed = self::drop_query_vars(JRoute::_($build, false), $drop_vars);
    $routed_urls[$url][$checksum] = $routed;
    return $routed;
  }

  /**
   *
   * Alternative to mb_substr (used when this is not available)
   *
   * @param  string   The input string
   * @param  integer  The starting index
   * @param  integer  The lenght
   *
   * @return  string  The UTF-8 substring
   *
   */
  private static function substru($str, $from, $len){
    return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'. $from .'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'. $len .'}).*#s','$1', $str);
  }

  /**
   *
   * Truncate the URL if its lenght exceed the max number of characters (UTF-8) in the varchar definition
   *
   * @param  string  The URL to sanitize
   *
   */
  private static function sanitizeURL($url)
  {
    if (function_exists('mb_substr'))
    {
      return mb_substr($url, 0, VARCHAR_SIZE, 'UTF-8');
    } else {
      return self::substru($url, 0, VARCHAR_SIZE);
    }
  }

  /**
   *
   * Replace macros in the body content of the custom error 404 page
   *
   */
  private static function replace404Macro($body, $errormessage)
  {
    // Define supported macros
    $app = JFactory::getApplication();
    $siteurl = self::staticParams('siteurl');
    $sitename = $app->getCfg('sitename');
    $sitemail = $app->getCfg('mailfrom');
    $fromname = $app->getCfg('fromname');

    // Replace macros with their values
    $body = str_replace('{siteurl}', $siteurl, $body);
    $body = str_replace('{sitename}', $sitename, $body);
    $body = str_replace('{sitemail}', $sitemail, $body);
    $body = str_replace('{fromname}', $fromname, $body);
    $body = str_replace('{errormessage}', $errormessage, $body);

    // Also replace {article} macro
    $regex_base = '\{(article)\s+(\d+)\}';
    $regex_search = "/(.*?)$regex_base(.*)/si";
    $regex_replace = "/$regex_base/si";
    $found = preg_match($regex_search, $body, $matches);
    while ($found) {
      $content_id = $matches[3];
      $content_query = "SELECT `introtext`, `fulltext` FROM #__content WHERE `id`=".(int)$content_id;
      $content_db = JFactory::getDbo();
      $content_db->setQuery($content_query);
      $content_row = $content_db->loadObject();
      if (is_object($content_row)) {
        $content = $content_row->introtext . $content_row->fulltext;
      } else {
        $content = '';
      }
      $body = preg_replace($regex_replace, $content, $body); // Replace all occurrences
      $found = preg_match($regex_search, $custombody, $matches);
    }

    return $body;
  }

  /**
   *
   * Manage any kind of error condition
   *
   * @param  string  $error_code  The error code
   *
   */
  static function manageError($error)
  {
    // Get plugin parameters
    $plugin = JPluginHelper::getPlugin('system', 'redj'); // Can't use $this as callback function
    $params = new JRegistry($plugin->params);
    $customerror404page = $params->get('customerror404page', 0);
    $error404page = $params->get('error404page', '');
    $error404link = $params->get('error404link', '');
    $trackerrors = $params->get('trackerrors', 0);
    $redirecterrors = $params->get('redirecterrors', 0);
    $redirectallerrors = $params->get('redirectallerrors', 0);
    $redirectallerrorsurl = $params->get('redirectallerrorsurl', '');
    $shortcutextensions = $params->get('shortcutextensions', '');

    // Get error code and error message
    $error_code = $error->getCode();
    $error_message = $error->getMessage();

    $db = JFactory::getDbo();

    if ($trackerrors)
    {
      // Track error URL calls
      $visited_url = self::getURL();
      $nowDate = JFactory::getDate()->toSql();
      $last_referer = @$_SERVER['HTTP_REFERER'];
      $db->setQuery("INSERT INTO #__redj_errors (`visited_url`, `error_code`, `redirect_url`, `hits`, `last_visit`, `last_referer`) VALUES (" . $db->quote( $visited_url ) . ", " . $db->quote( $error_code ) . ", '', 1, " . $db->quote( $nowDate ) . ", " . $db->quote( $last_referer ) . ") ON DUPLICATE KEY UPDATE `hits` = `hits` + 1, `last_visit` = " . $db->quote( $nowDate ) . ", `last_referer` = " . $db->quote( $last_referer ));
      $res = @$db->query();
    }

    if ($redirecterrors)
    {
      $db->setQuery("SELECT `redirect_url` FROM #__redj_errors WHERE `visited_url` = " . $db->quote( $visited_url ));
      $redirect_url = $db->loadResult();
      if (strlen($redirect_url) > 0)
      {
        @ob_end_clean();
        header("HTTP/1.1 301 Moved Permanently");
        header('Location: ' . $redirect_url);
        exit();
      }
    }

    if (self::staticParams('error_only_matching_rule') !== 0)
    {
      self::processRule(self::staticParams('error_only_matching_rule'));
      $tourl = self::staticParams('tourl');
      if (!empty($tourl))
      {
        @ob_end_clean();
        switch (self::staticParams('redirect')) {
          case 307:
            header("HTTP/1.1 307 Temporary Redirect");
            header('Location: ' . self::staticParams('tourl'));
            exit();
          default:
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: ' . self::staticParams('tourl'));
            exit();
        }
      }
    }

    if ( ($redirectallerrors) && (!empty($redirectallerrorsurl)) )
    {
      $siteurl = self::staticParams('siteurl');
      $tourl = str_replace('{siteurl}', $siteurl, $redirectallerrorsurl);
      @ob_end_clean();
      header("HTTP/1.1 301 Moved Permanently");
      header('Location: ' . $tourl);
      exit();
    }

    $custom_error = 0; // No
    $custom_error_page = '';

    if (($error_code == 404) || ($error_code == 500))
    {

      if ($customerror404page == 1) // Page
      {
        if (strlen($error404page) > 0)
        {
          $db->setQuery("SELECT * FROM #__redj_pages404 WHERE id=" . $db->quote( $error404page ));
          $items = $db->loadObjectList();
          if ((count($items) > 0) && (!empty($items[0]->page))) // Use custom error page
          {
            // Update hits
            $nowDate = JFactory::getDate()->toSql();
            $db->setQuery("UPDATE #__redj_pages404 SET hits = hits + 1, last_visit = " . $db->quote( $nowDate ) . " WHERE id = " . $db->quote( $error404page ));
            $res = @$db->query();
            $custom_error = 1; // Page
            $custom_error_page = $items[0]->page;
          }
        }
      } else if ($customerror404page == 2) { // Link
        if (strlen($error404link) > 0)
        {
          $error404link_content = file_get_contents($error404link);
          if ($error404link_content)
          {
            $custom_error = 2; // Link
            $custom_error_page = $error404link_content;
          }
        }
      }

      if (!$custom_error)
      {
        // Check shortcut extensions
        $shortexts = array_filter(array_map("trim", explode(',', trim($shortcutextensions))));
        if (count($shortexts) > 0)
        {
          $request_parts = explode('?', basename(self::staticParams('visited_request_uri')));
          $extension = pathinfo($request_parts[0], PATHINFO_EXTENSION);
          if (array_search($extension, $shortexts) !== false)
          {
            $custom_error = 2; // Link
            $custom_error_page = "<h1>404 Not Found</h1>\r\nThe page that you have requested could not be found.";
          }
        }
      }

    }

    if ($custom_error == 1) // Page
    {
      // Initialize variables
      jimport('joomla.document.document');
      $app = JFactory::getApplication();
      $document = JDocument::getInstance('error');
      $config = JFactory::getConfig();

      // Get the current template from the application
      $template = $app->getTemplate();

      // Push the error object into the document
      $document->setError($error);

      // Call default render to set header
      @ob_end_clean();
      $document->setTitle(JText::_('Error').': '.$error_code);
      $data = $document->render(false, array (
        'template' => $template,
        'directory' => JPATH_THEMES,
        'debug' => $config->get('debug')
      ));

      // Replace macros in custom body
      $custombody = self::replace404Macro($custom_error_page, $error_message);

      // Do not allow cache
      JResponse::allowCache(false);

      // Set the custom body
      JResponse::setBody($custombody);
      echo JResponse::toString();
      @ob_flush();
      exit();
    } else if ($custom_error == 2) { // Link
      header("HTTP/1.0 404 Not Found");
      echo $custom_error_page;
      exit();
    }

  }

  /**
   *
   * Custom error callback function
   *
   */
  static function customError(JException $error)
  {
    // This is a static method so could be called as a callback function (plgSystemReDJ::custom_error())
    // and no $this reference to object instance can be used

    // Get the application object.
    $app = JFactory::getApplication();

    // Make sure we are not in the administrator
    if (!$app->isAdmin() and (JError::isError($error) === true))
    {
      $e = new Exception($error->get('message'), $error->get('code'));
      plgSystemReDJ::manageError($e);
    }

    // Restore saved error handler
    JError::setErrorHandling(E_ERROR, $GLOBALS["_REDJ_JERROR_HANDLER"]["mode"], array($GLOBALS["_REDJ_JERROR_HANDLER"]["options"]["0"], $GLOBALS["_REDJ_JERROR_HANDLER"]["options"]["1"]));

    // Re-raise original error... cannot be done anymore inside the callback to avoid failure due to loop detection
    // JError::raise($error->get('level'), $error->get('code'), $error->get('message'), $error->get('info'), $error->get('backtrace'));
    // So let's do what the raise do...
    jimport('joomla.error.exception');
    // Build error object
    $exception = new JException($error->get('message'), $error->get('code'), $error->get('level'), $error->get('info'), $error->get('backtrace'));
    // See what to do with this kind of error
    $handler = JError::getErrorHandling($exception->get('level'));
    $function = 'handle'.ucfirst($handler['mode']);
    if (is_callable(array('JError', $function))) {
      $reference = call_user_func_array(array('JError', $function), array(&$exception, (isset($handler['options'])) ? $handler['options'] : array()));
    }
    else {
      // This is required to prevent a very unhelpful white-screen-of-death
      jexit(
        'JError::raise -> Static method JError::' . $function . ' does not exist.' .
        ' Contact a developer to debug' .
        '<br /><strong>Error was</strong> ' .
        '<br />' . $exception->getMessage()
      );
    }

  }

  /**
   *
   * Custom exception callback function
   *
   */
  static function customException($error)
  {
    // This is a static method so could be called as a callback function (plgSystemReDJ::custom_exception())
    // and no $this reference to object instance can be used

    // Get the application object.
    $app = JFactory::getApplication();

    // Make sure we are not in the administrator
    if (!$app->isAdmin())
    {
      plgSystemReDJ::manageError($error);
    }

    // Call saved exception handler
    $exception_handler = (gettype($GLOBALS["_REDJ_EXCEPTION_HANDLER"]) == 'object') ? get_class($GLOBALS["_REDJ_EXCEPTION_HANDLER"]) : $GLOBALS["_REDJ_EXCEPTION_HANDLER"];
    if (is_callable($exception_handler)) call_user_func($exception_handler, $error);
  }

  /**
   *
   * Manage params for supported macros (e.g. used as callback function to replace macros with parameters)
   *
   * $macro[0] contains the complete match (when used with preg_replace_callback)
   * $macro[1] contains the 'action'
   * $macro[2] ... $macro[N] contain the additional params
   *
   */
  private static function manageMacroParams($macro)
  {
    static $siteurl = '';             // The (called) {siteurl} (e.g. www.youdomain.com/site) from the current server variables
    static $url = '';                 // The (called) full URL (e.g. http://www.youdomain.com/site/index.php?id=12) from the current server variables
    static $urlparts = array();       // Array of JUri parts for the (called) full URL
    static $urlvars = array();        // Array of all query variables (e.g. array([task] => view, [id] => 32) )
    static $urlpaths = array();       // Array of paths (e.g. array([baseonly] => /path/to/Joomla, [path] => /path/to/Joomla/section/cat/index.php, [pathfrombase] => /section/cat/index.php) )
    static $globalinfo = array();     // Array of global info
    static $placeholders = array();   // Array of evaluated placeholders

    $macro = (array)$macro;
    if (!isset($macro[1])) return '';
    if (!isset($macro[2])) $macro[2] = '';

    $value = '';
    switch ($macro[1])
    {
      // set methods
      case 'setsiteurl':
        $siteurl = $macro[2];
        break;
      case 'seturl':
        $url = $macro[2];
        break;
      case 'seturlparts':
        $urlparts = $macro[2];
        break;
      case 'seturlvars':
        $urlvars = $macro[2];
        break;
      case 'seturlpaths':
        $urlpaths = $macro[2];
        break;
      case 'setglobalinfo':
        $globalinfo = $macro[2];
        break;
      case 'setplaceholder':
        // $macro[2] = placeholder name
        // $macro[3] = placeholder value
        if (isset($macro[2]))
        {
          if (isset($macro[3]))
          {
            $placeholders[$macro[2]] = $macro[3];
          } else {
            unset($placeholders[$macro[2]]);
          }
        }
        break;
      // get methods
      case 'getsiteurl':
        $value = $siteurl;
        break;
      case 'geturl':
        $value = $url;
        break;
      case 'geturlparts':
        $value = $urlparts;
        break;
      case 'geturlvars':
        $value = $urlvars;
        break;
      case 'geturlpaths':
        $value = $urlpaths;
        break;
      case 'getglobalinfo':
        $value = $globalinfo;
        break;
      case 'getplaceholder':
        // $macro[2] = placeholder name
        if (isset($placeholders[$macro[2]])) $value = $placeholders[$macro[2]];
        break;
      // macro methods
      case 'querybuild':
      case 'querybuildfull':
      case 'querybuildappend':
        $build_vars = explode(',', $macro[2]);
        foreach ($build_vars as $k => $v)
        {
          $p = strpos($v, "=");
          if ($p === false)
          {
            // Only parameter name
            if (isset($urlvars[$v])) // Need to check only not-null values
            {
              $value .= (($value === '') ? '' : '&') . $v . '=' . $urlvars[$v];
            }
          } else {
            // New parameter or overrides existing
            $pn = substr($v, 0, $p);
            $pv = substr($v, $p + 1, strlen($v) - $p - 1);
            if ((strlen($pn) > 0) && (strlen($pv) > 0)) // Need to take only not-null names and values
            {
              $value .= (($value === '') ? '' : '&') . $pn . '=' . $pv;
            }
          }
        }
        if (strlen($value) > 0)
        {
          if ($macro[1] === 'querybuildfull') { $value = '?' . $value; }
          if ($macro[1] === 'querybuildappend') { $value = '&' . $value; }
        }
        break;
      case 'querydrop':
      case 'querydropfull':
      case 'querydropappend':
        $drop_vars = explode(',', $macro[2]);
        $drop_vars_keys = array();
        $add_vars_keys = array();
        foreach ($drop_vars as $k => $v)
        {
          $p = strpos($v, "=");
          if ($p === false)
          {
            $drop_vars_keys[$v] = '';
          } else {
            $pn = substr($v, 0, $p);
            $pv = substr($v, $p + 1, strlen($v) - $p - 1);
            if (isset($urlvars[$pn]))
            {
              $drop_vars_keys[$pn] = $pv;
            } else {
              $add_vars_keys[$pn] = $pv;
            }
          }
        }
        foreach ($urlvars as $k => $v)
        {
            if (isset($drop_vars_keys[$k]))
            {
              // To drop or to change
              if (strlen($drop_vars_keys[$k]) > 0)
              {
                // Value not empty (to change)
                $value .= (($value === '') ? '' : '&') . $k . '=' . $drop_vars_keys[$k];
              }
            } else {
              // To add
              $value .= (($value === '') ? '' : '&') . $k . '=' . $v;
            }
        }
        // Finally check variables to add
        foreach ($add_vars_keys as $k => $v)
        {
          $value .= (($value === '') ? '' : '&') . $k . '=' . $v;
        }
        if (strlen($value) > 0)
        {
          if ($macro[1] === 'querydropfull') { $value = '?' . $value; }
          if ($macro[1] === 'querydropappend') { $value = '&' . $value; }
        }
        break;
      case 'queryvar':
        // $macro[2] = variable name
        // $macro[3] = default value if variable is not set (optional)
        if (array_key_exists($macro[2], $urlvars)) {
          $value = $urlvars[$macro[2]];
        } else {
          if (isset($macro[3])) $value = $macro[3];
        }
        break;
      case 'requestvar':
        // $macro[2] = variable name
        // $macro[3] = default value if variable is not set (optional)
        if (isset($macro[3]))
        {
          $value = JFactory::getApplication()->input->get($macro[2], $macro[3], 'RAW');
        } else {
          $value = JFactory::getApplication()->input->get($macro[2], NULL, 'RAW');
        }
        break;
      case 'pathltrim':
        $value = $urlpaths['path'];
        if (strpos($value, $macro[2]) === 0)
        {
          $value = substr($value, strlen($macro[2]), strlen($value) - strlen($macro[2]));
        }
        break;
      case 'pathrtrim':
        $value = $urlpaths['path'];
        if (strpos($value, $macro[2]) === (strlen($value) - strlen($macro[2])))
        {
          $value = substr($value, 0, strlen($value) - strlen($macro[2]));
        }
        break;
      case 'pathfrombaseltrim':
        $value = $urlpaths['pathfrombase'];
        if (strpos($value, $macro[2]) === 0)
        {
          $value = substr($value, strlen($macro[2]), strlen($value) - strlen($macro[2]));
        }
        break;
      case 'pathfrombasertrim':
        $value = $urlpaths['pathfrombase'];
        if (strpos($value, $macro[2]) === (strlen($value) - strlen($macro[2])))
        {
          $value = substr($value, 0, strlen($value) - strlen($macro[2]));
        }
        break;
      case 'preg_match':
        if (@preg_match($macro[3], $url, $matches) !== false)
        {
          @$arg = (int)$macro[2]; // Default to 0 if key not exists
          if (array_key_exists($arg, $matches)) { $value = $matches[$arg]; }
        }
        break;
      case 'routeurl':
        $vars = array();
        if (isset($macro[2]))
        {
          $build_vars = explode(',', $macro[2]);
          foreach ($build_vars as $k => $v)
          {
            $p = strpos($v, "=");
            if ($p === false)
            {
              // Only parameter name
              $vars[$v] = NULL;
            } else {
              // New parameter or overrides existing
              $pn = substr($v, 0, $p);
              $pv = substr($v, $p + 1, strlen($v) - $p - 1);
              if ((strlen($pn) > 0) && (strlen($pv) > 0)) // Need to take only not-null names and values
              {
                $vars[$pn] = $pv;
              }
            }
          }
        }
        $value = self::route_url($url, $vars);
        break;
      case 'pathfolder':
        $parts = explode("/", trim($urlpaths['path'], "/"));
        if (preg_match('/^last(-\d+)?/', $macro[2], $matches))
        {
          $pi = (isset($matches[1])) ? count($parts) - 1 + $matches[1] : count($parts) - 1;
        } else {
          $pi = (int) $macro[2] - 1; // Array of parts is zero-based
        }
        if (isset($parts[$pi])) { $value = $parts[$pi]; }
        break;
      case 'username':
        $user = JFactory::getUser();
        $value = ($user->get('guest') == 1) ? $macro[2] : $user->get('username');
        break;
      case 'userid':
        $user = JFactory::getUser();
        $value = ($user->get('guest') == 1) ? $macro[2] : $user->get('id');
        break;
      case 'tableselect':
        // $macro[2] = table,column,key
        // $macro[3] = value
        // table: table name to query (support #__ notation)
        // column: field name to return
        // key: field name to use as selector in where condition
        // value: value to use for comparison in the where condition (WHERE key = value)
        $arg = explode(",", trim($macro[2]));
        if (count($arg) == 3)
        {
          $arg = array_map("trim", $arg);
          $value = $macro[3];
          if ($value != '')
          {
            // Perform a DB query
            $db = JFactory::getDbo();
            $db->setQuery("SELECT `" . $arg[1] . "` FROM `" . $arg[0] . "` WHERE `" . $arg[2] . "`=" . $db->quote($value));
            $result = $db->loadResult();
            if (isset($result)) { $value = $result; }
          }
        }
        break;
      case 'preg_select':
        // $macro[2] = table,column,key,N
        // $macro[3] = pattern
        // table: table name to query (support #__ notation)
        // column: field name to return
        // key: field name to use as selector in where condition
        // N: regexp pattern index to use for comparison in the where condition (WHERE key = ${N})
        if (@preg_match($macro[3], $url, $matches) !== false)
        {
          $arg = explode(",", trim($macro[2]));
          if (count($arg) == 4)
          {
            $arg = array_map("trim", $arg);
            $arg[3] = (int)$arg[3];
            if (array_key_exists($arg[3], $matches))
            {
              $value = $matches[$arg[3]];
              if ($value != '')
              {
                // Perform a DB query
                $db = JFactory::getDbo();
                $db->setQuery("SELECT `" . $arg[1] . "` FROM `" . $arg[0] . "` WHERE `" . $arg[2] . "`=" . $db->quote($value));
                $result = $db->loadResult();
                if (isset($result)) { $value = $result; }
              }
            }
          }
        }
        break;
      case 'placeholder_select':
        // $macro[2] = table,column,key1=placeholder1,..,keyN=placeholderN
        // table: table name to query (support #__ notation)
        // column: field name to return
        // key: field name to use as selector in where condition
        // placeholder: name of placeholder whose value must be compared with key (WHERE key = ${placeholder})
        $arg = explode(",", trim($macro[2]));
        if (count($arg) > 2) // At least table,column,key1=placeholder1
        {
          // Build the where condition
          $db = JFactory::getDbo();
          $where = ''; $i = 2; $n = count($arg);
          while ($i < $n)
          {
            $p = strpos($arg[$i], "=");
            if ($p !== false)
            {
              $pk = trim(substr($arg[$i], 0, $p));
              $pp = trim(substr($arg[$i], $p + 1, strlen($arg[$i]) - $p - 1));
              if ((strlen($pk) > 0) && (strlen($pp) > 0)) // Need to take only not-null keys and placeholders
              {
                if (isset($placeholders[$pp]))
                {
                  if ($where != '') { $where .= " AND "; }
                  $where .= "`" . $pk . "`=" . $db->quote($placeholders[$pp]);
                }
              }
            }
            $i++;
          }
          if ($where != '')
          {
            // Perform a DB query
            $db->setQuery("SELECT `" . $arg[1] . "` FROM `" . $arg[0] . "` WHERE " . $where);
            $result = $db->loadResult();
            if (isset($result)) { $value = $result; }
          }
        }
        break;
      case 'placeholder_insert':
        // $macro[2] = table,column1=placeholder1,..,columnN=placeholderN
        // table: table name to query (support #__ notation)
        // column: field name to insert
        // placeholder: name of placeholder whose value must be inserted (INSERT INTO table (column1, ... columnN) VALUES (${placeholder1}, ... ${placeholderN}))
        // returns 0 if all is ok or the error code in case of error
        $value = '0';
        $arg = explode(",", trim($macro[2]));
        if (count($arg) > 1) // At least table,column1=placeholder1
        {
          $db = JFactory::getDbo();
          $columns = array();
          $values = array();
          // Build the query
          for ($i = 1; $i < count($arg); $i++)
          {
            $p = strpos($arg[$i], "=");
            if ($p !== false)
            {
              $pk = trim(substr($arg[$i], 0, $p));
              $pp = trim(substr($arg[$i], $p + 1, strlen($arg[$i]) - $p - 1));
              if ((strlen($pk) > 0) && (strlen($pp) > 0)) // Need to take only not-null keys and placeholders
              {
                if (isset($placeholders[$pp]))
                {
                  $columns[] = $pk;
                  $values[] = $db->quote($placeholders[$pp]);
                }
              }
            }
          }
          if (count($columns) > 0)
          {
            // Perform a DB query
            $query = $db->getQuery(true);
            $query->insert($db->quoteName($arg[0]))
                  ->columns($db->quoteName($columns))
                  ->values(implode(',', $values));
            $db->setQuery($query);
            try
            {
              $db->execute();
            }
            catch (Exception $e)
            {
              $value = $e->getCode();
            }
          }
        }
        break;
      case 'placeholder_update':
        // $macro[2] = table,column=placeholder,key1=placeholder1,..,keyN=placeholderN
        // table: table name to query (support #__ notation)
        // column: field name to update
        // key: field name to use as selector in where condition
        // placeholder: name of placeholder whose value updates the column (SET column = ${placeholder}) and must be compared with key (WHERE key = ${placeholder})
        // returns 0 if all is ok or the error code in case of error
        $value = '0';
        $arg = explode(",", trim($macro[2]));
        if (count($arg) > 2) // At least table,column=placeholder,key1=placeholder1
        {

          $db = JFactory::getDbo();
          // Get the column
          $fields = array();
          $c = strpos($arg[1], "=");
          if ($c !== false)
          {
            $ck = trim(substr($arg[1], 0, $c));
            $cp = trim(substr($arg[1], $c + 1, strlen($arg[1]) - $c - 1));
            if ((strlen($ck) > 0) && (strlen($cp) > 0)) // Need to take only not-null keys and placeholders
            {
              if (isset($placeholders[$cp]))
              {
                $fields[] = $db->quoteName($ck) . ' = ' . $db->quote($placeholders[$cp]);
              }
            }
          }
          if (count($fields) > 0) // There is something to update
          {
            // Build the query
            $conditions = array();
            for ($i = 2; $i < count($arg); $i++)
            {
              $p = strpos($arg[$i], "=");
              if ($p !== false)
              {
                $pk = trim(substr($arg[$i], 0, $p));
                $pp = trim(substr($arg[$i], $p + 1, strlen($arg[$i]) - $p - 1));
                if ((strlen($pk) > 0) && (strlen($pp) > 0)) // Need to take only not-null keys and placeholders
                {
                  if (isset($placeholders[$pp]))
                  {
                    $conditions[] = $db->quoteName($pk) . ' = ' . $db->quote($placeholders[$pp]);
                  }
                }
              }
            }
            if (count($conditions) > 0) // At least one condition is required to avoid to update all rows
            {
              // Perform a DB query
              $query = $db->getQuery(true);
              $query->update($db->quoteName($arg[0]))->set($fields)->where($conditions);
              $db->setQuery($query);
              try
              {
                $db->execute();
              }
              catch (Exception $e)
              {
                $value = $e->getCode();
              }
            }
          }
        }
        break;
      case 'substr':
        // $macro[2] = start,length
        // $macro[3] = input string
        // start: start index for the portion string (0 based)
        // length: lenght of the portion string
        if (isset($macro[3]))
        {
          $value = $macro[3];
          $arg = array_map("trim", explode(",", $macro[2]));
          $cnt_arg = count($arg);
          if (($cnt_arg == 1) || ($cnt_arg == 2))
          {
            $value = ($cnt_arg == 1) ? substr($value, (int)$arg[0]) : substr($value, (int)$arg[0], (int)$arg[1]);
          }
        }
        break;
      case 'strip_tags':
        $value = strip_tags($macro[2]);
        break;
      case 'extract':
        $rows = preg_split("/[\r\n]+/iU" , $macro[3]);
        $index = (int)$macro[2] - 1;
        if (isset($rows[$index])) { $value = strip_tags($rows[$index]); }
        break;
      case 'extractp':
        preg_match_all("/<p>(.*)<\/p>/iU" , $macro[3], $rows);
        $index = (int)$macro[2] - 1;
        if (isset($rows[1][$index])) { $value = strip_tags($rows[1][$index]); }
        break;
      case 'extractdiv':
        preg_match_all("/<div>(.*)<\/div>/iU" , $macro[3], $rows);
        $index = (int)$macro[2] - 1;
        if (isset($rows[1][$index])) { $value = strip_tags($rows[1][$index]); }
        break;
      case 'preg_subject':
        // $macro[2] = N,subject
        // $macro[3] = pattern
        // N: regexp pattern to return
        // subject: input string
        preg_match("/^([^\,]+)\,(.*)$/iU", trim($macro[2]), $arg);
        if (count($arg) == 3)
        {
          $arg[1] = (int)$arg[1]; // Default to 0 if not numeric
          $arg[2] = trim($arg[2]);
          if (@preg_match($macro[3], $arg[2], $matches) !== false)
          {
            if (array_key_exists($arg[1], $matches)) { $value = $matches[$arg[1]]; }
          }
        }
        break;
      case 'preg_placeholder':
        // $macro[2] = N,placeholder
        // $macro[3] = pattern
        // N: regexp pattern to return
        // placeholder: placeholder name
        preg_match("/^([^\,]+)\,(.*)$/iU", trim($macro[2]), $arg);
        if (count($arg) == 3)
        {
          $arg[1] = (int)$arg[1]; // Default to 0 if not numeric
          $arg[2] = trim($arg[2]);
          if (isset($placeholders[$arg[2]]))
          {
            if (@preg_match($macro[3], $placeholders[$arg[2]], $matches) !== false)
            {
              if (array_key_exists($arg[1], $matches)) { $value = $matches[$arg[1]]; }
            }
          }
        }
        break;
      case 'lowercase':
        $value = strtolower($macro[2]);
        break;
      case 'uppercase':
        $value = strtoupper($macro[2]);
        break;
      case 'camelcase':
        $value = ucwords(strtolower($macro[2]));
        break;
      case 'ucwords':
        $value = ucwords($macro[2]);
        break;
      case 'urldecode':
        $value = urldecode($macro[2]);
        break;
      case 'urlencode':
        $value = urlencode($macro[2]);
        break;
      case 'rawurldecode':
        $value = rawurldecode($macro[2]);
        break;
      case 'rawurlencode':
        $value = rawurlencode($macro[2]);
        break;
      case 'str_replace':
        // $macro[2] = 'search','replace'
        // $macro[3] = input string
        if (isset($macro[3]))
        {
          $value = $macro[3];
          $r = preg_match("/^'(.*)','(.*)'$/suU", $macro[2], $arg);
          if ($r == 1)
          {
            $value = str_replace($arg[1], $arg[2], $value);
          }
        }
        break;
    }
    return $value;
  }

  /**
   *
   * Replace supported macros from the content provided
   *
   */
  private static function replaceMacros($content)
  {
    /*
      http://fredbloggs:itsasecret@www.example.com:8080/path/to/Joomla/section/cat/index.php?task=view&id=32#anchorthis
      \__/   \________/ \________/ \_____________/ \__/\___________________________________/ \_____________/ \________/
       |          |         |              |        |                   |                           |             |
     scheme     user       pass          host      port                path                       query       fragment

    Supported URL macros:
       {siteurl}                                                    www.example.com/path/to/Joomla
       {scheme}                                                     http
       {host}                                                       www.example.com
       {port}                                                       8080
       {user}                                                       fredbloggs
       {pass}                                                       itsasecret
       {path}                                                       /path/to/Joomla/section/cat/index.php
       {query}                                                      task=view&id=32
       {queryfull}                                                  ?task=view&id=32
       {queryappend}                                                &task=view&id=32
       {querybuild id,task}                                         id=32&task=view
       {querybuild id,task=edit}                                    id=32&task=edit
       {querybuild id,task=view,ItemId=12}                          id=32&task=view&ItemId=12
       {querybuildfull id,task}                                     ?id=32&task=view
       {querybuildfull id,task=save}                                ?id=32&task=save
       {querybuildfull id,task,action=close}                        ?id=32&task=view&action=close
       {querybuildappend id,task}                                   &id=32&task=view
       {querybuildappend id,task=save}                              &id=32&task=save
       {querybuildappend id,task,action=close}                      &id=32&task=view&action=close
       {querydrop task}                                             id=32
       {querydrop id,task=edit}                                     task=edit
       {querydrop id,task=save,action=close}                        task=save&action=close
       {querydropfull task}                                         ?id=32
       {querydropfull id,task=save}                                 ?task=save
       {querydropfull id,task=edit,action=close}                    ?task=edit&action=close
       {querydropappend task}                                       &id=32
       {querydropappend id,task=save}                               &task=save
       {querydropappend id,task=edit,action=close}                  &task=edit&action=close
       {queryvar varname,default}                                   Returns the current value for the variable 'varname' of the URL, or the value 'default' if 'varname' is not defined (where default = '' when not specified)
       {queryvar task}                                              view
       {queryvar id}                                                32
       {queryvar maxsize,234}                                       234
       {requestvar varname,default}                                 Returns the current value for the variable 'varname' of the request, no matter about method (GET, POST, ...), or the value 'default' if 'varname' is not defined (where default = '' when not specified)
       {requestvar id}                                              32
       {requestvar limit,100}                                       100
       {authority}                                                  fredbloggs:itsasecret@www.example.com:8080
       {baseonly}                                                   /path/to/Joomla (empty when installed on root, i.e. it will never contains a trailing slash)
       {pathfrombase}                                               /section/cat/index.php
       {pathltrim /path/to}                                         /Joomla/section/cat/index.php
       {pathrtrim /index.php}                                       /path/to/Joomla/section/cat
       {pathfrombaseltrim /section}                                 /cat/index.php
       {pathfrombasertrim index.php}                                /section/cat/
       {preg_match N}pattern{/preg_match}                           (return the N-th matched pattern on the full source url, where N = 0 when not specified)
       {preg_match}/([^\/]+)(\.php|\.html|\.htm)/i{/preg_match}     index.php
       {preg_match 2}/([^\/]+)(\.php|\.html|\.htm)/i{/preg_match}   .php
       {preg_select table,column,key,N}pattern{/preg_select}        (uses the N-th matched result to execute a SQL query (SELECT column FROM table WHERE key = matchN). Support #__ notation for table name)
       {routeurl}                                                   Returns the routed (relative) URL using all current variables
       {routeurl var1,var2,var3=myvalue,..,varN}                    Returns the routed (relative) URL for specified variables (index.php?var1=value1&var2=value2&var3=myvalue&..&varN=valueN)
       {pathfolder N}                                               Returns the N-th folder of the URL path (e.g. with /path/to/Joomla/section/cat/index.php N=4 returns section)
       {pathfolder last-N}                                          Returns the (last-N)-th folder of the URL path, where N = 0 when not specified (e.g. with /path/to/Joomla/section/cat/index.php last-1 returns cat)
    */

    static $patterns;
    static $replacements;
    static $patterns_callback;

    if (!isset($patterns)) {
      // Supported static macros patterns
      $patterns = array();
      // URL macros
      $patterns[0] = "/\{siteurl\}/";
      $patterns[1] = "/\{scheme\}/";
      $patterns[2] = "/\{host\}/";
      $patterns[3] = "/\{port\}/";
      $patterns[4] = "/\{user\}/";
      $patterns[5] = "/\{pass\}/";
      $patterns[6] = "/\{path\}/";
      $patterns[7] = "/\{query\}/";
      $patterns[8] = "/\{queryfull\}/";
      $patterns[9] = "/\{queryappend\}/";
      $patterns[10] = "/\{authority\}/";
      $patterns[11] = "/\{baseonly\}/";
      $patterns[12] = "/\{pathfrombase\}/";
      // Site macros
      $patterns[13] = "/\{sitename\}/";
      $patterns[14] = "/\{globaldescription\}/";
      $patterns[15] = "/\{globalkeywords\}/";
    }

    if (!isset($replacements))
    {
      // Get data needed for macros replacements
      $visited_siteurl = self::manageMacroParams(array(1 => 'getsiteurl'));
      $uri_visited_full_url_parts = self::manageMacroParams(array(1 => 'geturlparts'));
      $uri_visited_full_url_paths = self::manageMacroParams(array(1 => 'geturlpaths'));
      $global_info = self::manageMacroParams(array(1 => 'getglobalinfo'));

      // Supported static macros replacements
      $replacements = array();
      // URL macros
      $replacements[0] = $visited_siteurl;
      $replacements[1] = $uri_visited_full_url_parts['scheme'];
      $replacements[2] = $uri_visited_full_url_parts['host'];
      $replacements[3] = $uri_visited_full_url_parts['port'];
      $replacements[4] = $uri_visited_full_url_parts['user'];
      $replacements[5] = $uri_visited_full_url_parts['pass'];
      $replacements[6] = $uri_visited_full_url_parts['path'];
      $replacements[7] = $uri_visited_full_url_parts['query'];
      $replacements[8] = (strlen($uri_visited_full_url_parts['query']) > 0) ? '?' . $uri_visited_full_url_parts['query'] : '';
      $replacements[9] = (strlen($uri_visited_full_url_parts['query']) > 0) ? '&' . $uri_visited_full_url_parts['query'] : '';
      $replacements[10] = $uri_visited_full_url_parts['authority'];
      $replacements[11] = $uri_visited_full_url_paths['baseonly'];
      $replacements[12] = $uri_visited_full_url_paths['pathfrombase'];
      // Site macros
      $replacements[13] = $global_info['sitename'];
      $replacements[14] = $global_info['MetaDesc'];
      $replacements[15] = $global_info['MetaKeys'];
    }

    if (!isset($patterns_callback))
    {
      // Supported dynamic macros patterns
      $patterns_callback = array();
      // URL macros
      $patterns_callback[0] = "/\{(querybuild) ([^\}]+)\}/suU";
      $patterns_callback[1] = "/\{(querybuildfull) ([^\}]+)\}/suU";
      $patterns_callback[2] = "/\{(querybuildappend) ([^\}]+)\}/suU";
      $patterns_callback[3] = "/\{(querydrop) ([^\}]+)\}/suU";
      $patterns_callback[4] = "/\{(querydropfull) ([^\}]+)\}/suU";
      $patterns_callback[5] = "/\{(querydropappend) ([^\}]+)\}/suU";
      $patterns_callback[6] = "/\{(queryvar) ([^\}\,]+)\}/suU";
      $patterns_callback[7] = "/\{(queryvar) ([^\}]+),([^\}]+)\}/suU";
      $patterns_callback[8] = "/\{(requestvar) ([^\}\,]+),?\}/suU";
      $patterns_callback[9] = "/\{(requestvar) ([^\}]+),([^\}]+)\}/suU";
      $patterns_callback[10] = "/\{(pathltrim) ([^\}]+)\}/suU";
      $patterns_callback[11] = "/\{(pathrtrim) ([^\}]+)\}/suU";
      $patterns_callback[12] = "/\{(pathfrombaseltrim) ([^\}]+)\}/suU";
      $patterns_callback[13] = "/\{(pathfrombasertrim) ([^\}]+)\}/suU";
      $patterns_callback[14] = "/\{(preg_match)\}(.*)\{\/preg_match\}/suU";
      $patterns_callback[15] = "/\{(preg_match) ([^\}]+)\}(.*)\{\/preg_match\}/suU";
      $patterns_callback[16] = "/\{(routeurl)\}/";
      $patterns_callback[17] = "/\{(routeurl) ([^\}]+)\}/suU";
      $patterns_callback[18] = "/\{(pathfolder) ([^\}]+)\}/suU";
      // Site macros
      $patterns_callback[19] = "/\{(username)\}/";
      $patterns_callback[20] = "/\{(username) ([^\}]+)\}/suU";
      $patterns_callback[21] = "/\{(userid)\}/";
      $patterns_callback[22] = "/\{(userid) ([^\}]+)\}/suU";
      // Database macros
      $patterns_callback[23] = "/\{(tableselect) ([^\}]+)\}(.*)\{\/tableselect\}/suU";
      $patterns_callback[24] = "/\{(preg_select) ([^\}]+)\}(.*)\{\/preg_select\}/suU";
      $patterns_callback[25] = "/\{(placeholder_select) ([^\}]+)\}/suU";
      $patterns_callback[26] = "/\{(placeholder_insert) ([^\}]+)\}/suU";
      $patterns_callback[27] = "/\{(placeholder_update) ([^\}]+)\}/suU";
      // String macros
      $patterns_callback[28] = "/\{(substr) ([^\}]+)\}(.*)\{\/substr\}/suU";
      $patterns_callback[29] = "/\{(strip_tags)\}(.*)\{\/strip_tags\}/suU";
      $patterns_callback[30] = "/\{(extract) ([^\}]+)\}(.*)\{\/extract\}/suU";
      $patterns_callback[31] = "/\{(extractp) ([^\}]+)\}(.*)\{\/extractp\}/suU";
      $patterns_callback[32] = "/\{(extractdiv) ([^\}]+)\}(.*)\{\/extractdiv\}/suU";
      $patterns_callback[33] = "/\{(preg_subject) ([^\}]+)\}(.*)\{\/preg_subject\}/suU";
      $patterns_callback[34] = "/\{(preg_placeholder) ([^\}]+)\}(.*)\{\/preg_placeholder\}/suU";
      $patterns_callback[35] = "/\{(lowercase)\}(.*)\{\/lowercase\}/suU";
      $patterns_callback[36] = "/\{(uppercase)\}(.*)\{\/uppercase\}/suU";
      $patterns_callback[37] = "/\{(camelcase)\}(.*)\{\/camelcase\}/suU";
      $patterns_callback[38] = "/\{(ucwords)\}(.*)\{\/ucwords\}/suU";
      $patterns_callback[39] = "/\{(urldecode)\}(.*)\{\/urldecode\}/suU";
      $patterns_callback[40] = "/\{(urlencode)\}(.*)\{\/urlencode\}/suU";
      $patterns_callback[41] = "/\{(rawurldecode)\}(.*)\{\/rawurldecode\}/suU";
      $patterns_callback[42] = "/\{(rawurlencode)\}(.*)\{\/rawurlencode\}/suU";
      $patterns_callback[43] = "/\{(str_replace) ([^\}]+)\}(.*)\{\/str_replace\}/suU";
    }

    $content = preg_replace($patterns, $replacements, $content);
    $content = preg_replace_callback($patterns_callback, 'plgSystemReDJ::manageMacroParams', $content);
    return $content;
  }

  /**
   *
   * Evaluate and return placeholders defined in the list
   *
   */
  private static function evaluatePlaceholders($list)
  {
    $placeholders = array();
    $placeholders_count = 0;
    $rows = preg_split("/[\r\n]+/", $list);
    foreach ($rows as $row_key => $row_value)
    {
      $row_value = trim($row_value);
      if (strlen($row_value) > 0)
      {
        $parts = array_map('trim', explode("=", $row_value, 2));
        $parts_key = @$parts[0];
        /* Placeholder names follow the same rules as other labels and variables in PHP. A valid placeholder name starts with a letter or underscore, followed by any number of letters, numbers, or underscores. As a regular expression, it would be expressed thus: '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*' */
        if ((strlen($parts_key) > 0) && (preg_match(PLACEHOLDERS_MATCH_MASK, $parts_key) === 1))
        {
            $parts_value = @$parts[1];
            if (strlen($parts_value) > 0)
            {
                $placeholders[$parts_key] = $parts_value;
                // Replace already defined placeholders in the new placeholder
                preg_match_all(PLACEHOLDERS_MATCH_ALL_MASK, $placeholders[$parts_key], $matches);
                foreach ($matches[1] as $current)
                {
                  if (array_key_exists($current, $placeholders))
                  {
                    $placeholders[$parts_key] = str_replace('${' . $current . '}', $placeholders[$current], $placeholders[$parts_key]);
                  }
                }

                // Replace macros
                $placeholders[$parts_key] = self::replaceMacros($placeholders[$parts_key]);

                // Load placeholder
                $ph = array(1 => 'setplaceholder', 2 => $parts_key, 3 => $placeholders[$parts_key]);
                self::manageMacroParams($ph);

                $placeholders_count++;
            } else {
                // Unset placeholder
                unset($placeholders[$parts_key]);

                // Unload placeholder
                $ph = array(1 => 'setplaceholder', 2 => $parts_key);
                self::manageMacroParams($ph);
            }
        }
      }
    }
    return $placeholders;
  }

  /**
   *
   * Replace and return defined array of placeholders from the input string
   *
   */
  private static function replacePlaceholders($placeholders, $string)
  {
    $value = $string;
    preg_match_all(PLACEHOLDERS_MATCH_ALL_MASK, $value, $matches);
    foreach ($matches[1] as $current)
    {
      if (array_key_exists($current, $placeholders))
      {
        $value = str_replace('${' . $current . '}', $placeholders[$current], $value);
      }
    }
    return $value;
  }

  public function onAfterInitialise()
  {
    // Save the current error handler (legacy)
    $GLOBALS["_REDJ_JERROR_HANDLER"] = JError::getErrorHandling(E_ERROR);
    if ( !( isset($GLOBALS["_REDJ_JERROR_HANDLER"]["mode"]) && isset($GLOBALS["_REDJ_JERROR_HANDLER"]["options"]["0"]) && isset($GLOBALS["_REDJ_JERROR_HANDLER"]["options"]["1"]) ) ) {
      $GLOBALS["_REDJ_JERROR_HANDLER"] = array("mode" => "callback", "options" => array("0" => "JError", "1" => "customErrorPage"));
    }

    // Set new error handler (legacy)
    JError::setErrorHandling(E_ERROR, 'callback', array('plgSystemReDJ', 'customError'));

    // Set new exception handler
    $current_exception_handler = set_exception_handler(array('plgSystemReDJ', 'customException'));

    // Save the current exception handler
    $GLOBALS["_REDJ_EXCEPTION_HANDLER"] = $current_exception_handler;

    // Get the application object.
    $app = JFactory::getApplication();

    // Make sure we are not in the administrator
    if ( $app->isAdmin() ) return;

    $trackreferers = $this->params->get('trackreferers', 0);
    if ($trackreferers)
    {
      // Track referers URL calls
      $excludereferers = preg_split("/[\r\n]+/", $this->params->get('excludereferers', ''));
      foreach ($excludereferers as $key => $value)
      {
        if (trim($value) == '')
        {
          // Remove blanks
          unset($excludereferers[$key]);
        } else {
          // Replace macros
          if ($value == '{self}') { $excludereferers[$key] = self::staticParams('self'); }
          if ($value == '{none}') { $excludereferers[$key] = ''; }
        }
      }

      $visited_url = self::sanitizeURL(self::staticParams('visited_full_url'));
      $referer_url = self::sanitizeURL(@$_SERVER['HTTP_REFERER']);
      $domain = self::sanitizeURL(self::getHost($referer_url));

      if ( !in_array($domain, $excludereferers) ) {
        $nowDate = JFactory::getDate()->toSql();

        $db = JFactory::getDbo();
        $db->setQuery("INSERT IGNORE INTO `#__redj_visited_urls` (`id`, `visited_url`) VALUES (NULL, " . $db->quote( $visited_url ) . ")");
        $res = @$db->query();

        $db->setQuery("INSERT IGNORE INTO `#__redj_referer_urls` (`id`, `referer_url`, `domain`) VALUES (NULL, " . $db->quote( $referer_url ) . ", " . $db->quote( $domain ) . ")");
        $res = @$db->query();

        $db->setQuery("INSERT INTO `#__redj_referers` (`id`, `visited_url`, `referer_url`, `hits`, `last_visit`) VALUES (NULL, (SELECT `id` FROM `#__redj_visited_urls` WHERE `visited_url` = " . $db->quote( $visited_url ) . "), (SELECT `id` FROM `#__redj_referer_urls` WHERE `referer_url` = " . $db->quote( $referer_url ) . "), 1, " . $db->quote( $nowDate ) . " ) ON DUPLICATE KEY UPDATE `hits` = `hits` + 1, `last_visit` = " . $db->quote( $nowDate ));
        $res = @$db->query();
      }
    }

    $this->setRedirect();
    $tourl = self::staticParams('tourl');
    if (!empty($tourl))
    {
      @ob_end_clean();
      switch (self::staticParams('redirect')) {
        case 307:
          header("HTTP/1.1 307 Temporary Redirect");
          header('Location: ' . self::staticParams('tourl'));
          exit();
        case 200:
          // Replace the current URL parts in server variables before JApplication::route happens
          self::doInternalRedirect(self::staticParams('baseonly'), self::staticParams('visited_full_url'), self::staticParams('tourl'));
          break;
        default:
          header("HTTP/1.1 301 Moved Permanently");
          header('Location: ' . self::staticParams('tourl'));
          exit();
      }
    }

  }

  /**
   *
   * Drop all rules where any of skip user group in the list matches any of the current user's groups
   *
   */
  private function skipGroups($rules, $skipall = true)
  {
    $user = JFactory::getUser();
    $groups = (array) $user->groups;
    foreach ($rules as $key => $rule)
    {
      $skip_usergroups = array_filter(explode(',', $rule->skip_usergroups));
      $commons = array_intersect($groups, $skip_usergroups);
      if (!empty($commons))
      {
        // Drop the current rule
        unset($rules[$key]);
      } else {
        // Break after the first valid rule
        if (!$skipall) break;
      }
    }
    return $rules;
  }

  /**
   *
   * Drop all rules where error only is true (also set as global variable the first error only matching rule)
   *
   */
  private function skipErrorOnly($rules)
  {
    foreach ($rules as $key => $rule)
    {
      if ($rule->error_only)
      {
        // Set the first found as global
        if ((self::staticParams('error_only_matching_rule') === 0) && (!empty($rule->tourl))) self::staticParams('error_only_matching_rule', $rule);
        // Drop the current rule
        unset($rules[$key]);
      }
    }
    return $rules;
  }

  /**
   *
   * Process the rule and set the target destination
   *
   */
  private function processRule($rule)
  {
    // Get the application object
    $app = JFactory::getApplication();

    // Initialize URL related variables
    $visited_siteurl = self::staticParams('siteurl');

    $visited_full_url = self::staticParams('visited_full_url');

    JUri::reset();
    $uri_visited_full_url = JUri::getInstance($visited_full_url);
    $uri_visited_full_url_parts['scheme'] = $uri_visited_full_url->getScheme();
    $uri_visited_full_url_parts['host'] = $uri_visited_full_url->getHost();
    $uri_visited_full_url_parts['port'] = $uri_visited_full_url->getPort();
    $uri_visited_full_url_parts['user'] = $uri_visited_full_url->getUser();
    $uri_visited_full_url_parts['pass'] = $uri_visited_full_url->getPass();
    $uri_visited_full_url_parts['path'] = $uri_visited_full_url->getPath();
    $uri_visited_full_url_parts['query'] = $uri_visited_full_url->getQuery();
    $uri_visited_full_url_parts['authority'] = (strlen($uri_visited_full_url_parts['port']) > 0) ? $uri_visited_full_url_parts['host'] . ':' . $uri_visited_full_url_parts['port'] : $uri_visited_full_url_parts['host'];
    $uri_visited_full_url_parts['authority'] = (strlen($uri_visited_full_url_parts['user']) > 0) ? $uri_visited_full_url_parts['user'] . ':' . $uri_visited_full_url_parts['pass'] . '@' . $uri_visited_full_url_parts['authority'] : $uri_visited_full_url_parts['authority'];

    $uri_visited_full_url_vars = $uri_visited_full_url->getQuery(true);

    $baseonly = self::staticParams('baseonly');
    $pathfrombase = (strlen($baseonly) > 0) ? substr($uri_visited_full_url_parts['path'], strlen($baseonly), strlen($uri_visited_full_url_parts['path']) - strlen($baseonly)) : $uri_visited_full_url_parts['path'];
    $uri_visited_full_url_paths['baseonly'] = $baseonly;
    $uri_visited_full_url_paths['path'] = $uri_visited_full_url_parts['path'];
    $uri_visited_full_url_paths['pathfrombase'] = $pathfrombase;

    // Set URL related variables in callback function
    self::manageMacroParams(array(1 => 'setsiteurl', 2 => $visited_siteurl));
    self::manageMacroParams(array(1 => 'seturl', 2 => $visited_full_url));
    self::manageMacroParams(array(1 => 'seturlparts', 2 => $uri_visited_full_url_parts));
    self::manageMacroParams(array(1 => 'seturlvars', 2 => $uri_visited_full_url_vars));
    self::manageMacroParams(array(1 => 'seturlpaths', 2 => $uri_visited_full_url_paths));

    // Set global info in callback function
    $global_info['sitename'] = $app->getCfg('sitename');
    $global_info['MetaDesc'] = $app->getCfg('MetaDesc');
    $global_info['MetaKeys'] = $app->getCfg('MetaKeys');
    self::manageMacroParams(array(1 => 'setglobalinfo', 2 => $global_info));

    // Update hits counter
    $nowDate = JFactory::getDate()->toSql();
    $db = JFactory::getDbo();
    $db->setQuery("UPDATE #__redj_redirects SET hits = hits + 1, last_visit = " . $db->quote( $nowDate ) . " WHERE id = " . $db->quote( $rule->id ));
    $res = @$db->query();

    // Evaluate placeholders
    $placeholders = self::evaluatePlaceholders($rule->placeholders);

    // Replace placeholders
    $tourl = self::replacePlaceholders($placeholders, $rule->tourl);

    // Replace macros
    $tourl = self::replaceMacros($tourl);

    // Set the redirect (destination URL and redirect type)
    self::staticParams('tourl', $tourl);
    self::staticParams('redirect', $rule->redirect);
  }

  /**
   *
   * Set the destination URL and the redirect type if a redirect item is found
   *
   */
  private function setRedirect()
  {
    $currenturi_encoded = self::staticParams('visited_request_uri'); // Raw (encoded): with %## chars
    // Remove the base path
    $basepath = trim($this->params->get('basepath', ''), ' /'); // Decoded: without %## chars (now you can see spaces, cyrillics, ...)
    $basepath = urlencode($basepath); // Raw (encoded): with %## chars
    $basepath = str_replace('%2F', '/', $basepath);
    $basepath = str_replace('%3A', ':', $basepath);
    if ($basepath != '')
    {
      if (strpos($currenturi_encoded, '/'.$basepath.'/') === 0)
      {
        $currenturi_encoded = substr($currenturi_encoded, strlen($basepath) + 1); // Raw (encoded): with %## chars
      }
    }
    $currenturi = urldecode($currenturi_encoded); // Decoded: without %## chars (now you can see spaces, cyrillics, ...)
    $currentfullurl_encoded = self::staticParams('visited_full_url'); // Raw (encoded): with %## chars
    $currentfullurl = urldecode($currentfullurl_encoded); // Decoded: without %## chars (now you can see spaces, cyrillics, ...)

    $db = JFactory::getDbo();
    $db->setQuery('SELECT * FROM ( '
    . 'SELECT * FROM #__redj_redirects '
    . 'WHERE ( '
    . '( (' . $db->quote($currenturi) . ' REGEXP BINARY fromurl)>0 AND (case_sensitive<>0) AND (decode_url<>0) AND (request_only<>0) ) '
    . 'OR ( (' . $db->quote($currenturi_encoded) . ' REGEXP BINARY fromurl)>0 AND (case_sensitive<>0) AND (decode_url=0) AND (request_only<>0) ) '
    . 'OR ( (' . $db->quote($currentfullurl) . ' REGEXP BINARY fromurl)>0 AND (case_sensitive<>0) AND (decode_url<>0) AND (request_only=0) ) '
    . 'OR ( (' . $db->quote($currentfullurl_encoded) . ' REGEXP BINARY fromurl)>0 AND (case_sensitive<>0) AND (decode_url=0) AND (request_only=0) ) '
    . 'OR ( (' . $db->quote($currenturi) . ' REGEXP fromurl)>0 AND (case_sensitive=0) AND (decode_url<>0) AND (request_only<>0) ) '
    . 'OR ( (' . $db->quote($currenturi_encoded) . ' REGEXP fromurl)>0 AND (case_sensitive=0) AND (decode_url=0) AND (request_only<>0) ) '
    . 'OR ( (' . $db->quote($currentfullurl) . ' REGEXP fromurl)>0 AND (case_sensitive=0) AND (decode_url<>0) AND (request_only=0) ) '
    . 'OR ( (' . $db->quote($currentfullurl_encoded) . ' REGEXP fromurl)>0 AND (case_sensitive=0) AND (decode_url=0) AND (request_only=0) ) '
    . ') '
    . 'AND published=1 '
    . 'ORDER BY ordering ) AS A '
    . 'WHERE A.skip=\'\' '
    . 'OR ( (' . $db->quote($currentfullurl) . ' REGEXP BINARY A.skip)=0 AND (case_sensitive<>0) AND (decode_url<>0) ) '
    . 'OR ( (' . $db->quote($currentfullurl_encoded) . ' REGEXP BINARY A.skip)=0 AND (case_sensitive<>0) AND (decode_url=0) ) '
    . 'OR ( (' . $db->quote($currentfullurl) . ' REGEXP A.skip)=0 AND (case_sensitive=0) AND (decode_url<>0) ) '
    . 'OR ( (' . $db->quote($currentfullurl_encoded) . ' REGEXP A.skip)=0 AND (case_sensitive=0) AND (decode_url=0) ) ');
    $items = $db->loadObjectList();
    $items = $this->skipGroups($items);
    $items = $this->skipErrorOnly($items);
    $total = count($items);
    // Notes: if more than one item matches with current URI, we takes only the first one with ordering set
    for ($i = 0; $i < $total; $i++)
    {
      if (!empty($items[$i]->tourl))
      {
        $this->processRule($items[$i]);
        break;
      }
    }

  }

  /**
   *
   * Do the internal redirect (200)
   *
   */
  private static function doInternalRedirect($basepath, $fromurl, $tourl)
  {
    // Clean "from url" for security reasons
    $fromURI = urldecode($fromurl);
    $fromURI = str_replace('"', '&quot;', $fromURI);
    $fromURI = str_replace('<', '&lt;', $fromURI);
    $fromURI = str_replace('>', '&gt;', $fromURI);
    $fromURI = preg_replace('/eval\((.*)\)/', '', $fromURI);
    $fromURI = preg_replace('/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/', '""', $fromURI);
    JUri::reset();
    $fromuri = JUri::getInstance($fromURI);
    $fromuri_vars = $fromuri->getQuery(true);

    // Unset "from url" query vars
    foreach ($fromuri_vars as $name => $value) {
      unset($_GET[$name]);
      unset($_POST[$name]);
      unset($_REQUEST[$name]);
    }

    // Clean "to url" for security reasons
    $toURI = urldecode($tourl);
    $toURI = str_replace('"', '&quot;', $toURI);
    $toURI = str_replace('<', '&lt;', $toURI);
    $toURI = str_replace('>', '&gt;', $toURI);
    $toURI = preg_replace('/eval\((.*)\)/', '', $toURI);
    $toURI = preg_replace('/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/', '""', $toURI);
    JUri::reset();
    $touri = JUri::getInstance($toURI);
    $touri_scheme = $touri->getScheme();
    $touri_host = $touri->getHost();
    $touri_path = $touri->getPath();
    $touri_query = $touri->getQuery();
    $touri_vars = $touri->getQuery(true);
    $touri_fragment = $touri->getFragment();

    // Set "to url" $_SERVER variables
    if ($touri_scheme === 'https') {
      $_SERVER['HTTPS'] = 'on';
    } else {
      if (isset($_SERVER['HTTPS'])) unset($_SERVER['HTTPS']);
    }
    $_SERVER['HTTP_HOST'] = $touri_host;
    $_SERVER['REQUEST_URI'] = $touri_path;
    if ( strlen($touri_query) > 0) { $_SERVER['REQUEST_URI'] .= '?' . $touri_query; }
    if ( strlen($touri_fragment) > 0) { $_SERVER['REQUEST_URI'] .= '#' . $touri_fragment; }
    $_SERVER['QUERY_STRING'] = $touri_query;

    // Variables php_self and script_name are always set with Joomla's index.php
    $_SERVER['PHP_SELF'] = $basepath . '/index.php';
    $_SERVER['SCRIPT_NAME'] = $basepath . '/index.php';

    // Variables path_info and path_translated are always unset
    unset($_SERVER['PATH_INFO']);
    unset($_SERVER['PATH_TRANSLATED']);

    // Unset mod_rewrite variables
    unset($_SERVER['SCRIPT_URL']);
    unset($_SERVER['SCRIPT_URI']);

    // Set 'SERVER' instance of JUri
    JUri::reset();
    $server_uri = JUri::getInstance();
    $server_uri->parse($toURI);

    // First, set the routed "to url" variables
    $router = JSite::getRouter();
    $result = $router->parse($touri);
    JRequest::set($result, 'get', true);

    // Then, set "to url" query variables
    $method = strtoupper($_SERVER['REQUEST_METHOD']);
    foreach ($touri_vars as $name => $value)
    {
      switch ($method)
      {
        case 'GET' :
          $_GET[$name] = $value;
          $_REQUEST[$name] = $value;
          break;
        case 'POST' :
          $_POST[$name] = $value;
          $_REQUEST[$name] = $value;
          break;
      }
    }
  }

}
