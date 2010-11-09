Ext.ns('Stachl');
Stachl.Debug = function(config) {
	Ext.apply(this, config);
	Stachl.Debug.superclass.constructor.call(this);
};

Ext.extend(Stachl.Debug, Object, {
	log: function() {
		if (Ext.isDefined(window.console)) {
			Ext.each(arguments, function(i) {
				console.log(i);
			});
		}
	},
	error: function(o) {
		if (Ext.isDefined(window.console)) {
			Ext.each(arguments, function(i) {
				console.error(i);
			});
		}
	}
});
var Debug = new Stachl.Debug();