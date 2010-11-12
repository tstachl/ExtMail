Ext.ns('ExtMail.Login');
ExtMail.Login.Window = Ext.extend(Stachl.Login, {
	usernameVtype: 'email',
	languageLabel: 'language',
	hostField: 'host',
	hostLabel: 'Host',
	portLabel: 'Port',
	portField: 'port',
	portVType: 'alphanum',
	sslField: 'ssl',
	sslLabel: 'SSL',
	encrypt: false,
	salt: '',
	url: '/login/process',
	method: 'post',
	imagePath: '/images/icons',
	titleIcon: '/small/locked.png',
	bgIcon: '/large/lock.png',
	submitIcon: '/medium/button_ok.png',
	cancelIcon: '/medium/cancel.png',
	forgotIcon: '/medium/search.png',
	warningIcon: '/small/warning.png',
	modal: true,
	initComponent: function() {    
    	ExtMail.Login.Window.superclass.initComponent.call(this);
    	
		this.hostId = Ext.id();
		this.sslId = Ext.id();
		
//    	this.height = 320;
    	
//    	this.on('beforerender', this.addAdditionalFields, this);
//    	this.on('show', function() {
//    		Ext.getCmp(this.hostId).setValue('localhost');
//    		Ext.getCmp(this.sslId).setValue('none');
//    	}, this);
    	
		if (this.fail == true) {
			this.on('show', function() {
		        this.setMessage (this.message + '<br /><span class="error">' + this.failMessage + '</span>');
			}, this);
		}
	},
	addAdditionalFields: function() {
		
		this._formPanel.insert(2, {
			xtype: 'combo',
			id: this.hostId,
			typeAhead: true,
			triggerAction: 'all',
			lazyRender: true,
			mode: 'local',
			store: new Ext.data.ArrayStore({
				fields: ['host'],
				data: [['localhost'], ['Gmail']]
			}),
			name: this.hostField,
			fieldLabel: this.hostLabel,
			width: 300,
			displayField: 'host',
			valueField: 'host'
		});
		
		this._formPanel.insert(3, {
            xtype: 'textfield',
            name: this.portField,
            fieldLabel: this.portLabel,
            vtype: this.portVtype,
            validateOnBlur: true,
            allowBlank: false,
            value: '143'
		});
		
		this._formPanel.insert(4, {
			xtype: 'combo',
			id: this.sslId,
			autoSelect: true,
			triggerAction: 'all',
			lazyRender: true,
			mode: 'local',
			forceSelection: true,
			store: new Ext.data.ArrayStore({
				fields: ['name'],
				data: [['none'], ['SSL'], ['TLS']]
			}),
			name: this.sslField,
			fieldLabel: this.sslLabel,
			width: 300,
			displayField: 'name',
			valueField: 'name'
		});
	},
	submit: function() {
		var form = this._formPanel.getForm();
		if (form.isValid()) {
			Ext.getDom(form.getEl()).submit();
		}
	}
});