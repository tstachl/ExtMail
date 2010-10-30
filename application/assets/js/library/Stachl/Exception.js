Ext.namespace('Stachl');
Stachl.Exception = Ext.extend(Error, {
	console: false,
	message: null,
	name: 'Stachl Error',
	fileName: null,
	lineNumber: null,
	stack: null,
	fn: null,
	scope: null,
	
	constructor: function(message, filename, linenumber, fn, scope) {
		if (typeof(fn) == 'function') {
			this.fn = fn;
		}
		if (typeof(scope) == 'object') {
			this.scope = scope
		}
		if (typeof(message) !== 'undefined') {
			this.message = message;
		}
		if (typeof(filename) !== 'undefined') {
			this.fileName = filename;
		}
		if (typeof(linenumber) !== 'undefined') {
			this.lineNumber = linenumber;
		}
		
		if (typeof(window.console) !== 'undefined') {
			this.writeConsole();
		}		
		Stachl.Exception.superclass.constructor.call(this);
	},
	getMessage: function() {
		return this.message;
	},
	getFunction: function() {
		if (null !== this.fn) {
			return this.fn;
		}
		return false;
	},
	getScope: function() {
		if (null !== this.scope) {
			return this.scope;
		}
		return false;
	},
	getName: function() {
		return this.name;
	},
	getFileName: function() {
		return this.fileName;
	},
	getLineNumber: function() {
		return this.lineNumber;
	},
	getStack: function() {
		return this.stack;
	},
	writeConsole: function() {
		Debug.error(this.getMessage() + ' | File: ' + this.getFileName() + ' | Line: ' + this.getLineNumber());
	}
});