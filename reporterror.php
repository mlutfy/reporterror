<?php

require_once 'reporterror.civix.php';

define('REPORTERROR_CIVICRM_SUBJECT_LEN', 100);
define('REPORTERROR_SETTINGS_GROUP', 'ReportError Extension');
define('REPORTERROR_EMAIL_SEPARATOR', ',');

/**
 * Implementation of hook_civicrm_config
 */
function reporterror_civicrm_config(&$config) {
  _reporterror_civix_civicrm_config($config);

  // override the error handler
  $config =& CRM_Core_Config::singleton( );
  $config->fatalErrorHandler = 'reporterror_civicrm_handler';
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function reporterror_civicrm_xmlMenu(&$files) {
  _reporterror_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function reporterror_civicrm_install() {
  return _reporterror_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function reporterror_civicrm_uninstall() {
  // Send final email
  $subject = ts('CiviCRM Error Report was uninstalled', array('domain' => 'ca.bidon.reporterror'));
  $output = $subject . _reporterror_civicrm_get_session_info();
  $to = CRM_Core_BAO_Setting::getItem(REPORTERROR_SETTINGS_GROUP, 'mailto');

  if (!empty($to)) {
    $destinations = explode(REPORTERROR_SETTINGS_GROUP, $to);
    foreach ($destinations as $dest) {
      $dest = trim($dest);
      reporterror_civicrm_send_mail($dest, $subject, $output);
    }
  }
  else {
    CRM_Core_Error::debug_log_message('Report Error Extension could not send since no email address was set.');
  }

  // Delete our settings
  $params = array(
    1 => array(REPORTERROR_SETTINGS_GROUP, 'String'),
  );

  $sql = "DELETE FROM civicrm_setting WHERE group_name = %1";
  $dao = CRM_Core_DAO::executeQuery($sql, $params);

  return _reporterror_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function reporterror_civicrm_enable() {
  // rebuild the menu so our path is picked up
  CRM_Core_Invoke::rebuildMenuAndCaches();

  return _reporterror_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function reporterror_civicrm_disable() {
  return _reporterror_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function reporterror_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _reporterror_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function reporterror_civicrm_managed(&$entities) {
  return _reporterror_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_navigationMenu
 */
function reporterror_civicrm_navigationMenu(&$params) {
/*
  _reporterror_civix_insert_navigationMenu($params, 'Administer/System Settings', array(
    'name'      => 'Report Error Settings',
    'url'        => 'civicrm/admin/reporterror',
    'permission' => 'administer CiviCRM',
  ));
*/

  // Get the ID of the 'Administer/System Settings' menu
  $adminMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Administer', 'id', 'name');
  $settingsMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'System Settings', 'id', 'name');

  // Skip adding menu if there is no administer menu
  if (! $adminMenuId) {
    CRM_Core_Error::debug_log_message('Report Error Extension could not find the Administer menu item. Menu item to configure this extension will not be added.');
    return;
  }

  if (! $settingsMenuId) {
    CRM_Core_Error::debug_log_message('Report Error Extension could not find the System Settings menu item. Menu item to configure this extension will not be added.');
    return;
  }

  // get the maximum key under administer menu
  $maxSettingsMenuKey = max(array_keys($params[$adminMenuId]['child'][$settingsMenuId]['child']));
  $nextSettingsMenuKey = $maxSettingsMenuKey + 1;

  $params[$adminMenuId]['child'][$settingsMenuId]['child'][$nextSettingsMenuKey] =  array(
    'attributes' => array(
      'name'       => 'Report Error Settings',
      'label'      => 'Report Error Settings',
      'url'        => 'civicrm/admin/setting/reporterror?reset=1',
      'permission' => 'administer CiviCRM',
      'parentID'   => $settingsMenuId,
      'navID'      => $nextSettingsMenuKey,
      'active'      => 1,
    ),
  );
}

/**
 * Custom error handler.
 * This is registered as a callback in hook_civicrm_config().
 *
 * @param $vars Array with the 'message' and 'code' of the error.
 */
function reporterror_civicrm_handler($vars, $options_overrides = array()) {
  $sendreport = TRUE;
  $generate_404 = FALSE;

  $redirect_path = NULL;
  $redirect_options = array();

  //
  // Try to handle the error in a more user-friendly way
  //

  // Contribution forms: error with no HTTP_REFERER (most likely a bot, restored session, or copy-pasted link)
  $config = CRM_Core_Config::singleton();
  $urlVar = $config->userFrameworkURLVar;
  $arg = explode('/', $_GET[$urlVar]);

  // Redirect for Contribution pages without a referrer (close / restore browser page)
  if ($arg[0] == 'civicrm' && $arg[1] == 'contribute' && $arg[2] == 'transact' && ! $_SERVER['HTTP_REFERER'] && $_SERVER['REQUEST_METHOD'] != 'HEAD') {
    $handle = reporterror_setting_get('noreferer_handle', $options_overrides);
    $pageid = reporterror_setting_get('noreferer_pageid', $options_overrides);
    $sendreport = reporterror_setting_get('noreferer_sendreport', $options_overrides, 1);

    if ($handle == 1 || ($handle == 2 && ! $pageid)) {
      $redirect_path = CRM_Utils_System::baseCMSURL();
    }
    elseif ($handle == 2) {
      $redirect_path = CRM_Utils_System::url('civicrm/contribute/transact', 'reset=1&id=' . $pageid);
    }
  }
  elseif ($arg[0] == 'civicrm' && $arg[1] == 'event' && ! $_SERVER['HTTP_REFERER'] && $_SERVER['REQUEST_METHOD'] != 'HEAD') {
    $handle = reporterror_setting_get('noreferer_handle_event', $options_overrides);
    $pageid = reporterror_setting_get('noreferer_handle_eventid', $options_overrides);
    $sendreport = reporterror_setting_get('noreferer_sendreport_event', $options_overrides, 1);

    if ($handle == 1 || ($handle == 2 && ! $pageid)) {
      $redirect_path = CRM_Utils_System::baseCMSURL();
    }
    elseif ($handle == 2) {
      $redirect_path = CRM_Utils_System::url('civicrm/event/register', 'reset=1&id=' . $pageid);
    }
  }

  // Identify and possibly ignore bots
  $is_bot = FALSE;
  $bots_regexp = reporterror_setting_get('bots_regexp', $options_overrides);

  if ($bots_regexp && preg_match('/' . $bots_regexp . '/', $_SERVER['HTTP_USER_AGENT'])) {
    $is_bot = TRUE;

    $bots_sendreport = reporterror_setting_get('bots_sendreport', $options_overrides);
    $bots_404 = reporterror_setting_get('bots_404', $options_overrides);

    if (! $bots_sendreport) {
      $sendreport = FALSE;
    }

    if ($bots_404) {
      $generate_404 = TRUE;
    }

  }

  // Send email report
  if ($sendreport) {
    $domain = CRM_Core_BAO_Domain::getDomain();
    $site_name = $domain->name;

    $len = REPORTERROR_CIVICRM_SUBJECT_LEN;

    $extra_info = array();

    if ($redirect_path) {
      $extra_info[] = ts('redirected', array('domain' => 'ca.bidon.reporterror'));
    }

    if ($is_bot) {
      $extra_info[] = ts('bot', array('domain' => 'ca.bidon.reporterror'));
    }

    if (count($extra_info)) {
      $subject = ts('CiviCRM error [%2] at %1', array(1 => $site_name, 2 => implode(',', $extra_info), 'domain' => 'ca.bidon.reporterror'));
    }
    else {
      $subject = ts('CiviCRM error at %1', array(1 => $site_name, 'domain' => 'ca.bidon.reporterror'));
    }

    if ($len) {
      $subject .= ' (' . substr($vars['message'], 0, $len) . ')';
    }

    $to = reporterror_setting_get('mailto', $options_overrides);

    if (!empty($to)) {
      $destinations = explode(REPORTERROR_EMAIL_SEPARATOR, $to);
      $output = reporterror_civicrm_generatereport($site_name, $vars, $redirect_path, $options_overrides);

      foreach ($destinations as $dest) {
        $dest = trim($dest);
        reporterror_civicrm_send_mail($dest, $subject, $output);
      }
    }
    else {
      CRM_Core_Error::debug_log_message('Report Error Extension could not send since no email address was set.');
    }
  }

  if ($generate_404) {
    $config = CRM_Core_Config::singleton();

    switch ($config->userFramework) {
      case 'Drupal':
      case 'Drupal6':
        drupal_not_found();
        drupal_exit();
        break;

      case 'Drupal8':
        // TODO: not tested.
        // use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        // throw new NotFoundHttpException();
        break;

      case 'WordPress':
        // TODO: not tested.
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        break;

      case 'Joomla':
        // TODO: not tested.
        header("HTTP/1.0 404 Not Found");
        break;

      default:
        header("HTTP/1.0 404 Not Found");
    }
  }

  // A redirection avoids displaying the error to the user.
  if ($redirect_path) {
    // 307 = temporary redirect. Assuming it reduces the chances that the browser
    // keeps the redirection in cache.
    CRM_Utils_System::redirect($redirect_path);
    return TRUE;
  }

  // We let CiviCRM display the regular fatal error
  return FALSE;
}

/**
 * Returns a plain text output for the e-mail report.
 */
function reporterror_civicrm_generatereport($site_name, $vars, $redirect_path, $options_overrides = array()) {
  $show_full_backtrace = reporterror_setting_get('show_full_backtrace', $options_overrides);
  $show_post_data = reporterror_setting_get('show_post_data', $options_overrides);
  $show_session_data = reporterror_setting_get('show_session_data', $options_overrides);

  $output = ts('There was a CiviCRM error at %1.', array(1 => $site_name)) . "\n";
  $output .= ts('Date: %1', array(1 => date('c'))) . "\n\n";

  if ($redirect_path) {
    $output .= ts("Error handling rules redirected the user to:") . "\n";
    $output .= $redirect_path . "\n\n";
  }

  // Error details
  $output .= "\n\n***ERROR***\n";
  $output .= _reporterror_civicrm_parse_array($vars);

  // The "last error" can sometimes help, but it can also mislead
  // (ex: PHP notice during the error).
  if (function_exists('error_get_last')) {
    $output .= "***LAST ERROR***\n";
    $output .= print_r(error_get_last(), TRUE);
  }

  // User information and the session variable
  $output .= _reporterror_civicrm_get_session_info($show_session_data);

  // Backtrace
  $output .= "\n\n***BACKTRACE***\n";

  $backtrace = debug_backtrace();
  $output .= CRM_Core_Error::formatBacktrace($backtrace, TRUE, 120);

  // $_POST
  if ($show_post_data) {
    $output .= "\n\n***POST***\n";
    $output .= _reporterror_civicrm_parse_array($_POST);
  }

  if ($show_full_backtrace) {
    $output .= "\n\n***FULL BACKTRACE***\n";

    foreach ($backtrace as $call) {
      $output .= "**next call**\n";
      $output .= _reporterror_civicrm_parse_array($call);
    }
  }

  return $output;
}

/**
 * Send the e-mail using CRM_Utils_Mail::send()
 */
function reporterror_civicrm_send_mail($to, $subject, $output) {
  $email = '';

  $result = civicrm_api('OptionValue', 'get', array('option_group_name' => 'from_email_address', 'is_default' => TRUE, 'version' => 3));

  if ($result['is_error']) {
    CRM_Core_Error::debug_log_message('Report Error Extension: failed to get the default from email address');
    return;
  }

  $val = array_pop($result['values']);
  $email = $val['label'];

  if (! $email) {
    return;
  }

  $params = array(
    'from' => $email,
    'toName' => 'Site Administrator',
    'toEmail' => $to,
    'subject' => $subject,
    'text' => $output,
  );

  $mail_sent = CRM_Utils_Mail::send($params);

  if (! $mail_sent) {
    CRM_Core_Error::debug_log_message('Report Error Extension: Could not send mail');
  }
}

/**
 *  Helper function to return a pretty print of the given array
 *
 *  @param array $array
 *    The array to print out.
 *  @return string
 *    The printed array.
 */
function _reporterror_civicrm_parse_array($array) {
  $output = '';

  $array = (array) $array;

  foreach ($array as $key => $value) {
    if (is_array($value) || is_object($value)) {
      $value = print_r($value, TRUE);
    }

    $key = str_pad($key . ':', 20, ' ');
    $output .= $key . _reporterror_civicrm_check_length($value) . "\n";
  }

  // Remove sensitive data.
  // We do this hackishly this way, because:
  // - doing a search/replace in the $array can cause changes in the $_SESSION, for example, because of references.
  // - re-writing print_r() seemed a bit ambitious, and likely to introduce bugs.
  $output = preg_replace('/\[credit_card_number\] => (\d{6})\d+/', '[credit_card_number] => \1[removed]', $output);
  $output = preg_replace('/\[cvv2\] => \d+/', '[cvv2] => [removed]', $output);
  $output = preg_replace('/\[password\] => [^\s]+/', '[password] => [removed]', $output);

  return $output . "\n";
}

/**
 *  Helper function to add elipses and return spaces if null
 *
 *  @param string $item
 *    String to check.
 *  @return string
 *    The truncated string.
 */
function _reporterror_civicrm_check_length($item) {
  if (is_null($item)) {
    return ' ';
  }

  if (strlen($item) > 2000) {
    $item = substr($item, 0, 2000) .'...';
  }

  return (string) $item;
}

/**
 *  Helper function to get user session info for email body.
 *
 *  @return string
 *    Partial email body string with user session info.
 */
function _reporterror_civicrm_get_session_info($show_session_data = FALSE) {
  $output = '';

  // User info
  $session = CRM_Core_Session::singleton();
  $userId = $session->get('userID');

  if ($userId) {
    $output .= "\n\n***LOGGED IN USER***\n";

    $params = array(
      'version' => 3,
      'id' => $userId,
      'return' => 'id,display_name,email',
    );

    $contact = civicrm_api('Contact', 'getsingle', $params);

    if ($contact['is_error']) {
      $output .= "Failed to fetch user info using the API:\n";
    }

    $output .= _reporterror_civicrm_parse_array($contact);
  }
  else {
    // Show the remote IP and user-agent of anon users, to facilitate
    // identification of bots and other source of false positives.
    $output .= "\n\n***ANONYMOUS USER***\n";
  }

  $output .= "REMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . "\n";
  $output .= "HTTP_USER_AGENT: " . $_SERVER['HTTP_USER_AGENT'] . "\n";

  if ($show_session_data) {
    $output .= "\n\n***SESSION***\n";
    $output .= _reporterror_civicrm_parse_array($_SESSION);
  }

  // $_SERVER
  $output .= "\n\n***SERVER***\n";
  $output .= _reporterror_civicrm_parse_array($_SERVER);
  return $output;
}

/**
 * Helper function to get a specific setting of the extension,
 * or lookup an override option.
 *
 * Option overrides is an array of settings that the calling function
 * can set to override the behavior of the report. For example, if a
 * payment processor caught an exception doing a curl/soap request, it
 * will probably want to disable the full backtrace and session info.
 */
function reporterror_setting_get($name, $options_overrides, $default = NULL) {
  if (isset($options_overrides[$name])) {
    return $options_overrides[$name];
  }

  return CRM_Core_BAO_Setting::getItem(REPORTERROR_SETTINGS_GROUP, $name, NULL, $default);
}

