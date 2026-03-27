<#import "template.ftl" as layout>
<@layout.registrationLayout displayMessage=!messagesPerField.existsError('username','password') displayInfo=false; section>
    <#if section = "header">
        ${msg("loginAccountTitle")}
    <#elseif section = "form">
    <div id="kc-form">
      <div id="kc-form-wrapper">
        <#if realm.password>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <form action="${url.loginAction}" method="post">
                    <input type="hidden" name="username" value="john.doe@example.com" />
                    <input type="hidden" name="password" value="Pa55w0rd" />
                    <input type="hidden" name="credentialId" <#if auth.selectedCredential?has_content>value="${auth.selectedCredential}"</#if> />
                    <input type="submit" class="${properties.kcButtonClass!} ${properties.kcButtonPrimaryClass!} ${properties.kcButtonBlockClass!} ${properties.kcButtonLargeClass!}" value="Log in as user" />
                </form>
                <form action="${url.loginAction}" method="post">
                    <input type="hidden" name="username" value="chuck.norris@example.com" />
                    <input type="hidden" name="password" value="Pa55w0rd" />
                    <input type="hidden" name="credentialId" <#if auth.selectedCredential?has_content>value="${auth.selectedCredential}"</#if> />
                    <input type="submit" class="${properties.kcButtonClass!} ${properties.kcButtonPrimaryClass!} ${properties.kcButtonBlockClass!} ${properties.kcButtonLargeClass!}" value="Log in as admin" />
                </form>
            </div>
        </#if>
      </div>
    </div>
    </#if>
</@layout.registrationLayout>
