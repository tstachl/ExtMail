Stachl.StoreMgr = Ext.apply(new Ext.util.MixedCollection(), {
	add: function(key, o) {
	    if(arguments.length == 1){
	        o = arguments[0];
	        key = this.getKey(o);
	    }
	    if(typeof key != 'undefined' && key !== null){
	        var old = this.map[key];
	        if(typeof old != 'undefined'){
	            return this.replace(key, o);
	        }
	        this.map[key] = o;
	    }
		o.on('load', function(o) {
			o.isLoaded = true;
			Stachl.StoreMgr.load();
		}, this);
	    this.length++;
	    this.items.push(o);
	    this.keys.push(key);
	    this.fireEvent('add', this.length-1, o, key);
	    return o;
	},	
    register : function(){
        for(var i = 0, s; (s = arguments[i]); i++){
            this.add(s);
        }
    },
    unregister : function(){
        for(var i = 0, s; (s = arguments[i]); i++){
            this.remove(this.lookup(s));
        }
    },
    lookup : function(id){
        if(Ext.isArray(id)){
            var fields = ['field1'], expand = !Ext.isArray(id[0]);
            if(!expand){
                for(var i = 2, len = id[0].length; i <= len; ++i){
                    fields.push('field' + i);
                }
            }
            return new Ext.data.ArrayStore({
                fields: fields,
                data: id,
                expandData: expand,
                autoDestroy: true,
                autoCreated: true

            });
        }
        return Ext.isObject(id) ? (id.events ? id : Ext.create(id, 'store')) : this.get(id);
    },
    getKey : function(o){
         return o.storeId;
    },
    prepare: function() {
    },
    load: function() {
    	this.each(function(item, index, length) {
    		if (item.isLoaded !== true) {
    			if (item.isTreeLoader === true) {
    				item.load(item.root);
    			} else {
    				item.load();
    			}
    			return false;
    		} else {
    			return true;
    		}
    	}, this);
    }
});