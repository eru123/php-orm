<?php

namespace eru123\orm;

use eru123\orm\Table\Management;
use eru123\orm\Table\CRUD;

abstract class Model
{
    use Management;
    use CRUD;
}