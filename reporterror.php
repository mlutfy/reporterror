<?php

require_once 'reporterror.civix.php';

define('REPORTERROR_CIVICRM_SUBJECT_LEN', 100);
define('REPORTERROR_SETTINGS_GROUP', 'ReportError Extension');

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

  // insert into civicrm_setting (group_name, name, value, domain_id) values ('reporterror', 'mailto', 'mathieu@bidon.ca', 1);

  return _reporterror_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function reporterror_civicrm_uninstall() {
  return _reporterror_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function reporterror_civicrm_enable() {
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
function reporterror_civicrm_navigationMenu( &$params ) {
  _reporterror_civix_insert_navigationMenu($params, 'Administer/System Settings', array(
    'name'      => 'Report Error Settings',
    'url'        => 'civicrm/admin/reporterror',
    'permission' => 'administer CiviCRM',
  ));
}

/**
 *  Custom error function
 *  Set CiviCRM » Administer CiviCRM » Global Settings » Debugging » Fatal Error Handler
 *  to use this function.
 */
function reporterror_civicrm_handler($vars) {
  $sendreport = TRUE;
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
  if ($arg[0] == 'civicrm' && $arg[1] == 'contribute' && $arg[2] == 'transact' && ! $_SERVER['HTTP_REFERER']) {
    $handle = CRM_Core_BAO_Setting::getItem(REPORTERROR_SETTINGS_GROUP, 'noreferer_handle');
    $pageid = CRM_Core_BAO_Setting::getItem(REPORTERROR_SETTINGS_GROUP, 'noreferer_pageid');
    $sendreport = CRM_Core_BAO_Setting::getItem(REPORTERROR_SETTINGS_GROUP, 'noreferer_sendreport', NULL, 1);

    if ($handle == 1 || ($handle == 2 && ! $pageid)) {
      $redirect_path = CRM_Utils_System::baseCMSURL();
    }
    elseif ($handle == 2) {
      $redirect_path = CRM_Utils_System::url('civicrm/contribute/transact', 'reset=1&id='.$pageid);
    }
  }

  /*
  if (arg(0) == 'civicrm' && arg(1) == 'contribute' && arg(2) == 'campaign' && ! isset($_REQUEST['component'])) {
    $handle = variable_get('civicrm_error_fallback_pcp_nocomponent_handle', 0);
    $pageid = $_REQUEST['pageId'];

    if ($handle == 1) {
      $redirect_path = '<front>';
    }
    elseif ($handle == 2) {
      $redirect_path = 'civicrm/contribute/campaign';
      $redirect_options['query'] = array(
        'reset' => 1,
        'action' => 'add',
        'pageId' => $pageid,
        'component' => 'contribute',
      );
    }

    $sendreport = variable_get('civicrm_error_fallback_pcp_nocomponent_alert', 1);
  }
  */

  // Send email report
  if ($sendreport) {
    $domain = CRM_Core_BAO_Domain::getDomain();
    $site_name = $domain->name;

    $len = REPORTERROR_CIVICRM_SUBJECT_LEN;
  
    if ($redirect_path) {
      $subject = ts('CiviCRM error [redirected] at %1', array(1 => $site_name, 'domain' => 'ca.bidon.reporterror'));
    }
    else {
      $subject = ts('CiviCRM error at %1', array(1 => $site_name, 'domain' => 'ca.bidon.reporterror'));
    }

    if ($len) {
      $subject .= ' (' . substr($vars['message'], 0, $len) . ')';
    }
  
    $to = CRM_Core_BAO_Setting::getItem(REPORTERROR_SETTINGS_GROUP, 'mailto');

    if (! $to) {
      // FIXME: log to error log?
      return;
    }

    $destinations = explode(',', $to);
    $output = reporterror_civicrm_generatereport($site_name, $vars, $redirect_path);
  
    foreach ($destinations as $dest) {
      $dest = trim($dest);
      reporterror_civicrm_send_mail($dest, $subject, $output);
    }
  }

  // A redirection avoids displaying the error to the user.
  if ($redirect_path) {
    // 307 = temporary redirect. Assuming it reduces the chances that the browser
    // keeps the redirection in cache.
    CRM_Utils_System::redirect($redirect_path);
  }
}

/**
 * Returns a plain text output for the e-mail report.
 */
function reporterror_civicrm_generatereport($site_name, $vars, $redirect_path) {
  $output = ts('There was a CiviCRM error at %1.', array(1 => $site_name)) . "\n";
  $output .= ts('Date: %1', array(1 => date('c'))) . "\n\n";

  if ($redirect_path) {
    $output .= ts("Error handling rules redirected the user to:") . "\n";
    $output .= $redirect_path . "\n\n";
  }

  // Error details
  if (function_exists('error_get_last')) {
    $output .= "***ERROR***\n";
    $output .= print_r(error_get_last(), TRUE);
  }

  $output .= print_r($vars, TRUE);

  // User info
  $session = CRM_Core_Session::singleton();
  $userId = $session->get('userID');
  $params = array(
    'version' => 3,
    'id' => $userId,
    'return' => 'id,display_name,email',
  );
  $contact = civicrm_api('Contact', 'getsingle', $params);
  $output .= "\n\n***LOGGED IN USER***\n";
  $output .= _reporterror_civicrm_parse_array($contact);

  // $_SERVER
  $output .= "\n\n***SERVER***\n";
  $output .= _reporterror_civicrm_parse_array($_SERVER);

  // Backtrace
  $backtrace = debug_backtrace();
  $output .= "\n\n***BACKTRACE***\n";
  foreach ($backtrace as $call) {
    $output .= "**next call**\n";
    $output .= _reporterror_civicrm_parse_array($call);
  }

  return $output;
}

/**
 * Send the e-mail using drupal_mail()
 */
function reporterror_civicrm_send_mail($to, $subject, $output) {
    if ($domain_id = CRM_Core_Config::domainID()) {
      // Gather information from domain settings
      $params = array('id' => $domain_id);
      CRM_Core_BAO_Domain::retrieve($params, $domain);
      unset($params['id']);
      $locParams = $params + array('entity_id' => $domain_id, 'entity_table' => 'civicrm_domain');
      $defaults = CRM_Core_BAO_Location::getValues($locParams);
      $email_struct = reset(CRM_Utils_Array::value('email', $defaults));
      $email = CRM_Utils_Array::value('email', $email_struct);
    }
    if (!$email) { // FIXME: Just in case ...
      $email = 'nobody@nowhere.com';
    }

    $params = array(
    'from' => $email,
    'toName' => 'Site Administrator',
    'toEmail' => $to,
    'subject' => $subject,
    'text' => $output,
  );

  if (!CRM_Utils_Mail::send($params)) {
    //FIXME: Output an error message to log
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

  foreach ((array)$array as $key => $value) {
    if (is_array($value) || is_object($value)) {
      $value = print_r($value, TRUE);
    }
    $key = str_pad($key .':', 20, ' ');
    $output .= $key . (string)_reporterror_civicrm_check_length($value) ." \n";
  }
  return $output ."\n";
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
  return $item;
}

