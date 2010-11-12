Ext.ns('ExtMail.Email');
ExtMail.Email.NavigationMenu = Ext.extend(Ext.util.Observable, {
    constructor: function(config) {
	    this.addEvents({
	    	'renamefolder': true,
	    	'createfolder': true,
	    	'deletefolder': true,
	    	'openfolder': true,
	    	'openfoldertab': true
	    });
	    
	    this.listeners = config.listeners;
	    
	    ExtMail.Email.NavigationMenu.superclass.constructor.call(this, config);
	},
    
	init: function(tree) {
        this.tree = tree;
        tree.on({
            scope: this,
            contextmenu: this.onContextMenu,
            containercontextmenu: this.onContainerContextMenu,
            destroy: this.destroy
        });
    },
    
    destroy: function() {
        Ext.destroy(this.menu);
        delete this.menu;
        delete this.tree;
        delete this.active;    
    },
    
    onMenuHide: function() {
    	Ext.destroy(this.menu);
    	delete this.menu;
    },
    
    onContextMenu: function(node, e) {
    	var m = this.createMenu();
    	this.active = node;
    	
    	var items = [{
    		text: _('Open'),
    		handler: this.openFolder,
    		scope: this
    	}, {
    		text: _('Open in New Tab'),
    		handler: this.openFolderTab,
    		scope: this
    	}, {
    		text: _('Rename folder'),
    		handler: this.renameFolder,
    		iconCls: 'ico_folder_rename',
    		scope: this
    	}, {
    		text: _('Create folder'),
    		handler: this.createFolder,
    		iconCls: 'ico_folder_create',
    		scope: this
    	}, {
    		text: _('Delete folder'),
    		handler: this.deleteFolder,
    		iconCls: 'ico_folder_delete',
    		scope: this
    	}];
    	
    	m.add(items);
    	
    	e.stopEvent();
    	m.showAt(e.getPoint());
    },
    
    onContainerContextMenu: function(tree, e) {
    	var m = this.createMenu();

    	var items = [{
			text: _('Open')
		}, {
			text: _('Open in new tab')
		}, '-', {
			text: _('New folder')
		}, '-', {
			text: _('Mark folder read')
		}];
    	
    	m.add(items);
    	
    	e.stopEvent();
    	m.showAt(e.getPoint());
    },
    
    createMenu: function() {    	
    	this.menu = new Ext.menu.Menu({
    		listeners: {
    			scope: this,
    			hide: this.onMenuHide
    		}
    	});
    	
    	return this.menu;
    },
    
    renameFolder: function() {
    	this.fireEvent('renamefolder', this.active);
    },
    
    createFolder: function() {
    	this.fireEvent('createfolder', this.active);
    },
    
    deleteFolder: function() {
    	this.fireEvent('deletefolder', this.active);
    },
    
    openFolder: function() {
    	this.fireEvent('openfolder', this.active);
    },
    
    openFolderTab: function() {
    	this.fireEvent('openfoldertab', this.active);
    }
    
});