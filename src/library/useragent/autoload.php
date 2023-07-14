<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * File autoload.php
 * Created By ankio.
 * Date : 2023/7/13
 * Time : 21:39
 * Description :
 */

use cleanphp\base\EventManager;
use cleanphp\base\Route;

EventManager::addListener("__route_before__", function ($event, &$data) {
   $image = __DIR__.DS."icon".DS.$data;

   if(is_file($image)){
       Route::renderStatic($image);
   }
});
