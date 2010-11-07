Ext.ns('Stachl');
Stachl.BufferGridView = Ext.extend(Ext.grid.GridView, {
	/**
	 * @cfg {Number} rowHeight
	 * The height of a row in the grid.
	 */
	rowHeight: 19,

	/**
	 * @cfg {Number} borderHeight
	 * The combined height of border-top and border-bottom of a row.
	 */
	borderHeight: 2,

	/**
	 * @cfg {Boolean/Number} scrollDelay
	 * The number of milliseconds before rendering rows out of the visible
	 * viewing area. Defaults to 100. Rows will render immediately with a config
	 * of false.
	 */
	scrollDelay: 100,

	/**
	 * @cfg {Number} cacheSize
	 * The number of rows to look forward and backwards from the currently viewable
	 * area.  The cache applies only to rows that have been rendered already.
	 */
	cacheSize: 20,

	/**
	 * @cfg {Number} cleanDelay
	 * The number of milliseconds to buffer cleaning of extra rows not in the
	 * cache.
	 */
	cleanDelay: 500,
	
	constructor: function() {
		Stachl.BufferGridView.superclass.constructor.apply(this, arguments);
		this.addEvents('beforeupdate');
		Stachl.BufferGridView.superclass.constructor.call(this);
	},
	
	initTemplates: function() {
		Stachl.BufferGridView.superclass.initTemplates.call(this);
		var ts = this.templates;
		// empty div to act as a place holder for a row
		ts.rowHolder = new Ext.Template(
			'<div class="x-grid3-row {alt}" style="{tstyle}"></div>'
		);
		ts.rowHolder.disableFormats = true;
		ts.rowHolder.compile();
		
		ts.rowBody = new Ext.Template(
		        '<table class="x-grid3-row-table" border="0" cellspacing="0" cellpadding="0" style="{tstyle}">',
			'<tbody><tr>{cells}</tr>',
			(this.enableRowBody ? '<tr class="x-grid3-row-body-tr" style="{bodyStyle}"><td colspan="{cols}" class="x-grid3-body-cell" tabIndex="0" hidefocus="on"><div class="x-grid3-row-body">{body}</div></td></tr>' : ''),
			'</tbody></table>'
		);
		ts.rowBody.disableFormats = true;
		ts.rowBody.compile();
	},
		
	getStyleRowHeight: function() {
		return Ext.isBorderBox ? (this.rowHeight + this.borderHeight) : this.rowHeight;
	},
		
	getCalculatedRowHeight: function() {
		return this.rowHeight + this.borderHeight;
	},
		
	getVisibleRowCount: function() {
		var rh = this.getCalculatedRowHeight(),
		    visibleHeight = this.scroller.dom.clientHeight;
		return (visibleHeight < 1) ? 0 : Math.ceil(visibleHeight / rh);
	},
		
	getVisibleRows: function() {
		var count = this.getVisibleRowCount(),
		    sc = this.scroller.dom.scrollTop,
		    start = (sc === 0 ? 0 : Math.floor(sc/this.getCalculatedRowHeight())-1);
		return {
			first: Math.max(start, 0),
			last: Math.min(start + count + 2, this.ds.getCount()-1)
		};
	},
	
	doRender: function(cs, rs, ds, startRow, colCount, stripe, onlyBody) {
		var ts = this.templates, 
        ct = ts.cell, 
        rt = ts.row, 
        rb = ts.rowBody, 
        last = colCount-1,
	    rh = this.getStyleRowHeight(),
	    vr = this.getVisibleRows(),
	    tstyle = 'width:'+this.getTotalWidth()+';height:'+rh+'px;',
	    // buffers
	    buf = [], 
        cb, 
        c, 
        p = {}, 
        rp = {tstyle: tstyle}, 
        r;
		for (var j = 0, len = rs.length; j < len; j++) {
			r = rs[j]; cb = [];
			var rowIndex = (j+startRow),
			    visible = rowIndex >= vr.first && rowIndex <= vr.last;
			if (visible) {
				for (var i = 0; i < colCount; i++) {
					c = cs[i];
					p.id = c.id;
					p.css = i === 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');
					p.attr = p.cellAttr = "";
					p.value = c.renderer(r.data[c.name], p, r, rowIndex, i, ds);
					p.style = c.style;
					if (p.value === undefined || p.value === "") {
						p.value = "&#160;";
					}
					if (r.dirty && typeof r.modified[c.name] !== 'undefined') {
						p.css += ' x-grid3-dirty-cell';
					}
					cb[cb.length] = ct.apply(p);
				}
			}
			var alt = [];
			if (stripe && ((rowIndex+1) % 2 === 0)) {
			    alt[0] = "x-grid3-row-alt";
			}
			if (r.dirty) {
			    alt[1] = " x-grid3-dirty-row";
			}
			rp.cols = colCount;
			if (this.getRowClass) {
			    alt[2] = this.getRowClass(r, rowIndex, rp, ds);
			}
			rp.alt = alt.join(" ");
			rp.cells = cb.join("");
			buf[buf.length] =  !visible ? ts.rowHolder.apply(rp) : (onlyBody ? rb.apply(rp) : rt.apply(rp));
		}
		return buf.join("");
	},
	
	isRowRendered: function(index) {
		var row = this.getRow(index);
		return row && row.childNodes.length > 0;
	},
	
	syncScroll: function() {
		Stachl.BufferGridView.superclass.syncScroll.apply(this, arguments);
		this.update();
	},

	// a (optionally) buffered method to update contents of gridview
	update: function() {
		if (this.scrollDelay) {
			if (!this.renderTask) {
				this.renderTask = new Ext.util.DelayedTask(this.doUpdate, this);
			}
			this.renderTask.delay(this.scrollDelay);
		}else{
			this.doUpdate();
		}
	},
	
    onRemove: function(ds, record, index, isUpdate) {
        Stachl.BufferGridView.superclass.onRemove.apply(this, arguments);
        if (isUpdate !== true) {
            this.update();
        }
    },
    
    onLoad: function() {
    	// overwritten so it doesn't scroll to the top!
    },
    
	doUpdate: function() {
		if (this.getVisibleRowCount() > 0) {
	    	this.fireEvent('beforeupdate', this);
			var g = this.grid, 
                cm = g.colModel, 
                ds = g.store,
    	        cs = this.getColumnData(),
		        vr = this.getVisibleRows(),
                row;
			for (var i = vr.first; i <= vr.last; i++) {
				// if row is NOT rendered and is visible, render it
				if (!this.isRowRendered(i) && (row = this.getRow(i))) {
					var html = this.doRender(cs, [ds.getAt(i)], ds, i, cm.getColumnCount(), g.stripeRows, true);
					row.innerHTML = html;
				}
			}
			this.clean();
		}
	},

	// a buffered method to clean rows
	clean: function() {
		if (!this.cleanTask) {
			this.cleanTask = new Ext.util.DelayedTask(this.doClean, this);
		}
		this.cleanTask.delay(this.cleanDelay);
	},

	doClean: function() {
		if (this.getVisibleRowCount() > 0) {
			var vr = this.getVisibleRows();
			vr.first -= this.cacheSize;
			vr.last += this.cacheSize;

			var i = 0, rows = this.getRows();
			// if first is less than 0, all rows have been rendered
			// so lets clean the end...
			if (vr.first <= 0) {
				i = vr.last + 1;
			}
			for (var len = this.ds.getCount(); i < len; i++) {
				// if current row is outside of first and last and
				// has content, update the innerHTML to nothing
				if ((i < vr.first || i > vr.last) && rows[i].innerHTML) {
					rows[i].innerHTML = '';
				}
			}
		}
	},
    
    removeTask: function(name) {
        var task = this[name];
        if (task && task.cancel) {
            task.cancel();
            this[name] = null;
        }
    },
    
    destroy: function() {
        this.removeTask('cleanTask');
        this.removeTask('renderTask');  
        Stachl.BufferGridView.superclass.destroy.call(this);
    },

	layout: function() {
		Stachl.BufferGridView.superclass.layout.call(this);
		this.update();
	}
});

