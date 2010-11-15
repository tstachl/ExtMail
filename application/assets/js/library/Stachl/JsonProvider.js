Ext.ns('Stachl', 'Stachl.state');
Stachl.state.JsonProvider = Ext.extend(Ext.util.Observable, {
	store: null,
	constructor: function(config) {
		Ext.apply(this, config);
	
		this.addEvents('statechange');
		
		this.store = new Ext.data.JsonStore({
			proxy: new Ext.data.HttpProxy({
				api: {
					read: this.api.read,
					create: this.api.create,
					update: this.api.update,
					destroy: this.api.destroy
				}
			}),
			root: this.root,
			idProperty: this.idProperty,
			fields: ['name', 'value']
		});
		this.store.load();
		
		Debug.log('JsonProvider: constructor');
		
		Stachl.state.JsonProvider.superclass.constructor.call(this);
	},
	findByName: function(name) {
		var r = this.store.findExact('name', name);
		if (r !== -1) {
			return this.store.getAt(r);
		}
		return false;
	},
	get: function(name, defaultValue) {
		Debug.log('JsonProvider: get', name, defaultValue);
		return this.findByName(name) ? this.findByName(name).get('value') : defaultValue;
	},
	set: function(name, value) {
		Debug.log('JsonProvider: set');
		if (!Ext.isDefined(value) || value === null) {
			this.clear(name);
			return
		}
		if (this.findByName(name)) {
			this.findByName(name).set(value);
		} else {
			this.store.add(new this.store.recordType({
				name: name,
				value: value
			}));
		}
		this.fireEvent('statechange', this, name, value);
	},
	clear: function(name) {
		Debug.log('JsonProvider: clear');
		if (this.findByName(name)) {
			this.store.remove(this.findByName(name));
		}
		this.fireEvent('statechange', this, name, null);
	}
});