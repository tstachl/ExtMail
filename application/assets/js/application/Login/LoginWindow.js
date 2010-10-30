Ext.ns('ExtMail.Login');
ExtMail.Login.Window = Ext.extend(Stachl.Login, {
	usernameVtype: 'email',
	languageLabel: 'language',
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
	submit: function() {
		var form = this._formPanel.getForm();
		if (form.isValid()) {
			Ext.getDom(form.getEl()).submit();
		}
	}
});