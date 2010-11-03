Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.Status = Ext.extend(Ext.ux.StatusBar, {
	//statusAlign: 'right',
	defaultText: 'Status',
	initComponent: function() {
		ExtMail.Email.Status.superclass.initComponent.call(this);
	}
});
Ext.reg('extmail_email_status', ExtMail.Email.Status);