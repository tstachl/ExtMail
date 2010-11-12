Ext.ns('Stachl');
Stachl.TabPanel = Ext.extend(Ext.TabPanel, {
	onStripMouseDown: function(e) {
		Stachl.TabPanel.superclass.onStripMouseDown.call(this, e);
		if (e.button !== 1) {
			return;
		}
		e.preventDefault();
		var t = this.findTargets(e);
		if (t.item && t.item.closable) {
			this.closeTab(t.item);
			return;
		}
	},
	closeTab: function(tab) {
		if (tab.fireEvent('beforeclose', tab) !== false) {
			tab.fireEvent('close', tab);
			this.remove(tab);
		}
	}
});