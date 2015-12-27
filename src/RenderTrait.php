<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator;

use Zend\View;

trait RenderTrait
{
    /**
     * View instance used for self rendering
     *
     * @var \Zend\View\Renderer\RendererInterface
     */
    protected $view = null;

    /**
     * Retrieves the view instance.
     *
     * If none registered, instantiates a PhpRenderer instance.
     *
     * @return \Zend\View\Renderer\RendererInterface|null
     */
    public function getView()
    {
        if ($this->view === null) {
            $this->setView(new View\Renderer\PhpRenderer());
        }

        return $this->view;
    }

    /**
     * Sets the view object.
     *
     * @param  \Zend\View\Renderer\RendererInterface $view
     * @return Paginator
     */
    public function setView(View\Renderer\RendererInterface $view = null)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Renders the paginator.
     *
     * @param  \Zend\View\Renderer\RendererInterface $view
     * @return string
     */
    public function render(View\Renderer\RendererInterface $view = null)
    {
        if (null !== $view) {
            $this->setView($view);
        }

        $view = $this->getView();

        return $view->paginationControl($this);
    }

    /**
     * Serializes the object as a string.  Proxies to {@link render()}.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $return = $this->render();
            return $return;
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return '';
    }

}