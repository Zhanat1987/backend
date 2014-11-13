/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.lib.PagingToolbar', {
    alias: 'widget.pagingToolbar',
    extend:'Ext.toolbar.Paging',
    // private
    onLoad     :function () {
        var me = this,
            pageData,
            currPage,
            pageCount,
            afterText,
            count,
            isEmpty;

        count = me.store.getCount();
        isEmpty = count === 0;
        if (!isEmpty) {
            pageData = me.getPageData();
            currPage = pageData.currentPage;
            pageCount = pageData.pageCount;
            afterText = Ext.String.format(me.afterPageText, isNaN(pageCount) ? 1 : pageCount);
        } else {
            currPage = 0;
            pageCount = 0;
            afterText = Ext.String.format(me.afterPageText, 0);
        }
        currPage = currPage > pageCount ? pageCount : currPage;

        Ext.suspendLayouts();
        me.child('#afterTextItem').setText(afterText);
        me.child('#inputItem').setDisabled(isEmpty).setValue(currPage);
        me.child('#first').setDisabled(currPage === 1 || isEmpty);
        me.child('#prev').setDisabled(currPage === 1 || isEmpty);
        me.child('#next').setDisabled(currPage === pageCount || isEmpty);
        me.child('#last').setDisabled(currPage === pageCount || isEmpty);
        me.child('#refresh').enable();
        me.updateInfo();
        Ext.resumeLayouts(true);

        if (me.rendered) {
            me.fireEvent('change', me, pageData);
        }
    },

    // get data for Grid paging toolbar
    getPageData:function () {
        var store = this.store,
            totalCount = store.getTotalCount();

        return {
            total      :totalCount,
            currentPage:(totalCount == 0 ? 0 : store.currentPage), // write 0 value, in inputItem field, if no results in grid
            pageCount  :Math.ceil(totalCount / store.pageSize),
            fromRecord :((store.currentPage - 1) * store.pageSize) + 1 > totalCount ? 1 : ((store.currentPage - 1) * store.pageSize) + 1,
            toRecord   :Math.min(store.currentPage * store.pageSize, totalCount)

        };
    }
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
// Create the combo box, attached to the data store of each grid, for number of items per page(pageSize)
Ext.define('Application.lib.ItemsPerPageCombo', {
    extend       :'Ext.form.ComboBox',
    alias        :'widget.itemsPerPageCombo',

    // override component initialization to add combo data from app
    initComponent:function () {

        this.callParent(arguments);

        this.store.loadData(config.pageSizeValues);

    },
    store        :{
        fields:['value', 'option'],
        data  :[]
    },
    value        :10,
    width: 60,
    editable     :false,
    queryMode    :'local',
    listeners    :{
        'change'     :function (obj, newVal) {

            obj.up('pagingtoolbar').store.pageSize = newVal;
            obj.up('pagingtoolbar').store.load();
        },
        initComponent:function () {

            // load store with default data
            this.store.loadData(config.pageSizeValues);

        }
    },
    displayField :'option',
    valueField   :'value'
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
/**
 * Remote validation plugin for fields
 */
Ext.define('Application.lib.RemoteValidator', {

    /**
     * @var {object} default options
     */
    options:{

        // set validator to emptyFn
        validator        :null,

        // parameter name
        paramName        :'value',

        // validation message
        message          :'Remote validation failed',

        // extra parameters to send to validation function
        extraParams      :{},

        // validation enabled by default
        validationEnabled:true,

        // number of miliseconds to delay the callback
        callbackDelay    :500


    },

    /**
     * @var {string} Will hold last valided value
     */
    lastRemoteValidationValue:'',

    /**
     * @var {string} Currently validating value
     */
    validatingValue:'',

    /**
     * @var {bool} Will hold last validation result
     */
    lastRemoteValidationResult:true,

    /**
     * @var {bool} Will hold last validation result
     */
    lastRemoteValidationMessage:'',

    /**
     * @var {object} Reference to validated field
     */
    field:null,

    /**
     * @var {function} Reference to the original
     */
    originalIsValid:null,

    /**
     * @var {Ext.util.DelayedTask} The delayed task for sending the callback
     */
    delayedCallbackTask:null,


    /**
     * Plugin initialization method (called automatically
     * by ExtJS when field is created)
     *
     * @param {Object} field
     */
    init:function (field) {

        // field has validation options ?
        if (field.remoteValidationOptions !== undefined) {

            // copy field specific options
            this.options = Ext.clone(Ext.apply(this.options, field.remoteValidationOptions));

        }

        // save field reference
        this.field = field;

        // intercept call
        this.originalIsValid = this.field.isValid;
        this.field.isValid = this.customIsValid;

        // attach plugin
        this.field.remoteValidationPlugin = this;

        // create the delayCallback task
        this.delayedCallbackTask = new Ext.util.DelayedTask();

    },

    /**
     * Custom field validator
     */
    customIsValid:function () {

        // get plugin reference
        var plugin = this.remoteValidationPlugin;

        // call original validation
        var isValid = plugin.originalIsValid.call(this);

        // invalid value or field disabled or nor remote validation?
        if (!isValid || this.disabled || !plugin.options.validationEnabled || typeof plugin.options.validator === 'undefined' || !plugin.options.validator) {

            // do nothing else
            return isValid;

        }

        // get raw value
        var rawValue = this.getRawValue.call(this);

        // process raw value
        rawValue = this.processRawValue.call(this, rawValue);

        // is same with last validated value ?
        if (rawValue === plugin.lastRemoteValidationValue) {

            // validation succeeded ?
            if (plugin.lastRemoteValidationResult) {

                // validation succeeded
                this.clearInvalid();

            } else {

                // validation failed
                this.markInvalid(plugin.lastRemoteValidationMessage);

            }

            // should fire validation change event ?
            if (plugin.lastRemoteValidationResult != this.wasValid) {

                this.wasValid = plugin.lastRemoteValidationResult;

                // fire validation changed event
                this.fireEvent('validitychange', this, plugin.lastRemoteValidationResult);

            }

            // return last validation result
            return plugin.lastRemoteValidationResult;

        }

        // already validation in progress for given value ?

        if (plugin.validatingValue !== rawValue) {

            // build parameters
            var parameters = {};
            parameters[plugin.options.paramName] = rawValue;

            // apply extra params
            Ext.applyIf(parameters, plugin.options.extraParams);

            // save validating value
            plugin.validatingValue = rawValue;

            // delay callback task
            plugin.delayedCallbackTask.delay(plugin.options.callbackDelay, function (parameters) {

                // call remote method
                plugin.options.validator(parameters, {

                    // on success handler
                    success:function (response) {

                        // get plugin reference
                        var plugin = this.remoteValidationPlugin;

                        // reset the validating value
                        plugin.validatingValue = '';

                        // set last validation value
                        plugin.lastRemoteValidationValue = rawValue;

                        // validation succeeded ?
                        if (response.success) {

                            // save last result
                            plugin.lastRemoteValidationResult = true;
                            plugin.lastRemoteValidationMessage = '';

                        } else {

                            // save last result
                            plugin.lastRemoteValidationResult = false;
                            plugin.lastRemoteValidationMessage = typeof response.message == 'string' ? response.message : plugin.options.message;

                        }

                        // force re-validation
                        this.isValid();

                    },

                    // called when remote call failed
                    failure:function (exception, response) {

                        // get plugin reference
                        var plugin = this.remoteValidationPlugin;

                        // reset the validating value
                        plugin.validatingValue = '';

                        // save last result
                        plugin.lastValidationResult = false;
                        plugin.lastValidationMessage = __('Remote validation could not be performed');

                        // force revalidation
                        this.isValid();

                    },

                    // call scope is the field
                    scope  :this

                });
            }, this, [parameters]);

        }

        // fail validation (will be corrected by callback)
        return false;

    },

    /**
     * Sets extra params for the validation function
     *
     * @param {object} extraParams Extra parameters to be sent when calling validation function
     */
    setExtraParameters:function (extraParams) {

        // reset last remote validation value
        this.lastRemoteValidationValue = '';

        // save parameter into private property
        this.options.extraParams = extraParams;

    },

    /**
     * Disable remote validation
     */
    disableValidation:function () {

        this.options.validationEnabled = false;

    },

    /**
     * Enable remote validation
     */
    enableValidation:function () {

        this.options.validationEnabled = true;

    }

});
Ext.define('Application.lib.TreeFilter', {
    extend: 'Ext.AbstractPlugin'
    , alias: 'plugin.treefilter'

    , collapseOnClear: false                                                 // collapse all nodes when clearing/resetting the filter
    , allowParentFolders: false                                             // allow nodes not designated as 'leaf' (and their child items) to  be matched by the filter

    , init: function (tree) {
        var me = this;
        me.tree = tree;

        tree.filter = Ext.Function.bind(me.filter, me);
        tree.clearFilter = Ext.Function.bind(me.clearFilter, me);
    }

    , filter: function (value, property, re) {
        var me = this
            , tree = me.tree
            , matches = []                                                  // array of nodes matching the search criteria
            , root = tree.getRootNode()                                     // root node of the tree
            , property = property || 'text'                                 // property is optional - will be set to the 'text' propert of the  treeStore record by default
            , re = re || new RegExp(value, "ig")                            // the regExp could be modified to allow for case-sensitive, starts  with, etc.
            , visibleNodes = []                                             // array of nodes matching the search criteria + each parent non-leaf  node up to root
            , viewNode;

        if (Ext.isEmpty(value)) {                                           // if the search field is empty
            me.clearFilter();
            return;
        }

        //tree.expandAll();                                                   // expand all nodes for the the following iterative routines

        // iterate over all nodes in the tree in order to evalute them against the search criteria
        root.cascadeBy(function (node) {
            if (node.get(property).match(re)) {                             // if the node matches the search criteria and is a leaf (could be  modified to searh non-leaf nodes)
                matches.push(node);                                         // add the node to the matches array
            }
        });

        if (me.allowParentFolders === false) {                              // if me.allowParentFolders is false (default) then remove any  non-leaf nodes from the regex match
            Ext.each(matches, function (match) {
                if (!match.isLeaf()) {
                    Ext.Array.remove(matches, match);
                }
            });
        }

        Ext.each(matches, function (item, i, arr) {                         // loop through all matching leaf nodes
            root.cascadeBy(function (node) {                                // find each parent node containing the node from the matches array
                if (node.contains(item) == true) {
                    visibleNodes.push(node);                                // if it's an ancestor of the evaluated node add it to the visibleNodes  array
                }
            });
            if (me.allowParentFolders === true && !item.isLeaf()) {        // if me.allowParentFolders is true and the item is  a non-leaf item
                item.cascadeBy(function (node) {                            // iterate over its children and set them as visible
                    visibleNodes.push(node);
                });
            }
            visibleNodes.push(item);                                        // also add the evaluated node itself to the visibleNodes array
        });

        root.cascadeBy(function (node) {                                    // finally loop to hide/show each node
            viewNode = Ext.fly(tree.getView().getNode(node));               // get the dom element assocaited with each node
            if (viewNode) {                                                 // the first one is undefined ? escape it with a conditional
                viewNode.setVisibilityMode(Ext.Element.DISPLAY);            // set the visibility mode of the dom node to display (vs offsets)
                viewNode.setVisible(Ext.Array.contains(visibleNodes, node));
            }
        });
    }

    , clearFilter: function () {
        var me = this
            , tree = this.tree
            , root = tree.getRootNode();

        if (me.collapseOnClear) {
            tree.collapseAll();                                             // collapse the tree nodes
        }
        root.cascadeBy(function (node) {                                    // final loop to hide/show each node
            viewNode = Ext.fly(tree.getView().getNode(node));               // get the dom element assocaited with each node
            if (viewNode) {                                                 // the first one is undefined ? escape it with a conditional and show  all nodes
                viewNode.show();
            }
        });
    }
});/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.lib.SearchField', {
    extend:'Ext.form.field.Trigger',

    alias:'widget.searchfield',

    trigger1Cls:Ext.baseCSSPrefix + 'form-clear-trigger',

    trigger2Cls:Ext.baseCSSPrefix + 'form-search-trigger',

    hasSearch:false,
    paramName:'filter',

    initComponent:function () {
        var me = this;

        me.callParent(arguments);
        me.on('specialkey', function (f, e) {
            if (e.getKey() == e.ENTER) {
                me.onTrigger2Click();
            }
        });

        // We're going to use filtering
        me.store.remoteFilter = true;

        // Set up the proxy to encode the filter in the simplest way as a name/value pair

        // If the Store has not been *configured* with a filterParam property, then use our filter parameter name
        if (!me.store.proxy.hasOwnProperty('filterParam')) {
            me.store.proxy.filterParam = me.paramName;
        }
        me.store.proxy.encodeFilters = function (filters) {
            return filters[0].value;
        }
    },

    afterRender:function () {
        this.callParent();
        this.triggerCell.item(0).setDisplayed(false);
    },

    clearFilter: function(suppressEvent) {
        var me = this;

        if (me.hasSearch) {
            me.setValue('');
            me.store.clearFilter(suppressEvent);
            me.hasSearch = false;
            me.triggerCell.item(0).setDisplayed(false);
            me.updateLayout();
        }
    },

    onTrigger1Click:function () {
        this.clearFilter();
    },

    onTrigger2Click:function () {
        var me = this,
            value = me.getValue();

        if (value.length > 0) {
            // Param name is ignored here since we use custom encoding in the proxy.
            // id is used by the Store to replace any previous filter
            me.store.filter({
                id      :me.paramName,
                property:me.paramName,
                value   :value
            });
            me.hasSearch = true;
            me.triggerCell.item(0).setDisplayed(true);
            me.updateLayout();
        }
    }
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.model.field', {
    extend:'Ext.data.Model',
    fields:[
        {
            type: 'string',
            name: 'key'
        },
        {
            type:'auto',
            name:'value'
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.model.jsonDocument', {
    extend:'Ext.data.Model',
    idProperty:'_id',
    fields:[
        {
            name:'_id',
            type:'string'
        },
        {
            name: 'data',
            type: 'string'
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.model.connection', {
    extend:'Ext.data.Model',
    fields:[
        {
            name:'id',
            type:'string'
        },
        {
            name:'name',
            type:'string',
            defaultValue: 'New connection'
        },
        {
            name:'host',
            type:'string',
            defaultValue: 'localhost'
        },
        {
            name:'port',
            type:'int',
            defaultValue: 27017
        },
        {
            name:'user',
            type:'string'
        },
        {
            name:'password',
            type:'string'
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.model.collection', {
    extend:'Ext.data.Model',
    fields:[
        {
            type: 'string',
            name: 'type'
        },
        {
            type:'string',
            name:'name'
        },
        {
            type: 'string',
            name: 'database'
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.model.database', {
    extend:'Ext.data.Model',
    fields:[
        {
            type:'string',
            name:'name'
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.model.documentTree', {
    extend:'Ext.data.Model',
    fields:[
        {
            type:'string',
            name:'property'
        },
        {
            type: 'auto',
            name: 'value'
        },
        {
            type: 'auto',
            name: 'originalValue'
        },
        {
            type: 'string',
            name: 'type'
        },
        {
            type: 'string',
            name: 'json'
        },
        {
            type: 'string',
            name: 'flattenProperty'
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.model.document', {
    extend:'Ext.data.Model',
    idProperty:'_id',
    fields:[
        {
            name:'_id',
            type:'string'
        },
        {
            name: 'database',
            type: 'string'

        },
        {
            name: 'collection',
            type: 'string'
        },
        {
            name:'data',
            type:'auto'
        }
    ],

    get:function (key) {
        if (Ext.isString(key) && key.indexOf('.') !== -1) {
            var parts = key.split('.');
            var result = this.callParent([ parts[0] ]);
            return JSON.stringify(result[parts[1]]);
        }
        return  this.callParent(arguments);
    },
    set:function (key, value) {
        if (Ext.isString(key) && key.indexOf('.') !== -1) {
            var parts = key.split('.');
            var result = this.get(parts[0]);
            result[parts[1]] = value;

            this.callParent([ parts[0], result ]);
            return;
        }
        this.callParent(arguments);
    }

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.store.databases', {
    extend  :'Ext.data.Store',
    id      :'databases',
    model   :'Application.model.database',

    autoSync:false,
    autoLoad:false,

    proxy:{
        type  :'direct',
        api   :{
            read   :Remote.Databases.read,
            create :Remote.Databases.create,
            destroy:Remote.Databases.drop
        },
        reader:{
            type:'json',
            root:'data'
        },
        writer:{
            type       :'json',
            root       :'records',
            allowSingle:false
        },

        simpleSortMode:true
    }

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.store.collection', {
    extend  :'Ext.data.Store',
    id      :'collection',
    model   :'Application.model.document',
    autoSync:false,
    autoLoad:false,
    remoteSort: true,
    pageSize:10,
    proxy   :{
        type          :'direct',
        api           :{
            read    : Remote.Collection.read,
            destroy : Remote.Collection.destroy,
            update  : Remote.Collection.update,
            create  : Remote.Collection.create
        },
        reader        :{
            type:'json',
            root:'data'
        },
        writer        :{
            type       :'json',
            root       :'records',
            allowSingle:false
        },
        simpleSortMode:true
    }

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.store.collections', {
    extend  :'Ext.data.TreeStore',
    id      :'collections',
    model   :'Application.model.collection',

    autoSync:false,
    autoLoad:false,

    root:{
        type  :'root',
        name:'Collections'
    },

    proxy:{
        type  :'direct',
        api   :{
            read   :Remote.Collections.read,
            create :Remote.Collections.create,
            destroy:Remote.Collections.destroy,
            update: function() {

            }
        },
        reader:{
            type:'json',
            root:'data'
        },
        writer:{
            type       :'json',
            root       :'records',
            allowSingle:false
        },

        simpleSortMode:true
    }

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.store.connections', {
    extend:'Ext.data.Store',
    model :'Application.model.connection',

    id:'connections',

    autoSync: false,
    autoLoad: false,

    proxy:{
        type  :'direct',
        api   :{
            read   :Remote.Connections.read,
            create :Remote.Connections.create,
            update :Remote.Connections.update,
            destroy:Remote.Connections.destroy
        },
        reader:{
            type:'json',
            root:'data'
        },
        writer:{
            type       :'json',
            root       :'records',
            allowSingle:false
        },

        simpleSortMode:true
    },

    listeners: {
        write: function(store, operation) {
            switch(operation.action) {
                case 'create':
                    for (var r in operation.records) {
                        var record = operation.records[r];

                        if (operation.response.result.ids[record.internalId]) {
                            record.data.id = operation.response.result.ids[record.internalId];
                        }
                    }
                    break;
            }
        }
    }
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.store.document', {
    extend  :'Ext.data.TreeStore',
    id      :'databases',
    model   :'Application.model.documentTree',

    autoSync:false,
    autoLoad:false,

    listeners: {
        beforeload: function() {
            return false;
        }
    }
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.login.window', {
    extend   : 'Ext.window.Window',
    alias    : 'widget.loginWindow',
    modal    : true,
    closable : false,
    plain    : true,
    title    : 'Login',
    resizable: false,
    width: 300,
    items: [
        {
            xtype  : 'form',
            padding: 10,
            baseCls: 'x-plain',
            items  : [
                {
                    xtype     : 'textfield',
                    fieldLabel: 'Host',
                    name      : 'host',
                    allowBlank: true
                },
                {
                    xtype     : 'textfield',
                    fieldLabel: 'Port',
                    name      : 'port',
                    allowBlank: true
                },
                {
                    xtype     : 'textfield',
                    fieldLabel: 'Username',
                    name      : 'user',
                    allowBlank: true
                },
                {
                    xtype     : 'textfield',
                    inputType : 'password',
                    fieldLabel: 'Password',
                    name      : 'password',
                    allowBlank: true
                },
                {
                    xtype       : 'combo',
                    id          : 'connections-combo',
                    store       : Ext.create('Application.store.connections'),
                    fieldLabel  : 'Connection',
                    queryMode   : 'local',
                    displayField: 'name',
                    valueField  : 'id',
                    name        : 'connectionId'
                }

            ]
        }
    ],

    buttons: [
        {
            xtype: 'button',
            text: 'Update available',
            action: 'update-available',
            hidden: true,
            icon: 'images/update-icon.png'
        },
        {
            xtype : 'button',
            text  : 'Connections',
            action: 'connections'
        },
        {
            xtype : 'button',
            text  : 'Login',
            action: 'login'
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.login.connectionsWindow', {
    extend     :'Ext.window.Window',
    title      :'Manage Connections',
    alias      :'widget.connectionsWindow',
    closeAction:'hide',
    layout     :'fit',
    modal      :true,

    items:[
        {
            xtype    :'grid',
            selModel: Ext.create('Ext.selection.RowModel', {
                mode: 'MULTI'
            }),
            minWidth :500,
            minHeight:200,
            tbar     :[
                {
                    xtype :'button',
                    text  :'Add',
                    action:'add'
                },
                {
                    xtype :'button',
                    text  :'Edit',
                    action:'edit',
                    disabled: true

                },
                {
                    xtype :'button',
                    text  :'Delete',
                    action:'delete',
                    disabled: true
                }
            ],
            store    :'connections',
            columns  :[
                {
                    menuDisabled:true,
                    dataIndex   :'name',
                    text        :'Name',
                    width       :150
                },
                {
                    menuDisabled:true,
                    dataIndex   :'host',
                    text        :'Host'
                },
                {
                    menuDisabled:true,
                    dataIndex   :'port',
                    text        :'Port'
                },
                {
                    menuDisabled:true,
                    dataIndex   :'user',
                    text        :'User'
                }
            ]
        }
    ],

    buttons:[
        {
            xtype :'button',
            text  :'Close',
            action:'close'
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.login.connectionWindow', {
    extend     : 'Ext.window.Window',
    alias      : 'widget.connectionWindow',
    title      : 'Connection',
    closeAction: 'hide',
    modal      : true,
    hidden     : true,
    resizable  : false,

    items: [
        {
            xtype  : 'form',
            padding: 10,
            baseCls: 'x-plain',
            items  : [
                {
                    xtype     : 'textfield',
                    name      : 'name',
                    fieldLabel: 'Name',
                    allowBlank: false
                },
                {
                    xtype     : 'textfield',
                    name      : 'host',
                    fieldLabel: 'Host',
                    allowBlank: true
                },
                {
                    xtype     : 'textfield',
                    name      : 'port',
                    fieldLabel: 'Port',
                    allowBlank: true
                },
                {
                    xtype     : 'textfield',
                    name      : 'user',
                    fieldLabel: 'User',
                    allowBlank: true
                },
                {
                    xtype     : 'textfield',
                    inputType : 'password',
                    name      : 'password',
                    fieldLabel: 'Password',
                    allowBlank: true
                }
            ]
        }
    ],

    buttons: [
        {
            xtype   : 'button',
            text    : 'Save',
            action  : 'save',
            formBind: true
        },
        {
            xtype : 'button',
            text  : 'cancel',
            action: 'cancel'
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.documentTree.contextMenu', {
    extend: 'Ext.menu.Menu',
    alias: 'widget.documentTreeContextMenu',
    items : [
        {
            text    : 'Quick query',
            action  : 'query',
            icon: 'images/search-icon.png'
        },
        {
            text: 'Build query',
            menu: [
                {
                    text: 'Add to query',
                    action: 'add-to-query',
                    icon: 'images/search-add-icon.png'
                },
                {
                    text : 'Remove from query',
                    action: 'remove-from-query',
                    icon: 'images/search-remove-icon.png',

                    menu: [
                        {
                            text: 'Remove key',
                            action: 'remove-key-from-query'
                        },
                        {
                            text: 'Remove key with this value',
                            action: 'remove-key-value-from-query'
                        }
                    ]
                }
            ]
        },
        {
            text   : 'Add key',
            action : 'add-key',
            icon   : 'images/add-icon.png'
        },
        {
            text   : 'Edit',
            action : 'edit',
            icon   : 'images/edit-icon.png'
        },
        {
            text : 'Remove',
            action : 'remove',
            icon: 'images/remove-icon.png'
        }
    ]

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.documentTree.editField', {
    extend     :'Ext.form.field.Trigger',
    alias      :'widget.editField',
    triggerCls :'x-form-date-trigger',
    allowBlank :false,
    hideTrigger:true,

    onTriggerClick:function () {
//        this.showCalendar();
    },

    validator:function (value) {
        try {
            value = Ext.JSON.decode(value);
            return true;
        } catch (ex) {
            return 'Wrong value';
        }
    },

    showCalendar:function () {
        this.calendar = Ext.create('Ext.picker.Date', {
            floating:true
        });

        this.showCalendar = function () {
            this.calendar.show();
        };

        this.showCalendar();
    }

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.documentTree.addKeyWindow', {
    extend     :'Ext.window.Window',
    alias      :'widget.addKeyWindow',
    closeAction:'hide',
    layout     :'fit',
    modal      :true,
    resizable  :{
        handles:'e w'
    },


    items:[
        {
            xtype  :'form',
            padding: 10,
            baseCls: 'x-plain',
            items  :[
                {
                    xtype     :'textfield',
                    name      :'key',
                    fieldLabel:'Key',
                    allowBlank:false,
                    regex     :/^[a-z0-9_\-\.]+[a-z0-9_]$/i,
                    regexText :'Invalid key name',
                    anchor: '100%'
                },
                {
                    xtype     :'editField',
                    name      :'value',
                    fieldLabel:'Value',
                    anchor: '100%'
                }
            ],
            buttons:[
                {
                    xtype   :'button',
                    text    :'Save',
                    action  :'save',
                    formBind:true
                },
                {
                    xtype  :'button',
                    text   :'cancel',
                    action :'cancel',
                    handler:function () {
                        this.up('window').close();
                    }
                }
            ]
        }
    ]

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.documentTree.editWindow', {
    extend     :'Ext.window.Window',
    alias      :'widget.editFieldWindow',
    title      :'Edit value',
    closeAction:'hide',
    layout     :'fit',
    modal      :true,
    resizable  :{
        handles:'e w'
    },

    items:[
        {
            xtype  :'form',
            padding: 10,
            baseCls: 'x-plain',
            items  :[
                {
                    xtype     :'editField',
                    name      :'value',
                    fieldLabel:'Value',
                    anchor    :'100%'
                }
            ],
            buttons:[
                {
                    xtype   :'button',
                    text    :'Save',
                    action  :'save',
                    formBind:true
                },
                {
                    xtype  :'button',
                    text   :'cancel',
                    action :'cancel',
                    handler:function () {
                        this.up('window').close();
                    }
                }
            ]
        }
    ]

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.collectionsTree.createCollectionWindow', {
    extend     :'Ext.window.Window',
    alias      :'widget.createCollectionWindow',
    title      :'Create collection',
    closeAction:'hide',
    modal      :true,
    resizable : false,

    items:[
        {
            xtype:'form',
            padding: 10,
            baseCls: 'x-plain',
            items:[
                {
                    xtype     :'textfield',
                    fieldLabel:'Name',
                    name      :'name'
                }
            ],

            buttons:[
                {
                    text    :'OK',
                    action  :'ok',
                    formBind:true
                },
                {
                    text   :'Cancel',
                    action :'cancel',
                    handler:function () {
                        this.up('window').close();
                    }
                }
            ]

        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.collectionsTree.databasesContainerContextMenu', {
    extend: 'Ext.menu.Menu',
    alias: 'widget.collectionsTreeDatabasesContainerContextMenu',
    items : [
        {
            text   : 'New database',
            action : 'new-database',
            icon   : 'images/database-add-icon.png'
        },
        {
            text: 'Import database(s)',
            action : 'import-database',
            icon: 'images/database-import-icon.png'
        },
        {
            text   : 'Refresh',
            action : 'refresh-all-databases',
            icon   : 'images/refresh-icon.png'
        }
    ]

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.collectionsTree.createDatabaseWindow', {
    extend     :'Ext.window.Window',
    alias      :'widget.createDatabaseWindow',
    title      :'Create database',
    closeAction:'hide',
    modal      :true,
    resizable : false,

    items:[
        {
            xtype:'form',
            padding: 10,
            baseCls: 'x-plain',
            items:[
                {
                    xtype     :'textfield',
                    fieldLabel:'Name',
                    name      :'name'
                }
            ],

            buttons:[
                {
                    text  :'OK',
                    action:'ok',
                    formBind: true
                },
                {
                    text   :'Cancel',
                    action :'cancel',
                    handler:function () {
                        this.up('window').close();
                    }
                }
            ]
        }
    ]

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.collectionsTree.smartItemContextMenu', {
    extend: 'Ext.menu.Menu',
    alias: 'widget.collectionsTreeSmartItemContextMenu',

    items: [
        {
            text: 'Snaphshots',
            action : 'snapshots',
            icon: 'images/database-icon-snapshot.png',
            menu : {
                items: [
                    {
                        text: 'Take snapshot',
                        icon: 'images/take-snapshot.png',
                        action: 'take-snapshot'
                    },
                    {
                        text: 'Revert to snapshot',
                        icon : 'images/revert-to-snapshot.png',
                        action: 'revert-to-snapshot',
                        disabled: true
                    }
                ]
            }

        },
        {
            text   : 'New database',
            action : 'new-database',
            icon   : 'images/database-add-icon.png'
        },
        {
            text    : 'Drop database(s)',
            action  : 'drop-database',
            icon: 'images/database-remove-icon.png'
        },
        {
            text : 'Export',
            action: 'export',
            icon : 'images/database-export-icon.png'
        },

        {
            text   : 'New collection',
            action : 'new-collection',
            icon   : 'images/table-add-icon.png'
        },
        {
            text   : 'Refresh',
            action : 'refresh-databases',
            icon   : 'images/refresh-icon.png'
        },

        {
            text    : 'Clear collection(s)',
            action  : 'clear-collection',
            icon: 'images/table-delete-icon.png'
        },
        {
            text    : 'Drop collection(s)',
            action  : 'drop-collection',
            icon: 'images/table-delete-icon.png'
        },
        {
            text   : 'Refresh',
            action : 'refresh-collections',
            icon   : 'images/refresh-icon.png'
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.collectionGrid.contextMenu', {
    extend: 'Ext.menu.Menu',
    alias: 'widget.collectionGridContextMenu',
    items : [
        {
            text: 'New Document',
            action: 'new-document',
            icon   : 'images/new-document-icon.png'
        },
        {
            text: 'Update Document',
            action: 'update-document',
            icon: 'images/edit-icon.png'
        },
        {
            text    : 'Remove selected',
            action  : 'remove',
            icon: 'images/remove-icon.png'
        }
    ]

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.collectionGrid.editDocumentWindow', {
    extend: 'Ext.window.Window',
    alias: 'widget.editDocumentWindow',
    title: 'Edit document',
    closeAction: 'hide',
    layout: 'fit',
    modal: true,
    width: 500,
    height: 400,
    items: [{
        xtype: 'form',
        baseCls: 'x-plain',
        layout: 'fit',
        items: [{
            xtype: 'jsEditor',
            name: 'data',
            anchor: '100%',
            grow: true
        }],

        buttons: [
            {
                text: 'OK',
                action: 'ok',
                formBind: true
            },
            {
                text: 'Cancel',
                action: 'cancel',
                handler: function() {
                    this.up('window').close();
                }
            }
        ]
    }]
});
/**
 * @author victor
 * @date 10/27/14.
 */
Ext.define('Application.view.collectionGrid.jsEditor', {
    extend: 'Ext.form.field.Base',

    alias: 'widget.jsEditor',

    fieldSubTpl : '',

    editor: null,

    value: "",

    validity: true,

    listeners: {
        render: function(jsEditor) {
            var id = jsEditor.bodyEl.id;

            //var langTools = ace.require("ace/ext/language_tools");
            var aceEditor = ace.edit(id);
            jsEditor.editor = aceEditor;
            aceEditor.setTheme("ace/theme/chrome");
            aceEditor.getSession().setMode("ace/mode/json");
            //editor.setOptions({enableBasicAutocompletion: true});

            aceEditor.setValue(this.value, -1);

            aceEditor.getSession().on("changeAnnotation", function() {

                var annotations = aceEditor.getSession().getAnnotations();
                var initialValidity = jsEditor.validity;
                jsEditor.validity = true;
                for(var i=0; i<annotations.length; i++) {
                    if (annotations[i].type == 'error') {
                        jsEditor.validity = false;
                        break;
                    }
                }

                if (initialValidity != jsEditor.validity) {
                    jsEditor.fireEvent('validitychange', jsEditor, jsEditor.validity);
                }
            });

        }
    },

    setValue: function(value) {

        this.value = value;

        if (this.editor) {
            this.editor.setValue(value, -1);
        }
    },

    getValue: function() {

        if (this.editor) {
            this.value = this.editor.getValue();
        }
        return this.value;
    },

    isValid: function() {
        return this.validity;
    }
});/**
 * @author <volaru@bitdefender.com>
 */
Ext.define('Application.view.main.updateWindow', {
    extend      : 'Ext.window.Window',
    alias       : 'widget.updateWindow',
    modal       : true,
    title       : 'Update',
    width       : 300,
    height      : 200,
    closeAction : 'hide',

    items: [
        {
            xtype: 'displayfield',
            fieldLabel: 'Current Version',
            name: 'currentVersion',
            value: 'N/A'
        },
        {
            xtype      : 'displayfield',
            fieldLabel : 'New Version',
            name       : 'newVersion',
            value      : 'N/A'
        }
    ],
    buttons: [
        {
            xtype   : 'button',
            action  : 'check',
            text    : 'Check new version'
        },
        {
            xtype  : 'button',
            action : 'update',
            text   : 'Update',
            disabled: true
        },
        {
            xtype   : 'button',
            action  : 'close',
            text    : 'Close',
            handler : function () {
                this.up('window').close();
            }
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.main.importDatabaseWindow', {
    extend     :'Ext.window.Window',
    alias      :'widget.importDatabaseWindow',
    closeAction:'hide',
    modal      :true,
    layout: 'border',
    title      :'Import database',
    width: 300,
    height: 100,

    items  :[
        {
            xtype: 'form',
            region: 'center',
            items: [
                {
                    xtype: 'fileuploadfield',
                    name: 'file',
                    fieldLabel: 'Upload file',
                    buttonText: 'Select file'
                }
            ]
        }
    ],
    buttons:[
        {
            xtype :'button',
            action:'import',
            text  :'Import'
        },
        {
            xtype  :'button',
            action :'cancel',
            text   :'Cancel',
            handler:function () {
                this.up('window').close();
            }
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.main.documentTree', {
    extend     :'Ext.tree.Panel',
    store: 'Application.store.document',
    alias      :'widget.documentTree',
    title      :'Document',
    collapsible:true,
    flex       :1,
    rootVisible:false,
    useArrows  :true,
    hideHeaders: true,

    columns:[
        {
            xtype    :'treecolumn',
            text     :'Property',
            dataIndex:'property',
            menuDisabled: true,
            flex: 1,
            sortable: false,
            renderer: function(value){
                return value;
            }
        },
        {
            text     :'Value',
            dataIndex:'value',
            menuDisabled:true,
            flex: 5,
            sortable : false,
            renderer: function(value) {
                value = JsonSyntaxColor.colorValue(value);
                return value;
            }
        }
    ],

    loadDocument:function (document) {
        this.document = document;
        this.setRootNode({
            expanded:true,
            children:this.convertDocument(document)
        });
    },

    initComponent: function() {
        this.callParent(arguments);
        this.contextMenu = Ext.create('Application.view.documentTree.contextMenu', {
            floating : true, // usually you want this set to True (default)
            renderTo : Ext.getBody() // usually rendered by it's containing component

        });
    },

    convertDocument:function (document, flattenProperty) {
        var result = [];

        if (flattenProperty == undefined) {
            flattenProperty = '';
        }

        if (Ext.isObject(document) || Ext.isArray(document)) {
            for (var property in document) {
                var value = document[property];

                var typeofValue = 'scalar';

                var isLeaf;

                if (typeof value == 'string' || typeof value == 'number' || (Ext.isObject(value) && (value.$id !== undefined || value.$date !== undefined))) {
                    if (typeof value == 'string') {
                        value = Ext.String.htmlEncode(document[property]);
                    }

                    isLeaf = true;
                } else if (value === null) {
                    value = null;
                    isLeaf = true;
                } else if (typeof value == 'boolean') {
                    //value = value ? 'true' : 'false';
                    isLeaf = true;
                } else if (Ext.isObject(value) || Ext.isArray(value)) {

                    if (Ext.isObject(value)) {
                        typeofValue = 'object';
                        value = '{';
                    } else if (Ext.isArray(value)) {
                        typeofValue = 'array';
                        value = '[';
                    }

                    isLeaf = false;

                }

                var thisFlattenProperty = flattenProperty !== '' ? flattenProperty + '.' + property : property;

                var node = {
                    property:property,
                    flattenProperty: thisFlattenProperty ,
                    value: typeofValue == 'scalar'? Ext.JSON.encode(value): value,
                    originalValue: value,
                    json: Ext.JSON.encode(document[property]),
                    type: typeofValue,
                    leaf: isLeaf,
                    icon: 'images/blank.png',
                    iconCls : 'bracket-open'
                };

                if (!isLeaf) {

                    var collapsed = Ext.util.Cookies.get('collapsed');
                    if (collapsed) {
                        collapsed = Ext.JSON.decode(collapsed);
                        node.expanded = collapsed[thisFlattenProperty]?false:true;
                    } else {
                        node.expanded = true;
                    }

                    node.children = this.convertDocument(document[property], thisFlattenProperty);
                }

                if (!isLeaf && !node.expanded) {
                    node.value = Ext.JSON.encode(document[property]);
                }

                result.push(node);

                if (!isLeaf) {

                    var next;

                    switch (typeofValue) {
                        case 'object':
                            next = {
                                icon: 'images/blank.png',
                                property: '}',
                                leaf: true,
                                iconCls : 'bracket-open'
                            };

                            if (node.expanded) {
                                result.push(next);
                            } else {
                                node.next = next;
                            }

                            break;
                        case 'array':

                            next= {
                                icon: 'images/blank.png',
                                property : ']',
                                leaf: true,
                                iconCls : 'bracket-open'
                            };

                            if (node.expanded) {
                                result.push(next);
                            } else {
                                node.next = next;
                            }
                            break;
                    }
                }

            }
        }

        return result;
    }
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.main.exportDatabaseTree', {
    extend      :'Ext.tree.Panel',
    alias       :'widget.exportDatabaseTree',
    rootVisible :false,
    displayField:'name',
    useArrows   :true,
    title: 'Select database/collections',

    initComponent: function() {

        this.store = Ext.create('Application.store.collections');

        this.callParent(arguments);

    }

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.main.collectionsTree', {
    extend      :'Ext.tree.Panel',
    alias       :'widget.collectionsTree',
    rootVisible :false,
    displayField:'name',
    useArrows   :true,
    selModel:{
      mode:"MULTI"
    },


    title:'Databases and collections',
    store:'Application.store.collections',

    initComponent: function() {
        this.callParent(arguments);

        this.databasesContainerContextMenu = Ext.create('Application.view.collectionsTree.databasesContainerContextMenu', {
            floating : true, // usually you want this set to True (default)
            renderTo : Ext.getBody() // usually rendered by it's containing component
        });

        this.smartItemContextMenu = Ext.create('Application.view.collectionsTree.smartItemContextMenu', {
            floating : true, // usually you want this set to True (default)
            renderTo : Ext.getBody() // usually rendered by it's containing component
        });
    }

    , plugins: [{
        ptype: 'treefilter'
        , allowParentFolders: true
    }]

    , dockedItems: [{
        xtype: 'toolbar'
        , dock: 'top'
        , items: [{
            xtype: 'trigger'
            , width: '100%'
            , triggerCls: 'x-form-clear-trigger'
            , onTriggerClick: function () {
                this.reset();
                this.focus();
            }
            , listeners: {
                change: function (field, newVal) {
                    var tree = field.up('treepanel');
                    tree.filter(newVal, 'name');
                }
                , buffer: 250
            }
        }]
    }]

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.main.dropCollectionsTree', {
    extend      :'Ext.tree.Panel',
    alias       :'widget.dropCollectionsTree',
    rootVisible :false,
    displayField:'name',
    useArrows   :true,

    initComponent: function() {

        this.store = Ext.create('Application.store.collections');

        this.callParent(arguments);

    }

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.main.panel', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.mainPanel',
//    title: 'Mongo Web Admin',
    layout: {
        type: 'border'
    },


    tbar: [
        {
            action: 'databases',
            text: 'Databases',
            icon: 'images/database-icon.png',
            menu: [
                {
                    text: 'Add Database',
                    action : 'new-database',
                    icon: 'images/database-add-icon.png'
                },
                {
                    text: 'Drop database(s)',
                    action : 'drop-databases',
                    icon: 'images/database-remove-icon.png'
                },
                {
                    text: 'Import database(s)',
                    action : 'import-database',
                    icon: 'images/database-import-icon.png'
                },
                {
                    text: 'Export database(s)',
                    action : 'export-database',
                    icon: 'images/database-export-icon.png'
                }

            ]
        },
        {
            action: 'collections',
            text: 'Collections',
            icon: 'images/table-icon.png',
            menu: [
                {
                    text: 'Add collection',
                    action: 'new-collection',
                    icon: 'images/table-add-icon.png'
                },
                {
                    text: 'Drop collections',
                    action: 'drop-collections',
                    icon: 'images/table-delete-icon.png'
                }
            ]
        },
        {
            xtype: 'button',
            action: 'help',
            text: 'Help',
            icon: 'images/help-icon.png',
            menu: [
                {
                    text: 'Update',
                    action: 'update',
                    icon: 'images/update-icon.png'
                },
                {
                    action : 'about',
                    text   : 'About',
                    icon   : 'images/info-icon.png'
                }
            ]
        },
        '->',
        {
            xtype :'button',
            action:'logout',
            text  :'Logout',
            icon: 'images/logout-icon.png'
        }
    ],

    items: [
        {
            xtype: 'collectionsTree',
            split: true,
            width: 300,
            minWidth: 300,
            region: 'west'
        },
        {
            xtype: 'panel',
            layout:{
                type:'border'
            },
            items: [
                {
                    xtype:'collectionGrid',
                    region: 'center'
                },
                {
                    xtype: 'documentTree',
                    region: 'south',
                    split: true
                }
            ],
            region: 'center',
            minWidth: 500
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.main.collectionGrid', {
    alias      :'widget.collectionGrid',
    extend     :'Ext.grid.Panel',
    hidden     :true,
    minHeight: 200,
    enableTextSelection: true,

    initComponent: function() {
        //this.plugins = [Ext.create('Ext.grid.plugin.CellEditing')];
        this.store = Ext.create('Application.store.collection');
//        this.selModel = Ext.create('Ext.selection.CheckboxModel');
        this.selModel = {
            mode: 'MULTI'
        };

        this.tbar = [
            {
                xtype     :'searchfield',
                fieldLabel:'Query',
                labelWidth:50,
                width     :'100%',
                store     :this.store
            }
        ];

        this.bbar = [
            {
                xtype       : 'pagingToolbar',
                width       : '100%',
                store       : this.store,
                displayInfo : true,
                items      :[
                    {
                        xtype:'itemsPerPageCombo'
                    }
                ]
            }
        ];

        this.contextMenu = Ext.create('Application.view.collectionGrid.contextMenu', {
            floating : true, // usually you want this set to True (default)
            renderTo : Ext.getBody() // usually rendered by it's containing component

        });

        this.store.addListener('beforeload', this.beforeLoadStore, this);
        this.store.addListener('load', this.afterLoadStore, this);
        this.store.addListener('write', this.storeWrite, this);

        this.callParent(arguments);
    },

    columns    :[
        {
            name        :'_id',
            text        :'_id',
            dataIndex   :'_id',
            menuDisabled:true
        }
    ],

    storeWrite: function(store, operation){
        switch (operation.action) {
            case 'create':
            case 'update':
                this.refreshGrid();
                break;
        }
    },

    beforeLoadStore: function(store) {
        var selected = this.getSelectionModel().getSelection();
        store.preselected = selected;
    },

    afterLoadStore: function(store) {
        var preselected = store.preselected;

        if (preselected.length!=1) {
            return;
        }

        var preselect = store.findRecord('_id', preselected[0].get('_id'));

        if (preselect) {
            this.getSelectionModel().select(preselect);
        }

    },

    refreshGrid: function(collectionName) {

        var collectionStore = this.getStore();
        var selected = this.getSelectionModel().getSelection();
        var collectionGrid = this;
        var preselected;

        if (collectionName) {
            this.collectionName = collectionName;
        }

        preselected = selected.length==1 && !collectionName;

        var params = {
            page   : 1,
            limit  : collectionStore.pageSize,
            sort   : null,
            dir    : null,
            filter : []
        };

        Ext.apply(params, collectionStore.getProxy().extraParams);

        Remote.Collection.getHeader(params, {
            scope   : this,
            success : function (response) {

                var fields = [];

                for (var i in response) {

                    var column = {
                        text         : response[i],
                        dataIndex    : 'data.' + response[i],
                        menuDisabled : true,
                        scope        : {
                            fieldName : response[i]
                        },
                        editor       : 'textfield',
                        renderer     : function (value, meta, record) {
                            return JsonSyntaxColor.colorValue(JSON.stringify(record.get('data')[this.fieldName]));
                        }
                    };

                    if (response[i] == '_id') {
                        column.width = 200;
                    }

                    fields.push(column);
                }

                this.reconfigure(collectionStore, fields);
                this.show();
                this.setTitle(this.collectionName);

                if (preselected) {
                    collectionStore.load();
                } else {
                    collectionGrid.down('searchfield').clearFilter();
                    collectionGrid.down('pagingToolbar').moveFirst();
                }


            }
        });
    }
});
/**
 * @author <victor.olaru@gmail.com>
 */
Ext.define('Application.view.main.dropCollectionsWindow', {
    extend      : 'Ext.window.Window',
    alias       : 'widget.dropCollectionsWindow',
    closeAction : 'hide',
    layout      : 'fit',
    modal       : true,
    title: 'Drop collections',
    minWidth: 300,
    width: 300,
    height: 200,

    items: [
        {
            xtype: 'dropCollectionsTree',
            forceFit : true
        }
    ],

    buttons: [
        {
            xtype: 'button',
            text: 'Drop',
            action: 'drop-collections'
        },
        {
            xtype: 'button',
            text: 'Cancel',
            handler: function() {
                this.up('window').close();
            }
        }
    ]
});
/**
 * @author <victor.olaru@gmail.com>
 */
Ext.define('Application.view.main.dropDatabasesWindow', {
    extend      : 'Ext.window.Window',
    alias       : 'widget.dropDatabasesWindow',
    closeAction : 'hide',
    layout      : 'fit',
    modal       : true,
    title: 'Drop databases',
    minWidth: 300,

    items: [
        {
            xtype: 'gridpanel',
            forceFit : true,
            hideHeaders : true,
            columns: [
                {
                    name: 'name',
                    dataIndex: 'name'
                }
            ],

            store: 'Application.store.databases',

            selModel : Ext.create('Ext.selection.CheckboxModel', {headerWidth: 10})

        }
    ],

    buttons: [
        {
            xtype: 'button',
            text: 'Drop',
            action: 'drop-databases'
        },
        {
            xtype: 'button',
            text: 'Cancel',
            handler: function() {
                this.up('window').close();
            }
        }
    ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.view.main.exportDatabaseWindow', {
    extend     :'Ext.window.Window',
    alias      :'widget.exportDatabaseWindow',
    closeAction:'hide',
    modal      :true,
    layout: 'border',
    title      :'Export database',
    minWidth   :300,
    width      :500,
    height     :400,

            items  :[

                {
                    xtype     :'exportDatabaseTree',
                    region: 'center'

                },
                {
                    xtype: 'form',
                    region: 'south',
                    items: [
                        {
                            xtype: 'checkboxfield',
                            name: 'export-all',
                            fieldLabel: 'Export All'
                        }
                    ]
                }
            ],
            buttons:[
                {
                    xtype :'button',
                    action:'export',
                    text  :'Export'
                },
                {
                    xtype  :'button',
                    action :'cancel',
                    text   :'Cancel',
                    handler:function () {
                        this.up('window').close();
                    }
                }
            ]
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.controller.login', {
    extend:'Ext.app.Controller',

    views:[
        'Application.view.login.window',
        'Application.view.login.connectionsWindow',
        'Application.view.login.connectionWindow'
    ],

    init:function () {
        this.control({
            'loginWindow' : {
                render: this.checkAvailableUpdate
            },

            'loginWindow button[action=update-available]' : {
                click: this.updateAvailable
            },

            'loginWindow form': {
                render: this.loginSubmit
            },

            'loginWindow button[action=login]':{
                click:this.login
            },

            'loginWindow button[action=connections]':{
                click:this.showConnectionsWindow
            },

            '#connections-combo':{
                render:this.renderConnectionsCombo,
                change: this.changeCurrentConnection
            },

            'connectionsWindow button[action=close]':{
                click:this.closeConnectionsWindow
            },

            'connectionsWindow button[action=add]':{
                click:this.addConnection
            },

            'connectionsWindow button[action=edit]':{
                click:this.editConnection
            },

            'connectionsWindow button[action=delete]':{
                click:this.deleteConnection
            },

            'connectionWindow button[action=save]':{
                click:this.saveConnection
            },

            'connectionWindow form' : {
                render: this.enterKeySubmit
            },

            'connectionWindow button[action=cancel]':{
                click:this.closeConnectionWindow
            }
        });
    },

    checkAvailableUpdate: function(loginWindow) {
        Remote.Update.checkLatest({},{
            success: function(result){

                if (result.current.stage != result.new.stage || result.current.currentVersion < result.new.currentVersion) {
                    loginWindow.down('[action=update-available]').show();
                }

            }
        });
    },

    updateAvailable: function() {
        this.application.getController('main').showUpdateWindow();
    },

    login:function () {
        var loginWindow = Ext.ComponentQuery.query('loginWindow')[0];
        var values = loginWindow.down('form').getValues();

        Remote.Authentication.authenticate(values, {
            scope: this,
            success: function() {
                init();
            },
            failure: function() {
                Ext.Msg.show({
                    title  :'Error',
                    msg    : 'Authentication failure',
                    icon   :Ext.Msg.ERROR,
                    buttons:Ext.Msg.OK,
                    fn     :function () {
                        this.close();
                    }
                });
            }
        });
    },

    enterKeySubmit: function() {
        var form = this.connectionWindow.down('form');

        form.keyNav = Ext.create('Ext.util.KeyNav', form.el, {
            enter:this.saveConnection,
            scope:this
        });
    },

    loginSubmit:function () {
        var loginWindow = Ext.ComponentQuery.query('loginWindow')[0];
        var form = loginWindow.down('form');

        form.keyNav = Ext.create('Ext.util.KeyNav', form.el, {
            enter:this.login,
            scope:this
        });
    },

    changeCurrentConnection: function(combo, newValue, oldValue) {
        var loginWindow = Ext.ComponentQuery.query('loginWindow')[0];
        var form = loginWindow.down('form');
        var store = combo.getStore();
        var connection = store.findRecord('id', newValue);

        form.loadRecord(connection);
    },

    addConnection:function () {
        var record = Ext.create('Application.model.connection');
        record.data.id = record.internalId;

        this.showConnectionWindow(record);
    },

    deleteConnection: function() {
        var msg = 'Are you sure you want to delete this connection?';
        var grid = this.connectionsWindow.down('grid');
        var selected = grid.getSelectionModel().getSelection();

        if (selected.length>1) {
            msg = 'Are you sure you want to delete these connections?';
        }

        Ext.Msg.show({
            title: 'Delete confirmation',
            msg: msg,
            icon: Ext.Msg.QUESTION,
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId) {
                if (buttonId == 'yes') {
                    grid.getStore().remove(selected);
                    grid.getStore().sync();
                } else {
                    this.close();
                }
            }
        });
    },

    editConnection:function () {
        var grid = this.connectionsWindow.down('grid');
        var selected = grid.getSelectionModel().getSelection()[0];
        this.showConnectionWindow(selected);
    },

    saveConnection:function () {
        var form = this.connectionWindow.down('form');
        var gridStore = this.connectionsWindow.down('grid').getStore();
        var record = form.getRecord();
        var values = form.getValues();

        record.set(values);

        if (record.phantom) {
            gridStore.add(record);
        }

        gridStore.sync();

        this.connectionWindow.close();
    },

    showConnectionWindow:function (record) {
        this.connectionWindow = Ext.create('Application.view.login.connectionWindow');

        this.showConnectionWindow = function (record) {
            var form = this.connectionWindow.down('form');

            form.loadRecord(record);

            this.connectionWindow.show();
        };

        this.showConnectionWindow(record);
    },

    connectionsProxyException:function (proxy, response, operation) {
        switch (operation.action) {
            case 'create':
                var store = this.connectionsWindow.down('grid').getStore();
                store.remove(operation.records);
                store.removed = [];
                break;
        }

        Ext.Msg.show({
            title  :'Error',
            msg    :response.message,
            icon   :Ext.Msg.ERROR,
            buttons:Ext.Msg.OK,
            fn     :function () {
                this.close();
            }
        });
    },

    connectionsSelectionChange: function(model, selected) {
        var grid = this.connectionsWindow.down('grid');
        var deleteButton = this.connectionsWindow.down('button[action=delete]');
        var editButton = this.connectionsWindow.down('button[action=edit]');

        if (selected.length>0) {
            deleteButton.enable();
        } else {
            deleteButton.disable();
        }

        if (selected.length == 1) {
            editButton.enable();
        } else {
            editButton.disable();
        }
    },

    showConnectionsWindow:function () {
        this.connectionsWindow = Ext.create('Application.view.login.connectionsWindow');

        this.connectionsWindow.down('grid').getStore().getProxy().addListener('exception', this.connectionsProxyException, this);
        this.connectionsWindow.down('grid').getSelectionModel().addListener('selectionchange', this.connectionsSelectionChange, this);

        this.showConnectionsWindow = function () {
            this.connectionsWindow.show();
        };

        this.showConnectionsWindow();
    },

    closeConnectionsWindow:function () {
        this.connectionsWindow.close();
    },

    closeConnectionWindow:function () {
        this.connectionWindow.close();
    },

    renderConnectionsCombo:function (combo) {

        combo.getStore().load({
            scope   :combo,
            callback:function (records) {
                // set default value to the first (default) option
                this.setValue(records[0].get('id'));
            }
        });
    }
});
/**
 * @author <volaru@bitdefender.com>
 */
Ext.define('Application.controller.update', {
    extend : 'Ext.app.Controller',
    views: [
        'Application.view.main.updateWindow'
    ],

    init: function(){
        this.control({
            'updateWindow [action=check]' : {
                click: this.checkNewVersion
            },

            'updateWindow' : {
                show: this.onUpdateWindowShow
            },

            'updateWindow [action=update]' : {
                click: this.updateToLatest
            }
        });
    },

    onUpdateWindowShow: function() {
        this.checkNewVersion();
    },

    updateToLatest: function() {
        var viewport = Ext.ComponentQuery.query('viewport')[0];

        viewport.setLoading('Updating, please wait...');

        Remote.Update.updateToLatest({}, {
            success: function(result){
                if (result.success) {
                    Ext.Msg.show({
                        title: 'Update completed',
                        icon : Ext.Msg.INFO,
                        msg: 'Update completed, press OK to reload',
                        buttons: Ext.Msg.OK,
                        fn: function() {
                            window.location.reload();
                        }
                    });
                }
            },

            failure: function(result, exception) {

                var msg = '';

                switch (exception.exceptionType) {
                    case "Application\\Exceptions\\InsufficientRightsException":
                        msg = 'Could not complete update because the user does not have the right to write into the Mongo Admin directory';
                        break;

                    default:

                        msg = "Unknown server error";
                }

                Ext.Msg.show({
                    title   : 'Update failed',
                    icon    : Ext.Msg.ERROR,
                    msg     : msg,
                    buttons : Ext.Msg.OK,
                    fn      : function () {
                        viewport.setLoading(false);
                    }
                });
            }
        });
    },

    checkNewVersion: function() {
        var updateWindow = Ext.ComponentQuery.query('updateWindow')[0],
            updateButton = updateWindow.down('[action=update]'),
            checkButton = updateWindow.down('[action=check]'),
            currentVersion = updateWindow.down('[name=currentVersion]'),
            newVersion = updateWindow.down('[name=newVersion]');

        updateWindow.setLoading('Checking for new version');
        updateButton.setDisabled(true);
        checkButton.setDisabled(true);
        newVersion.setValue('N/A');

        Remote.Update.checkLatest({}, {
            success: function(version){
                var updateAvailable = false;

                if (!version || !version['new'] || !version['current']) {
                    updateAvailable = false;
                } else {
                    if (version['new'].currentVersion != version['current'].currentVersion || version['new'].stage != version['current'].stage) {
                        updateAvailable = true;
                    }

                    if (updateAvailable) {
                        newVersion.setValue(version['new'].currentVersion + (version['new'].stage != 'release' ? version['new'].stage : ''));
                    }

                }

                if (version && version['current']) {
                    currentVersion.setValue(version['current'].currentVersion + (version['current'].stage != 'release' ? version['current'].stage : ''));
                }

                updateButton.setDisabled(!updateAvailable);
                checkButton.setDisabled(false);
                updateWindow.setLoading(false);
            }
        });
    }
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.controller.documentTree', {
    extend : 'Ext.app.Controller',

    views : [
        'Application.view.main.documentTree',
        'Application.view.documentTree.contextMenu',
        'Application.view.documentTree.editField'
    ],

    init: function() {
        this.control({
            'documentTree' : {
                'itemexpand' : this.itemExpand,
                'itemcollapse': this.itemCollapse,
                'itemcontextmenu': this.itemContextMenu,
                'itemdblclick' : this.itemDblClick
            },

            'documentTreeContextMenu [action=query]': {
                click: this.contextMenuQuery
            },

            'documentTreeContextMenu [action=add-to-query]' : {
                click : this.contextMenuQuery
            },

            'documentTreeContextMenu [action=remove-key-from-query]' : {
                click : this.contextMenuQuery
            },

            'documentTreeContextMenu [action=remove-key-value-from-query]' : {
                click : this.contextMenuQuery
            },

            'documentTreeContextMenu [action=edit]' : {
                click : this.contextMenuEdit
            },

            'documentTreeContextMenu [action=add-key]' : {
                click: this.contextMenuAddKey
            },

            'documentTreeContextMenu [action=remove]' : {
                click: this.contextMenuRemove
            }

        });
    },

    itemDblClick: function(tree, node) {

        if (!node.isLeaf()) {
            return;
        }

        var fieldRecord = Ext.create('Application.model.field', {
            value: node.get('type') == 'scalar' ? node.get('value') : '',
            key: node.get('flattenProperty')
        });
        this.showEditWindow(fieldRecord);
    },

    itemExpand: function(node) {

        var collapsed = Ext.util.Cookies.get('collapsed');

        if (collapsed !== null) {
            collapsed = Ext.JSON.decode(collapsed);
            delete collapsed[node.get('flattenProperty')];

            Ext.util.Cookies.set('collapsed', Ext.JSON.encode(collapsed));
        }

        var value;
        var property;

        switch (node.get('type')) {
            case 'array':
                value = '[';
                property = ']';
                break;

            case 'object':
                value = '{';
                property = '}';
                break;

        }


        node.set('value', value);
        node.commit();

        var next = node.nextSibling;

        var closingBracket  = {
            icon: 'images/blank.png',
            property: property,
            leaf: true,
            iconCls : 'bracket-open'
        };

        node.parentNode.insertBefore(closingBracket, next);

    },

    itemCollapse: function(node) {

        var collapsed = Ext.util.Cookies.get('collapsed');

        if (collapsed===null) {
            collapsed = {};
        } else {
            collapsed = Ext.JSON.decode(collapsed);
        }

        collapsed[node.get('flattenProperty')] = true;

        Ext.util.Cookies.set('collapsed', Ext.JSON.encode(collapsed));

        var next = node.nextSibling;

        next.remove();

        node.set('value', node.get('json'));
        node.commit();

    },

    query: {},

    contextMenuQuery: function(button) {
        var action = button.action;
        var keys, key, i;
        var selectedNode = button.up('documentTreeContextMenu').selectedRecord;

        if (selectedNode.get('type') == 'scalar') {
            var query = {};

            if (selectedNode.get('property') == '$id') {
                query[selectedNode.parentNode.get('flattenProperty')] = {};
                query[selectedNode.parentNode.get('flattenProperty')]['$id'] = Ext.JSON.decode(selectedNode.get('value'));
            } else {
                query[selectedNode.get('flattenProperty')] = Ext.JSON.decode(selectedNode.get('value'));
            }

            var searchField = Ext.ComponentQuery.query('searchfield')[0];

            switch (action) {
                case 'query':
                    this.query = query;
                    break;

                case 'add-to-query':
                    Ext.apply(this.query, query);
                    break;

                case 'remove-key-from-query':
                    key = Ext.Object.getKeys(query);

                    for (i in this.query) {
                        if (key == i) {
                            this.query[i] = undefined;
                            delete this.query[i];
                        }
                    }
                    break;

                case 'remove-key-value-from-query':
                    key = Ext.Object.getKeys(query)[0];
                    for (i in this.query) {
                        if (key == i && this.isEqual(this.query[i],query[key])) {
                            this.query[i] = undefined;
                            delete this.query[i];
                        }
                    }
                    break;

            }

            searchField.setValue(Ext.JSON.encode(this.query));
            searchField.onTrigger2Click();
        }
    },

    isEqual : function(a, b) {
        var i,j;
        if (typeof a == 'object' && typeof b == 'object') {
            var keysInA = Ext.Object.getKeys(a).length;
            var keysInB = Ext.Object.getKeys(b).length;

            if (keysInA != keysInB) {
                return false;
            }

            var matchedKeysInB = 0;

            for(i in a) {

                for(j in b) {
                    if (i==j) {
                        matchedKeysInB++;
                        if (!this.isEqual(a[i], b[j])) {
                            return false;
                        }
                    }
                }
            }

            return matchedKeysInB == keysInA;
        }

        return a === b;
    },

    contextMenuEdit: function(button) {
        var selectedNode = button.up('documentTreeContextMenu').selectedRecord;
        var fieldRecord = Ext.create('Application.model.field', {
            value: selectedNode.get('type') == 'scalar' ? selectedNode.get('value') : '',
            key: selectedNode.get('flattenProperty')
        });
        this.showEditWindow(fieldRecord);
    },

    contextMenuAddKey: function(button) {
        var selectedNode = button.up('documentTreeContextMenu').selectedRecord;
        var key = selectedNode.get('flattenProperty');
        key = key.substr(0, key.lastIndexOf('.'));

        if (key.length) {
            key += '.';
        }

        var fieldRecord = Ext.create('Application.model.field', {key: key});
        this.showAddKeyWindow(fieldRecord);
    },

    contextMenuRemove: function(button) {


        Ext.Msg.confirm('Confirmation', 'Are you sure you want to remove this field?', function(buttonId){

            if (buttonId != 'yes') {
                return;
            }

            var selectedNode = button.up('documentTreeContextMenu').selectedRecord;

            var collectionGrid = Ext.ComponentQuery.query('collectionGrid')[0];
            var collectionProxy = collectionGrid.getStore().getProxy();
            var database = collectionProxy.extraParams.database;
            var collection = collectionProxy.extraParams.collection;

            var documentTree = Ext.ComponentQuery.query('documentTree')[0];
            var document = documentTree.document;

            var key = selectedNode.get('flattenProperty');

            Remote.Collection.removeField({
                database: database,
                collection: collection,
                id: document._id.$id,
                field: key
            }, {
                scope: this,
                success: function(){
                    collectionGrid.refreshGrid();
                }
            });



        }, this);
    },

    showAddKeyWindow: function(fieldRecord) {
        this.addKeyWindow = Ext.create('Application.view.documentTree.addKeyWindow');
        this.addKeyWindow.addListener('afterrender', function(addKeyWindow){
            var saveButton = addKeyWindow.down('button[action=save]');
            saveButton.addListener('click', this.saveValue);
            var form = addKeyWindow.down('form');

            form.keyNav = Ext.create('Ext.util.KeyNav', form.el, {
                enter:function(){
                    this.saveValue(saveButton);
                },
                scope:this
            });

        }, this);


        this.showAddKeyWindow = function(fieldRecord) {
            this.addKeyWindow.down('form').loadRecord(fieldRecord);
            this.addKeyWindow.title = 'Add key';
            this.addKeyWindow.show();
        };

        this.showAddKeyWindow(fieldRecord);
    },

    showEditWindow: function(fieldRecord) {
        this.editWindow = Ext.create('Application.view.documentTree.editWindow');
        this.editWindow.addListener('afterrender', function(editWindow){
            var saveButton = editWindow.down('button[action=save]');
            saveButton.addListener('click', this.saveValue);
            var form = editWindow.down('form');

            form.keyNav = Ext.create('Ext.util.KeyNav', form.el, {
                enter:function(){
                    this.saveValue(saveButton);
                },
                scope:this
            });

        }, this);


        this.showEditWindow = function(fieldRecord) {
            this.editWindow.down('form').loadRecord(fieldRecord);
            this.editWindow.setTitle('Edit ' + fieldRecord.get('key'));
            this.editWindow.show();
        };

        this.showEditWindow(fieldRecord);
    },

    itemContextMenu: function (view, record, item, index, event) {
        var tree = Ext.ComponentQuery.query('documentTree')[0];
        tree.contextMenu.showAt(event.getXY());
        tree.contextMenu.selectedRecord = record;
        event.stopEvent();
    },

    saveValue: function(button) {
        var window = button.up('window');
        var form = window.down('form');
        var values = form.getValues();
        var record = form.getRecord();

        record.set(values);

        var value = record.get('value');
        var key = record.get('key');
        var collectionGrid = Ext.ComponentQuery.query('collectionGrid')[0];
        var documentTree = Ext.ComponentQuery.query('documentTree')[0];
        var document = documentTree.document;
        var collectionProxy = collectionGrid.getStore().getProxy();
        var database = collectionProxy.extraParams.database;
        var collection = collectionProxy.extraParams.collection;

        try {

            Remote.Collection.updateField({
                database: database,
                collection: collection,
                id: document._id.$id,
                field: key,
                value: Ext.JSON.decode(value)
            }, {
                scope: this,
                success: function(){
                    collectionGrid.refreshGrid();
                    window.close();
                }
            });

        } catch (ex) {
            Logger.warn('wrong json');
        }
    }
});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.controller.collectionsTree', {
    extend : 'Ext.app.Controller',

    models: [
        'Application.model.collection',
        'Application.model.database'

    ],

    stores: [
        'Application.store.collections',
        'Application.store.databases'
    ],

    views: [
        'Application.view.main.panel',
        'Application.view.main.collectionsTree',
        'Application.view.collectionsTree.createDatabaseWindow',
        'Application.view.collectionsTree.createCollectionWindow',
        'Application.view.main.dropDatabasesWindow',
        'Application.view.main.dropCollectionsWindow',
        'Application.view.main.dropCollectionsTree'

    ],

    init: function() {

        this.control({

            'collectionsTree' : {
                select : this.selectCollection,
                render: this.collectionsTreeRender,
                beforerender: this.collectionsTreeBeforeRender,
                itemcontextmenu : this.itemContextMenu,
                itemcollapse: this.itemCollapse,
                containercontextmenu: this.containerContextMenu
            },

            'dropCollectionsTree' : {
                itemcollapse: this.itemCollapse
            },

            '[action=new-database]' : {
                click: this.showCreateDatabaseWindow
            },

            '[action=drop-database]' : {
                click: this.dropDatabase
            },

            '[action=take-snapshot]' : {
                click: this.takeSnapshot
            },

            '[action=revert-to-snapshot]' : {
                click: this.revertToSnapshot
            },

            'mainPanel [action=drop-databases]' : {
                click: this.dropDatabase
            },

            'mainPanel [action=drop-collections]': {
                click: this.showDropCollectionsWindow
            },

            '[action=drop-collection]' : {
                click: this.dropCollection
            },

            '[action=clear-collection]' : {
                click : this.clearCollection
            },

            'dropCollectionsWindow [action=drop-collections]' : {
                click: this.dropCollections
            },

            '[action=new-collection]' : {
                click: this.showCreateCollectionWindow
            },

            '[action=refresh-databases]' : {
                click: this.refreshDatabases
            },

            '[action=refresh-all-databases]' : {
                click: this.refreshAllDatabases
            },

            '[action=refresh-collections]' : {
                click: this.refreshCollections
            },

            '[action=export]': {
                click: this.exportSelection
            },

            'createDatabaseWindow [action=ok]' : {
                click: this.createDatabase
            },

            'createCollectionWindow [action=ok]' : {
                click: this.createCollection
            },

            'collectionsTreeSmartItemContextMenu': {
                beforeshow: this.beforeShowSmartItemContextMenu
            },

            'dropDatabasesWindow [action=drop-databases]': {
                click: this.dropDatabases
            },

            'dropDatabasesWindow': {
                'show' : this.centerWindow
            },

            'mainPanel': {
                'render': this.onMainPanelRender
            }

        });

    },

    onMainPanelRender: function() {
        this.refreshLatestSnapshots();
    },

    refreshLatestSnapshots: function() {

        var controller = this;

        Remote.Snapshots.getLatest({}, {success: function(latest){
            controller.latestSnapshots = latest;
        }});
    },

    centerWindow : function(windowComponent){
        windowComponent.center();
    },

    exportSelection: function() {

        var nodes = this.getSelectedNodes();
        var mainPanel = Ext.ComponentQuery.query('mainPanel')[0];

        mainPanel.setLoading('Exporting, please wait...');
        Remote.Databases.export({nodes: nodes}, {
            scope: this,
            success: function(){
                mainPanel.setLoading(false);
                window.location.replace(config.webservice.url + '?class=Databases&method=downloadExport');
            }});
    },

    getSelectedNodes: function(){
        var nodes = [];
        var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0],
            selected = collectionsTree.getSelectionModel().getSelection();

        if (selected.length) {

            for (var i=0; i<selected.length; i++) {
                var node = {
                    type: selected[i].get('type'),
                    name: selected[i].get('name')
                };

                if (selected[i].get('type') == 'collection') {
                    node.database = selected[i].parentNode.get('name');
                }

                nodes.push(node);
            }

        }

        return nodes;

    },

    takeSnapshot: function() {
        var nodes = this.getSelectedNodes();

        var controller = this;
        Remote.Databases.takeSnapshot({nodes: nodes}, {
            success: function(){
                controller.refreshLatestSnapshots();
        }});

    },

    revertToSnapshot: function() {
        var nodes = this.getSelectedNodes();

        var controller = this;
        Remote.Databases.revertToSnapshot({nodes: nodes}, {
            success: function(){

                var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0],
                    selected = collectionsTree.getSelectionModel().getSelection();

                if (selected.length) {

                    for (var i=0; i<selected.length; i++) {
                        switch(selected[i].get('type')) {
                            case 'database':
                                if (selected[i].isExpanded()) {
                                    collectionsTree.getStore().load({node: selected[i]});
                                }
                                break;
                            case 'collection':
                                this.selectCollection(null, selected[i]);
                                break;
                        }
                    }

                }

                Ext.Msg.show({
                    title: 'Success',
                    msg: 'Reverted to snapshot successful',
                    icon: Ext.Msg.INFO,
                    buttons: Ext.Msg.OK
                });
            }, scope: this});
    },

    dropCollections: function(button) {
        var window = button.up('window');
        var tree = window.down('treepanel');
        var records = [];
        var rootNode = tree.getRootNode();

        var checked = tree.getChecked();

        for (var i=0; i<checked.length; i++) {
            records.push({
                'database' : checked[i].parentNode.get('name'),
                'name': checked[i].get('name')
            });
        }

        if (records.length) {
            Ext.Msg.show({
                title: 'Confirm collections drop',
                msg: 'Are you sure you want to drop the selected collections?',
                icon: Ext.Msg.WARNING,
                buttons: Ext.Msg.YESNO,
                scope: this,
                fn: function(buttonId) {

                    if (buttonId != 'yes') {
                        return;
                    }

                    Remote.Collections.drop({records: records}, {
                        scope: this,
                        success: function(response){
                        this.refreshDatabases();
                        this.dropCollectionsWindow.close();
                    }});

                }
            });
        }
    },

    dropDatabases: function(button) {

        var window = button.up('window');
        var grid = window.down('grid');
        var selectionModel = grid.getSelectionModel();
        var selection = selectionModel.getSelection();

        Ext.Msg.show({
            title: 'Confirm databases drop',
            msg: 'Are you sure you want to drop the selected databases?',
            icon: Ext.Msg.WARNING,
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId) {

                if (buttonId != 'yes') {
                    return;
                }

                var store = grid.getStore();
                store.remove(selection);
                store.sync();
            }
        });

    },

    showDropCollectionsWindow: function() {
        this.dropCollectionsWindow = Ext.create('Application.view.main.dropCollectionsWindow');
        var dropCollectionsTree = this.dropCollectionsWindow.down('dropCollectionsTree');
        dropCollectionsTree.getStore().addListener('beforeload', this.collectionsTreeBeforeLoad, this);
        dropCollectionsTree.getStore().addListener('append', this.appendDropCollectionNode, this);
        this.showDropCollectionsWindow = function(){
            this.dropCollectionsWindow.down('treepanel').getRootNode().expand();
            this.dropCollectionsWindow.show();
        };

        this.showDropCollectionsWindow();
    },

    appendDropCollectionNode: function(parentNode, addedNode) {

        if (addedNode.get('type') == 'root') {
            return;
        }

        switch (parentNode.get('type')) {
            case 'root':
                addedNode.set('type', 'database');
                addedNode.set('leaf', false);
                addedNode.set('iconCls', 'database-icon');
                break;

            case 'database':
                addedNode.set('type', 'collection');
                addedNode.set('leaf', true);
                addedNode.set('icon', 'images/table-icon.png');
                addedNode.set('checked', false);
                break;
        }
    },

    showDropDatabasesWindow: function() {

        var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0],
            databasesGrid = null,
            databasesGridStore = null;

        this.dropDatabasesWindow = Ext.create('Application.view.main.dropDatabasesWindow');
        databasesGrid = this.dropDatabasesWindow.down('grid');
        databasesGridStore = databasesGrid.getStore();
        databasesGridStore.addListener('write', this.writeDatabasesStore, this)

        // rewrite handler to exclude initialization
        this.showDropDatabasesWindow = function() {

            var selectedItems = collectionsTree.getSelectionModel().getSelection(),
                selectedDatabases = [],
                item = null;

            // get selected databases from tree (if any)
            if (itemsCount = selectedItems.length){
                for (var i = 0; i<itemsCount; i++){
                    item = selectedItems[i];
                    if (item.get("type") == 'database'){
                        selectedDatabases.push(item.get("name"));
                    }
                }
            }

            // load list of databases in grid
            databasesGridStore.load({

                // preselect databases in window with selection from tree
                "callback": function(records, operation, success){

                    var recordsToSelect = [],
                        index = null,
                        i = 0,
                        l = 0;

                    // anything to preselect ?
                    if (l = selectedDatabases.length){

                        // iterate through list of databases that should be preselected and for
                        // each one try to find a match in the grid's store
                        // if match found, add it to a list of recordsToSelect that will be used for preselection
                        for (i = 0; i < l; i++){

                            // try to find the database by name in list of recordsToSelect from grid's store
                            // the match should be exact, case sensitive
                            index = databasesGridStore.find("name", selectedDatabases[i], 0, false, true, true);

                            // record found, add its index in the recordsToSelect array
                            if (index !== null){
                                recordsToSelect.push(databasesGridStore.getAt(index));
                            }

                        }

                        // if anything to select, just do it!
                        databasesGrid.getSelectionModel().deselectAll();
                        if (recordsToSelect.length){
                            databasesGrid.getSelectionModel().select(recordsToSelect, false, true);
                        }
                    }
                }

            });

            this.dropDatabasesWindow.show();
        };

        this.showDropDatabasesWindow();
    },

    writeDatabasesStore: function(store, operation){

        if (operation.action == 'destroy') {
            this.refreshDatabases();
            this.dropDatabasesWindow.close();
        }
    },

    refreshCollections: function(){
        var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0];
        var selected = collectionsTree.getSelectionModel().getSelection()[0];

        collectionsTree.getStore().load({node: selected.parentNode});
    },

    refreshDatabases: function(){
        var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0];
        var selected = collectionsTree.getSelectionModel().getSelection();

        for(var i=0; i<selected.length; i++) {
            if (selected[i].get('type') == 'database') {
                collectionsTree.getStore().load({node: selected[i]});
                break;
            }
        }
    },

    refreshAllDatabases: function() {
        var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0];
        collectionsTree.getStore().load();
    },

    itemCollapse: function(node) {
        // when node collapses set it to false so on next expand it will reload
        node.data.loaded = false;
    },

    collectionsTreeBeforeRender: function(tree) {
        Remote.Connections.getCurrentConnection({}, {scope: this,
            success: function(connection){
                tree.setTitle('Databases and collections at ' + connection['host'] + ':' + connection['port']);
            }});
    },

    collectionsTreeRender: function(treePanel) {
        // on collections tree render, get the databases,
        var store = treePanel.getStore(),
            rootNode = treePanel.getRootNode();

        store.addListener('beforeload', this.collectionsTreeBeforeLoad, this);
        store.addListener('append', this.appendNode, this);

        rootNode.expand();
    },

    selectCollection : function (rowModel, record) {

        if (record.get('type') != 'collection') {
            return false;
        }

        var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0],
            selectedItems = collectionsTree.getSelectionModel().getSelection();

        if (selectedItems.length>1) {
            return false;
        }

        var collectionGrid = Ext.ComponentQuery.query('collectionGrid')[0];

        if (!record.get('name') || !record.parentNode.get('name')) {
            return false;
        }

        if (collectionGrid.getStore().getProxy().extraParams.collection == record.get('name')
            && collectionGrid.getStore().getProxy().extraParams.database == record.parentNode.get('name')) {
            return false;
        }

        collectionGrid.getStore().getProxy().extraParams = {
            collection : record.get('name'),
            database   : record.parentNode.get('name')
        };


        collectionGrid.refreshGrid(record.get('name'));

    },

    dropDatabase: function() {

        var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0],
            selectedItems = collectionsTree.getSelectionModel().getSelection(),
            databaseName = "";

        if (selectedItems.length == 1){
            databaseName = selectedItems[0].get("name");
            Ext.Msg.show({
                msg: 'Are you sure you want to drop the selected database ('+databaseName+')?',
                title: 'Drop database',
                icon: Ext.Msg.WARNING,
                buttons: Ext.Msg.YESNO,
                fn: function(buttonId) {
                    if (buttonId == 'yes') {
                        Remote.Databases.drop({records:[{name:databaseName}]}, {
                            success: function() {
                                collectionsTree.getStore().load();
                            },
                            scope: this
                        });
                    }
                }
            });
        } else {
            this.showDropDatabasesWindow();
        }

    },

    dropCollection: function() {

        var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0];
        var selected = collectionsTree.getSelectionModel().getSelection();

        if (!selected.length) {
            return;
        }

        var msg = 'Are you sure you want to drop the selected collection' + (selected.length>1 ? 's?' : ' ('+selected[0].get('name')+')?');

        Ext.Msg.show({
            msg: msg,
            title: 'Drop collections',
            icon: Ext.Msg.WARNING,
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId) {
                if (buttonId == 'yes') {
                    var records = [];

                    var databases = {};

                    for (var i=0; i<selected.length; i++) {
                        records.push({
                            name: selected[i].get('name'),
                            database: selected[i].parentNode.get('name')
                        });

                        databases[selected[i].parentNode.get('name')] = true;
                    }

                    databases = Object.keys(databases);

                    Remote.Collections.drop({records:records}, {
                        success: function() {

                            if (databases.length>1) {
                                collectionsTree.getStore().load();
                            } else {
                                collectionsTree.getStore().load({node: selected[0].parentNode});
                            }


                            var collectionGrid = Ext.ComponentQuery.query('collectionGrid')[0];
                            collectionGrid.refreshGrid();
                        },
                        scope: this
                    });
                }
            }
        });

    },

    clearCollection : function () {
        var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0];
        var selected = collectionsTree.getSelectionModel().getSelection();

        if (!selected.length) {
            return;
        }

        var msg = 'Are you sure you want to clear the selected collection' + (selected.length>1 ? 's?' : ' ('+selected[0].get('name')+')?');

        Ext.Msg.show({
            msg: msg,
            title: 'Clear collections',
            icon: Ext.Msg.WARNING,
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId) {
                if (buttonId == 'yes') {
                    var records = [];

                    var databases = {};

                    for (var i=0; i<selected.length; i++) {
                        records.push({
                            name: selected[i].get('name'),
                            database: selected[i].parentNode.get('name')
                        });

                        databases[selected[i].parentNode.get('name')] = true;
                    }

                    databases = Object.keys(databases);

                    Remote.Collections.clear({records:records}, {
                        success: function() {

                            if (databases.length>1) {
                                collectionsTree.getStore().load();
                            } else {
                                collectionsTree.getStore().load({node: selected[0].parentNode});
                            }


                            var collectionGrid = Ext.ComponentQuery.query('collectionGrid')[0];
                            collectionGrid.refreshGrid();
                        },
                        scope: this
                    });
                }
            }
        });
    },

    createDatabase:function () {
        var form = this.createDatabaseWindow.down('form');
        var record = form.getRecord();
        record.set(form.getValues());

        this.createDatabaseWindow.setLoading('Creating database');

        Remote.Databases.create({records:[record.data]}, {
            success: function() {
                var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0];

                collectionsTree.getStore().load();

                this.createDatabaseWindow.setLoading(false);

                this.createDatabaseWindow.close();
            },

            scope: this
        });
    },

    createCollection:function () {
        var form = this.createCollectionWindow.down('form');
        var record = form.getRecord();
        record.set(form.getValues());

        this.createCollectionWindow.setLoading('Creating collection');

        Remote.Collections.create({records:[record.data]}, {
            success: function() {
                var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0];

                var selected = collectionsTree.getSelectionModel().getSelection()[0];
                var databaseNode;
                switch (selected.get('type')) {
                    case 'database':
                        databaseNode = selected;
                        break;

                    case 'collection':
                        databaseNode = selected.parentNode;
                        break;
                }

                collectionsTree.getStore().load({node: databaseNode, callback: function(records, operation){
                    operation.node.expand();
                }});

                this.createCollectionWindow.setLoading(false);

                this.createCollectionWindow.close();
            },

            scope: this
        });
    },

    showCreateDatabaseWindow:function () {

        this.createDatabaseWindow = Ext.create('Application.view.collectionsTree.createDatabaseWindow');
        this.createDatabaseWindow.addListener('afterrender', function(window){
            var form = window.down('form');
            form.keyNav = Ext.create('Ext.util.KeyNav', form.el, {
                enter:function(){
                    form.down('button[formBind=true]').fireEvent('click');
                },
                scope:this
            });
        }, this);


        this.showCreateDatabaseWindow = function() {
            var database = Ext.create('Application.model.database');
            var form = this.createDatabaseWindow.down('form');

            form.loadRecord(database);
            this.createDatabaseWindow.show();
        };

        this.showCreateDatabaseWindow();

    },

    showCreateCollectionWindow:function () {

        this.createCollectionWindow = Ext.create('Application.view.collectionsTree.createCollectionWindow');
        this.createCollectionWindow.addListener('afterrender', function(window){
            var form = window.down('form');
            form.keyNav = Ext.create('Ext.util.KeyNav', form.el, {
                enter:function(){
                    form.down('button[formBind=true]').fireEvent('click');
                },
                scope:this
            });
        }, this);

        this.showCreateCollectionWindow = function(){
            var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0];
            var selected = collectionsTree.getSelectionModel().getSelection()[0];

            if (!selected) {
                Ext.Msg.show({
                    title: 'Could not find database',
                    msg: 'Please select a database from the tree to add the collection to, or right click the collection from the tree and select add collection from the context menu.',
                    icon: Ext.Msg.WARNING,
                    buttons: Ext.Msg.OK
                });

                return;
            }

            var databaseName;

            switch (selected.get('type')) {
                case 'database':
                    databaseName = selected.get('name');
                    break;

                case 'collection':
                    databaseName = selected.parentNode.get('name');
                    break;
            }

            var database = Ext.create('Application.model.collection', {
                database: databaseName
            });
            var form = this.createCollectionWindow.down('form');

            form.loadRecord(database);
            this.createCollectionWindow.setTitle('Create collection for database ' + databaseName);
            this.createCollectionWindow.show();
        };

        this.showCreateCollectionWindow();

    },

    appendNode : function (parentNode, addedNode) {
        if (addedNode.get('type') == 'root') {
            return;
        }

        switch (parentNode.get('type')) {
            case 'root':
                addedNode.set('type', 'database');
                addedNode.set('leaf', false);
                addedNode.set('iconCls', 'database-icon');
                break;

            case 'database':
                addedNode.set('type', 'collection');
                addedNode.set('leaf', true);
                addedNode.set('icon', 'images/table-icon.png');
                break;
        }
    },

    collectionsTreeBeforeLoad: function(store, operation) {

        switch (operation.action) {

            case 'read':

                switch (operation.node.get('type')) {

                    case 'root':
                        store.getProxy().extraParams = {
                            type: 'database'
                        };
                        break;

                    case 'database':
                        store.getProxy().extraParams = {
                            type : 'collection',
                            database: operation.node.get('name')
                        };
                        break;

                }

                break;

        }

    },

    itemContextMenu : function (view, record, item, index, event) {

        var tree = Ext.ComponentQuery.query('collectionsTree')[0];
        var contextMenu = tree.smartItemContextMenu;

        contextMenu.showAt(event.getXY());

        event.stopEvent();
    },

    containerContextMenu: function(view, event) {
        var tree = Ext.ComponentQuery.query('collectionsTree')[0];
        var contextMenu= tree.databasesContainerContextMenu;
        contextMenu.showAt(event.getXY());

        event.stopEvent();
    },

    checkLatestSnapshotsForContextMenus: function() {
        var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0];
        var selected = collectionsTree.getSelectionModel().getSelection();
        var revertButtons = Ext.ComponentQuery.query('[action=revert-to-snapshot]');
        var canRevert = true;
        var i;

        for (i=0; i<selected.length; i++) {

            switch (selected[i].get('type')) {
                case 'database':
                    if (typeof this.latestSnapshots['databases'] == 'undefined') {
                        canRevert = false;
                        break;
                    }

                    if (typeof this.latestSnapshots['databases'][selected[i].get('name')] == 'undefined') {
                        canRevert = false;
                    }

                    break;
                case 'collection':

                    if (typeof this.latestSnapshots['collections'] == 'undefined') {
                        canRevert = false;
                        break;
                    }

                    var parent = selected[i].parentNode;

                    var database = parent.get('name');

                    if (typeof this.latestSnapshots['collections'][database] == 'undefined') {
                        canRevert = false;
                        break;
                    }

                    if (typeof this.latestSnapshots['collections'][database][selected[i].get('name')] == 'undefined') {
                        canRevert = false;
                        break;
                    }

                    break;
            }

            if (!canRevert) {
                break;
            }
        }

        for (i=0; i<revertButtons.length; i++) {
            revertButtons[i].setDisabled(!canRevert);
        }
    },

    setVisibilityForSmartItemContextMenuActions : function(contextMenu, actions, visible) {
        for(var i=0; i<actions.length; i++) {
            var action = contextMenu.down('[action=' + actions[i] + ']');
            if (action) {
                action.setVisible(visible);
            }
        }
    },


    beforeShowSmartItemContextMenu: function(contextMenu) {
        var databasesOnlyActions = ['new-database', 'drop-database', 'new-collection', 'refresh-databases'];
        var collectionsOnlyActions = ['clear-collection', 'drop-collection', 'refresh-collections'];

        this.setVisibilityForSmartItemContextMenuActions(contextMenu, databasesOnlyActions, true);
        this.setVisibilityForSmartItemContextMenuActions(contextMenu, collectionsOnlyActions, true);

        var collectionsTree = Ext.ComponentQuery.query('collectionsTree')[0];
        var selected = collectionsTree.getSelectionModel().getSelection();

        for (var i=0; i<selected.length; i++) {
            switch (selected[i].get('type')) {
                case 'database':
                    this.setVisibilityForSmartItemContextMenuActions(contextMenu, collectionsOnlyActions, false);
                    break;
                case 'collection':
                    this.setVisibilityForSmartItemContextMenuActions(contextMenu, databasesOnlyActions, false);
                    break;
            }
        }


        this.checkLatestSnapshotsForContextMenus();
    }

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.controller.collectionGrid', {
    extend : 'Ext.app.Controller',

    models : [
        'Application.model.collection',
        'Application.model.document',
        'Application.model.jsonDocument',
        'Application.model.documentTree'
    ],

    stores : [
        'Application.store.collection',
        'Application.store.document'
    ],

    views : [
        'Application.view.main.collectionGrid',
        'Application.view.collectionGrid.contextMenu',
        'Application.view.collectionGrid.editDocumentWindow',
        'Application.view.collectionGrid.jsEditor'

    ],

    init: function() {
        this.control({
            'collectionGrid':{
                selectionchange: this.selectionChange,
                itemcontextmenu: this.itemContextMenu,
                containercontextmenu: this.containerContextMenu,
                afterrender: this.gridAfterRender,
                itemdblclick: this.itemDoubleClick
            },

            'collectionGridContextMenu [action=remove]': {
                click: function() {
                    this.removeSelected();
                }
            },

            'collectionGridContextMenu [action=update-document]': {
                click: function() {
                    this.updateDocumentAction();
                }
            },

            'collectionGridContextMenu [action=new-document]': {
                click: function() {
                    this.newJsonDocumentAction();
                }
            },

            'editDocumentWindow textareafield' : {
                change:
                    this.changeTextAreaField
            },

            'editDocumentWindow button[action=ok]': {
                click: this.saveJsonDocument
            },

            'collectionGridContextMenu': {
                beforeshow: this.beforeShowContextMenuHandler
            }

        });
    },

    gridAfterRender: function(grid) {
        grid.getStore().addListener('write', this.gridStoreWriteHandler, this);
    },

    gridStoreWriteHandler: function(store, operation){
        var collectionGrid = Ext.ComponentQuery.query('collectionGrid')[0];
        collectionGrid.refreshGrid();
    },

    beforeShowContextMenuHandler: function(contextMenu) {
        var collectionGrid = Ext.ComponentQuery.query("collectionGrid")[0];
        var selected = collectionGrid.getSelectionModel().getSelection();

        var removeButton = contextMenu.down('[action=remove]');
        var updateButton = contextMenu.down('[action=update-document]');

        updateButton.setDisabled(selected.length!=1);
        removeButton.setDisabled(selected.length==0);

    },

    newJsonDocumentAction : function() {

        var extraParams = Ext.ComponentQuery.query("collectionGrid")[0].getStore().getProxy().extraParams;

        var jsonDocument = Ext.create('Application.model.jsonDocument', {
            _id: null,
            data: ""
        });

        var document = Ext.create('Application.model.document', {
            database: extraParams.database,
            collection: extraParams.collection,
            data: {}
        });

        this.showEditDocumentWindow(jsonDocument, document);
    },

    saveJsonDocument: function(button){
        var form = button.up('form');
        var window = form.up('window');
        window.close();

        var data = Ext.JSON.decode(form.down('jsEditor').getValue());
        var document = window.document;

        document.set('data', data);

        if (document.phantom) {
            var store = Ext.ComponentQuery.query("collectionGrid")[0].getStore();
            store.add(document);
            store.sync();
        } else {
            document.store.sync();
        }


    },

    changeTextAreaField: function(textAreaField, newValue, oldValue) {

        var window = textAreaField.up('window');

        window.delayedFormatTask.delay(1000, function(textAreaField){

            try {
                var object = Ext.JSON.decode(textAreaField.getValue());
                var json = JSON.stringify(object, null, 4);
                textAreaField.setValue(json);
            } catch (Ex) {
            }

        }, this, [textAreaField]);


    },

    itemDoubleClick: function() {
        this.updateDocumentAction();
    },

    updateDocumentAction: function() {
        var collectionGrid = Ext.ComponentQuery.query('collectionGrid')[0];

        var selected = collectionGrid.getSelectionModel().getSelection();

        if (selected.length!=1) {
            return;
        }

        var document = selected[0];

        var json = JSON.stringify(document.get('data'), null, 4);

        var jsonDocument = Ext.create('Application.model.jsonDocument', {
            _id: document.get('_id'),
            data: json
        });

        this.showEditDocumentWindow(jsonDocument, document);
    },

    showEditDocumentWindow: function (jsonDocument, document) {
        this.editDocumentWindow = Ext.create('Application.view.collectionGrid.editDocumentWindow');
        this.editDocumentWindow.delayedFormatTask = new Ext.util.DelayedTask();

        this.showEditDocumentWindow = function(jsonDocument, document) {
            var form = this.editDocumentWindow.down('form');
            form.loadRecord(jsonDocument);
            this.editDocumentWindow.document = document;

            if (document.phantom) {
                this.editDocumentWindow.setTitle("Create document");
            } else {
                this.editDocumentWindow.setTitle("Edit document");
            }

            this.editDocumentWindow.show();
        };

        this.showEditDocumentWindow(jsonDocument, document);
    },

    selectionChange: function(selectionModel, selected) {

        var documentTree = Ext.ComponentQuery.query('documentTree')[0];

        if (selected.length == 1) {
            documentTree.setLoading('Loading document');
            documentTree.loadDocument(selected[0].data.data);
            documentTree.setLoading(false);
        } else {
            documentTree.setRootNode({
                expanded:false,
                children:[]
            });
        }

    },

    itemContextMenu: function (view, record, item, index, event) {
        var collectionGrid = Ext.ComponentQuery.query('collectionGrid')[0];
        collectionGrid.contextMenu.showAt(event.getXY());
        event.stopEvent();
    },

    containerContextMenu: function(view, event) {
        var collectionGrid = Ext.ComponentQuery.query('collectionGrid')[0];
        collectionGrid.contextMenu.showAt(event.getXY());
        event.stopEvent();
    },

    removeSelected: function() {
        var collectionGrid = Ext.ComponentQuery.query('collectionGrid')[0];

        var selected = collectionGrid.getSelectionModel().getSelection();

        collectionGrid.getStore().remove(selected);

        collectionGrid.getStore().sync();
    }

});
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.define('Application.controller.main', {
    extend:'Ext.app.Controller',

    models:[
        'Application.model.collection',
        'Application.model.connection',
        'Application.model.document',
        'Application.model.documentTree'
    ],

    stores:[
        'Application.store.collection',
        'Application.store.document',
        'Application.store.databases'
    ],

    views:[
        'Application.view.main.panel',
        'Application.view.main.exportDatabaseWindow',
        'Application.view.main.exportDatabaseTree'
    ],

    init:function () {

        this.control({
            'mainPanel button[action=logout]':{
                click:this.logout
            },

            'mainPanel [action=about]' : {
                click: this.showAbout
            },

            'mainPanel [action=update]' : {
                click : this.showUpdateWindow
            },

            'mainPanel [action=export-database]' : {
                click: this.showExportDatabaseWindow
            },

            '[action=import-database]' : {
                click: this.showImportDatabaseWindow
            },

            'exportDatabaseTree' : {
                checkchange : this.exportDatabaseCheckChange
            },

            'exportDatabaseWindow [action=export]': {
                click: this.exportDatabase
            },

            'exportDatabaseWindow [name=export-all]': {
                change: this.exportAllCheckboxChange
            },

            'importDatabaseWindow [action=import]': {
                click: this.uploadFile
            }

        });
    },

    uploadFile: function(button) {
        var importDatabaseWindow = button.up('window');
        var form = importDatabaseWindow.down('form');

        form.setLoading("Uploading");

        form.submit({
            url: config.webservice.url + '?class=Databases&method=import',
            success: function(form, action){
                importDatabaseWindow.down('form').setLoading(false);
                importDatabaseWindow.close();
                Ext.Msg.show({
                    title:"Import successful",
                    msg: "Import was successful",
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.INFO
                });

            }, failure : function(form ,action) {

                importDatabaseWindow.down('form').setLoading(false);
                importDatabaseWindow.close();

                var msg;

                switch (action.result.exception) {
                    case 'Application\\Exceptions\\CouldNotImport':
                        msg = "Could not import";
                        break;

                    case 'Application\\Exceptions\\BadTarArchive':
                        msg = action.result.message;
                        break;

                    default:
                        msg = 'Unknown error';
                }

                Ext.Msg.show({
                    title: 'Import failed',
                    msg: msg,
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.ERROR
                });
            }
        });

    },

    exportDatabase: function(button) {


        var exportWindow = button.up('window');
        var exportDatabaseTree = exportWindow.down('exportDatabaseTree');
        var selected = exportDatabaseTree.getChecked();
        var exportAllCheckBox = exportWindow.down('[name=export-all]');
        var exportAll = exportAllCheckBox.getValue();
        var nodes = [];

        if (selected.length && !exportAll) {

            for (var i=0; i<selected.length; i++) {
                var node = {
                    type: selected[i].get('type'),
                    name: selected[i].get('name')
                };

                if (selected[i].get('type') == 'collection') {
                    node.database = selected[i].parentNode.get('name');
                }

                nodes.push(node);
            }

        }

        exportWindow.setLoading('Exporting, please wait...');

        Remote.Databases.export({nodes: nodes}, {
            scope: this,
            success: function(){
                exportWindow.setLoading(false);
                exportWindow.close();
                window.location.replace(config.webservice.url + '?class=Databases&method=downloadExport');
            }});
    },

    exportAllCheckboxChange: function(checkbox, newValue, oldValue) {
        var exportDatabaseTree = checkbox.up('window').down('exportDatabaseTree');
        exportDatabaseTree.setDisabled(newValue);
    },

    exportDatabaseCheckChange: function(node, checked) {

        var exportDatabaseTree = Ext.ComponentQuery.query('exportDatabaseTree')[0];

        if (!checked) {
            return;
        }

        switch (node.get('type')) {
            case 'database':

                for(var i=0; i<node.childNodes.length; i++){
                    node.childNodes[i].set('checked', false);
                }

                break;

            case 'collection':

                node.parentNode.set('checked', false);

                break;
        }
    },

    showExportDatabaseWindow: function() {
        this.exportDatabaseWindow = Ext.create('Application.view.main.exportDatabaseWindow');
        var exportDatabaseTree =this.exportDatabaseWindow.down('exportDatabaseTree');
        exportDatabaseTree.getStore().addListener('beforeload', this.exportTreeBeforeLoad, this);
        exportDatabaseTree.getStore().addListener('append', this.appendNode, this);

        this.showExportDatabaseWindow = function(){
            this.exportDatabaseWindow.down('exportDatabaseTree').getRootNode().expand();
            this.exportDatabaseWindow.show();
        };

        this.showExportDatabaseWindow();
    },

    showImportDatabaseWindow: function() {
        this.importDatabaseWindow = Ext.create('Application.view.main.importDatabaseWindow');

        this.showImportDatabaseWindow = function(){
            this.importDatabaseWindow.show();
        };

        this.showImportDatabaseWindow();
    },

    showUpdateWindow: function() {

        if (!this.updateWindow) {
            this.updateWindow = Ext.create('Application.view.main.updateWindow');
        }

        this.updateWindow.show();
    },

    appendNode : function (parentNode, addedNode) {
        if (addedNode.get('type') == 'root') {
            return;
        }

        switch (parentNode.get('type')) {
            case 'root':
                addedNode.set('type', 'database');
                addedNode.set('leaf', false);
                addedNode.set('iconCls', 'database-icon');
                addedNode.set('checked', false);
                break;

            case 'database':
                addedNode.set('type', 'collection');
                addedNode.set('leaf', true);
                addedNode.set('icon', 'images/table-icon.png');
                addedNode.set('checked', false);
                break;
        }
    },

    itemCollapse: function(node) {
        // when node collapses set it to false so on next expand it will reload
        node.data.loaded = false;
    },

    exportTreeBeforeLoad: function(store, operation) {

        switch (operation.action) {

            case 'read':

                switch (operation.node.get('type')) {

                    case 'root':
                        store.getProxy().extraParams = {
                            type: 'database'
                        };
                        break;

                    case 'database':
                        store.getProxy().extraParams = {
                            type : 'collection',
                            database: operation.node.get('name')
                        };
                        break;

                }

                break;

        }

    },

    showAbout: function() {
        Ext.Msg.show({
            title: 'About',
            msg: 'Created by Victor Olaru. Free to use and modify for everyone under GNU License v3.0.',
            buttons: Ext.Msg.OK,
            icon: Ext.Msg.INFO
        });
    },

    logout:function () {
        Ext.Msg.show({
            msg    :'Are you sure you want to logout?',
            icon   :Ext.Msg.QUESTION,
            buttons:Ext.Msg.YESNO,
            fn     :function (buttonId) {
                if (buttonId == 'yes') {
                    Remote.Authentication.logout({}, {
                        success:function () {
                            window.location.reload();
                        }
                    });
                } else {
                    this.close();
                }
            }
        });
    }
});
