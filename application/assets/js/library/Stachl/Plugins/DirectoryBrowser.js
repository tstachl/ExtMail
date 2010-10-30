Ext.ns('Stachl', 'Stachl.DirectoryBrowser');
Stachl.DirectoryBrowser.Plugin = Ext.extend(Ext.util.Observable, {
	events: {},
	/**
	 * Init of plugin
	 * @param {Ext.Component} field
	 */
	init: function(field) {
		this.addEvents({
			'expand': true,
			'collapse': true
		});

		if(field.getXType() != 'textfield')
			return;
		
		this.component = field;
		field.directoryBrowserPlugin = this;
		field.enableKeyEvents = true;
		if (field.browserConfig.showIcon) {
			field.width = (field.width - 24);
		}
		field.on({
			'destroy': this.destroy,
			'focus': function(f) {
				this.activeTarget = f;
				this.fireEvent('focus', f);
			},
			scope: this
		});
				
		Ext.apply(field, {
			onRender: field.onRender.createSequence(function(ct, position) {
				this.wrap = this.el.wrap({cls: "x-form-field-wrap"});
				if(this.fieldLabel && this.browserConfig.showIcon) {
					var label = this.el.findParent('.x-form-element', 5, true) || this.el.findParent('.x-form-field-wrap', 5, true);
					
					this._buttonId = Ext.id();
					
					this.directoryBrowserContainer = label.createChild({
						tag: 'div',
						id: this._buttonId,
						style: 'width:22px;top:0!important;margin-top:-22px;'
					});
					
					this.directoryBrowserIcon = new Ext.Button({
						renderTo: this._buttonId,
						iconCls: 'ico_folder',
						width: 22
					});
										
					this.alignBrowserIcon = function(){
						var el = this.el; 
						this.directoryBrowserContainer.alignTo(el, 'tl-tr', [2, 0]);
					}
					//Redefine alignErrorIcon to move the errorIcon (if it exist) to the right of helpIcon
					if(this.alignErrorIcon) {
						this.alignErrorIcon = function() {
							this.errorIcon.alignTo(this.directoryBrowserContainer, 'tl-tr', [2, 0]);
						}
					}
					
					this.on('resize', this.alignBrowserIcon, this);
					
					this.directoryBrowserIcon.on('click', function(e){
						if(this.disabled){
							return;
						}
						try {
							this.expandBrowser();
							this.el.focus();
						} catch (e) {
							Debug.error(e);
						}
					}, this);
					this.on('focus', function(e) {
						if(this.disabled){
							return;
						}
						try {
							this.expandBrowser();
							this.el.focus();
						} catch (e) {
							Debug.error(e);
						}
					}, this);
					
				}
			}), //end of onRender
			
			initBrowser: function(){
				var cls = 'x-dbrowser-container';
				
				this.browserContainer = new Ext.Layer({
					shadow: false,
					cls: [cls, this.directoryBrowserClass].join(' '),
					constrain: false
				});
				
				this.browserContainer.setWidth(250);
				
				var config = this.browserConfig || {};
				
				config.loader = new Ext.tree.TreeLoader({
                	url: config.url,
                	baseParams: config.baseParams
                });
				
				this.browser = new Ext.tree.TreePanel(config);
				this.browser.render(this.browserContainer);
				this.browser.on('click', this.nodeClick, this);
			},
			
			nodeClick: function(n, e) {
				if (Ext.isDefined(n.attributes.path)) {
					this.setValue(n.attributes.path);
					this.collapseBrowser();
				}
			},
			
			collapseBrowserIf : function(e){
				if(!e.within(this.wrap) && !e.within(this.browserContainer)){
					this.collapseBrowser();
				}
			},

			expandBrowser : function(){
				if(this.isBrowserExpanded() /*|| !this.hasFocus*/){
					return;
				}
				if(!this.browser)
					this.initBrowser();
				this.browserContainer.alignTo(this.wrap, this.browserAlign || 'tl-bl?');
				this.browserContainer.show();
				Ext.getDoc().on('mousewheel', this.collapseBrowserIf, this);
				Ext.getDoc().on('mousedown', this.collapseBrowserIf, this);
				this.directoryBrowserPlugin.fireEvent('expand', this);
			},

			collapseBrowser : function(){
				if(!this.isBrowserExpanded()){
					return;
				}
				this.browserContainer.hide();
				Ext.getDoc().un('mousewheel', this.collapseBrowserIf, this);
				Ext.getDoc().un('mousedown', this.collapseBrowserIf, this);
				this.directoryBrowserPlugin.fireEvent('collapse', this);
			},

			
			isBrowserExpanded : function(){
				return this.browserContainer && this.browserContainer.isVisible();
			}

		}); //end of Ext.apply
	}, // end of function init
	
	destroy: function(component){
		if(component){
			if(component.browser){
				component.browser.remove();
				delete component.browser;
			}
			if(component.browserContainer){
				component.browserContainer.remove();
				delete component.browserContainer;
			}
		}
	},
	
	expand: function(){
		if(this.activeTarget){
			this.activeTarget.expandBrowser();
		}
	}

}); // end of extend