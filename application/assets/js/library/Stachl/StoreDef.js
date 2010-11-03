Ext.ns('Stachl');
Stachl.StoreDef = Ext.extend(Ext.util.Observable, {
	fields: null,
	constructor: function(config) {
		this.fields = config;
		Stachl.StoreDef.superclass.constructor.call(this);
	},
	getGridColumns: function() {
		var r = [];
		Ext.iterate(this.fields, function(index, item, stack) {
			if (Ext.isDefined(item.grid)) {
				item.grid.dataIndex = index;
				r.push(item.grid);
			}
		});
		return r;
	},
	getStoreFields: function() {
		var r = [];
		Ext.iterate(this.fields, function(index, item, stack) {
			if (Ext.isDefined(item.store)) {
				item.store.name = index;
				r.push(item.store);
			} else {
				r.push(index);
			}
		});
		return r;
	}
});