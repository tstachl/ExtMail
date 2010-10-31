Ext.ns('Stachl', 'Stachl.Navigation');

Stachl.Navigation.Accordion = function(config) {
	Ext.apply(this, config);
	Stachl.Navigation.Accordion.superclass.constructor.call(this, config);
};

Ext.extend(Stachl.Navigation.Accordion, Stachl.AutoLoadPanel, {
	title: 'Navigation',
	initComponent: function() {
		Stachl.Navigation.Accordion.superclass.initComponent.call(this);
		
		var config = {
			layout: 'accordion',
			layoutConfig: {
				titleCollapse: true,
				animate: false
			}
		};
		Ext.apply(this, config);
		Stachl.Navigation.Accordion.superclass.initComponent.apply(this, arguments);
	},
    onBeforeExpand: function(p) {
    	if (p.preload === false) {
    		p.getRootNode().render();
    		p.preload = true;
    	}
    },
	afterAdd: function() {
		Stachl.Navigation.Accordion.superclass.afterAdd.call(this);
		for (var i in this.items.items) {
			if (i == parseInt(i)) {
				this.items.items[i].on({
					scope: this,
					'click': this.onNodeClick,
					'beforeexpand': this.onBeforeExpand,
					stopEvent:true
				});
			}
		}
	},
	onNodeClick: function(n, e) {
		if (this.CardContainer != null) {
			this.CardContainer.showContent(n);
		}
	}
});
Ext.reg('stachl_navigation_accordion', Stachl.Navigation.Accordion);

Stachl.Navigation.Tree = function(config) {
	Ext.apply(this, config);
	Stachl.Navigation.Tree.superclass.constructor.call(this, config);
};

Ext.extend(Stachl.Navigation.Tree, Ext.tree.TreePanel, {
	title: 'Navigation',
	initComponent: function() {
		Stachl.Navigation.Tree.superclass.initComponent.call(this);
		
		var config = {
			loader: new Ext.tree.TreeLoader({
				url: this.url
			}),
			root: new Ext.tree.AsyncTreeNode(),
			rootVisible: false
		};
		Ext.apply(this, config);
		Stachl.Navigation.Tree.superclass.initComponent.apply(this, arguments);
	},
    onBeforeExpand: function(p) {
    	if (p.preload === false) {
    		p.getRootNode().render();
    		p.preload = true;
    	}
    },
	afterAdd: function() {
		Stachl.Navigation.Tree.superclass.afterAdd.call(this);
		for (var i in this.items.items) {
			if (i == parseInt(i)) {
				this.items.items[i].on({
					scope: this,
					'click': this.onNodeClick,
					'beforeexpand': this.onBeforeExpand,
					stopEvent:true
				});
			}
		}
	},
	onNodeClick: function(n, e) {
		if (this.CardContainer != null) {
			this.CardContainer.showContent(n);
		}
	}
});
Ext.reg('stachl_navigation_tree', Stachl.Navigation.Tree);