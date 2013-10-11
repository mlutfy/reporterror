{* this template is used for setting-up the report Error extension *}
<div class="crm-block crm-form-block crm-reporterror-form-block">
  <h3>{ts domain='ca.bidon.reporterror'}General Setup{/ts}</h3>
  <table class="form-layout-compressed" style="width:100%;">
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.mailto.label}</td>
      <td>{$form.mailto.html}
        <div class="description">{ts}This usually is your domain administrator's email. Separate multiple email addresses with a comma (','). If left empty, no e-mails will be sent.{/ts}</div></td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.show_full_backtrace.label}</td>
      <td>{$form.show_full_backtrace.html}
        <div class="description">The full backtrace can provide more information on the variables passed to each function, but could expose more sensitive information.</div>
      </td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.show_post_data.label}</td>
      <td>{$form.show_post_data.html}
        <div class="description">POST data is usually the data submitted in forms. This can include sensitive information.</div>
      </td>
    </tr>
  </table>
  <h3>{ts domain='ca.bidon.reporterror'}Contribution pages with no referrer{/ts}</h3>
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
</div>
