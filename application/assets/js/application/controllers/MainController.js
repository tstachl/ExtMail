Ext.ns('ExtMail.Controllers');
ExtMail.Controllers.MainController = Ext.extend(Stachl.Controller, {
	title: 'Email - ExtMail - w3agency.net',
	copyright: '© 2010 by <a href="http://www.w3agency.net/" target="_blank" title="w3agency.net">w3agency.net</a>',
	statusId: null,
	initComponent: function() {
		this.addDefs();
		this.addStores();
		this.statusId = Ext.id();
		
		Ext.apply(this, {
			layout: 'border',
			border: false,
			region: 'center',
			style: '',
			items: [
			    this.getMainContainer(), {
			    xtype: 'extmail_status',
			    region: 'south',
			    id: this.statusId
			}]
		});
		
		ExtMail.Controllers.MainController.superclass.initComponent.call(this);
	},
	setMainContainer: function() {
		this.mainContainer = new Stachl.TabPanel({
	    	region: 'center',
	    	listeners: {
				beforetabchange: this.beforeTabChange,
				scope: this
			},
			tbar: new ExtMail.Toolbar({
				listeners: {
					movepreview: this.movePreview,
					receive: this.receive,
					scope: this
				}
			}),
			plugins: new Ext.ux.TabCloseMenu(),
	    	activeTab: 0
		});
		return this;
	},
	beforeTabChange: function(tp, t) {
		if (Ext.isDefined(t) && t.hideMenu) {
			tp.getTopToolbar().hide();
			tp.doLayout();
		} else {
			tp.getTopToolbar().show();
			tp.doLayout();
		}
	},
	show: function() {
		this.views.add('mainpanel', new ExtMail.MainPanel({
			title: _('Email'),
			iconCls: 'ico_email',
			controller: this
		}));
		
		this.views.each(function(item, index, length) {
			this.getMainContainer().add(item);
		}, this);
		this.setActiveItem('mainpanel');
		ExtMail.Controllers.MainController.superclass.show.call(this);
	},
	setActiveItem: function(i) {
		var v = this.views.getView(i);		
		return this.getMainContainer().setActiveTab(v);
	},
	getStatus: function() {
		return Ext.getCmp(this.statusId);
	},
	movePreview: function(tb, button) {
		this.getMainContainer().findByType('extmail_email_emailcontainer')[0].movePreview(button.name);
	},
	receive: function(tb) {
		this.getMainContainer().findByType('extmail_email_emailgrid')[0].checkForNew();
	},
	addDefs: function() {
		this.defs.add('emailgrid', new Stachl.StoreDef({
			message: {},
			folder: {},
			subject: {
				grid: {
					header: 'Subject',
					width: 450,
					sortable: true,
					editable: false
				}
			},
			sender: {
				grid: {
					header: 'Sender',
					width: 240,
					sortable: true,
					editable: false
				}
			},
			date: {
				grid: {
					header: 'Date',
					width: 160,
					sortable: true,
					xtype: 'datecolumn',
					format: 'd.m.Y H:i',
					editable: false
				},
				store: {
					type: 'date'
				}
			},
			flags: {},
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