Stachl.BufferGrid = Ext.extend(Ext.grid.GridPanel, {
	loadMask: true,
	loading: false,
	firstLoad: false,
	initComponent: function() {
		Stachl.BufferGrid.superclass.initComponent.call(this);
		Ext.apply(this, {
			view: new Stachl.BufferGridView(Ext.apply(this.viewConfig, {
				listeners: {
					beforeupdate: this.beforeUpdate,
					scope: this
				}
			}))
		});
		
		this.addEvents('beforeload', 'load');
	},
	beforeUpdate: function(gv) {
		var vrc = gv.getVisibleRowCount(),
			vr = gv.getVisibleRows(),
			cache = gv.cacheSize;
		if (!this.loading && 
				(this.getStore().getCount() < (vr.first + vrc + cache)) &&
				(!this.firstLoad ||
						(this.getStore().getCount() != this.getStore().getTotalCount()))) {
			this.loading = true;
			this.fireEvent('beforeload');
			this.getStore().load({
				params: {
					start: this.getStore().getCount(),
					limit: (vr.first + vrc + cache) - this.getStore().getCount()
				},
				callback: function() {
					this.firstLoad = true;
					this.loading = false;
					if (this.loadMask) {
						this.loadMask.hide();
						this.loadMask.destroy();
						this.loadMask = false;
					}
					this.fireEvent('load');
					if (Ext.isFunction(this.preload)) {
						this.preload();
					}
				},
				scope: this,
				add: true
			});
		}
	}
});