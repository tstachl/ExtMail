Ext.namespace('Stachl');

Stachl.Login = function(config) {
    Ext.apply(this, config);
    Stachl.Login.superclass.constructor.call(this, config);
};

Ext.extend(Stachl.Login, Ext.Window, {
	
	title: 'Login',
	message: 'Access to this location is restricted to authorized users only.<br />Please type your username and password.',
	failMessage: 'Unable to log in',
	waitMessage: 'Please wait ...',
	loginButton: 'Login',
	cancelButton: null,
	usernameField: 'username',
	usernameLabel: 'Username',
	usernameVtype: 'alphanum',
	passwordField: 'password',
	passwordLabel: 'Password',
	passwordVtype: 'alphanum',
	languageLabel: 'language',
	remembermeField: 'rememberme',
	remembermeLabel: 'Remember me',
	remembermeQtip: 'This is not recommended for shared computers!',
	forgotPasswordLabel: 'Forgot Password',
	encrypt: false,
	salt: '',
	url: '',
	method: '',
	imagePath: '',
	titleIcon: '',
	bgIcon: '',
	submitIcon: '',
	cancelIcon: '',
	forgotIcon: '',
	warningIcon: '',
	modal: false,
	_headPanel: null,
	_formPanel: null,
	languageStore: null,
	

	initComponent: function() {
	    // store username id to focus on window show event
	    this._usernameId = Ext.id();
	    this._passwordId = Ext.id();
	    this._loginButtonId = Ext.id();
	    this._cancelButtonId = Ext.id();
	    this._remembermeId = Ext.id();
	    this._forgotButtonId = Ext.id();
	    this._cssId = Ext.id();
	    
	    // The CSS needed to style the dialog.
	    var css = '.ux-auth-header-icon {background: url("' + this.imagePath + this.titleIcon + '") 0 4px no-repeat !important;}'
	        + '.ux-auth-header {background:transparent url("' + this.imagePath + this.bgIcon + '") no-repeat center right;padding:10px;padding-right:45px;font-weight:bold;}'
	        + '.ux-auth-login {background-image: url("' + this.imagePath + this.submitIcon + '") !important;}'
	        + '.ux-auth-close {background-image: url("' + this.imagePath + this.cancelIcon + '") !important;}'
	        + '.ux-auth-forgot {background-image: url("' + this.imagePath + this.forgotIcon + '") !important;}'
	        + '.ux-auth-warning {background:url("'+ this.imagePath + this.warningIcon + '") no-repeat center left; padding: 2px; padding-left:20px; font-weight:bold;}'
	        + '.ux-auth-header .error {color:red;}'
	        + '.ux-auth-form {padding:10px;}';
	    Ext.util.CSS.createStyleSheet(css, this._cssId);
	
	    // LoginDialog events
	    this.addEvents ({
	        'show': true, // when dialog is visible and rendered
	        'cancel': true, // When user cancelled the login
	        'success': true, // on succesfful login
	        'failure': true, // on failed login
	        'submit': true, // about to submit the data
	        'forgot': true,
	        'language': true
	    });
	
	    // head info panel
	    this._headPanel = new Ext.Panel ({
	        html: this.message,
	        region: 'north',
	        border: false,
	        bodyStyle: 'background:transparent;',
	        cls: 'ux-auth-header',
	        height: 70
	    });

	    // form panel
	    this._formPanel = new Ext.form.FormPanel ({
	        bodyStyle: "padding:10px;",
	        border: false,
	        waitMsgTarget: true,
	        region: 'center',
	        labelWidth: 75,
	        defaults: {width: 300},
	        listeners: {
	        	render: function() {
	        		this._formPanel.getForm().getEl().set({
	        			action: this.url
	        		});
	        	},
	        	scope: this
	        },
	        items: [{
	            xtype           : 'textfield',
	            id              : this._usernameId,
	            name            : this.usernameField,
	            fieldLabel      : this.usernameLabel,
	            vtype           : this.usernameVtype,
	            validateOnBlur  : true,
	            allowBlank      : false
	        }, {
	            xtype           : 'textfield',
	            inputType       : 'password',
	            id              : this._passwordId,
	            name            : this.passwordField,
	            fieldLabel      : this.passwordLabel,
	            vtype           : this.passwordVtype,
	            width           : 300,
	            validateOnBlur  : true,
	            allowBlank      : false
	        }, {
	            xtype: 'box',
	            autoEl: 'div',
	            height: 10
	        }, {
	            xtype       : 'checkbox',
	            id          : this._remembermeId,
	            name        : this.remembermeField,
	            boxLabel    : '&nbsp;' + this.remembermeLabel,
	            width       : 200,
	            listeners: {
	                render: function() {
	                    Ext.get(Ext.DomQuery.select('#x-form-el-' + this._remembermeId + ' input')).set({
	                        qtip: this.remembermeQtip
	                    });
	                },
	                scope: this
	            }
	        }]
	    });

	    var buttons = [{
	    	id			: this._forgotButtonId,
	    	text		: this.forgotPasswordLabel,
	    	iconCls		: 'ux-auth-forgot',
	    	width		: 140,
	    	handler		: this.forgot,
	    	scale		: 'medium',
	    	cls			: 'login-forgot-button',
	    	scope		: this
	    }];
	    
	    // Default buttons and keys
	    buttons.push({
	        id          : this._loginButtonId,
	        text        : this.loginButton,
	        iconCls     : 'ux-auth-login',
	        width       : 90,
	        handler     : this.submit,
	        scale       : 'medium',
	        cls         : 'login-submit-button',
	        scope       : this
	    });
	    var keys = [{
	        key     : [10,13],
	        handler : this.submit,
	        scope   : this
	    }];

	    // if cancel button exists
	    if (typeof(this.cancelButton) == 'string') {
	        buttons.push({
	            id      : this._cancelButtonId,
	            text    : this.cancelButton,
	            iconCls : 'ux-auth-close',
	            width   : 90,
	            handler : this.cancel,
	            scale   : 'medium',
	            cls     : 'login-cancel-button',
	            scope   : this
	        });
	        keys.push({
	            key     : [27],
	            handler : this.cancel,
	            scope   : this
	        });
	    }

	    Ext.apply(this, {
	        width       : 420,
	        height      : 280,
	        closable    : false,
	        resizable   : false,
	        draggable   : true,
	        modal       : this.modal,
	        iconCls     : 'ux-auth-header-icon',
	        title       : this.title,
	        layout      : 'border',
	        bodyStyle   : 'padding:5px;',
	        buttons     : buttons,
	        keys        : keys,
	        items       : [this._headPanel, this._formPanel]
	    });
	    
	    Stachl.Login.superclass.initComponent.apply(this, arguments);

	    // when window is visible set focus to the username field
	    // and fire "show" event
	    this.on ('show', function () {
	        Ext.getCmp(this._usernameId).focus(true, 500);
	        Ext.getCmp(this._passwordId).setRawValue('');
	    }, this);
	},
	
	setMessage: function(msg) {
		this._headPanel.body.update(msg);
	},
	
	destroy: function() {
		this.hide();
		Ext.util.CSS.removeStyleSheet(this._cssId);
		Stachl.Login.superclass.destroy.call(this);
	},
	
	cancel: function() {
		this.fireEvent('cancel', this);
	},
	
	submit: function() {
        var form = this._formPanel.getForm();

        if (form.isValid())
        {
            Ext.getCmp(this._loginButtonId).disable();
            if(Ext.getCmp(this._cancelButtonId)) {
                Ext.getCmp(this._cancelButtonId).disable();
            }
            if(this.encrypt) {
                Ext.getCmp(this._passwordId).setRawValue(
                    Ext.ux.Crypto.SHA1.hash(this.salt + Ext.getCmp(this._passwordId).getValue())
                );
            }

            if (this.fireEvent('submit', this, form.getValues()))
            {
                this.setMessage (this.message);
                form.submit ({
                    url     : this.url,
                    method  : this.method,
                    waitMsg : this.waitMessage,
                    success : this.onSuccess,
                    failure : this.onFailure,
                    scope   : this
                });
            }
        }
	},
	
	onSuccess: function(form, action) {
        if (this.fireEvent('success', this, action)) {
            // enable buttons
            Ext.getCmp(this._loginButtonId).enable();
            if(Ext.getCmp(this._cancelButtonId)) {
                Ext.getCmp(this._cancelButtonId).enable();
            }
        }
	},
	
	onFailure: function(form, action) {
        // enable buttons
        Ext.getCmp(this._loginButtonId).enable();
        if(Ext.getCmp(this._cancelButtonId)) {
            Ext.getCmp(this._cancelButtonId).enable();
        }
        if(this.encrypt) {
            Ext.getCmp(this._passwordId).setRawValue('');
        }
        
        Ext.getCmp(this._passwordId).focus(true);

        var msg = '';
        if (action.result && action.result.message) msg = action.result.message || this.failMessage;
        else msg = this.failMessage;
        this.setMessage (this.message + '<br /><span class="error">' + msg + '</span>');
        this.fireEvent('failure', this, action, msg);
	}

});
Ext.reg('stachl_login',Stachl.Login);