Ext.namespace('Stachl');
Stachl.Controller = Ext.extend(Ext.Container, {
	title: 'Stachl Ext Framework',
	copyright: '© 2010 by <a href="http://www.stachl.me/" target="_blank" title="Stachl.me">Stachl.me</a>',
	mainContainer: null,
	views: Stachl.ViewMgr,
	defs: Stachl.DefMgr,
	stores: Ext.StoreMgr,
	forceLayout: true,
	isController: true,
	initComponent: function() {
		Stachl.Controller.superclass.initComponent.call(this);
				
		var config = {
			flex: 16,
			layout: 'border',
			border: false,
			region: 'center',
			style: 'padding-top:5px;background:transparent;'
		};
		Ext.apply(this, config)
		
		var items = [{
	    	xtype:'container',
	    	region:'north',
	    	layout:'column',
	    	height:50,
	    	items:[{
	    		xtype:'box',
		    	autoEl:{tag:'div',html:'<h1><span>' + this.title + '</span></h1>',cls:'logo'}
		    }]
		}, this.getMainContainer() , {
			xtype:'box',
			region:'south',
			autoEl:{
				tag:'div',
				html:'<p class="copyright">' + this.copyright + '</p>'
			}
	    }];		
		
		this.add(items);
	},
	show: function() {},	
	setMainContainer: function() {
		this.mainContainer = new Ext.Container({
	    	style: 'padding-top:5px;background:transparent',
	    	border: false,
	    	region: 'center',
	    	layout: 'fit'
		});
		return this;
	},
	getMainContainer: function() {
		if (null === this.mainContainer) {
			this.setMainContainer();
		}
		return this.mainContainer;
	},
	getActiveItem: function() {
		if (Ext.isDefined(this.getMainContainer().getLayout().activeItem)) {
			return this.getMainContainer().getLayout().activeItem;
		}
		return false;
	},
	setActiveItem: function(i) {
		if (this.views.getView(i)) {
			this.getMainContainer().removeAll();
			this.getMainContainer().add(this.views.getView(i));
			this.getMainContainer().doLayout();
			return this.getMainContainer();
		}
		return false;
	}
});
Ext.reg('stachl_controller', Stachl.Controller);