Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.EmailGrid = Ext.extend(Stachl.BufferGrid, {
	cellSelectionModel: null,
	initComponent: function() {
		App.getInstance().updateLoading(_('Loading messages ...'));
		
		if (!Ext.StoreMgr.get('emailgrid_' + this.folder)) {
			Ext.StoreMgr.add('emailgrid_' + this.folder, new Ext.data.JsonStore({
				url: '/email/read',
				baseParams: {
					folder: this.folder
				},
				root: 'messages',
				totalProperty: 'total',
				idProperty: 'message',
				fields: Stachl.DefMgr.get('emailgrid').getStoreFields()
			}));
		}
	
		Ext.apply(this, {
			loadMask: false,
			columns: Stachl.DefMgr.get('emailgrid').getGridColumns(),
			sm: new Ext.grid.RowSelectionModel(),
			store: Ext.StoreMgr.get('emailgrid_' + this.folder),
			viewConfig: {
				rowHeight: 21,
				forceFit: true,
				cacheSize: 10,
				getRowClass: function(r, i) {
					var cls = 'emailrow';
					if (r.get('seen') == true) {
						cls += ' seen';
					}
					if (r.get('answered') == true) {
						cls += ' answered';
					}
					if (r.get('deleted') == true) {
						cls += ' deleted';
					}
					return cls;
				}
			},
			listeners: {
				beforeload: this.beforeLoad,
				load: this.load,
				mouseover: this.mouseEvent,
				mouseout: this.mouseEvent,
				scope: this
			}
		});
		
		this.tbStatusId = Ext.id();
		
		this.mainpanel.getSouth().add({
			xtype: 'tbtext',
			id: this.tbStatusId,
			text: String.format(_('{0} of {1} messages'), this.getStore().getCount(), this.getStore().getTotalCount())
		});
		this.mainpanel.getSouth().doLayout();
		
		ExtMail.Email.EmailGrid.superclass.initComponent.call(this);
	},
	beforeLoad: function() {
		if (this.mainpanel.getSouth()) {
			this.mainpanel.getSouth().showBusy(_('Loading messages ...'));
		}
	},
	load: function() {
		if (this.mainpanel.getSouth()) {
			this.mainpanel.getSouth().clearStatus();
			var status = String.format(_('{0} of {1} messages'), this.getStore().getCount(), this.getStore().getTotalCount());
			Ext.getCmp(this.tbStatusId).update(status);
		}
		
		if (App.getInstance().isLoadingVisible()) {
			App.getInstance().hideLoading();
		}
	},
	mouseEvent: function(e) {
		e.stopEvent();
	},
	preload: function() {
//		if (!this.preloadTask) {
//			this.preloadTask = new Ext.util.DelayedTask(this.doPreload, this);
//		}
//		this.preloadTask.delay(100);
	},
	doPreload: function() {
		if (!this.loading && this.getStore()) {
			this.loading = true;
			this.fireEvent('beforeload');
			var t = this.getStore().getTotalCount(),
				c = this.getStore().getCount(),
				cache = this.getView().cacheSize,
				l = ((c + cache) < t ? cache : (t + c));
			this.getStore().load({
				params: {
					start: c,
					limit: l
				},
				callback: function() {
					this.loading = false;
					this.fireEvent('load');
					if (c < t) {
						this.preload();
					}
				},
				scope: this,
				add: true
			});
		}
	},    
    removeTask: function(name) {
        var task = this[name];
        if (task && task.cancel) {
            task.cancel();
            this[name] = null;
        }
    },
	destroy: function() {
        this.removeTask('preloadTask');
		if (Ext.isDefined(this.tbStatusId)) {
			Ext.getCmp(this.tbStatusId).destroy();
		}
		this.getStore().suspendEvents();
		ExtMail.Email.EmailGrid.superclass.destroy.call(this);
	}
});
Ext.reg('extmail_email_emailgrid', ExtMail.Email.EmailGrid);