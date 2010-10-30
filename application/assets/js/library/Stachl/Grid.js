Ext.ns('Stachl');
Stachl.Grid = function(config) {
	Ext.apply(this, config);
	Stachl.Grid.superclass.constructor.call(this, config);
};

Ext.extend(Stachl.Grid, Ext.grid.GridPanel, {
	searchFields: [],
	nogoFields: [],
	columnDef: null,
	bbarDisplay: null,
	bbarEmpty: null,
	initComponent: function() {
		Stachl.Grid.superclass.initComponent.call(this);
		
		var c = this.columnDef.getGridColumns();
		c.unshift(new Ext.grid.RowNumberer());
		
		this._createButton = Ext.id();
		this._editButton = Ext.id();
		this._destroyButton = Ext.id();
		
		var config = {
			viewConfig: {
				forceFit: true
			},
			loadMask: true,
			colModel: new Ext.grid.ColumnModel({
				columns: c
			}),
			selModel: new Ext.grid.RowSelectionModel({
				singleSelect:true
			}),
			plugins: [new Ext.ux.grid.Search({
				position: 'top',
				align: 'right',
				width: 200,
				checkIndexes: this.searchFields,
				disableIndexes: this.nogoFields,
				searchTipText: _('Try to search, you might be faster.'),
				searchText: _('Search'),
				selectAllText: _('Select all'),
				iconCls: '',
				showSelectAll: false,
				showMenuButton: false
			})],
			tbar: new Ext.Toolbar({
				items: [{
					id: this._createButton,
					text: _('New'),
					iconCls: 'ico_new',
					disabled: ((this.permission == 2) ? false : true),
					handler: this.create,
					scope: this
				}, {
					id: this._editButton,
					text: _('Edit'),
					iconCls: 'ico_edit',
					disabled: true,
					handler: this.edit,
					scope: this
				}, {
					id: this._destroyButton,
					text: _('Delete'),
					iconCls: 'ico_delete',
					disabled:true,
					handler: this.destroy,
					scope: this
				}]
			}),
			bbar: new Ext.PagingToolbar({
				store: this.store,
				pageSize: 20,
				displayInfo: true,
	        	displayMsg: this.bbarDisplay + ' {0} - {1} ' + _('of') + ' {2}',
	        	emptyMsg: this.bbarEmpty,
	        	beforePageText: _('Page'),
	        	afterPageText: _('of') + ' {0}',
	        	firstText: _('First page'),
	        	lastText: _('Last page'),
	        	nextText: _('Next page'),
	        	prevText: _('Previous page'),
	        	refreshText: _('Refresh')
			})
		};
		Ext.applyIf(this, config);
		
		this.addEvents({
			'create': true,
			'edit': true,
			'destroy': true
		});
		
		Stachl.Grid.superclass.initComponent.apply(this, arguments);
		
		this.on('beforerender', this.onBeforeRender, this);
		if (this.permission == 2) {
			this.getSelectionModel().on('rowselect', this.enableButtons, this);
		}
	},
	onBeforeRender: function() {
		if (Ext.isDefined(this.getStore().baseParams.query)) {
			this.getStore().baseParams.query = '';
		}
		this.getStore().load();
	},
	create: function() {
		this.fireEvent('create');
	},
	edit: function() {
		this.fireEvent('edit');
	},
	destroy: function() {
		this.fireEvent('destroy');
	},
	enableButtons: function() {
		Ext.getCmp(this._editButton).enable();
		Ext.getCmp(this._destroyButton).enable();
	}
});
Ext.reg('stachl_grid', Stachl.Grid);