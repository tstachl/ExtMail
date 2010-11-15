Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.New = Ext.extend(Ext.Panel, {
	initComponent: function() {		
		this.editor = new Stachl.form.HtmlEditor({
			name: 'message',
			hideLabel: true,
			allowBlank: false,
			flex: 1
		});
		
		this.form = new Ext.form.FormPanel({
			bodyStyle:'padding:5px',
			labelWidth: 75,
			frame: true,
			url: '/email/send',
			defaults: {
	            xtype: 'textfield'
	        },
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			items: [{
				xtype: 'grouptextfield',
				pattern: /^(\w+)([\-+.][\w]+)*@(\w[\-\w]*\.){1,5}([A-Za-z]){2,6};$/,
				plugins: [ Ext.ux.FieldLabeler ],
				fieldLabel: _('To'),
				name: 'to',
				allowBlank: false
			}, {
				xtype: 'grouptextfield',
				pattern: /^(\w+)([\-+.][\w]+)*@(\w[\-\w]*\.){1,5}([A-Za-z]){2,6};$/,
				plugins: [ Ext.ux.FieldLabeler ],
				fieldLabel: _('Cc'),
				name: 'cc'
			}, {
				plugins: [ Ext.ux.FieldLabeler ],
				fieldLabel: _('Subject'),
				name: 'subject',
				listeners: {
					change: this.titleChange,
					scope: this
				}
			}, this.editor]
		});
		
		Ext.apply(this, {
			layout: 'fit',
			tbar: [{
				text: _('Send'),
				iconCls: 'ico_send',
				handler: this.send,
				scope: this
			}, {
				text: _('Spell'),
				iconCls: 'ico_spellcheck',
				disabled: true
			}, {
				text: _('Attach'),
				iconCls: 'ico_attach',
				disabled: true
			}, {
				text: _('Save'),
				iconCls: 'ico_save',
				disabled: true
			}],
			items: [this.form]
		});
		
		ExtMail.Email.New.superclass.initComponent.call(this);
	},
	titleChange: function(tf, nv, ov) {
		this.setTitle(nv);
	},
	setTitle: function(title, iconCls) {
		title = String.format('{0} {1}', _('Write:'), title);
		ExtMail.Email.New.superclass.setTitle.call(this, title, iconCls);
	},
	send: function() {
		var form = this.findByType('form')[0].getForm();
		if (form.isValid()) {
			if (this.fireEvent('submit', this, form.getValues())) {
				form.submit({
					success: this.successfulSent
				});
			}
		}
	},
	successfulSent: function() {
		
	}
});