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
      'url'        => 'civicrm/admin/setting/reporterror&reset=1',
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
      $redirect_path = CRM_Utils_System::url('civicrm/contribute/transact', 'reset=1&id=' . $pageid);
    }
  }

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

    if (!empty($to)) {
      $destinations = explode(REPORTERROR_SETTINGS_GROUP, $to);
      $output = reporterror_civicrm_generatereport($site_name, $vars, $redirect_path);

      foreach ($destinations as $dest) {
        $dest = trim($dest);
        reporterror_civicrm_send_mail($dest, $subject, $output);
      }
    }
    else {
      CRM_Core_Error::debug_log_message('Report Error Extension could not send since no email address was set.');
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
function reporterror_civicrm_generatereport($site_name, $vars, $redirect_path) {
  $show_full_backtrace = CRM_Core_BAO_Setting::getItem(REPORTERROR_SETTINGS_GROUP, 'show_full_backtrace');
  $show_post_data = CRM_Core_BAO_Setting::getItem(REPORTERROR_SETTINGS_GROUP, 'show_post_data');

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

  $output .= _reporterror_civicrm_get_session_info();

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

/**
 *  Helper function to get user session info for email body.
 *
 *  @return string
 *    Partial email body string with user session info.
 */
function _reporterror_civicrm_get_session_info() {
  $output = '';

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
  return $output;
}

