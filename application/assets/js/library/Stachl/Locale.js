Ext.namespace('Stachl', 'Stachl.Locale');

Stachl.Locale = function(c) {
	if (Ext.isDefined(c.defaultLanguage)) {
		this.defaultLanguage = c.defaultLanguage;
	}
	if (Ext.isDefined(c.language)) {
		this.language = c.language;
	}
	
	Stachl.Locale.superclass.constructor.call(this, {
		url: '/',
		reader: new Stachl.Locale.Reader()
	});
};
Ext.extend(Stachl.Locale, Ext.data.Store, {
	defaultLanguage: 'en_US',
	language: null,
	getLanguage: function() {
		return this.language;
	},
	getMsg: function(key) {
		return this.getById(key)? Ext.util.Format.htmlDecode(this.getById(key).data) : key;
	}
});