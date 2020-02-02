<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Ikarus\SPS\Logic\Plugin;

use Ikarus\Logic\EngineInterface;
use Ikarus\Logic\Model\Component\ComponentModelAwareInterface;
use Ikarus\SPS\Plugin\AbstractPlugin;
use Ikarus\SPS\Plugin\PluginChildrenInterface;
use Ikarus\SPS\Plugin\PluginInterface;
use Ikarus\SPS\Plugin\SetupPluginInterface;
use Ikarus\SPS\Plugin\TearDownPluginInterface;

abstract class AbstractLogicEnginePlugin extends AbstractPlugin implements TearDownPluginInterface, SetupPluginInterface, PluginChildrenInterface
{
    /** @var string */
    private $identifier;
    /** @var EngineInterface */
    private $engine;
    /** @var PluginInterface[] */
    private $commonPlugins = [];

    /**
     * AbstractLogicEnginePlugin constructor.
     * @param string $identifier
     * @param EngineInterface $engine
     */
    public function __construct(string $identifier, EngineInterface $engine)
    {
        $this->identifier = $identifier;
        $this->engine = $engine;

        $model = $engine->getModel();
        if($model instanceof ComponentModelAwareInterface) {
            foreach($model->getComponents() as $component) {
                if($component instanceof PluginInterface AND $this->insertComponentIntoSPS($component))
                    $this->commonPlugins[] = $component;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getChildPlugins(): array
    {
        return $this->getCommonPlugins();
    }

    /**
     * Asks the instance to import plugins from logic engine into the sps engine.
     *
     * @param PluginInterface $enginePlugin
     * @return bool
     */
    protected function insertComponentIntoSPS(PluginInterface $enginePlugin): bool {
        return true;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    public function setup()
    {
        $this->getEngine()->activate();
    }

    public function tearDown()
    {
        $this->getEngine()->terminate();
    }

    /**
     * @return PluginInterface[]
     */
    public function getCommonPlugins(): array
    {
        return $this->commonPlugins;
    }
}