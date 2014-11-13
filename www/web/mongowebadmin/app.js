/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.Loader.setConfig({enabled:false});
Ext.Ajax.cors = true;
Ext.Ajax.withCredentials = true;


// add the web service
Ext.Ajax.request({
    url    :config.webservice.url + 'index.php?action=describe',
    success:function (response) {
        var provider = Ext.JSON.decode(response.responseText, true);
        if (provider == null) {
            Logger.error("Invalid JSON response from server", response.responseText);

            Ext.Msg.show({
                msg    :'Webservice failed to load, bad response from server',
                icon   :Ext.Msg.ERROR,
                buttons:Ext.Msg.OK,
                fn     :function () {
                    this.close();
                }
            });

            return;
        }

        Ext.direct.Manager.addProvider(
            Ext.apply(provider, {
                enableBuffer:false,
                namespace   :'Remote',
                maxRetries  :0
            })
        );

        Ext.direct.Manager.addListener('exception', function(exception){
            if (exception.exceptionType == "Application\\Exceptions\\AuthenticationFailure") {
                Ext.Msg.show({
                    msg     : 'Failed to authenticate to db. Press OK to reload.',
                    title   : 'Authentication failure',
                    icon    : Ext.Msg.ERROR,
                    buttons : Ext.Msg.OK,
                    fn      : function () {
                        window.location.reload(true);
                    }
                });
            }
        });

        Logger.info('Webservice loaded');

        if (Ext.Loader.getConfig('enabled')) {
            launchApplication();
        } else {
            Ext.Loader.loadScript({
                url   : window.location.href + 'app-release.js',
                scope :this,
                onLoad:function () {
                    Logger.info('app-release loaded');
                    launchApplication();
                }
            });
        }

    },

    failure:function () {
        Logger.error('Webservice failed to load');
        Ext.Msg.show({
            msg    :'Webservice failed to load',
            icon   :Ext.Msg.ERROR,
            buttons:Ext.Msg.OK,
            fn     :function () {
                this.close();
            }
        });
    }
});


function init() {
    var loginWindow = Ext.ComponentQuery.query('loginWindow')[0];

    if (loginWindow) {
        loginWindow.close();
    }

    Ext.ComponentQuery.query('viewport')[0].add({
        xtype: 'mainPanel'
    });
}

function launchApplication() {

    Logger.info('launchApplication');

    Ext.application({
        name:'Application',

        appFolder:'app',

        controllers:[
            'login',
            'main',
            'collectionsTree',
            'collectionGrid',
            'documentTree',
            'update'
        ],

        launch:function () {

            Ext.create('Ext.container.Viewport', {
                layout:'fit'
            });

            Logger.info('loadApplication check authentication');

            var result = Remote.Authentication.isAuthenticated({}, {
                scope  :this,
                success:function (isAuthenticated) {

                    if (isAuthenticated) {
                        init();
                    } else {
                        Ext.create('Application.view.login.window').show();
                    }
                },
                failure:function (response) {
                    Logger.error('Failed to check authentication status', response);
                    Ext.Msg.show({
                        msg    :'Failed to check authentication status',
                        icon   :Ext.Msg.ERROR,
                        buttons:Ext.Msg.OK,
                        fn     :function () {
                            this.close();
                        }
                    });
                }
            });

        }
    });
}
