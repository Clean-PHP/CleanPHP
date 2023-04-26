<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\waf
 * Class Rule
 * Created By ankio.
 * Date : 2023/4/26
 * Time : 11:05
 * Description :
 */

namespace library\waf;

class Rule
{
    public string $rule = "";
    public string $name = "";
    public int $count = 0;
    public function __construct($rule,$name,$count)
    {
      $this->rule = $rule;
      $this->name = $name;
      $this->count = $count;
    }
}