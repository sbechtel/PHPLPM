#!/usr/bin/env php
<?php
/**
 * Copyright (c) 2010 Sebastian Bechtel <me@sebastian-bechtel.info>
 * All rights reserved.
 *
 * @package phplpm
 * @author Sebastian Bechtel <me@sebastian-bechtel.info>
 * @copyright Sebastian Bechtel <me@sebastian-bechtel.info>. All rights reserved.
 * @license BSD License
 * @since 2010
 */
set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());

require_once "PHPLPM/TextUI/Command.php";

$command = new Command;
$command->main();