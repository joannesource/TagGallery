<?php

namespace TagGallery;

use DiDom\Element;
use DiDom\Document;

/**
 * Gallery viewer.
 *
 * @author joannesource
 */
class Viewer {

    /**
     *
     * @var ViewerConf 
     */
    private $conf;
    private $path;
    private $basePath = '';
    private $injectedFiles = null;

    public function __construct(ViewerConf $conf, $path = null) {
        $this->conf = $conf;
        $this->path = ltrim($path, '/');
    }

    function getBasePath() {
        return $this->basePath;
    }

    function setBasePath($basePath) {
        $this->basePath = $basePath;
    }

    public function view() {

        if ($this->getInjectedFiles()) {
            $this->showFiles();
            $document = new Document();
            $element = new Element('a', 'Return');
            $base_path = $_GET['origin'];
            $element->attr('href', $this->getBasePath() . "/?folder=$base_path");
            $element->attr('class', 'return');
            $document->appendChild($element);
            echo $document->html();
        } elseif (empty($this->path)) {
            $this->listDirectories($this->conf->getRoots());
        } else {

            $document = new Document();
            $element = new Element('a', 'Return');
            if (count(explode('/', $this->path)) > 1) {
                $base_path = dirname($this->path);
            } else {
                $base_path = '';
            }
            $element->attr('href', $this->getBasePath() . "/?folder=$base_path");
            $element->attr('class', 'return');
            $document->appendChild($element);
            echo $document->html();

            $this->showDirectory($this->path);
        }
    }

    private function listDirectories($directories) {

        $document = new Document();

        foreach ($directories as $path) {
            if (!($base_path = $this->getBasePathForAbsolutePath($path))) {
                continue;
            }
            $basename = basename($base_path);
            $element = new Element('a', $basename);
            $element->attr('href', $this->getBasePath() . "/?folder=$base_path");
            $element->attr('class', 'folder');
            $document->appendChild($element);
        }
        echo $document->html();
    }

    private function getRootPathForBase($base) {
        foreach ($this->conf->roots as $root) {
            if (strpos($base, basename($root)) === 0) {
                return $root;
            }
        }
    }

    private function getBasePathForAbsolutePath($path) {
        foreach ($this->conf->roots as $root) {
            if (strpos($path, $root) === 0) {
                $base = basename($root) . substr($path, strlen($root));
                return $base;
            }
        }
    }

    private function showDirectory($directory) {
        $document = new Document();
        $files = array();
        $folders = array();

        $directory_base = basename($directory);

        if (!($root_path = $this->getRootPathForBase($directory))) {
            throw new \Exception('Unknow directory: ' . $directory);
        }
        $full_path = dirname($root_path) . '/' . $directory;

        $base_path = $this->getBasePathForAbsolutePath($full_path);

        $dir = new \DirectoryIterator($full_path);

        foreach ($dir as $fileinfo) {
            if ($fileinfo->getFilename() == '..' || $fileinfo->getFilename() == '.') {
                continue;
            }

            if ($fileinfo->isFile() && in_array($fileinfo->getExtension(), ['jpg', 'png', 'gif'])) {
                $files[] = $fileinfo->getFilename();

                $element = new Element('image');
                $thumb = $this->getThumbnail($fileinfo->getPathname());
                $element->attr('src', '/cache/' . $thumb);

                $label = new Element('label');
                $label->attr('for', 'cbx-iferlay');
                $label->appendChild($element);

                $link = new Element('a');
//                $link->attr('href', "/$base_path/" . $fileinfo->getFilename());
                $link->attr('href', "/watch/$base_path/" . $fileinfo->getFilename());
                $link->attr('target', 'iferlay');
                $link->appendChild($label);

                $document->appendChild($link);
            } elseif ($fileinfo->isDir()) {
                $folders[] = $fileinfo->getPathname();
            }
        }
        $this->listDirectories($folders);
        echo $document->html();
    }

    private function getThumbnail($path) {
        $pathinfo = pathinfo($path);
        $cache_path = $this->conf->getCache() . '/' . md5($path) . '.' . $pathinfo['extension'];

        if (file_exists($cache_path)) {
            return basename($cache_path);
        }

        try {
            $thumb = new \PHPThumb\GD($path);
            $thumb->resize(200, 100);
            $thumb->save($cache_path);
            return basename($cache_path);
        } catch (Exception $e) {
            throw new Exception('Thumbnail error');
        }
    }

    function getInjectedFiles() {
        return $this->injectedFiles;
    }

    function setInjectedFiles($injectedFiles) {
        $this->injectedFiles = $injectedFiles;
    }

    private function showFiles() {
        $document = new Document();
        foreach ($this->injectedFiles as $path) {
            $rootPath = dirname($this->getRootPathForBase(urldecode($path)));
            $fileinfo = new \SplFileObject($rootPath . '/' . urldecode($path));

            $basePath = $this->getBasePathForAbsolutePath($rootPath);
//            var_dump($path);
//            var_dump($rootPath);
//            var_dump($basePath);
            $basePath = dirname($path);

            if ($fileinfo->getFilename() == '..' || $fileinfo->getFilename() == '.') {
                continue;
            }

            if ($fileinfo->isFile() && in_array($fileinfo->getExtension(), ['jpg', 'png', 'gif'])) {
                $files[] = $fileinfo->getFilename();

                $element = new Element('image');
                $thumb = $this->getThumbnail($fileinfo->getPathname());
                $element->attr('src', '/cache/' . $thumb);

                $label = new Element('label');
                $label->attr('for', 'cbx-iferlay');
                $label->appendChild($element);

                $link = new Element('a');
//                $link->attr('href', "/$base_path/" . $fileinfo->getFilename());
                $link->attr('href', "/watch/$basePath/" . $fileinfo->getFilename());
                $link->attr('target', 'iferlay');
                $link->appendChild($label);

                $document->appendChild($link);
            } elseif ($fileinfo->isDir()) {
                $folders[] = $fileinfo->getPathname();
            }
        }
        $this->listDirectories($folders);
        echo $document->html();
    }

}
