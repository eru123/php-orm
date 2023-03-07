<?php

namespace eru123\orm\Table\CRUD;

use eru123\orm\Query;
use eru123\orm\Table\Base;

trait Callback {
    use Base;

    protected function beforeInsert(array $data) {
        return $data;
    }

    protected function afterInsert(array $data, Query $query) {
        return;
    }

    protected function beforeUpdate(array $data) {
        return $data;
    }

    protected function afterUpdate(array $data, Query $query) {
        return;
    }
}