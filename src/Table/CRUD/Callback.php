<?php

namespace eru123\orm\Table\CRUD;

use eru123\orm\Query;
use eru123\orm\Table\Base;

trait Callback {
    use Base;

    /**
     * Called before an insert query is executed
     * @param   array  $data  The data to be inserted
     * @return  array  The data to be inserted
     */
    protected function beforeInsert(array $data) {
        return $data;
    }

    /**
     * Called after an insert query is executed
     * @param   array  $data  The data that was inserted
     * @param   Query  $query The query that was executed
     * @return  void
     */
    protected function afterInsert(array $data, Query $query) {
        return;
    }

    /**
     * Called before an update query is executed
     * @param   array  $data  The data to be updated
     * @return  array  The data to be updated
     */
    protected function beforeUpdate(array $data) {
        return $data;
    }

    /**
     * Called after an update query is executed
     * @param   array  $data  The data that was updated
     * @param   Query  $query The query that was executed
     * @return  void
     */
    protected function afterUpdate(array $data, Query $query) {
        return;
    }
}