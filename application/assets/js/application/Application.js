Ext.ns('ExtMail');
App = function() {
	var _instance = null;
	return {
		getInstance: function(config) {
			try {
				if (_instance === null) {
					_instance = new ExtMail.Application(config);
				}
				return _instance;
			} catch (e) {
				Debug.error(e);
				if (typeof(e.getName) == 'function') {
					Ext.Msg.show({
						title: e.getName(),
						msg: e.getMessage(),
						buttons: Ext.Msg.OK,
						fn: e.getFunction(),
						scope: e.getScope(),
						icon: Ext.Msg.ERROR
					});
				}
			}
		}

	};
}();
ExtMail.Application = Ext.extend(Stachl.Application, {
	initComponent: function() {
	
		Ext.apply(this, {
			layout: 'border',
			border: false,
			items: [this.getController()]
		});
		
		ExtMail.Application.superclass.initComponent.call(this);
		
		this.ajaxErrorHandler();
	},
	ajaxErrorHandler: function() {
		var me = this;
		Ext.Ajax.on('requestexception', function(conn, response, options) {
			try {
				var r = Ext.util.JSON.decode(response.responseText);
			} catch(e) {}
			if (Ext.isDefined(r) && Ext.isDefined(r.success)) {
				if (r.success == false) {
					me.showError(r.error, r.exception, Ext.util.Format.nl2br(r.stack));
				}
			}			
		}, this);
	},
	showError: function(title, msg) {
		message  = '<h1>' + _('An error occured') + '</h1>';
		message += '<h2>' + _('Exception information') + '</h2>';
		message += '<strong>' + _('Message') + ':</strong>&nbsp;' + msg;
		if (Ext.isDefined(arguments[2])) {
			message += '<br /><strong>Stack trace:</strong><br />';
			message += arguments[2];
		}
		this.hideLoading();
		Ext.Msg.show({
			title: title,
			msg: message,
			maxWidth: 950,
			buttons: Ext.Msg.OK,
			icon: Ext.Msg.ERROR
		});
	},
	_run: function() {
		ExtMail.Application.superclass._run.call(this);
	}
});