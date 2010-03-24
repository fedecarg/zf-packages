<?php
/**
 * Copyright (c) 2010, Federico Cargnelutti. All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgment:
 *    This product includes software developed by Federico Cargnelutti.
 * 4. Neither the name of Federico Cargnelutti nor the names of its contributors 
 *    may be used to endorse or promote products derived from this software without 
 *    specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY FEDERICO CARGNELUTTI "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL FEDERICO CARGNELUTTI BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Zf
 * @package     Zf_Orm
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @copyright   Copyright (c) 2010 Federico Cargnelutti
 * @license     New BSD License
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Orm
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @copyright   Copyright (c) 2010 Federico Cargnelutti
 * @license     New BSD License
 * @version     $Id: $
 */
class Zf_Orm_MethodExpressionParserException extends Zf_Orm_Exception {}

class Zf_Orm_MethodExpressionParser 
{    
    // Methods
    const METHOD_FIND             = 'find';
    const METHOD_FIND_BY          = 'findBy';
    const METHOD_FIND_ALL         = 'findAll';
    const METHOD_FIND_ALL_BY      = 'findAllBy';
    const METHOD_FIND_ALL_WHERE   = 'findAllWhere';
    const METHOD_FIND_WHERE       = 'findWhere';
    
    // Expressions
    const EXPRESSION_EQ           = 'Equals';
    const EXPRESSION_NOT_EQ       = 'IsNotEqual';
    const EXPRESSION_IS_NULL      = 'IsNull';
    const EXPRESSION_IS_NOT_NULL  = 'IsNotNull';
    const EXPRESSION_LT           = 'LessThan';
    const EXPRESSION_LT_EQ        = 'LessThanEquals';
    const EXPRESSION_GT           = 'GreaterThan';
    const EXPRESSION_GT_EQ        = 'GreaterThanEquals';
    const EXPRESSION_LIKE         = 'Like';
    const EXPRESSION_IN           = 'In';
    
    // Logical operators
    const OPERATOR_AND            = 'And';
    const OPERATOR_OR             = 'Or';
    
    /**
     * Objects of type Zf_Orm_Expression
     * @var array 
     */
    private $expressions = array(
        self::EXPRESSION_EQ          => null,
        self::EXPRESSION_NOT_EQ      => null,
        self::EXPRESSION_IS_NOT_NULL => null,
        self::EXPRESSION_IS_NULL     => null,
        self::EXPRESSION_LT          => null,
        self::EXPRESSION_LT_EQ       => null, 
        self::EXPRESSION_GT          => null, 
        self::EXPRESSION_GT_EQ       => null,
        self::EXPRESSION_LIKE        => null,
        self::EXPRESSION_IN          => null
    );
    
    /**
     * @param string $key
     * @param Zf_Orm_Expression $expression
     * @return void
     */
    public function setExpression($key, Zf_Orm_Expression $expression)
    {
        $this->expressions[$key] = $expression;
    }

    /**
     * @param string $key
     * @return Zf_Orm_Expression
     */
    public function getExpression($key)
    {
        if (null === $this->expressions[$key]) {
            $className = 'Zf_Orm_Expression_' . $key;
            $this->setExpression($key, new $className());
        }
        return $this->expressions[$key];
    }

    /**
     * @return array
     */
    public function getExpressions()
    {
        return $this->expressions;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return array
     */
    public function parse($method, array $arguments)
    {
        $finderMethod = $this->determineFinderMethod($method);
        $expressions = substr($method, strlen($finderMethod));
        $attributes = $this->extractAttributeNames($expressions);
        return array($finderMethod => $this->map($arguments, $attributes));
    }

    /**
     * @param array $arguments
     * @param array $attributes
     * @return array
     * @throws Zf_Orm_MethodExpressionParserException
     */
    public function map(array $arguments, array $attributes)
    {
        $numberOfReceivedArgs = count($arguments);
        $numberOfExpectedArgs = 0;
        try {
            $offset = 0;
            $numberOfAttributes = count($attributes);
            for ($i = 0; $i < $numberOfAttributes; $i++) {
                $length = count($attributes[$i]);
                $arrayValues = array_slice($arguments, $offset, $length);
                for ($n = 0; $n < $length; $n++) {
                    $attributes[$i][$n]['argument'] = isset($arrayValues[$n]) ? $arrayValues[$n] : null;
                    $numberOfExpectedArgs += $attributes[$i][$n]['placeholders'];
                }
                $offset += $length;
            }
            if ($numberOfReceivedArgs !== $numberOfExpectedArgs) {
                throw new InvalidArgumentException(sprintf('Missing argument %s', $numberOfReceivedArgs + 1));
            }            
            // Find out if it has an extra argument of type array
            if (1 === ($numberOfReceivedArgs - $numberOfExpectedArgs) && is_array(end($arguments))) {
                $numberOfReceivedArgs -= 1;
            }
            if ($numberOfExpectedArgs !== $numberOfReceivedArgs) {
                $message = sprintf('Invalid argument count (expected %d, received %d)', $numberOfExpectedArgs, $numberOfReceivedArgs);
                throw new LengthException($message);
            }
        } catch (LengthException $e) {
            throw new Zf_Orm_MethodExpressionParserException($e->getMessage());
        } catch (InvalidArgumentException $e) {
            throw new Zf_Orm_MethodExpressionParserException($e->getMessage());
        }
        return $attributes;
    }

    /**
     * @param string $method
     * @return string
     * @throws Zf_Orm_MethodExpressionParserException
     */
    public function determineFinderMethod($method)
    {
        $matches = array();
        preg_match("/^find{1}(?:By|AllBy|AllWhere|All|Where)?/", $method, $matches);
        if (isset($matches[0])) {
            return $matches[0];
        }
        throw new Zf_Orm_MethodExpressionParserException('Invalid method: ' . $method);
    }

    /**
     * @param string $expressions
     * @return array
     */
    public function extractAttributeNames($expressions)
    {
        $attributes = array();
        $pattern = '/([A-Z{1}a-z]+){1,4}(' . implode('|', array_keys($this->getExpressions())) . '){1}/';
        $expressionList = explode(self::OPERATOR_OR, $expressions);
        for ($i = 0; $i < count($expressionList); $i++) {
            $attributeList = explode(self::OPERATOR_AND, $expressionList[$i]);
            for ($n = 0; $n < count($attributeList); $n++) {
                $matches = array();
                preg_match_all($pattern, $attributeList[$n], $matches, PREG_SET_ORDER);
                if (isset($matches[0][2])) {
                    $attributeName = $matches[0][1];
                    $expressionName = $matches[0][2];
                } else {
                    $attributeName = $attributeList[$n];
                    $expressionName = self::EXPRESSION_EQ;
                }
                $attributes[$i][] = array(
                    'attribute'     => Zf_Orm_StringInflector::underscore($attributeName),
                    'expression'    => $expressionName,
                    'format'        => $this->getExpression($expressionName)->getFormat(),
                    'placeholders'  => $this->getExpression($expressionName)->getNumberOfArguments()
                );
            }
        }
        return $attributes;
    }
}
