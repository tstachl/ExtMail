Ext.ns('ExtMail');
ExtMail.Toolbar = Ext.extend(Ext.Toolbar, {
	initComponent: function() {
		Ext.apply(this, {
			listeners: {
				beforerender: this.beforeRender
			}
		});
		ExtMail.Toolbar.superclass.initComponent.call(this);
		
		this.addEvents('movepreview');
	},
	beforeRender: function() {
		this.add([{
			text: _('Get Email'),
			iconCls: 'ico_get_email',
			disabled: true
		}, {
			text: _('Write'),
			iconCls: 'ico_write',
			disabled: true
		}, '-', {
			text: _('Address Book'),
			iconCls: 'ico_addressbook',
			disabled: true
		}, '-', {
			text: _('Tags'),
			iconCls: 'ico_tags',
			disabled: true
		}, {
			text: _('Reading Pane'),
			iconCls: 'ico_reading_pane',
			menu: [{
				text: _('Bottom'),
				name: 'bottom',
				iconCls: 'ico_reading_pane_bottom',
				handler: this.movePreview,
				scope: this
			}, {
				text: _('Right'),
				name: 'right',
				iconCls: 'ico_reading_pane_right',
				handler: this.movePreview,
				scope: this
			}, {
				text: _('Hide'),
				name: 'hide',
				iconCls: 'ico_reading_pane_hide',
				handler: this.movePreview,
				scope: this
			}]
		}]);
	},
	receive: function() {
		
	},
	write: function() {
		
	},
	addressbook: function() {
		
	},
	tags: function() {
		
	},
	movePreview: function(button) {
		this.fireEvent('movepreview', this, button);
	}
});