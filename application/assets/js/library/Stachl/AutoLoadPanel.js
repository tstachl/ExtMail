Ext.ns('Stachl');
Stachl.AutoLoadPanel = Ext.extend(Ext.Panel, {
	url: null,
	loader: null,
	initComponent: function(){
		Stachl.AutoLoadPanel.superclass.initComponent.call(this);
		
		var config = {
			loadingText: _('Please wait ...')
		};
		Ext.apply(this, config);
		
		Stachl.AutoLoadPanel.superclass.initComponent.apply(this, arguments);
		
		this.addEvents({
			'load': true,
			'success': true,
			'afteradd': true
		});
	
		this.store = new Ext.data.JsonStore({
			url: this.url,
			baseParams: this.baseParams,
			fields: [{}]
		});
		
		this.on('afteradd', this.afterAdd, this);
	},
	afterRender: function() {
		Stachl.AutoLoadPanel.superclass.afterRender.call(this);
		this.loadingMask = new Ext.LoadMask(this.body, {msg: this.loadingText});
		this.loadingMask.show();
        if (this.store) {
            this.bindStore(this.store);
        }
        this.store.load();
	},
    bindStore: function(store){
		store = Ext.StoreMgr.lookup(store);
        store.on('load', this.refresh, this);
        this.store = store;
    },
    refresh : function(){
        var records = this.store.getRange();
        if (records.length >= 1) {
        	this.addAllItems(records);
        }
    },
    addAllItems: function(records) {
		for (var k in records) {
			if (k == parseInt(k) && (Ext.ComponentMgr.isRegistered(records[k].json.xtype))) {
				this.add(Ext.apply(records[k].json));
			}
		}
		this.fireEvent('afteradd');
    },
	afterAdd: function() {
		this.doLayout();
		this.loadingMask.hide();
	},
	update: function(u) {
		this.loadingMask.show();
		this.removeAll();
		if (null !== u) {
			this.store.proxy.setUrl(u);
		}
		Debug.log(this.store.url);
		Debug.log(this.store);
		this.store.load();
	}
});