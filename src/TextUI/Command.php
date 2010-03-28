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
/**
 * load PHPLPM Core Files
 */
require_once "PHPLPM/Exception.php";
require_once "PHPLPM/Parser.php";

/**
 * load eZ Componants Base to use ConsoleTools
 */
require "ezc/Base/base.php";

function __autoload($class) {
    ezcBase::autoload($class);
}

/**
 * TextUI frontend for phplpm
 *
 * @author Sebastian Bechtel <me@sebastian-bechtel.info>
 * @copyright Sebastian Bechtel <me@sebastian-bechtel.info>. All rights reserved.
 * @since 2010
 * @version 1.0.0
 */
class Command {
    public function main() {
        $input = new ezcConsoleInput;
        $output = new ezcConsoleOutput;

        $parser = new Parser;
    }
}