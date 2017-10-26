<?php

class CRM_ReportError_Handler_SmartGroupRefresh {

  /**
   * Try to handle a failing smartgroup refresh.
   * This will automatically disable a broken smartgroup.
   *
   * FIXME: add a configuration option, this should be opt-in.
   */
  static public function handling($vars, $options_overrides) {
    if (!isset($vars['exception'])) {
      return FALSE;
    }

    $extra_params = $vars['exception']->getExtraParams();

    if (!isset($extra_params['sql'])) {
      return FALSE;
    }

    if (preg_match('/^CREATE TEMPORARY TABLE civicrm_temp_group_contact_cache\d+ \(SELECT (\d+) as group_id/', $extra_params['sql'], $matches)) {
      $broken_group_id = $matches[1];

      $backtrace = debug_backtrace();

      foreach ($backtrace as $hop) {
        if ($hop['function'] == 'reporterror_civicrm_handler') {
          $t = $hop['args'][0]['exception']->getTrace();

          foreach ($t as $tt) {
            if ($tt['function'] == 'getGroupList') {
              $output = [
                'data' => [],
              ];

              $result = civicrm_api3('Group', 'getsingle', [
                'group_id' => $broken_group_id,
              ]);

              $description = $result['description'] . ' -- Disabled automatically: ' . $vars['exception']->getMessage();

              civicrm_api3('Group', 'create', [
                'group_id' => $broken_group_id,
                'description' => $description,
                'is_active' => 0,
              ]);

              $output['data'][] = [
                'id' => 99999,
                'count' => 1,
                'title' => ts('ERROR: Group ID %1 could not be loaded and has been disabled. This may be the result of a deleted custom field or a bug in a custom search.', [1 => $broken_group_id]),
                'description' => '',
                'group_type' => '',
                'visibility' => '',
                'links' => '',
                'created_by' => '',
                'DT_RowId' => 'row_99999',
                'DT_RowClass' => 'crm-group-parent',
                'DT_RowAttr' => [
                  'data-id' => 99999,
                  'data-entity' => 'group',
                ],
              ];

              echo json_encode($output);

              $vars['reporterror_subject'] = "SmartGroupRefresh";
              CRM_ReportError_Utils::sendReport($vars, $options_overrides);

              return TRUE;
            }
          }
        }
      }
    }

    return FALSE;
  }

}
