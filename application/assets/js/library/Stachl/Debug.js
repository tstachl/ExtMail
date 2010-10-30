Ext.ns('Stachl');
Stachl.Debug = function(config) {
	Ext.apply(this, config);
	Stachl.Debug.superclass.constructor.call(this);
};

Ext.extend(Stachl.Debug, Object, {
	log: function(o) {
		if (window.console !== 'undefined') {
			console.log(o);
		}
	},
	error: function(o) {
		if (window.console !== 'undefined') {
			console.error(o);
		}
	}
});
var Debug = new Stachl.Debug();