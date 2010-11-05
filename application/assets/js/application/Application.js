Ext.ns('ExtMail');
ExtMail.Instance = function() {
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
	},
	_run: function() {
		ExtMail.Application.superclass._run.call(this);
	}
});