<?php

namespace Civi\ReportError;

use \Civi\DataExplorer\Event\DataExplorerEvent;
use CRM_ReportError_ExtensionUtil as E;

class Events {

  static public function fireDataExplorerBoot(DataExplorerEvent $event) {
    $sources = $event->getDataSources();
    $sources['errorreport'] = E::ts('Error Reports');
    $event->setDataSources($sources);

    $filters = $event->getFilters();
    $filters['is_bot'] = [
      'type' => 'items',
      'label' => 'Is Bot?',
      'items' => [
        1 => ts('Yes'),
        2 => ts('No'),
      ],
      'depends' => [
        'errorreport',
      ],
    ];
    $filters['is_handled'] = [
      'type' => 'items',
      'label' => 'Is Handled?',
      'items' => [
        1 => ts('Yes'),
        2 => ts('No'),
      ],
      'depends' => [
        'errorreport',
      ],
    ];
    $event->setFilters($filters);
  }

}
