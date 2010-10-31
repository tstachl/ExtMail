Ext.ns('ExtMail');
ExtMail.MainPanel = Ext.extend(Ext.TabPanel, {
	initComponent: function() {
		var config = {
			style: 'background: transparent;',
			cls: 'extmail-tabpanel',
			activeItem: 0,
			items: [{
				title: _('Email'),
				xtype: 'extmail_email_mainpanel'
			}, {
				title: _('Address Book'),
				html: 'Address Book'
			}, {
				title: _('Settings'),
				html: 'Settings',
				tabCls: 'settings'
			}, {
				title: _('Logout'),
				listeners: {
					beforeshow: function() {
						window.location.href = '/login/logout';
					}
				}
			}]
		};
		
		Ext.apply(this, config);
		ExtMail.MainPanel.superclass.initComponent.call(this);
	},
	afterRender: function() {
		ExtMail.MainPanel.superclass.afterRender.call(this);
	}
});