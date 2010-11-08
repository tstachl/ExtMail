Ext.ns('Stachl');

Stachl.CardContainer = function(config) {
	Ext.apply(this, config);
	Stachl.CardContainer.superclass.constructor.call(this, config);
};

Ext.extend(Stachl.CardContainer, Ext.Container, {
	addTitle: true,
	addIconCls: true,
	initComponent: function() {
		Stachl.CardContainer.superclass.initComponent.call(this);
		
		var config = {
			layout: 'card'
		};
		Ext.apply(this, config);
		
		Stachl.CardContainer.superclass.initComponent.apply(this, arguments);
	},
	showContent: function(n) {
		try {
			var np = Ext.apply(n.attributes.classConfig, {
				iconCls: (this.addIconCls ? n.attributes.iconCls : ''),
				title: (this.addTitle ? n.attributes.text : ''),
				mainpanel: this.mainpanel
			});
			var i = this.add(np);
			if ((this.layout.activeItem !== null) && (Ext.isFunction(this.layout.activeItem.destroy))) {
				var id = this.layout.activeItem.getId();
				Ext.getCmp(id).destroy();
				Ext.removeNode(Ext.get(id));
			}
			this.layout.setActiveItem(i);
			this.doLayout();
		} catch(e) {
			Ext.MessageBox.show({
				title: 'Error',
				msg: e,
				buttons: Ext.MessageBox.OK,
				icon: 'ext-mb-error'
			});
			Debug.error(e);
		}
	}
});
Ext.reg('stachl_cardcontainer', Stachl.CardContainer);