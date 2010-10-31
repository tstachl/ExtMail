Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.MainPanel = Ext.extend(Stachl.Module, {
	moduleNavigation: 'extmail_email_navigation',
	moduleContainer: 'extmail_email_cardcontainer'
});
Ext.reg('extmail_email_mainpanel', ExtMail.Email.MainPanel);