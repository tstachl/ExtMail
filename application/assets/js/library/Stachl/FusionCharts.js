Ext.ns('Fusion');
Fusion.Chart = Ext.extend(Ext.FlashComponent, {
    refreshBuffer: 100,
    disableCaching: Ext.isIE || Ext.isOpera,
    disableCacheParam: '_dc',
    initComponent : function(){
	    Fusion.Chart.superclass.initComponent.call(this);
	    if(!this.url){
	        throw new Error('No url set!');
	    }
	    if(this.disableCaching){
	        this.url = Ext.urlAppend(this.url, String.format('{0}={1}', this.disableCacheParam, new Date().getTime()));
	    }
	    this.addEvents(
	        'itemmouseover',
	        'itemmouseout',
	        'itemclick',
	        'itemdoubleclick',
	        'itemdragstart',
	        'itemdrag',
	        'itemdragend'
	    );
	    this.store = Ext.StoreMgr.lookup(this.store);
	},
    bindStore : function(store, initial){
        if(!initial && this.store){
            this.store.un("datachanged", this.refresh, this);
            this.store.un("add", this.delayRefresh, this);
            this.store.un("remove", this.delayRefresh, this);
            this.store.un("update", this.delayRefresh, this);
            this.store.un("clear", this.refresh, this);
            if(store !== this.store && this.store.autoDestroy){
                this.store.destroy();
            }
        }
        if(store){
            store = Ext.StoreMgr.lookup(store);
            store.on({
                scope: this,
                datachanged: this.refresh,
                add: this.delayRefresh,
                remove: this.delayRefresh,
                update: this.delayRefresh,
                clear: this.refresh
            });
        }
        this.store = store;
        if(store && !initial){
            this.refresh();
        }
    },

    onSwfReady : function(isReset){
        Ext.chart.Chart.superclass.onSwfReady.call(this, isReset);
        this.swf.setType(this.type);

        if(this.chartStyle){
            this.setStyles(Ext.apply(this.extraStyle || {}, this.chartStyle));
        }

        if(this.categoryNames){
            this.setCategoryNames(this.categoryNames);
        }

        if(this.tipRenderer){
            this.setTipRenderer(this.tipRenderer);
        }
        if(!isReset){
            this.bindStore(this.store, true);
        }
        this.refresh.defer(10, this);
    },


});