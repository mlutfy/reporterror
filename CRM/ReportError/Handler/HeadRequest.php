<?php

use CRM_ReportError_ExtensionUtil as E;

class CRM_ReportError_Handler_HeadRequest {

  /**
   * Bots, proxies or services such as Google/Facebook will do a HEAD
   * request to check whether the content has changed. CiviCRM pages
   * are rarely cached, so we will return headers to pretend that the
   * page is already expired.
   *
   * See: CRM_Core_Controller
   */
  static public function handler($vars, $options_overrides) {
    if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
      return FALSE;
    }

    // Reply with Bad Request
    http_response_code(400);
    CRM_Utils_System::civiExit();

    return TRUE;
  }

}
