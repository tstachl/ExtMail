Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.MainPanel = Ext.extend(Stachl.Module, {
	west: 'extmail_email_navigation',
	westConfig: {
		split: true,
		width: 250		
	},
	center: 'extmail_email_cardcontainer',
	south: 'extmail_email_status'
});
Ext.reg('extmail_email_mainpanel', ExtMail.Email.MainPanel);