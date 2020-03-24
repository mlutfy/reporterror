<?php
use CRM_ReportError_ExtensionUtil as E;

class CRM_ReportError_BAO_ErrorReport extends CRM_ReportError_DAO_ErrorReport {

  /**
   * Create a new ErrorReport based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_ReportError_DAO_ErrorReport|NULL
   *
  public static function create($params) {
    $className = 'CRM_ReportError_DAO_ErrorReport';
    $entityName = 'ErrorReport';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
