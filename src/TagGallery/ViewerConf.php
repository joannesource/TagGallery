<?php

namespace TagGallery;

/**
 * Description of ViewerConf
 *
 * @author joannesource
 */
class ViewerConf {

    public $roots;
    public $cache;
    private $isEditionMode = true;

    function getRoots() {
        return $this->roots;
    }

    function setRoots($roots) {
        $this->roots = $roots;
    }

    function setCache($cache) {
        if (!file_exists($cache)) {
            if (!mkdir($cache)) {
                throw new Exception('Cache dir make error');
            }
        }
        $this->cache = $cache;
    }

    function getCache() {
        return $this->cache;
    }

    function isEditionMode() {
        return $this->isEditionMode;
    }

    function setIsEditionMode($isEditionMode) {
        $this->isEditionMode = $isEditionMode;
    }

}
