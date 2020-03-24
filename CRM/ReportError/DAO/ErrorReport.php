<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from /home/gitlab-runner/buildkit/build/pr16838.dev501.symbiodev.xyz/sites/default/files/civicrm/ext/reporterror/xml/schema/CRM/ReportError/ErrorReport.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:19e6f3936cda01fed9b7ccc2c7b170ef)
 */

/**
 * Database access object for the ErrorReport entity.
 */
class CRM_ReportError_DAO_ErrorReport extends CRM_Core_DAO {

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_error_report';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = FALSE;

  /**
   * Unique ErrorReport ID
   *
   * @var int
   */
  public $id;

  /**
   * Date and time that the error occurred.
   *
   * @var datetime
   */
  public $event_date;

  /**
   * @var string
   */
  public $url;

  /**
   * IP address of the visitor who triggered the error.
   *
   * @var string
   */
  public $ip;

  /**
   * Whether the error was triggerred by a bot.
   *
   * @var bool
   */
  public $is_bot;

  /**
   * Whether the error was handled by reporterror.
   *
   * @var bool
   */
  public $is_handled;

  /**
   * Short one-line description of the error.
   *
   * @var string
   */
  public $message;

  /**
   * Details about the error (headers, backtrace, etc).
   *
   * @var longtext
   */
  public $report;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_error_report';
    parent::__construct();
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => CRM_ReportError_ExtensionUtil::ts('Unique ErrorReport ID'),
          'required' => TRUE,
          'where' => 'civicrm_error_report.id',
          'table_name' => 'civicrm_error_report',
          'entity' => 'ErrorReport',
          'bao' => 'CRM_ReportError_DAO_ErrorReport',
          'localizable' => 0,
        ],
        'event_date' => [
          'name' => 'event_date',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => CRM_ReportError_ExtensionUtil::ts('Event Date'),
          'description' => CRM_ReportError_ExtensionUtil::ts('Date and time that the error occurred.'),
          'where' => 'civicrm_error_report.event_date',
          'table_name' => 'civicrm_error_report',
          'entity' => 'ErrorReport',
          'bao' => 'CRM_ReportError_DAO_ErrorReport',
          'localizable' => 0,
        ],
        'url' => [
          'name' => 'url',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => CRM_ReportError_ExtensionUtil::ts('Url'),
          'maxlength' => 128,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_error_report.url',
          'table_name' => 'civicrm_error_report',
          'entity' => 'ErrorReport',
          'bao' => 'CRM_ReportError_DAO_ErrorReport',
          'localizable' => 0,
        ],
        'ip' => [
          'name' => 'ip',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => CRM_ReportError_ExtensionUtil::ts('Ip'),
          'description' => CRM_ReportError_ExtensionUtil::ts('IP address of the visitor who triggered the error.'),
          'maxlength' => 128,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_error_report.ip',
          'table_name' => 'civicrm_error_report',
          'entity' => 'ErrorReport',
          'bao' => 'CRM_ReportError_DAO_ErrorReport',
          'localizable' => 0,
        ],
        'is_bot' => [
          'name' => 'is_bot',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => CRM_ReportError_ExtensionUtil::ts('Is a bot?'),
          'description' => CRM_ReportError_ExtensionUtil::ts('Whether the error was triggerred by a bot.'),
          'where' => 'civicrm_error_report.is_bot',
          'default' => '0',
          'table_name' => 'civicrm_error_report',
          'entity' => 'ErrorReport',
          'bao' => 'CRM_ReportError_DAO_ErrorReport',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
          ],
        ],
        'is_handled' => [
          'name' => 'is_handled',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => CRM_ReportError_ExtensionUtil::ts('Is handled?'),
          'description' => CRM_ReportError_ExtensionUtil::ts('Whether the error was handled by reporterror.'),
          'where' => 'civicrm_error_report.is_handled',
          'default' => '0',
          'table_name' => 'civicrm_error_report',
          'entity' => 'ErrorReport',
          'bao' => 'CRM_ReportError_DAO_ErrorReport',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
          ],
        ],
        'message' => [
          'name' => 'message',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => CRM_ReportError_ExtensionUtil::ts('Message'),
          'description' => CRM_ReportError_ExtensionUtil::ts('Short one-line description of the error.'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_error_report.message',
          'table_name' => 'civicrm_error_report',
          'entity' => 'ErrorReport',
          'bao' => 'CRM_ReportError_DAO_ErrorReport',
          'localizable' => 0,
        ],
        'report' => [
          'name' => 'report',
          'type' => CRM_Utils_Type::T_LONGTEXT,
          'title' => CRM_ReportError_ExtensionUtil::ts('Report'),
          'description' => CRM_ReportError_ExtensionUtil::ts('Details about the error (headers, backtrace, etc).'),
          'where' => 'civicrm_error_report.report',
          'table_name' => 'civicrm_error_report',
          'entity' => 'ErrorReport',
          'bao' => 'CRM_ReportError_DAO_ErrorReport',
          'localizable' => 0,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'error_report', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'error_report', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}