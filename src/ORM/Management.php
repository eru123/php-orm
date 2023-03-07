<?php

namespace eru123\orm\ORM;

trait Management
{
    use Base;
    
    /**
     * Migrate all Models
     * @return static
     */
    public function migrate()
    {
        $this->debug_info("Migrating all models", 'yellow');
        foreach (array_keys($this->models) as $model) {
            $this->debug_info(" --- $model", 'cyan');
            $this->__call($model, [])->migrate();
        }

        return $this;
    }

    /**
     * Drop all Models
     * @return static
     */
    public function drop()
    {
        $this->debug_info("Dropping all models", 'yellow');
        foreach (array_keys($this->models) as $model) {
            $this->debug_info(" --- $model", 'cyan');
            $this->__call($model, [])->drop();
        }

        return $this;
    }

    /**
     * Test connection
     * @return static
     */
    public function test()
    {
        $this->debug_info("Testing connection", 'cyan');
        $this->exec("SELECT 1");
        return $this;
    }
}