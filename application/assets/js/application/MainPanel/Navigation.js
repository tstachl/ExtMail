Ext.ns('ExtMail');
ExtMail.Navigation = Ext.extend(Ext.Panel, {
	initComponent: function() {
	ExtMail.Navigation.superclass.initComponent.call(this);
	
	Ext.apply(this, {
		layout: 'accordion',
		layoutConfig: {
			titleCollapse: true,
			animate: false
		},
		items: [{
			title: _('Email'),
			xtype: 'extmail_email_navigation',
			mainpanel: this.mainpanel
		}, {
			title: _('Address Book'),
			html: '<p>Panel content!</p>'
		}, {
			title: _('Settings'),
			html: '<p>Panel content!</p>'
		}, {
			title: _('Logout'),
			html: '<p>Panel content!</p>'
		}]
	});
	ExtMail.Navigation.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('extmail_navigation', ExtMail.Navigation);