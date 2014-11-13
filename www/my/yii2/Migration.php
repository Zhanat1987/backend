<?php

namespace my\yii2;

use yii\db\Migration as Yii2Migration;

class Migration extends Yii2Migration
{

    /**
     * @var string
     */
    protected  $_tableOptions;

    /**
     * @return string return sql query, which set table options
     */
    public function getTableOptions()
    {
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $this->_tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        return $this->_tableOptions;
    }

} 