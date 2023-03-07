<?php

namespace eru123\orm\Table;

use eru123\orm\Table\CRUD\Delete;
use eru123\orm\Table\CRUD\Insert;
use eru123\orm\Table\CRUD\Update;

trait CRUD
{
    use Insert;
    use Update;
    use Delete;
}