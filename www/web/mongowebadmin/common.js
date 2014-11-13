/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
/**
 * Logging helper
 *
 * @type {object}
 */
var Logger = (function (loggerConfig) {

    // initialize config with default values
    var config = {

        // Logger enabled by default
        enabled:true,

        // Default log level is DEBUG
        logLevel:Constants.Logger.LOG_LEVEL_DEBUG

    };

    // logger config set ?
    if (typeof loggerConfig != 'undefined') {

        // save logger config
        config = loggerConfig;

    }

    // set enabled state
    var enabled = typeof config.enabled == 'boolean' && config.enabled && typeof console == 'object';

    // get console features
    var consoleFeatures = {

        info:typeof console.info == 'function',
        warn:typeof console.warn == 'function',
        debug:typeof console.log == 'function',
        error:typeof console.error == 'function',
        clear:typeof console.clear == 'function'

    };

    /**
     * Formats arguments for displaying
     */
    function formatArguments() {

        // should add date?
        if (typeof config.appendDate == 'boolean' && config.appendDate) {

            //convert arguments that is an object to array so we can put the time in front
            arguments = Array.prototype.slice.call(arguments);
            time = new Date();

            //timestamp
            arguments.unshift(time);

        }

        // return formatted arguments
        return arguments;

    }

    /**
     * log message with LOG_LEVEL_INFO
     *
     * @private
     */
    function _info() {

        // should log ?
        if (!enabled || !consoleFeatures.info || config.logLevel > Constants.Logger.LOG_LEVEL_INFO) {

            // criteria not met, do no log
            return;

        }

        // format arguments
        var formattedArguments = formatArguments.apply(this, arguments);

        // do actual logging
        console.info.apply(console, formattedArguments);

    }

    /**
     * log message with LOG_LEVEL_DEBUG
     *
     * @private
     */
    function _debug() {

        // should log ?
        if (!enabled || !consoleFeatures.debug || config.logLevel > Constants.Logger.LOG_LEVEL_DEBUG) {

            // criteria not met, do no log
            return;

        }

        // format arguments
        var formattedArguments = formatArguments.apply(this, arguments);

        // do actual logging
        console.log.apply(console, formattedArguments);

    }

    /**
     * log message with LOG_LEVEL_WARNING
     *
     * @private
     */
    function _warn() {

        // should log ?
        if (!enabled || !consoleFeatures.warn || config.logLevel > Constants.Logger.LOG_LEVEL_WARNING) {

            // criteria not met, do no log
            return;

        }

        // format arguments
        var formattedArguments = formatArguments.apply(this, arguments);

        // do actual logging
        console.warn.apply(console, formattedArguments);

    }

    /**
     * log message with LOG_LEVEL_ERROR
     *
     * @private
     */
    function _error() {

        // should log ?
        if (!enabled || !consoleFeatures.error) {

            // criteria not met, do no log
            return;

        }

        // format arguments
        var formattedArguments = formatArguments.apply(this, arguments);

        // do actual logging
        console.error.apply(console, formattedArguments);

    }

    /**
     * Clears the console (if logger is enabled)
     *
     * @private
     */
    function _clear() {

        // should (and can) clear ?
        if (!enabled || !consoleFeatures.clear) {

            // do nothing
            return;

        }

        // clear console
        console.clear();
    }

    /**
     * Return public properties/methods
     */
    return {

        // info
        info:_info,

        // warn
        warn:_warn,

        // error
        error:_error,

        // debug
        debug:_debug,

        // clear console
        clear:_clear

    };

})(config.logger);

var JsonSyntaxColor = {
    colorValue: function(value) {

        var cssClass;
        var jsonDecodeError;
        try {
            var jsonValue = Ext.JSON.decode(value);
        } catch (e) {
            jsonDecodeError = true;
        }

        if (jsonDecodeError) {
            return value;
        }


        switch (typeof jsonValue) {
            case 'string':
                cssClass ='string';
                break;
            case 'number':
                cssClass = 'int';
                break;
            case 'boolean':
                cssClass = 'bool';
                break;

            case 'object':
                if (jsonValue === null) {
                    break;
                }

                var result = jsonValue instanceof Array ? '[' : '{';
                for(var key in jsonValue) {
                    result += (jsonValue instanceof Array ? '' : '<span class="value-key">"'+key+'"</span>:') + this.colorValue(Ext.JSON.encode(jsonValue[key])) + ',';
                }

                result = result.replace(/,$/, jsonValue instanceof Array ? ']' : '}');
                return result;
        }

        if (jsonValue === null) {
            cssClass = 'null';
        }

        if (cssClass) {
            value = '<span class="'+cssClass+'">'+value+'</span>'
        }

        return value;
    }
};