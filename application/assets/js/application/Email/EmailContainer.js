Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.EmailContainer = Ext.extend(Ext.Panel, {
	layout: 'border',
	initComponent: function() {
		ExtMail.Email.EmailContainer.superclass.initComponent.call(this);
		
		var config = {
			layout: 'border',
			border: false
		};
		Ext.apply(this, config);
		
		this.gridId = Ext.id();
		this.previewSouth = Ext.id();
		this.previewEast = Ext.id();
		
		this.add({
			xtype: 'extmail_email_emailgrid',
			id: this.gridId,
			region: 'center',
			mainpanel: this.mainpanel,
			folder: this.folder,
			listeners: {
				rowclick: this.rowClick,
				scope: this
			}
		});
		
		if (ExtMail.Instance.getInstance().options.preview) {
			this.add({
				xtype: 'extmail_email_preview',
				id: this.previewSouth,
				region: 'south',
				height: 300,
				split: true,
				mainpanel: this.mainpanel,
				folder: this.folder
			});
		}
	},
	rowClick: function(grid, rowIndex, e) {
		if (grid.getSelectionModel().getCount() === 1 && ExtMail.Instance.getInstance().options.preview) {
			var r = grid.getSelectionModel().getSelected();
			this.getPreviewPanel().getTemplate().overwrite(this.getPreviewPanel().body, r.data);
		}
	},
	getPreviewPanel: function() {
		// at the moment we only have south
		return Ext.getCmp(this.previewSouth);
	}
});
Ext.reg('extmail_email_emailcontainer', ExtMail.Email.EmailContainer);