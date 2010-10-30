Ext.ns('ExtMail.Controllers');
ExtMail.Controllers.LoginController = Ext.extend(Stachl.Controller, {
	title: 'ExtMail - Login',
	copyright: '© 2010 by <a href="http://www.stachl.me/" target="_blank" title="Stachl.me">Stachl.me</a>',
	show: function() {
		this.loginWindow = new ExtMail.Login.Window({
			title: _('Login'),
			message: _('Access to this location is restricted to authorized users only. Please enter your username and password.'),
			failMessage: _('Unable to log in'),
			waitMessage: _('Please wait ...'),
			loginButton: _('Login'),
			usernameLabel: _('Username'),
			passwordLabel: _('Password'),
			remembermeLabel: _('Remember me'),
			remembermeQtip: _('This is not recommended for shared computers!'),
			forgotPasswordLabel: _('Forgot Password')
		});
		this.loginWindow.show();
		ExtMail.Controllers.LoginController.superclass.show.call(this);
	}
});