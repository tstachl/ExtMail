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
			text: _('Get Email')
		}, {
			text: _('Write')
		}, {
			text: _('Address Book')
		}, '-', {
			text: _('Tags')
		}, {
			text: _('Reading Pane'),
			menu: [{
				text: _('Bottom'),
				name: 'bottom',
				handler: this.movePreview,
				scope: this
			}, {
				text: _('Right'),
				name: 'right',
				handler: this.movePreview,
				scope: this
			}, {
				text: _('Hide'),
				name: 'hide',
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