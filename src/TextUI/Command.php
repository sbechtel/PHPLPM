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
require_once "src/Exception.php";
require_once "src/Parser.php";

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

        $input->registerOption(new ezcConsoleOption(
            'h', 'help'
        ));
        $input->registerOption(new ezcConsoleOption(
            'p', 'process'
        ));

        $input->process();

        if($input->getOption("p")->value) {
            $parser = new Parser;
            $result = $parser->parse(file_get_contents("_testdata/sample.php", 1));

            $table = new ezcConsoleTable($output, 60);

            $table[0][0]->content = "Class";
            $table[0][1]->content = "Method";
            $table[0][2]->content = "Lines";

            $i = 1;

            foreach($result as $class => $methods) {
                foreach($methods as $method) {
                    $row = $table[$i];

                    $row[0]->content = $class;
                    $row[1]->content = $method["name"];
                    $row[2]->content = (string)$method["lines"];

                    $i++;
                }
            }

            $table->outputTable();
            print "\n";
            //print_r($result);
        }
        
    }
}