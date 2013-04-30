<?php
/*
 +--------------------------------------------------------------------------+
 | Copyright IT Bliss LLC (c) 2012-2013                                     |
 +--------------------------------------------------------------------------+
 | This program is free software: you can redistribute it and/or modify     |
 | it under the terms of the GNU Affero General Public License as published |
 | by the Free Software Foundation, either version 3 of the License, or     |
 | (at your option) any later version.                                      |
 |                                                                          |
 | This program is distributed in the hope that it will be useful,          |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 | GNU Affero General Public License for more details.                      |
 |                                                                          |
 | You should have received a copy of the GNU Affero General Public License |
 | along with this program.  If not, see <http://www.gnu.org/licenses/>.    |
 +--------------------------------------------------------------------------+
*/

class CRM_Admin_Form_Setting_ReportError extends CRM_Admin_Form_Setting {
  protected $_values;
  protected $_oauth_ok;
  protected $_scheduledJob;

  function preProcess() {
    // Needs to be here as from is build before default values are set
    $this->_values = CRM_Core_BAO_Setting::getItem(REPORTERROR_SETTINGS_GROUP);
  }

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    $this->applyFilter('__ALL__', 'trim');
    $element =& $this->add('text',
      'mailto',
      ts('Error Report Recipient'),
      $this->_values['mailto'],
      true);

    $results = civicrm_api('ContributionPage', 'get', array('version' => 3, 'is_active' => 1));
    if($results['is_error'] == 0) {
      $contribution_pages = $results['values'];
    }
    else {
      $contribution_pages = array();
    }

    $contribution_pages = array_merge(array(0 => ts('-Select-')), $contribution_pages);

    $radio_choices = array(
      '0' => ts('do nothing (show the CiviCRM error)', array('domain' => 'ca.bidon.reporterror')),
      '1' => ts('redirect to front page of CMS (recommended to avoid confusion to users)', array('domain' => 'ca.bidon.reporterror')),
      '2' => ts('redirect to a specific contribution page', array('domain' => 'ca.bidon.reporterror'))
    );

    $element = $this->addRadio('noreferer_handle',
      ts('Enable transparent redirection?', array('domain' => 'ca.bidon.reporterror')),
      $radio_choices
     );

    $element = $this->addYesNo('noreferer_sendreport',
      ts('Send error reports for this error?', array('domain' => 'ca.bidon.reporterror'))
    );

    $element = $this->add('select',
      'noreferer_pageid',
      ts('Redirect to Contribution Page', array('domain' => 'ca.bidon.reporterror')),
      $contribution_pages,
      true);

    $this->addRule('mailto', ts('Please enter a valid email address.',
      array('domain' => 'ca.bidon.reporterror')), 'email');

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    ));
  }

  function setDefaultValues() {
    $defaults = $this->_values;
    return $defaults;
  }

  /**
   * Function to validate the form
   *
   * @access public
   * @return None
   */

  /**
   * Function to process the form
   *
   * @access public
   * @return None
   */
  public function postProcess() {
    // store the submitted values in an array
    $params = $this->exportValues();

    // we will return to this form
    $session = CRM_Core_Session::singleton();
    $session->replaceUserContext(CRM_Utils_System::url('civicrm/admin/reporterror', $resetStr));


  } //end of function

} // end class
