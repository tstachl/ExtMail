Ext.ns('ExtMail');
App = function() {
	var _instance = null;
	return {
		getInstance: function(config) {
			if (_instance === null) {
				_instance = new ExtMail.Application(config);
			}
			return _instance;
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
		Ext.Ajax.on('requestexception', function(conn, response, options) {
			try {
				var r = Ext.util.JSON.decode(response.responseText);
			} catch(e) {}
			if (Ext.isDefined(r) && Ext.isDefined(r.success)) {
				if (r.success == false) {
					Ext.Msg.show({
						title: r.error,
						msg: r.exception,
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.ERROR
					});
				}
			}			
		}, this);
	},
	_run: function() {
		ExtMail.Application.superclass._run.call(this);
	}
});