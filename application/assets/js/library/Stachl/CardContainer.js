Ext.ns('Stachl');

Stachl.CardContainer = function(config) {
	Ext.apply(this, config);
	Stachl.CardContainer.superclass.constructor.call(this, config);
};

Ext.extend(Stachl.CardContainer, Ext.Container, {
	initComponent: function() {
		Stachl.CardContainer.superclass.initComponent.call(this);
		
		var config = {
			layout: 'card'
		};
		Ext.apply(this, config);
		
		Stachl.CardContainer.superclass.initComponent.apply(this, arguments);
	},
	showContent: function(n) {
		var c = n.attributes.classConfig;
		c.iconCls = n.attributes.iconCls;
		c.title = n.attributes.text;
		try {
			if ((this.layout.activeItem !== null) && (this.layout.activeItem.destroy === 'function')) {
				Ext.getCmp(this.layout.activeItem.getId()).destroy();
				Ext.removeNode(Ext.get(this.layout.activeItem.getId()));
				Ext.get(this.layout.activeItem.getId()).remove();
			}
			var np = Ext.apply(c);
			var i = this.add(np);
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