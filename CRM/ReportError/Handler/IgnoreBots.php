<?php

use CRM_ReportError_ExtensionUtil as E;

class CRM_ReportError_Handler_IgnoreBots {

  /**
   * Identify and possibly ignore bots.
   *
   * @param array $vars
   * @param array $options_overrides
   *
   * @return bool
   */
  public static function handler($vars, $options_overrides) {
    $bots_regexp = reporterror_setting_get('reporterror_bots_regexp', $options_overrides);

    if ($bots_regexp && preg_match('/' . $bots_regexp . '/', $_SERVER['HTTP_USER_AGENT'])) {

      $bots_sendreport = reporterror_setting_get('reporterror_bots_sendreport', $options_overrides);
      $bots_404 = reporterror_setting_get('reporterror_bots_404', $options_overrides);
      $vars['reporterror_subject'] = E::ts('reporterror_bot');

      if ($bots_sendreport) {
        CRM_ReportError_Utils::sendReport($vars, $options_overrides);
      }

      if ($bots_404) {
        CRM_ReportError_Utils::generate404();
        return TRUE;
      }

      // FIXME: should we continue going through other handlers?
      // For example, we might want to redirect a bot.
    }

    return FALSE;
  }

}
