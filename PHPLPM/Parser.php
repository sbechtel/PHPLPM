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
require_once "PHPLPM/Exception.php";

/**
 * PHP Lines per Method Parser
 *
 * @author Sebastian Bechtel <me@sebastian-bechtel.info>
 * @copyright Sebastian Bechtel <me@sebastian-bechtel.info>. All rights reserved.
 * @since 2010
 * @version 1.0.0
 */
class Parser {
    /**
     * @var array
     */
    private $tokens = null;

    private $classes = array();
    private $methods = array();

    /**
     * @var array
     */
    private $result = null;

    /**
     * Parse the given string
     *
     * Parses the given string and returns a result array:
     *
     * <pre>
     * array(
     *   [CLASS] => array(
     *     [0] => array(
     *       [name] => METHOD
     *       [lines] => 2
     *     )
     *   )
     * )
     * </pre>
     *
     * @param string $source
     * @return array
     */
    public function parse($source) {
        try {
            $this->_prepare($source);
        }

        catch(PHPLPMException $e) {
            throw new PHPLPMException("Failure on loading!");
        }

        try {
            $this->classes = $this->_findClasses($this->tokens);

            foreach($this->classes as $class) {
                $className = $this->_findNameToken($class);
                $classBlock = $this->_sliceBlock($class);

                try {
                    $this->methods = $this->_findMethods($classBlock);

                    foreach($this->methods as $method) {
                        $methodName = $this->_findNameToken($method);
                        $methodLines = $this->_countMethodLines($this->_sliceBlock($method));

                        $this->result[$className][] = array(
                            "name" => $methodName,
                            "lines" => $methodLines
                        );
                    }
                }

                catch(PHPLPMException $e) {
                    /**
                        * @todo chain exceptions that the user knows if method or class parsing failed!
                        */
                    throw new PHPLPMException;
                }
            }
        }

        catch(PHPLPMException $e) {
            throw new PHPLPMException("Failure on parsing! Seems that your file is not valid!");
        }

        return $this->result;
    }

    /**
     * Prepare source code for parsing
     *
     * The source code will be tokenized by php. The source code must be a string and
     * can not be ''. Their must be more than one token. One token is a hint for a file
     * without Opening Tag.
     *
     * @param string $source
     * @throws PHPLPMException
     */
    private function _prepare($source) {
        if(!is_string($source) || $source === '') throw new PHPLPMException;

        $tokens = token_get_all($source);

        if(count($tokens) <= 1) throw new PHPLPMException();

        $this->tokens = $tokens;
        unset($tokens);
    }

    /**
     * Split token array by a php token
     *
     * The tokens from parameter $tokens will be splitted by specified php token $php_token.
     * The last part are all tokens after the last php token.
     *
     * @param array $tokens
     * @param integer $php_token
     * @throws PHPLPMException
     * @return array
     */
    private function _splitByToken($tokens, $php_token) {
        if(!is_array($tokens)) throw new PHPLPMException;

        $tokenKey = false;
        $result = array();

        // split tokens between php token
        foreach($tokens as $key => $token) {
            if(!is_array($token) || $token[0] != $php_token) continue;

            if((bool)$tokenKey) $result[] = array_slice($tokens, $tokenKey, $key - $tokenKey);
            $tokenKey = $key;
        }

        // use the tokens after the last T_CLASS token as last class
        $result[] = array_slice($tokens, $tokenKey);

        return $result;
    }

    /**
     * Split token array by T_CLASS
     *
     * The tokens will be splitted by T_CLASS. The last part are all tokens after the last
     * T_CLASS token.
     *
     * @param array $tokens
     * @return array
     */
    private function _findClasses($tokens) {
        return $this->_splitByToken($tokens, T_CLASS);
    }

    /**
     * Split token array by T_FUNCTION
     *
     * The tokens will be splitted by T_FUNCTION. The last part are all tokens after the last
     * T_FUNCTION token.
     *
     * @param array $tokens
     * @return array
     */
    private function _findMethods($tokens) {
        return $this->_splitByToken($tokens, T_FUNCTION);
    }

    /**
     * Slice first php code block
     *
     * Slices the tokens in the first occuring php code block ( { ... } ). Throws
     * an PHPLPMException if no block is found (this function should only be called after
     * a T_CLASS token, a T_FUNCTION token or something similar.
     *
     * @param array $tokens
     * @throws PHPLPMException
     * @return array
     */
    private function _sliceBlock($tokens) {
        if(!is_array($tokens)) throw new PHPLPMException;

        $blockDepth = 0;
        $blockStartKey = false;

        foreach($tokens as $key => $token) {
            if(is_array($token)) continue;

            switch($token) {
                case "{": $blockDepth++; break;
                case "}": $blockDepth--; break;
            }

            if($token == "{" && !(bool)$blockStartKey) $blockStartKey = $key;
            if($blockDepth === 0 && (bool)$blockStartKey) return array_slice($tokens, ++$blockStartKey, $key - $blockStartKey);
        }

        throw new PHPLPMException;
    }

    /**
     * Find name token
     *
     * Find the first occuring T_STRING token. This function will be called after T_CLASS
     * or T_FUNCTION so this token is the name of the class/function.
     *
     * @param array $tokens
     * @throws PHPLPMException
     * @return string
     */
    private function _findNameToken($tokens) {
        foreach($tokens as $token) {
            if(is_array($token) && $token[0] == T_STRING) return (string)$token[1];
        }

        throw new PHPLPMException;
    }


    /**
     * Count lines of method
     *
     * Count the lines of a method. The code will be trimmed what means, that beginning and
     * ending T_WHITESPACE are ignored. The number of lines is calculated with the
     * second token index.
     *
     * @param array $tokens
     * @throws PHPLPMException
     * @return integer
     */
    private function _countMethodLines($tokens) {
        if(!is_array($tokens)) throw new PHPLPMException;

        $startLine = false;
        $endLine = 0;

        foreach($tokens as $token) {
            if(!is_array($token) || $token[0] == T_WHITESPACE) continue;

            if(!(bool)$startLine) $startLine = $token[2];
            $endLine = $token[2];
        }

        if((bool)$startLine) return ++$endLine - $startLine;
        return 0;
    }
}