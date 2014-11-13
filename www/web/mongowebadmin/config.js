/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
var config = {
    webservice : {
        url : window.location.href + 'webservice/'
    },

    logger : {

        // enable/disable logger
        enabled  : true,

        // logging level
        logLevel : Constants.Logger.LOG_LEVEL_INFO

    },

    pageSizeValues : [
        {"value" : 10, "option" : "10"},
        {"value" : 20, "option" : "20"},
        {"value" : 30, "option" : "30"},
        {"value" : 50, "option" : "50"},
        {"value" : 100, "option" : "100"}
    ]

};
