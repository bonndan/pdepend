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
 * Generates dependency graph from generated metrics. Work in progress, requires
 * GraphViz
 * 
 * Usage:
 * <code>
 * /src/bin/pdepend --depgraph-graphviz=test.dot src/main/php/PHP/Depend
 * fdp -Tpng test.dot > test.png
 * </code>
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
class PHP_Depend_Log_Depgraph_Graphviz extends PHP_Depend_Log_Jdepend_Xml
{
    /**
     * The type of this class.
     */

    const CLAZZ = __CLASS__;

    /**
     * The output log file.
     *
     * @var string $_logFile
     */
    private $_logFile = null;

    /**
     * all nodes which represent an interface
     * @var type 
     */
    protected $interfaceNodes = array();

    /**
     * all nodes which represent a class
     * @var array 
     */
    protected $classNodes = array();

    /**
     * dependencies between interfaces in the form [interface::uuid][dependsupon::uuid
     * @var array 
     */
    protected $interfaceDependencies = array();

    /**
     * dependencies between classes in the form [class::uuid][dependsupon::uuid
     * @var array 
     */
    protected $classDependencies = array();

    /**
     * Sets the output log file.
     *
     * @param string $logFile The output log file.
     *
     * @return void
     */
    public function setLogFile($logFile)
    {
        $this->_logFile = $logFile;
    }

    /**
     * Closes the logger process and writes the output file.
     *
     * @return void
     * @throws PHP_Depend_Log_NoLogOutputException If the no log target exists.
     */
    public function close()
    {
        // Check for configured output
        if ($this->_logFile === null) {
            throw new PHP_Depend_Log_NoLogOutputException($this);
        }

        foreach ($this->code as $node) {
            $node->accept($this);
        }

        $buffer = "digraph depgraph {" . PHP_EOL . PHP_EOL;

        foreach ($this->interfaceNodes as $node) {
            $buffer .= $this->getRepresentationForInterface($node);
        }
        
        foreach ($this->interfaceDependencies as $from => $deps) {
            foreach ($deps as $uuid) {
                $buffer .= $this->getRepresentationForDependency($from, $uuid);
            }
        }

        foreach ($this->classNodes as $node) {
            $buffer .= $this->getRepresentationForClass($node);
        }
        
        foreach ($this->classDependencies as $from => $deps) {
            foreach ($deps as $uuid) {
                $buffer .= $this->getRepresentationForDependency($from, $uuid);
            }
        }
        
        $buffer .= PHP_EOL . "}";




        file_put_contents($this->_logFile, $buffer);
    }

    /**
     * returns a graphviz compatible uuid
     * 
     * @param string $uuid
     * 
     * @return string 
     */
    protected function getUuuidForDot($uuid)
    {
        return 'uuid' . str_replace('-', '', $uuid);
    }
    
    /**
     * returns a graphviz node representing a class
     * 
     * @param PHP_Depend_Code_AbstractClassOrInterface $node
     * 
     * @return string 
     */
    protected function getRepresentationForClass(PHP_Depend_Code_AbstractClassOrInterface $node)
    {
        $template = '%s [shape=box, label="%s"];' . PHP_EOL;
        return sprintf($template, $this->getUuuidForDot($node->getUUID()), $node->getName());
    }

    /**
     * returns a graphviz node representing a class
     * 
     * @param PHP_Depend_Code_AbstractClassOrInterface $node
     * 
     * @return string 
     */
    protected function getRepresentationForInterface(PHP_Depend_Code_AbstractClassOrInterface $node)
    {
        $template = '%s [shape=ellipse, label="%s"];' . PHP_EOL;
        return sprintf($template, $this->getUuuidForDot($node->getUUID()), $node->getName());
    }

    /**
     * get the dependency representation (edge)
     * 
     * @param string $from uuid
     * @param string $to   uuid
     * 
     * @return string
     */
    protected function getRepresentationForDependency($from, $to)
    {
        $template = '%s -> %s;' . PHP_EOL;
        return sprintf($template, $this->getUuuidForDot($from), $this->getUuuidForDot($to));
    }

    /**
     * Visits a class node.
     *
     * @param PHP_Depend_Code_Class $class The current class node.
     *
     * @return void
     * @see PHP_Depend_VisitorI::visitClass()
     */
    public function visitClass(PHP_Depend_Code_Class $class)
    {
        if (!$class->isUserDefined()) {
            return;
        }

        $this->classNodes[] = $class;

        foreach ($class->getDependencies() as $dependency) {
            /* @var $dependency PHP_Depend_Code_AbstractClassOrInterface */
            if (!$dependency->isUserDefined()) {
                continue;
            }

            $this->classDependencies[$class->getUUID()][] = $dependency->getUUID();
        }
    }

    /**
     * Visits a code interface object.
     *
     * @param PHP_Depend_Code_Interface $interface The context code interface.
     *
     * @return void
     * @see PHP_Depend_VisitorI::visitInterface()
     */
    public function visitInterface(PHP_Depend_Code_Interface $interface)
    {
        if (!$interface->isUserDefined()) {
            return;
        }

        $this->interfaceNodes[] = $interface;

        foreach ($interface->getDependencies() as $dependency) {
            /* @var $dependency PHP_Depend_Code_AbstractClassOrInterface */
            if (!$dependency->isUserDefined()) {
                continue;
            }

            $this->interfaceDependencies[$interface->getUUID()][] = $dependency->getUUID();
        }
    }

    /**
     * Visits a package node.
     *
     * @param PHP_Depend_Code_Class $package The package class node.
     *
     * @return void
     * @see PHP_Depend_VisitorI::visitPackage()
     */
    public function visitPackage(PHP_Depend_Code_Package $package)
    {
        if (!$package->isUserDefined()) {
            return;
        }

        foreach ($package->getTypes() as $type) {
            $type->accept($this);
        }

    }

    /**
     * @todo use these?
    public function visitProperty(PHP_Depend_Code_Property $property){}
    public function visitMethod(PHP_Depend_Code_Method $method){}
     * 
     */
}
