Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.New = Ext.extend(Ext.Panel, {
	initComponent: function() {
	
		Ext.apply(this, {
			title: String.format('{0} {1}', _('Write:'), this.title),
			layout: 'fit',
	        plain: true,
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
			items: [{
				xtype: 'form',
				bodyStyle:'padding:5px',
				labelWidth: 75,
				url: '/email/send',
				defaults: {
		            xtype: 'textfield'
		        },
				layout: {
					type: 'vbox',
					align: 'stretch'
				},
				items: [{
//					xtype: 'grouptextfield',
//					pattern: /^(\w+)([\-+.][\w]+)*@(\w[\-\w]*\.){1,5}([A-Za-z]){2,6};$/,
					plugins: [ Ext.ux.FieldLabeler ],
					fieldLabel: _('To'),
					name: 'to',
					allowBlank: false
				}, {
//					xtype: 'grouptextfield',
//					pattern: /^(\w+)([\-+.][\w]+)*@(\w[\-\w]*\.){1,5}([A-Za-z]){2,6};$/,
					plugins: [ Ext.ux.FieldLabeler ],
					fieldLabel: _('Cc'),
					name: 'cc'
				}, {
					plugins: [ Ext.ux.FieldLabeler ],
					fieldLabel: _('Subject'),
					name: 'subject'
				}, {
					xtype: 'textarea',
					name: 'message',
					hideLabel: true,
					allowBlank: false,
					flex: 1
				}]
			}]
		});
		
		ExtMail.Email.New.superclass.initComponent.call(this);
		
//		this.on('afterlayout', this.afterLayout, this);
		
	
	},
	afterLayout: function() {
		var ph = this.findByType('form')[0].getHeight(true),
			ms = this.findByType('htmleditor')[0];
		Debug.log(ms.getPosition().x);
		ms.setHeight(ph-ms.getPosition().x);
	},
	send: function() {
		var form = this.findByType('form')[0].getForm();
		if (form.isValid()) {
			if (this.fireEvent('submit', this, form.getValues())) {
				form.submit();
			}
		}
	}
});