Ext.ns('Stachl');
Stachl.GroupTextField = Ext.extend(Ext.form.TextField, {
	list: null,
	elements: [],
	delimiter: ';',
	initEvents: function() {
		Stachl.GroupTextField.superclass.initEvents.call(this);
		this.mon(this.el, 'keyup', this.extractElements, this);
	},
	onRender: function(ct, position) {
		Stachl.GroupTextField.superclass.onRender.call(this, ct, position);
		this.list = ct.insertFirst(Ext.DomHelper.createDom({
			tag: 'ul',
			cls: 'x-form-group'
		}));
	},
	extractElements: function() {
		var v = this.getRawValue();
		if (this.pattern && this.pattern.test(v)) {
			this.setValue('');
			this.setRawValue('');
			v = v.replace(this.delimiter, '');
			if (this.elements.indexOf(v) == -1) {
				this.elements.push(v);
				this.createNewElement(v);
			}
		}
	},
	createNewElement: function(v) {
		var el = this.list.appendChild(Ext.DomHelper.createDom({
			tag: 'li',
			cls: 'x-form-group-item',
			children: [{
				tag: 'a',
				cls: 'x-form-group-item-text',
				html: v
			}]
		}));
		var cb = el.insertFirst(Ext.DomHelper.createDom({
		    tag: 'a',
		    cls: 'x-form-group-item-close'
		}));
		
		this.ownerCt.doLayout();
		
		cb.on('click', this.removeFromList, this, { el: el, value: v});
		this.checkAllowBlank(true);
	},
	checkAllowBlank: function(flag) {
		if (flag && (this.initialConfig.allowBlank === false)) {
			this.allowBlank = true;
		} else if (!flag && (this.initialConfig.allowBlank === false)) {
			this.allowBlank = false;
		}
	},
	removeFromList: function(e, el, obj) {
		var item = Ext.get(obj.el.id);
		item.remove();
		item.purgeAllListeners();
		this.elements.remove(obj.value);
		this.ownerCt.doLayout();
	},
	getValue: function() {
		if (this.elements.length > 0) {
			var raw = this.getRawValue() + this.delimiter;
			if (this.pattern && this.pattern.test(raw)) {
				this.elements.push(raw.replace(this.delimiter, ''));
			}
			return this.elements.join(this.delimiter);
		}
		return Stachl.GroupTextField.superclass.getValue.call(this);
	}
});
Ext.reg('grouptextfield', Stachl.GroupTextField);