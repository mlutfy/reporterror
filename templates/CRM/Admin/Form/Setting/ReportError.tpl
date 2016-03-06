{* this template is used for setting-up the report Error extension *}
<div class="crm-block crm-form-block crm-reporterror-form-block">
  <h3>{ts domain='ca.bidon.reporterror'}General Setup{/ts}</h3>
  <table class="form-layout-compressed" style="width:100%;">
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.mailto.label}</td>
      <td>{$form.mailto.html}
        <div class="description">{ts domain="ca.bidon.reporterror"}This usually is your domain administrator's email. Separate multiple email addresses with a comma (','). If left empty, no e-mails will be sent.{/ts}</div></td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.show_full_backtrace.label}</td>
      <td>{$form.show_full_backtrace.html}
        <div class="description">{ts domain='ca.bidon.reporterror'}The full backtrace can provide more information on the variables passed to each function, but could expose more sensitive information.{/ts}</div>
      </td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.show_post_data.label}</td>
      <td>{$form.show_post_data.html}
        <div class="description">{ts domain='ca.bidon.reporterror'}POST data is usually the data submitted in forms. This can include sensitive information.{/ts}</div>
      </td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.show_session_data.label}</td>
      <td>{$form.show_session_data.html}
        <div class="description">{ts domain='ca.bidon.reporterror'}Session data can provide clues, but should probably be disabled most of the time, as it can include sensitive information.{/ts}</div>
      </td>
    </tr>
  </table>

  <h3>{ts domain='ca.bidon.reporterror'}Contribution pages with no referrer{/ts}</h3>

  <p>{ts domain='ca.bidon.reporterror'}Sometimes users might restore their browser session or share the link of the contribution 'thank you' page, which will result in a fatal error. You can use the options below to redirect visitors to a more relevant location.{/ts}</p>

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

  <h3>{ts domain='ca.bidon.reporterror'}Event registration pages with no referrer{/ts}</h3>

  <p>{ts domain='ca.bidon.reporterror'}Sometimes users might restore their browser session or share the link of the event confirmation page, which will result in a fatal error. You can use the options below to redirect visitors to a more relevant location.{/ts}</p>

  <table class="form-layout-compressed" style="width:100%;">
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.noreferer_sendreport_event.label}</td>
      <td>{$form.noreferer_sendreport_event.html}</td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.noreferer_handle_event.label}</td>
      <td>{$form.noreferer_handle_event.html}</td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.noreferer_handle_eventid.label}</td>
      <td>{$form.noreferer_handle_eventid.html}</td>
    </tr>
  </table>

  <h3>Bots and crawlers</h3>

  <p>{ts domain='ca.bidon.reporterror'}Web crawlers used by search engines can often generate a lot of errors. In some cases, this might be because you have invalid links, but in most cases, the bots are just being annoying and crawling where they shouldn't.{/ts}</p>

  <table class="form-layout-compressed" style="width:100%;">
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.bots_sendreport.label}</td>
      <td>
        {$form.bots_sendreport.html}
      </td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.bots_404.label}</td>
      <td>
        {$form.bots_404.html}
        <p class="description">By default, CiviCRM always responds '200 OK', even if there was a fatal error. By responding to the request with a '404 not found' code, the bot is less likely to try again.</p>
      </td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.bots_regexp.label}</td>
      <td>
        {$form.bots_regexp.html}
        <p class="description">{ts domain='ca.bidon.reporterror' 1='(Googlebot|bingbot)'}If in doubt, leave this as is. The default is: !1{/ts}</p>
      </td>
    </tr>
  </table>

  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
