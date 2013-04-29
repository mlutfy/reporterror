<?php

require_once 'reporterror.civix.php';

define('REPORTERROR_CIVICRM_SUBJECT_LEN', 100);
define('REPORTERROR_SETTINGS_GROUP', 'reporterror');

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
 
  /*
  // Contribution forms: error with no HTTP_REFERER (most likely a bot, restored session, or copy-pasted link)
  if (arg(0) == 'civicrm' && arg(1) == 'contribute' && arg(2) == 'transact' && ! $_SERVER['HTTP_REFERER']) {
    $handle = variable_get('civicrm_error_fallback_contrib_noreferer_handle', 0);
    $pageid = variable_get('civicrm_error_fallback_contrib_noreferer_dest', 0);

    if ($handle == 1 || ($handle == 2 && ! $pageid)) {
      $redirect_path = '<front>';
    }
    elseif ($handle == 2) {
      $redirect_path = 'civicrm/contribute/transact';
      $redirect_options['query'] = array(
        'reset' => 1,
        'id' => $pageid,
      );
    }

    $sendreport = variable_get('civicrm_error_fallback_contrib_noreferer_alert', 1);
  }

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
dsm($to, 'to');

    if (! $to) {
      // FIXME: log to error log?
      return;
    }

    $destinations = explode(',', $to);
    $output = civicrm_error_generatereport($vars, $redirect_path, $redirect_options);
  
    foreach ($destinations as $dest) {
      $dest = trim($dest);
      civicrm_error_send_mail($dest, $subject, $output);
    }
  }

  // A redirection avoids displaying the error to the user.
  if ($redirect_path) {
    // 307 = temporary redirect. Assuming it reduces the chances that the browser
    // keeps the redirection in cache.
    drupal_goto($redirect_path, $redirect_options, 307);
  }
}
