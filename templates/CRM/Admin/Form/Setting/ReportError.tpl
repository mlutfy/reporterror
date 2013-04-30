{* this template is used for setting-up the report Error extension *}
<div class="form-item">
<fieldset><legend>{ts}Setup{/ts}</legend>
<div class="crm-block crm-form-block crm-reporterror-form-block">
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
  <table class="form-layout-compressed">
    <tr class="crm-reporterror-form-block">
        <td class="label">{$form.domain.label}</td>
        <td>{$form.domain.html}</td>
    </tr>
{if $oauth_ok eq false}
    <tr class="crm-reporterror-form-block">
        <td class="label">{$form.oauth_email.label}</td>
        <td>{$form.oauth_email.html}<br />
            <span class="description">{ts}This usually is your domain administrator's email.{/ts}</span></td>
    </tr>
    <tr class="crm-reporterror-form-block">
        <td class="label">{$form.oauth_key.label}</td>
        <td>{$form.oauth_key.html}<br />
            <span class="description">{ts}Please click on the help icon at the end of this text for instructions.{/ts}</span>{help id="oauth"}</td>
    </tr>
    <tr class="crm-reporterror-form-block">
        <td class="label">{$form.oauth_secret.label}</td>
        <td>{$form.oauth_secret.html}<br />
            <span class="description">{ts}Please click on the help icon at the end of this text for instructions.{/ts}</span>{help id="oauth"}</td>
    </tr>
{else}
    <tr class="crm-reporterror-form-block">
        <td class="label">&nbsp;</td>
        <td>{ts}OAuth authentication and secrets fully configured and working.{/ts}</td>
    </tr>
{/if}
{if $registered eq false}
      <tr class="crm-reporterror-form-block">
        <td class="label">{$form.register.label}</td>
        <td>{$form.register.html} {help id="register"}</td>
    </tr>
{/if}
    <tr class="crm-reporterror-form-block">
        <td class="label">{$form.subscribed.label}</td>
        <td>{$form.subscribed.html} {help id="subscribe"}</td>
    </tr>
  </table>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div></fieldset>
<fieldset><legend>{ts}Status{/ts}</legend>
<div class="crm-block crm-content-block crm-discount-view-form-block">
{if $oauth_ok eq false}
  <p>{ts}Please enter required configuration information above to continue setup.{/ts}</p>
  <p>{ts}Per Google's restrictions, this extension can only work with Google Apps for Business or Education accounts. If you do have a free Google Apps account, you will need to upgrade.{/ts}</p>
{else}
  <p>{ts}Last sync{/ts}: {$job.last_sync} (<a href="{$job.log_url}">{ts}View log{/ts}</a>)</p>
  <p>{ts}Contacts remaining in queue{/ts}: {$job.remaining}</p>
  <p>{ts}Total contacts synchronized{/ts}: {$job.processed}</p>
  <p>{ts}To search CiviCRM contacts in Google, go to the Contacts apps and select 'Directory'.{/ts}
     {ts}Newly synch'ed contacts will take up to 24 hours to appear in Google due to their cache refresh policy.{/ts}
     {ts}You can install third-party applications such as
         '<a href="http://www.google.com/enterprise/marketplace/viewListing?productListingId=8056+6131117756098622400">Easy Shared Contacts</a> or
         '<a href="http://www.google.com/enterprise/marketplace/viewListing?productListingId=6723+662574286124949443">Smart Contacts Manager</a>'
         to see updates in real-time.{/ts}</p>
  <p>{ts}To search CiviCRM contacts on an iPhone, use the Contacts application, click the 'Groups' button up top and select 'Global Address List' from your Google account.{/ts}</p>
{/if}
</div></fieldset>
</div>
{literal}
<script type="text/javascript">
var dataUrl        = "{/literal}{$organizationURL}{literal}";
cj('#organization').autocomplete( dataUrl, {
                                      width        : 250,
                                      selectFirst  : false,
                                      matchCase    : true,
                                      matchContains: true
    }).result( function(event, data, formatted) {
        var foundContact   = ( parseInt( data[1] ) ) ? cj( "#organization_id" ).val( data[1] ) : cj( "#organization_id" ).val('');
    });

cj("form").submit(function() {
  if ( !cj('#organization').val() ) cj( "#organization_id" ).val('');
});

cj(function(){
//current organization default setting
var organizationId = "{/literal}{$currentOrganization}{literal}";
if ( organizationId ) {
    var dataUrl = "{/literal}{crmURL p='civicrm/ajax/rest' h=0 q="className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=contact&org=1&id=" }{literal}" + organizationId;
    cj.ajax({
        url     : dataUrl,
        async   : false,
        success : function(html){
            htmlText = html.split( '|' , 2);
            cj('input#organization').val(htmlText[0]);
            cj('input#organization_id').val(htmlText[1]);
        }
    });
}
});

cj("input#organization").click( function( ) {
    cj("input#organization_id").val('');
});

cj("#generate-code").click(function() {
    var chars = "abcdefghjklmnpqrstwxyz23456789";
    var len = 8;

    code = randomString(chars, len);
    cj("#code").val(code);

    return false;
});
</script>
{/literal}
