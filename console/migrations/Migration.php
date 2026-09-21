<?php
namespace console\migrations;

class Migration extends \yii\db\Migration{

    public function createTable($table, $columns, $options = null)
    {
        $time = $this->beginCommand("create table $table");
        if(!empty($options)){
            $options = "ENGINE=InnoDB COMMENT='".$options."'";
        }

        $this->db->createCommand()->createTable($table, $columns, $options)->execute();
        foreach ($columns as $column => $type) {
            if ($type instanceof ColumnSchemaBuilder && $type->comment !== null) {
                $this->db->createCommand()->addCommentOnColumn($table, $column, $type->comment)->execute();
            }
        }
        $this->endCommand($time);
    }
}