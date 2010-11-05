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
	_run: function() {
		ExtMail.Application.superclass._run.call(this);
	}
});