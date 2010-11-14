Ext.ns('Stachl');
Stachl.TabPanel = Ext.extend(Ext.TabPanel, {
	onStripMouseDown: function(e) {
		Debug.log(e);
		Stachl.TabPanel.superclass.onStripMouseDown.call(this, e);
		if ((Ext.isGecko && e.button !== 1) || (Ext.isWebkit && e.button !== 2)) {
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