Ext.ns('ExtMail.Controllers');
ExtMail.Controllers.LoginController = Ext.extend(Stachl.Controller, {
	title: 'Login - ExtMail - w3agency.net',
	copyright: '© 2010 by <a href="http://www.w3agency.net/" target="_blank" title="w3agency.net">w3agency.net</a>',
	setMainContainer: function() {
		this.mainContainer = new Ext.Container({
	    	style: 'padding:10px;background:transparent',
	    	border: false,
	    	region: 'center',
	    	layout: 'fit',
	    	html: '<h2>Demo Access</h2><dl style="font-size: 14px; margin-top: 10px;"><dt style="font-weight: bold;">Username:</dt><dd style="padding-left: 10px;">demo@w3agency.net</dd><dt style="font-weight: bold;">Password:</dt><dd style="padding-left: 10px;">demo</dd></dl>'
		});
		return this;
	},
	show: function() {
		this.loginWindow = new ExtMail.Login.Window({
			title: _('Login'),
			message: _('Access to this location is restricted to authorized users only. Please enter your username and password.'),
			failMessage: _('Unable to log in'),
			waitMessage: _('Please wait ...'),
			loginButton: _('Login'),
			usernameLabel: _('Username'),
			passwordLabel: _('Password'),
			hostLabel: _('Host'),
			portLabel: _('Port'),
			remembermeLabel: _('Remember me'),
			remembermeQtip: _('This is not recommended for shared computers!'),
			forgotPasswordLabel: _('Forgot Password'),
			fail: (this.error == 0) ? false : true
		});
		this.loginWindow.show();
		ExtMail.Controllers.LoginController.superclass.show.call(this);
	}
});