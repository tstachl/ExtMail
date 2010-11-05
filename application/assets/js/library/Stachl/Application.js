Ext.ns('Stachl');
Stachl.Application = Ext.extend(Ext.Viewport, {
	environment: 'development',
	controller: null,
	localizer: null,
	
	initComponent: function() {
		this.addListener('beforerender', this.setOptions);
		
		Ext.applyIf(this, {
			layout: {
				type: 'hbox',
				align: 'stretch'
			},
			border: false,
			items: [{
				xtype: 'box',
				flex: 1,
				autoEl: {
					tag: 'div',
					html: '<br />'
				}
			}, this.getController(), {
				xtype: 'box',
				flex: 1,
				autoEl: {
					tag: 'div',
					html: '<br />'
				}
			}]
		});
		
		Stachl.Application.superclass.initComponent.call(this);
	},
	
	setEnvironment: function(s) {
		this.environment = s;
		return this;
	},
	getEnvironment: function() {
		return this.environment;
	},
	setOptions: function() {		
		if (Ext.isDefined(this.contextmenu)) {
			this.setContextMenu(this.contextmenu);
		}
		if (Ext.isDefined(this.ajax)) {
			this.setAjax(this.ajax);
		}
		if (Ext.isDefined(this.quicktip)) {
			this.setQuicktip(this.quicktip);
		}
		return this;
	},
	setContextMenu: function(option) {
		if (option.disable == true) {
			document.getElementsByTagName('body')[0].oncontextmenu=function(){return false;};
		}
	},
	setAjax: function(option) {
		if (Ext.isDefined(option.autoAbort)) {
			Ext.Ajax.autoAbort = option.autoAbort;
		}
		if (Ext.isDefined(option.defaultHeaders)) {
			Ext.Ajax.defaultHeaders = option.defaultHeaders;
		}
		if (Ext.isDefined(option.disableCaching)) {
			Ext.Ajax.disableCaching = option.disableCaching;
		}
		if (Ext.isDefined(option.extraParams)) {
			Ext.Ajax.extraParams = option.extraParams;
		}
		if (Ext.isDefined(option.method)) {
			Ext.Ajax.method = option.method;
		}
		if (Ext.isDefined(option.timeout)) {
			Ext.Ajax.timeout = option.timeout;
		}
		if (Ext.isDefined(option.url)) {
			Ext.Ajax.url = option.url;
		}
	},
	setQuicktip: function(option) {
		if (Ext.isDefined(option.init)) {
			Ext.QuickTips.init();
		}
	},
	getController: function() {
		if (null === this.controller) {
			this.setController();
		}
		return this.controller;
	},
	setController: function(controller) {
		if (controller) {
			this.controller = controller;
		} else {
			this.controller = new Stachl.Controller();
		}
		return this;
	},
	getLocalizer: function () {
		if (null === this.localizer) {
			this.setLocalizer();
		}
		return this.localizer;
	},
	setLocalizer: function(o) {
		var c = ((Ext.isDefined(this.locale)) ? this.locale : {});
		if (typeof(o) == 'function') {
			this.localizer = new o(c);
			return this;
		}
		if (typeof(Stachl.Locale) == 'function') {
			this.localizer = new Stachl.Locale(c);
			return this;
		}
		throw new Stachl.Exception('No localizer defined!');
	},
	_run: function() {
		this.getController().show();
	},
	run: function() {
		try {
			this._run();
		} catch (e) {
			Debug.error(e);
			if (typeof(e.getName) == 'function') {
				Ext.Msg.show({
					title: e.getName(),
					msg: e.getMessage(),
					buttons: Ext.Msg.OK,
					fn: e.getFunction(),
					scope: e.getScope(),
					icon: Ext.Msg.ERROR
				});
			}
		}
	}
});