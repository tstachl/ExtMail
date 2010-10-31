Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.Navigation = Ext.extend(Stachl.Navigation.Tree, {
	initComponent: function() {
		ExtMail.Email.Navigation.superclass.initComponent.call(this);
		
		var config = {
			title: _('Folders'),
			url: '/email/folders'
		};
		Ext.apply(this, config);
		ExtMail.Email.Navigation.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('extmail_email_navigation', ExtMail.Email.Navigation);