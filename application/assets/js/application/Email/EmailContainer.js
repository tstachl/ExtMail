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
		this.add({
			xtype: 'extmail_email_preview',
			id: this.previewSouth,
			region: 'south',
			height: 300,
			split: true,
			mainpanel: this.mainpanel,
			folder: this.folder
		});
	},
	rowClick: function(grid, rowIndex, e) {
		if (grid.getSelectionModel().getCount() === 1) {
			var r  = grid.getSelectionModel().getSelected(),
				me = this;
			this.getPreviewPanel().showLoading();
			Ext.Ajax.request({
				url: '/email/body',
				params: {
					folder: this.folder,
					message: r.data.message
				},
				success: function(d) {
					var b = me.prepareBody(Ext.util.JSON.decode(d.responseText));
					me.overwriteTemplates(r.data, b);
					me.resizePreviewPanel();
					me.getPreviewPanel().scrollToTop();
					me.getPreviewPanel().hideLoading();
				},
				failure: function() {}
			});
		}
	},
	getPreviewPanel: function() {
		// at the moment we only have south
		return Ext.getCmp(this.previewSouth);
	},
	overwriteTemplates: function(headerData, bodyData) {
		this.getPreviewPanel().getTemplate().overwrite(this.getPreviewPanel().getHeader().body, headerData);
		this.getPreviewPanel().getBody().update('<div class="email-body">' + bodyData + '</div>');
	},
	resizePreviewPanel: function() {
		var bodySize = this.getPreviewPanel().getBody().getSize(),
			headerSize = this.getPreviewPanel().getHeader().getSize(),
			fullSize = this.getPreviewPanel().getSize();
		this.getPreviewPanel().getBody().setPosition(0, headerSize.height);
		this.getPreviewPanel().getBody().setHeight(fullSize.height - headerSize.height);
		this.isResized = headerSize;
	},
	prepareBody: function(body) {
		var t;
		if (Ext.isDefined(body['text/html'])) {
			t = Ext.util.Format.htmlDecode(body['text/html']);
		} else {
			t = Ext.util.Format.htmlDecode(body['text/plain']);
			t = Ext.util.Format.nl2br(t);
			t = Ext.util.Format.stripScripts(t);
			t = '<div style="font-family: Courier;">' + Ext.util.Format.trim(t) + '</div>';
		}
		return t;
	}
});
Ext.reg('extmail_email_emailcontainer', ExtMail.Email.EmailContainer);