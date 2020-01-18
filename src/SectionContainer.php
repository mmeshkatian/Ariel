<?php

namespace Mmeshkatian\Ariel;



class SectionContainer
{
    var $view;
    var $in;
    var $position;
    var $vars;

    /**
     * SectionContainer constructor.
     * @param $view
     * @param $in
     * @param $position
     * @param $vars
     */
    public function __construct($view,$in,$position,$vars)
    {
        $this->view = $view;
        $this->in = $in;
        $this->position = $position;
        $this->vars = $vars;
    }

    private function check($in,$position)
    {
        return ($in == $this->in && ($position == $this->position || $position == 'all'));
    }

    public function compile($data)
    {
        $this->view = view($this->view,compact('data'))->renderSections();
    }
    public function render($in,$position)
    {
        if($this->check($in,$position))
            return $this->view['content'] ?? '';
        return null;
    }

    public function getScript($in)
    {
        if($this->check($in,'all'))
            return $this->view['script'] ?? '';
    }

    public function getStyle($in)
    {
        if($this->check($in,'all'))
            return $this->view['style'] ?? '';
    }
}
