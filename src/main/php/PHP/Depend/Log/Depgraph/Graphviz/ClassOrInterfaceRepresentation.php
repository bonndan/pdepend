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
 * UML-like Representation of a class or interface
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
class PHP_Depend_Log_Depgraph_Graphviz_ClassOrInterfaceRepresentation
{
    /**
     * dot-compatible uuid 
     * @var string
     */
    protected $uuid;
    /**
     * name of the class or interface
     * @var string
     */
    protected $name;
    
    /**
     * public methods
     * @var array
     */
    protected $methods = array();
    
    /**
     * dot-compatible uuid of the parent
     * @var string 
     */
    protected $parent;
    
    /**
     * implemented interfaces (uuids)
     * @var array
     */
    protected $interfaces = array();
    /**
     * dependencies (uuids)
     * @var array
     */
    protected $dependencies = array();
    
    /**
     * constructor requires an abstract class or interface node
     * 
     * @param PHP_Depend_Code_AbstractClassOrInterface $node 
     */
    public function __construct(PHP_Depend_Code_AbstractClassOrInterface $node)
    {
        $this->uuid = PHP_Depend_Log_Depgraph_Graphviz::getUuuidForDot($node->getUUID());
        $this->name = $node->getName();
        if ($node->getModifiers() == PHP_Depend_ConstantsI::IS_IMPLICIT_ABSTRACT) {
            $this->name = '\<\<interface\>\>\n' . $this->name;
        }
        
        /* @var $method PHP_Depend_Code_Method */
        foreach ($node->getMethods() as $method) {
            if (!$method->isPublic()) {
                continue;
            }
            
            $returnType = $method->getReturnClass();
            if (is_object($returnType)) {
                $returnType = $returnType->getName();
            }
            if (trim($returnType == '')) {
                $returnType = '';
            } else {
                $returnType .= '\ ';
            }
            
            $this->methods[] = '+\ ' . $returnType . $method->getName() . '\l';
        }
        
        //parent 
        $parentClass = $node->getParentClass();
        if ($parentClass && $parentClass->isUserDefined()) {
            $this->parent = PHP_Depend_Log_Depgraph_Graphviz::getUuuidForDot($parentClass->getUUID());
        }
        
        //implemented interfaces
        foreach ($node->getInterfaces() as $interface) {
            if (!$interface->isUserDefined()) {
                continue;
            }
            $uuid = PHP_Depend_Log_Depgraph_Graphviz::getUuuidForDot($interface->getUUID());
            if ($uuid != $this->parent) {
                $this->interfaces[] = $uuid;
            }
        }
        
        foreach ($node->getDependencies() as $dependency) {
            /* @var $dependency PHP_Depend_Code_AbstractClassOrInterface */
            if (!$dependency->isUserDefined()) {
                continue;
            }
            $uuid = PHP_Depend_Log_Depgraph_Graphviz::getUuuidForDot($dependency->getUUID());
            if ($uuid != $this->parent && !in_array($uuid, $this->interfaces)) {
                $this->dependencies[] = $uuid;
            }
        }
    }
    
    /**
     * returns the dot representation
     * 
     * @return string 
     */
    public function __toString()
    {
        $template = '%s [shape=record, label="%s|%s"];' . PHP_EOL;
        $template = sprintf($template, $this->uuid, $this->name, implode('\n', $this->methods)) . PHP_EOL;
        
        if ($this->parent !== null) {
            $template .= $this->uuid . ' -> ' . $this->parent . ' [arrowhead=empty];'. PHP_EOL;
        }
        
        foreach ($this->interfaces as $dep) {
            $template .= $this->uuid . ' -> ' . $dep . ' [style=dashed, arrowhead=empty];'. PHP_EOL;
        }
        
        foreach ($this->dependencies as $dep) {
            $template .= $this->uuid . ' -> ' . $dep . ' [constraint=false, arrowhead=none];'. PHP_EOL;
        }
        
        return $template;
    }
}