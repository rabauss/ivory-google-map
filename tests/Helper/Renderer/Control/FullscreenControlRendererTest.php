<?php

/*
 * This file is part of the Ivory Google Map package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\Tests\GoogleMap\Helper\Renderer\Control;

use Ivory\GoogleMap\Control\FullscreenControl;
use Ivory\GoogleMap\Helper\Formatter\Formatter;
use Ivory\GoogleMap\Helper\Renderer\AbstractJsonRenderer;
use Ivory\GoogleMap\Helper\Renderer\Control\ControlPositionRenderer;
use Ivory\GoogleMap\Helper\Renderer\Control\ControlRendererInterface;
use Ivory\GoogleMap\Helper\Renderer\Control\FullscreenControlRenderer;
use Ivory\JsonBuilder\JsonBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author GeLo <geloen.eric@gmail.com>
 */
class FullscreenControlRendererTest extends TestCase
{
    /**
     * @var FullscreenControlRenderer
     */
    private $fullscreenControlRenderer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->fullscreenControlRenderer = new FullscreenControlRenderer(
            $formatter = new Formatter(),
            new JsonBuilder(),
            new ControlPositionRenderer($formatter)
        );
    }

    public function testInheritance()
    {
        $this->assertInstanceOf(AbstractJsonRenderer::class, $this->fullscreenControlRenderer);
        $this->assertInstanceOf(ControlRendererInterface::class, $this->fullscreenControlRenderer);
    }

    public function testControlPositionRenderer()
    {
        $controlPositionRenderer = $this->createControlPositionRendererMock();
        $this->fullscreenControlRenderer->setControlPositionRenderer($controlPositionRenderer);

        $this->assertSame($controlPositionRenderer, $this->fullscreenControlRenderer->getControlPositionRenderer());
    }

    public function testRender()
    {
        $this->assertSame(
            '{"position":google.maps.ControlPosition.RIGHT_TOP}',
            $this->fullscreenControlRenderer->render(new FullscreenControl())
        );
    }

    public function testRenderWithInvalidControl()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->fullscreenControlRenderer->render('foo');
    }

    /**
     * @return MockObject|ControlPositionRenderer
     */
    private function createControlPositionRendererMock()
    {
        return $this->createMock(ControlPositionRenderer::class);
    }
}
