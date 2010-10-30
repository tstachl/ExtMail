Stachl.TreePanel = Ext.extend(Ext.tree.TreePanel, {
	preload: true,
	initComponent: function(o) {
		Stachl.TreePanel.superclass.initComponent.call(this);
	},
    afterRender : function(){
        Ext.tree.TreePanel.superclass.afterRender.call(this);
        if (this.preload) {
	        this.root.render();
	        if(!this.rootVisible){
	            this.root.renderChildren();
	        }
        }
    }
});