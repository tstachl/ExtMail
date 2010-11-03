Ext.ns('ExtMail');
ExtMail.MainPanel = Ext.extend(Ext.ux.GroupTabPanel, {
	initComponent: function() {
		var config = {
			tabWidth: 130,
			cls: 'extmail-tabpanel',
			activeGroup: 0,
			items: [{
				mainItem: 0,
				items: [{
					title: _('Email'),
					xtype: 'extmail_email_mainpanel'
				}]
			}, {
				items: [{
					title: _('Address Book'),
					html: 'Address Book'
				}]
			}, {
				items: [{
					title: _('Settings'),
					html: 'Settings',
					tabCls: 'settings'
				}]
			}, {
				items: [{
					title: _('Logout'),
					listeners: {
						beforeshow: function() {
							window.location.href = '/login/logout';
						}
					}
				}]
			}]
		};
		
		Ext.apply(this, config);
		ExtMail.MainPanel.superclass.initComponent.call(this);
	},
	afterRender: function() {
		ExtMail.MainPanel.superclass.afterRender.call(this);
	}
});