Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.Navigation = Ext.extend(Stachl.Navigation.Tree, {
	initComponent: function() {
		App.getInstance().updateLoading(_('Loading folders ...'));
	
		ExtMail.Email.Navigation.superclass.initComponent.call(this);
		
		var config = {
			url: '/email/folders',
			loadMask: false
		};
		Ext.apply(this, config);
		
		ExtMail.Email.Navigation.superclass.initComponent.apply(this, arguments);
		
		this.getLoader().addListener('beforeload', function() {
			this.mainpanel.getSouth().showBusy(_('Loading folders ...'));
		}, this);
		this.getLoader().addListener('load', function() {
			this.mainpanel.getSouth().clearStatus();
		}, this);
	}
});
Ext.reg('extmail_email_navigation', ExtMail.Email.Navigation);