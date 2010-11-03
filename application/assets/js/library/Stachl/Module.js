Ext.ns('Stachl');

Stachl.Module = function(config) {
    Ext.apply(this, config);
    Stachl.Module.superclass.constructor.call(this, config);
};

Ext.extend(Stachl.Module, Ext.Panel, {
	north: null,
	northConfig: {},
	west: null,
	westConfig: {},
	center: null,
	centerConfig: {},
	south: null,
	southConfig: {},
	initComponent:function(){
		Stachl.Module.superclass.initComponent.call(this);
		
		var config = {
			layout: 'border',
			border: false
		};
		Ext.apply(this, config);
		
		this.addListener('beforerender', this.init);
	},
	init: function() {
		if ((Ext.isString(this.north) && !Ext.isEmpty(this.north))) {
			this.northId = Ext.id();
			this.add(Ext.apply(this.northConfig, {
				xtype: this.north,
				id: this.northId,
				region: 'north',
				mainpanel: this
			}));
		}
		if ((Ext.isString(this.west) && !Ext.isEmpty(this.west))) {
			this.westId = Ext.id();
			this.add(Ext.apply(this.westConfig, {
				xtype: this.west,
				id: this.westId,
				region: 'west',
				mainpanel: this
			}));
		}
		if ((Ext.isString(this.center) && !Ext.isEmpty(this.center))) {
			this.centerId = Ext.id();
			this.add(Ext.apply(this.centerConfig, {
				xtype: this.center,
				id: this.centerId,
				region: 'center',
				mainpanel: this
			}));
		}
		if ((Ext.isString(this.south) && !Ext.isEmpty(this.south))) {
			this.southId = Ext.id();
			this.add(Ext.apply(this.southConfig, {
				xtype: this.south,
				id: this.southId,
				region: 'south',
				mainpanel: this
			}));
		}
	},
	getNorth: function() {
		if (Ext.isDefined(this.northId)) {
			return Ext.getCmp(this.northId);
		}
		return false;
	},
	getWest: function() {
		if (Ext.isDefined(this.westId)) {
			return Ext.getCmp(this.westId);
		}
		return false;
	},
	getCenter: function() {
		if (Ext.isDefined(this.centerId)) {
			return Ext.getCmp(this.centerId);
		}
		return false;
	},
	getSouth: function() {
		if (Ext.isDefined(this.southId)) {
			return Ext.getCmp(this.southId);
		}
		return false;
	}
});
Ext.reg('Stachl_module',Stachl.Module);