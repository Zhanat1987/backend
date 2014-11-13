/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
Ext.form.Basic.override({

    // override validation method to allow custom validation
    checkValidity:function () {

        // initialize variables
        var me = this,
            valid = !me.hasInvalidField() && !(typeof this.customValidator == 'function' && !this.customValidator());

        // validation succeeded
        if (valid !== me.wasValid) {
            me.onValidityChange(valid);
            me.fireEvent('validitychange', me, valid);
            me.wasValid = valid;
        }
    }
});
