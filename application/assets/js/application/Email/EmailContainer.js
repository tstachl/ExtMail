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
			var r    = grid.getSelectionModel().getSelected(),
				me   = this,
				cell = e.getTarget('.x-grid3-col');
			
			if ('flag' == grid.getColumnModel().getDataIndex(cell.cellIndex)) {
				if (r.get('flag')) {
					this.mainpanel.getSouth().showBusy(String.format(_('Remove flag from: "{0}" ...'), r.get('subject')));
				} else {
					this.mainpanel.getSouth().showBusy(String.format(_('Add flag to: "{0}" ...'), r.get('subject')));
				}
				
				Ext.Ajax.request({
					url: '/email/flag',
					params: {
						message: r.get('message'),
						flag: (r.get('flag') ? 0 : 1)
					},
					success: function() {
						r.set('flag', (r.get('flag') ? false : true));
						grid.getStore().commitChanges();
						me.mainpanel.getSouth().clearStatus();
					}
				});
			} else {
				this.setRead(r);
				
				this.getPreviewPanel().showLoading();
				Ext.Ajax.request({
					url: '/email/body',
					params: {
						folder: this.folder,
						message: r.get('message')
					},
					success: function(d) {
						var b = me.prepareBody(Ext.util.JSON.decode(d.responseText));
						me.overwriteTemplates(r.data, b);
						me.resizePreviewPanel();
						me.getPreviewPanel().scrollToTop();
						me.getPreviewPanel().hideLoading();
					}
				});
			}
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
			t = '<div style="font-family: Courier; font-size: 0.83em;">' + Ext.util.Format.trim(t) + '</div>';
		}
		return t;
	},
	setRead: function(r) {
		if (r.get('seen') == false) {
			r.set('seen', true);
			--this.mainpanel.getWest().findByType('extmail_email_navigation')[0].getSelectionModel().getSelectedNode().attributes.newCount;
			this.mainpanel.getWest().findByType('extmail_email_navigation')[0].getSelectionModel().getSelectedNode().getUI().newEmailsLayout();
		}
	}
});
Ext.reg('extmail_email_emailcontainer', ExtMail.Email.EmailContainer);