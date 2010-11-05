Ext.ns('ExtMail.Controllers');
ExtMail.Controllers.MainController = Ext.extend(Stachl.Controller, {
	title: 'Email - ExtMail - Stachl.me',
	copyright: '© 2010 by <a href="http://www.stachl.me/" target="_blank" title="Stachl.me">Stachl.me</a>',
	initComponent: function() {
		this.addDefs();
		this.addStores();
		
		Ext.apply(this, {
			layout: 'border',
			border: false,
			region: 'center',
			style: '',
			items: [this.getMainContainer()]
		});
		
		ExtMail.Controllers.MainController.superclass.initComponent.call(this);
	},
	setMainContainer: function() {
		this.mainContainer = new Ext.Container({
	    	border: false,
	    	region: 'center',
	    	layout: 'fit'
		});
		return this;
	},	
	show: function() {
		this.views.add('mainpanel', new ExtMail.MainPanel());
		this.setActiveItem('mainpanel').doLayout();
		ExtMail.Controllers.MainController.superclass.show.call(this);
	},
	addDefs: function() {
		this.defs.add('emailgrid', new Stachl.StoreDef({
			message: {},
			folder: {},
			subject: {
				grid: {
					header: 'Subject',
					width: 450,
					sortable: true
				}
			},
			sender: {
				grid: {
					header: 'Sender',
					width: 240,
					sortable: true
				}
			},
			date: {
				grid: {
					header: 'Date',
					width: 160,
					sortable: true,
					xtype: 'datecolumn',
					format: 'd.m.Y H:i'
				},
				store: {
					type: 'date'
				}
			},
			flag: {
				grid: {
					header: 'Flag',
					width: 30,
					sortable: true,
					renderer: function(v) {
						if (v === true) {
							v = '<img src="/images/icons/small/star.png" />';
						} else {
							v = '';
						}
						return v;
					}
				}
			},
			seen: {},
			answered: {},
			deleted: {}
		}));
	},
	addStores: function() {
	}
});