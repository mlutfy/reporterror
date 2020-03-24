<?php

use CRM_ReportError_ExtensionUtil as E;

class CRM_Dataexplorer_Explore_Generator_Errorreport extends CRM_Dataexplorer_Explore_Generator {
  use CRM_Dataexplorer_Explore_Generator_GroupbyDateTrait;

  protected $_options;

  function config($options = []) {
    if ($this->_configDone) {
      return $this->_config;
    }

    $defaults = [
      'y_label' => E::ts('Error'),
      'y_series' => 'hours',
      'y_type' => 'number',
    ];

    $this->_options = array_merge($defaults, $options);

    // It helps to call this here as well, because some filters affect the groupby options.
    // FIXME: see if we can call it only once, i.e. remove from data(), but not very intensive, so not a big deal.
    $params = [];
    $this->whereClause($params);

    // We can only have 2 groupbys, otherwise would be too complicated
    switch (count($this->_groupBy)) {
      case 0:
        $this->_config['axis_x'] = [
          'label' => E::ts('Total'),
          'type' => 'number',
        ];
        $this->_config['axis_y'][] = [
          'label' => $this->_options['y_label'],
          'type' => $this->_options['y_type'],
          'series' => $this->_options['y_series'],
        ];

        $this->_select[] = '"Total" as x';
        break;
      case 1:
      case 2:
        // Find all the labels for this type of group by
        if (in_array('period-year', $this->_groupBy)) {
          $this->configGroupByPeriodYear('p.event_date');
        }
        if (in_array('period-month', $this->_groupBy)) {
          $this->configGroupByPeriodMonth('p.event_date');
        }
        if (in_array('period-week', $this->_groupBy)) {
          $this->configGroupByPeriodWeek('p.event_date');
        }
        if (in_array('period-day', $this->_groupBy)) {
          $this->configGroupByPeriodDay('p.event_date');
        }
        if (in_array('period-hour', $this->_groupBy)) {
          $this->configGroupByPeriodHour('p.event_date');
        }

        break;

      default:
        throw new Exception('Cannot groupby on ' . count($this->_groupBy) . ' elements. Max 2 allowed.');
    }

    // This happens if we groupby 'period' (month), but nothing else.
    if (empty($this->_config['axis_y'])) {
      $this->_config['axis_y'][] = [
        'label' => $this->_options['y_label'],
        'type' => $this->_options['y_type'],
        'series' => $this->_options['y_series'],
      ];
    }

    $this->_configDone = TRUE;
    return $this->_config;
  }

  function data() {
    $data = [];
    $params = [];

    // This makes it easier to check specific exceptions later on.
    $this->config();

    $this->_from[] = "civicrm_error_report as p ";

    // FIXME Yuk.
    if (in_array('period-year', $this->_groupBy)) {
      $this->queryAlterPeriod('year', 'p.event_date');
    }
    if (in_array('period-month', $this->_groupBy)) {
      $this->queryAlterPeriod('month', 'p.event_date');
    }
    if (in_array('period-week', $this->_groupBy)) {
      $this->queryAlterPeriod('week', 'p.event_date');
    }
    if (in_array('period-day', $this->_groupBy)) {
      $this->queryAlterPeriod('day', 'p.event_date');
    }
    if (in_array('period-hour', $this->_groupBy)) {
      $this->queryAlterPeriod('hour', 'p.event_date');
    }

    $where = $this->whereClause($params);
    $has_data = FALSE;

    $sql = 'SELECT ' . implode(', ', $this->_select) . ' '
         . ' FROM ' . implode(' ', $this->_from)
         . (!empty($where) ? ' WHERE ' . $where : '')
         . (!empty($this->_group) ? ' GROUP BY ' . implode(', ', $this->_group) : '');

    $dao = $this->executeQuery($sql, $params);

    while ($dao->fetch()) {
      if ($dao->x && $dao->y) {
        $has_data = TRUE;
        $x = $dao->x;

        if (isset($this->_config['x_translate']) && isset($this->_config['x_translate'][$x])) {
          $x = $this->_config['x_translate'][$x];
        }

        if (!empty($dao->yy)) {
          if (isset($this->_config['y_translate']) && isset($this->_config['y_translate'][$dao->yy])) {
            $yy = $this->_config['y_translate'][$dao->yy];
            $data[$x][$yy] = $dao->y;
          }
          else {
            $data[$x][$dao->yy] = $dao->y;
          }
        }
        else {
          $ylabel = $this->_options['y_label'];
          $data[$x][$ylabel] = $dao->y;
        }
      }
    }

    // FIXME: if we don't have any results, and we are querying two
    // types of data, the 2nd column of results (CSV) might get bumped into
    // the first column. This really isn't ideal, should fix the CSV merger.
    if (! $has_data) {
      $tlabel = $this->_config['axis_x']['label'];
      $data[$tlabel][$ylabel] = 0;
    }

    return $data;
  }

  function whereClause(&$params) {
    $where_clauses = array();
    $where_extra = '';

    $this->whereClauseCommon($params);

    foreach ($this->_filters as $filter) {
      // foo[0] will have 'period-start' and foo[1] will have 2014-09-01
      $foo = explode(':', $filter);

      // bar[0] will have 'period' and bar[1] will have 'start'
      $bar = explode('-', $foo[0]);

      if ($bar[0] == 'period') {
        // Transform to MySQL date: remove the dashes in the date (2014-09-01 -> 20140901).
        $foo[1] = str_replace('-', '', $foo[1]);

        if ($bar[1] == 'start' && ! empty($foo[1])) {
          $params[1] = array($foo[1], 'Timestamp');
          $where_clauses[] = 'p.event_date >= %1';
        }
        elseif ($bar[1] == 'end' && ! empty($foo[1])) {
          $params[2] = array($foo[1] . '235959', 'Timestamp');
          $where_clauses[] = 'p.event_date <= %2';
        }
      }
    }

    $where = implode(' AND ', $where_clauses);
    return $where;
  }

}
