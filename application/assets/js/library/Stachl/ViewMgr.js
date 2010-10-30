Stachl.ViewMgr = Ext.apply(new Ext.util.MixedCollection(), {
	show: function(i) {
		return this.key(i).show();
	},
	getView: function(i) {
		return this.key(i);
	},
	getId: function(i) {
		return this.key(i).getId();
	}
});