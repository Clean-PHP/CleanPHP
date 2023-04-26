<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: cleanphp\engine
 * Class CliEngine
 * Created By ankio.
 * Date : 2023/4/25
 * Time : 16:17
 * Description : 命令行工作引擎
 */

namespace cleanphp\engine;

class CliEngine extends BaseEngine
{

    /**
     * @inheritDoc
     */
    function getContentType(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    function render(...$data): string
    {
       return '';
    }

    /**
     * @inheritDoc
     */
    function renderError(string $msg, array $traces, string $dumps, string $tag)
    {
       return '';
    }

    function onNotFound($msg = "")
    {

    }
}