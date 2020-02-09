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


use Ikarus\Logic\Model\Component\ComponentModelAwareInterface;
use Ikarus\SPS\Logic\Component\ComponentByCycleUpdateInterface;
use Ikarus\SPS\Logic\Helper\AbstractComponent2NodeHelper;
use Ikarus\SPS\Plugin\Cyclic\CyclicPluginInterface;
use Ikarus\SPS\Plugin\Management\CyclicPluginManagementInterface;

class CyclicEnginePlugin extends AbstractLogicEnginePlugin implements CyclicPluginInterface
{
    /** @var ComponentByCycleUpdateInterface[] */
    private $activeNodeComponents = [];

    public function setup()
    {
        parent::setup();
        $model = $this->getEngine()->getModel();
        if($model instanceof ComponentModelAwareInterface) {
            foreach($model->getComponents() as $component) {
                $this->assignComponent($component);
            }
        }
    }

    protected function assignComponent($component) {
        if($component instanceof ComponentByCycleUpdateInterface)
            $this->activeNodeComponents[] = $component;
    }


    public function update(CyclicPluginManagementInterface $pluginManagement)
    {
        /** @var ComponentByCycleUpdateInterface $component */
        try {
            $this->getEngine()->beginRenderCycle();
            foreach($this->getActiveNodeComponents() as $component) {
                $nodes = AbstractComponent2NodeHelper::getAffectedNodesOfComponent($component->getName(), $this->getEngine());
                foreach($nodes as $nid => $nc) {
                    $this->getEngine()->updateNode($nid, $this->getValueProvider(), $error);
                    if($error)
                        throw $error;
                }
            }
        } catch (\Throwable $exception) {
            $this->getEngine()->endRenderCycle();
            throw $exception;
        }
    }

    /**
     * @return ComponentByCycleUpdateInterface[]
     */
    public function getActiveNodeComponents(): array
    {
        return $this->activeNodeComponents;
    }
}