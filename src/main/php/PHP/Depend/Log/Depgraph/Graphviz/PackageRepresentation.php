<?php

/**
 * This file is part of PHP_Depend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2012, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Log
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pdepend.org/
 */

/**
 * Representation of a package in dot language (as subgraph)
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Log
 * @author     Daniel Pozzi <bonndan76@googlemail.com>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pdepend.org/
 * @link       http://graphviz.org/
 */
class PHP_Depend_Log_Depgraph_Graphviz_PackageRepresentation
{
    /**
     * package uuid
     * @var string
     */
    protected $uuid = 'default';
    /**
     * package name
     * @var string
     */
    protected $name = 'default';
    
    /**
     * classes and interfaces
     * @var array
     */
    protected $nodes = array();
    
    /**
     * constructor takes a code package instance as argument if not the default
     * package
     * 
     * @param PHP_Depend_Code_Package $package 
     */
    public function __construct(PHP_Depend_Code_Package $package = null)
    {
        if ($package !== null) {
            $this->uuid = PHP_Depend_Log_Depgraph_Graphviz::getUuuidForDot($package->getUUID());
            $this->name = $package->getName();
        }
    }
    
    /**
     * display as subgraph
     * 
     * @return string 
     */
    public function __toString()
    {
        $buffer = PHP_EOL . 'subgraph cluster' . $this->uuid . '{' . PHP_EOL;
        $buffer .= sprintf('label = "%s";', $this->name) . PHP_EOL;
        $buffer .= 'style=filled;'.PHP_EOL;
        $buffer .= 'color=lightgrey;'.PHP_EOL;
        foreach ($this->nodes as $node) {
            $buffer .= $node->__toString();
        }
        $buffer .= '}' . PHP_EOL;
        
        return $buffer;
    }
    
    /**
     * add a class or interface representation
     * 
     * @param PHP_Depend_Log_Depgraph_Graphviz_ClassOrInterfaceRepresentation $node
     * 
     * @return void
     */
    public function addRepresentation(PHP_Depend_Log_Depgraph_Graphviz_ClassOrInterfaceRepresentation $node)
    {
        $this->nodes[] = $node;
    }
}