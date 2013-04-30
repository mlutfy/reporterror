{* this template is used for setting-up the report Error extension *}
<div class="form-item">
<fieldset>
<div class="crm-block crm-form-block crm-reporterror-form-block">
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
  <h3>{ts}General Setup{/ts}</h3>
  <table class="form-layout-compressed" style="width:100%;">
    <tr class="crm-reporterror-form-block">
        <td class="label">{$form.mailto.label}</td>
        <td>{$form.mailto.html}<br />
            <span class="description">{ts}This usually is your domain administrator's email. Separate multiple email addresses with ','.{/ts}</span></td>
    </tr>
  </table>
  <h3>{ts}Contribution pages with no referrer{/ts}</h3>
  <table class="form-layout-compressed" style="width:100%;">
    <tr class="crm-reporterror-form-block">
        <td class="label">{$form.noreferer_sendreport.label}</td>
        <td>{$form.noreferer_sendreport.html}</td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.noreferer_handle.label}</td>
      <td>{$form.noreferer_handle.html}</td>
    </tr>
    <tr class="crm-reporterror-form-block">
        <td class="label">{$form.noreferer_pageid.label}</td>
        <td>{$form.noreferer_pageid.html}</td>
    </tr>
  </table>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div></fieldset>
</div> {* class="form-item" *}