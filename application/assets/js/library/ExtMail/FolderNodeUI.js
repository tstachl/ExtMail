Ext.ns('ExtMail', 'ExtMail.library');
ExtMail.library.FolderNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {
	oldText: null,
	render: function() {
		ExtMail.library.FolderNodeUI.superclass.render.call(this);
		this.newEmailsLayout();
	},
	newEmailsLayout: function() {
		if (null == this.oldText) {
			this.oldText = this.node.attributes.text;
		}
		
		if (this.node.attributes.newCount > 0) {
			this.node.text = this.node.attributes.text = this.oldText + ' (' + this.node.attributes.newCount + ')';
			this.onTextChange(this.node, this.node.text, this.oldText);
			this.node.fireEvent('textchange', this.node, this.node.text, this.oldText);
			this.addClass('newemails');
		} else {
			if (this.node.text != this.oldText) {
				var oldText = this.node.text;
				this.node.text = this.node.attributes.text = this.oldText;
				this.onTextChange(this.node, this.node.text, oldText);
				this.node.fireEvent('textchange', this.node, this.node.text, oldText);
				this.removeClass('newemails');
			}
		}
	}
});