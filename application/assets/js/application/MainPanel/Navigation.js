Ext.ns('ExtMail');
ExtMail.Navigation = Ext.extend(Ext.Panel, {
	logoutPrepared: false,
	initComponent: function() {
		ExtMail.Navigation.superclass.initComponent.apply(this, arguments);
		
		this.logoutId = Ext.id();
		this.emailNavigationId = Ext.id();
		
		Ext.apply(this, {
			layout: 'accordion',
			layoutConfig: {
				titleCollapse: true,
				animate: true,
				fill: true
			},
			items: [{
				title: _('Email'),
				id: this.emailNavigationId,
				xtype: 'extmail_email_navigation',
				iconCls: 'ico_email',
				mainpanel: this.mainpanel
			}, {
//				title: _('Address Book'),
//				html: '<p>Panel content!</p>'
//			}, {
//				title: _('Settings'),
//				html: '<p>Panel content!</p>'
//			}, {
				title: _('Logout'),
				id: this.logoutId,
				iconCls: 'ico_logout',
				html: '&nbsp;'
			}]
		});
		
		this.addListener('afterlayout', this.prepareLogout);
		
		ExtMail.Navigation.superclass.initComponent.call(this);
	},
	prepareLogout: function() {
		if (this.logoutPrepared === false) {
			this.get(this.logoutId).header.removeAllListeners();
			this.get(this.logoutId).header.addListener('click', function() {
				location.href = '/login/logout';
			});
			Ext.destroy(Ext.get(this.get(this.logoutId).header.dom.firstChild));
			this.logoutPrepared = true;
		}
	},
	getEmailNavigation: function() {
		return Ext.getCmp(this.emailNavigationId);
	}
});
Ext.reg('extmail_navigation', ExtMail.Navigation);