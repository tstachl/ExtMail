Ext.ns('ExtMail', 'ExtMail.Library');
ExtMail.Library.FolderNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {
	render: function() {
		ExtMail.Library.FolderNodeUI.superclass.render.call(this);
		this.newEmailsLayout();
		this.node.on('textchange', this.newEmailsLayout, this);
		this.node.on('append', this.onAppend, this);
	},
	newEmailsLayout: function() {		
		if (this.node.attributes.newCount > 0) {
			this.addClass('newemails');
			this.textNode.innerHTML = String.format('{0} ({1})', this.node.text, this.node.attributes.newCount);
		} else {
			this.removeClass('newemails');
			this.textNode.innerHTML = this.node.text;
		}
	},
	onAppend: function() {
		this.node.leaf = false;
		this.updateExpandIcon();
		this.node.expand();
	}
});