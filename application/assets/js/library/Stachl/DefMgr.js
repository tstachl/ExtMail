Stachl.DefMgr = Ext.apply(new Ext.util.MixedCollection(), {
	getGridColumns: function(i) {
		return this.key(i).getGridColumns();
	},
	getStoreFields: function(i) {
		return this.key(i).getStoreFields();
	},
	getRecord: function(i) {
		return this.key(i).GetRecord();
	},
	getFormFields: function(i) {
		return this.key(i).GetFormFields();
	},
	getDef: function(i) {
		return this.key(i);
	}
});