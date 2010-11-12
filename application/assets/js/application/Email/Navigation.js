Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.Navigation = Ext.extend(Stachl.Navigation.Tree, {
	initComponent: function() {
		ExtMail.Email.Navigation.superclass.initComponent.call(this);
		
		var me = this;
		Ext.apply(this, {
			url: '/email/folders',
			loadMask: false,
			editDelay: 300,
			plugins: new ExtMail.Email.NavigationMenu({
				listeners: {
					renamefolder: this.renameFolder,
					createfolder: this.createFolder,
					deletefolder: this.deleteFolder,
					openfolder: this.openFolder,
					openfoldertab: this.openFolderTab,
					scope: this
				}
			})
		});
		
		ExtMail.Email.Navigation.superclass.initComponent.apply(this, arguments);
		
		this.getLoader().addListener('beforeload', function() {
			App.getInstance().updateLoading(_('Loading folders ...'));
			this.mainpanel.getSouth().showBusy(_('Loading folders ...'));
		}, this);
		this.getLoader().addListener('load', function() {
			App.getInstance().hideLoading();
			this.mainpanel.getSouth().clearStatus();
		}, this);
		
		this.treeEditor = new Ext.tree.TreeEditor(this, {
			cancelOnEsc: true,
			completeOnEnter: true,
			ignoreNoChange: true
		});
		
		this.treeEditor.on('complete', this.complete, this);
	},
	setStartPath: function(path) {
		this.startPath = path;
	},
	setLookupChild: function(o) {
		if (Ext.isObject(o) && Ext.isDefined(o.attribute) && Ext.isDefined(o.value)) {
			this.lookupChild = o;
		}
	},
	complete: function(editor, value, start) {
		var node = this.getNodeById(editor.editNode.id);
		
		if (value != start) {
			Ext.Ajax.request({
				url: '/email/folderrename',
				params: {
					folder: node.attributes.classConfig.folder,
					name: value,
					oldname: start
				},
				success: function(r) {
					r = Ext.decode(r.responseText);
					if (r.success) {
						node.attributes.classConfig.folder = r.folder;
					} else {
						msg = String.format(_('An error occured while renaming folder "{0}" to "{1}".<br />Make sure there are no forbidden characters in the new name and try it again.'), start, value);
						App.getInstance().showError(_('Error folder renaming'), msg);
						node.setText(start);
					}
				}
			});
		}
	},
	renameFolder: function(node) {
		this.treeEditor.triggerEdit(node);
	},
	createFolder: function(node) {
		var name = _('New folder'),
			me = this;
		Ext.Ajax.request({
			url: '/email/foldercreate',
			params: {
				folder: node.attributes.classConfig.folder,
				name: name
			},
			success: function(r) {
				r = Ext.decode(r.responseText);
				if (r.success) {
					var n = me.getLoader().createNode(r.folder);
					node.appendChild(n);
					me.treeEditor.triggerEdit(n);
				} else {
					msg = _('An error occured while creating the folder.');
					App.getInstance().showError(_('Error creating folder'), msg);
				}
			}
		});
	},
	deleteFolder: function(node) {
		node.getUI().addClass('deletefolder');
		Ext.Ajax.request({
			url: '/email/folderdelete',
			params: {
				folder: node.attributes.classConfig.folder
			},
			success: function(r) {
				r = Ext.decode(r.responseText);
				if (r.success) {
					node.destroy();
				} else {
					msg = _('The folder could not be deleted.');
					App.getInstance().showError(_('Error deleting folder'), msg);
					node.getUI().removeClass('deletefolder');
				}
			}
		});
	},
	openFolder: function(node) {
		this.selectPath(node.getPath());
		this.clicked();
	},
	openFolderTab: function(node) {
		var controller = this.mainpanel.controller,
			npId = Ext.id(),
			np;
		
		controller.views.add(npId, new ExtMail.MainPanel({
			title: String.format('{0} - {1}', node.attributes.text, _('Email')),
			iconCls: 'ico_email',
			controller: controller,
			closable: true
		}));
		
		np = controller.views.get(npId);
		np.getWest().getEmailNavigation().setLookupChild({
			attribute: 'text',
			value: node.attributes.text,
			deep: true
		});
		controller.getMainContainer().add(np);
		controller.setActiveItem(npId);
	}
});
Ext.reg('extmail_email_navigation', ExtMail.Email.Navigation);