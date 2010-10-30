Ext.ns('Stachl');

Stachl.Module = function(config) {
    Ext.apply(this, config);
    Stachl.Module.superclass.constructor.call(this, config);
};

Ext.extend(Stachl.Module, Ext.Panel, {
	initComponent:function(){
		Stachl.Module.superclass.initComponent.call(this);
		
		var config = {
			layout: 'border',
			border: false
		};
		Ext.apply(this, config);
		
		this.addListener('beforerender', this.init);
		this.addListener('add', this.conn);
	},
	init: function() {
		var items = [{
			xtype: this.moduleNavigation,
			split: true,
			width: 250,
			style: 'padding-right:10px;',
			region: 'west'
		}, {
			xtype: this.moduleContainer,
			region:'center'
		}];
		this.add(items);
	},
	conn: function() {
		if (Ext.isDefined(this.items.items[1])) {
			this.items.items[0].CardContainer = this.items.items[1];
		}
	}
});
Ext.reg('Stachl_module',Stachl.Module);