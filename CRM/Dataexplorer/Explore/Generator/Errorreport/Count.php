<?php

use CRM_ReportError_ExtensionUtil as E;

class CRM_Dataexplorer_Explore_Generator_Errorreport_Count extends CRM_Dataexplorer_Explore_Generator_Errorreport {

  function config($options = []) {
    $this->_select[] = "count(*) as y";
    return parent::config($options);
  }

}
