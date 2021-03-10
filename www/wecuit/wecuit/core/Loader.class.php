<?php
class Loader
{
    // Load library classes
    public function library()
    {
        foreach(func_get_args() as $v)
            include_once LIB_PATH . "{$v}.class.php";
    }

    // loader helper functions. Naming conversion is xxx_helper.php;
    public function helper($helper)
    {
        include_once HELPER_PATH . "{$helper}_helper.php";
    }
}
