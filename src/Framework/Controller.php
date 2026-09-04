<?php
namespace Framework;

abstract class Controller {
    protected Viewer $viewer;

    public function setViewer(Viewer $viewer) {
        $this->viewer = $viewer;
    }
}