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
		if (this.mainpanel.getCenter() != false) {
			this.mainpanel.getCenter().showContent(n);
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
			layout: 'fit',
			listeners: {
				click: this.clicked,
				scope: this
			},
			loadingText: _('Please wait ...'),
			loader: new Ext.tree.TreeLoader({
				url: this.url,
				listeners: {
					load: function() {
						if (this.loadMask) {
							this.loadingMask.hide();
						}
						
						var node;
						if (this.lookupChild) {
							node = this.getRootNode().findChild(this.lookupChild.attribute, this.lookupChild.value, this.lookupChild.deep || false);
						}
						
						var path = (node ? node.getPath() : false) || this.startPath || this.getRootNode().firstChild.getPath();
						
						this.selectPath(path);
						this.clicked();
					},
					scope: this
				}
			}),
			useArrows: true,
			root: new Ext.tree.AsyncTreeNode(),
			rootVisible: false
		};
		Ext.apply(this, config);
		Stachl.Navigation.Tree.superclass.initComponent.apply(this, arguments);
	},
	afterRender: function() {
		Stachl.Navigation.Tree.superclass.afterRender.call(this);
		if (this.loadMask) {
			this.loadMask = new Ext.LoadMask(this.body, {msg: this.loadingText});
			this.loadMask.show();
		}
	},
	clicked: function(n) {
		if (this.mainpanel.getCenter() != false) {
			this.mainpanel.getCenter().showContent((Ext.isDefined(n) ? n : this.getSelectionModel().getSelectedNode()));
		}
	}
});
Ext.reg('stachl_navigation_tree', Stachl.Navigation.Tree);