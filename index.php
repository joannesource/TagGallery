<?php
// php -S localhost:8080 index.php
require __DIR__ . '/vendor/autoload.php';

$path = pathinfo($_SERVER["SCRIPT_FILENAME"]);

if ($path["extension"] == "jpg") {
    header("Content-Type: image/jpg");
    readfile($_SERVER["SCRIPT_FILENAME"]);
    return;
}
if ($path["extension"] == "css") {
    header("Content-Type: text/css");
    readfile($_SERVER["SCRIPT_FILENAME"]);
    return;
}

/**
 * Available routes.
 */
class ExampleRoutes {

    const HOME = '/tagger';
    const FRAME_TAGGED = '/tagger/tagged/(.*)';
    const FRAME_TAGS = '/tagger/tags/(.*)';
    const FRAME_INPUT = '/tagger/input/(.*)';
    const ADD = '/tagger/add/([a-z0-9_\-]+)/(.*)';
    const POST_ADD = '/tagger/add';
    const REMOVE = '/tagger/remove/([a-z0-9_\-]+)/(.*)';
    const ADD_TPL = '/tagger/add/:tag/:id';
    const REMOVE_TPL = '/tagger/remove/:tag/:id';
    const FRAME_TAGGED_TPL = '/tagger/tagged/:id';
    const FRAME_TAGS_TPL = '/tagger/tags/:id';
    const FRAME_INPUT_TPL = '/tagger/input/:id';
    const FILTER_TAG = '/tag/([a-z0-9_\-]+)';

}

/**
 * Demo storage.
 */
$db = new \Iframous\JsonStorage(__DIR__);

if (!$db->isPopulated()) {
    $db->populate('tags', []); // string
    $db->populate('tagged', []); // id -->> array<string>
    $db->populate('reverse_tagged', []); // string -->> array<id>
    $db->populate('objects', []); // id -->> path
}

/**
 * Tagger configuration.
 */
$conf = new \Iframous\TaggerConf;
$conf->setAddUrlTpl(ExampleRoutes::ADD_TPL);
$conf->setRemoveUrlTpl(ExampleRoutes::REMOVE_TPL);
$conf->setPostAddRoute(ExampleRoutes::POST_ADD);
$conf->setSelectableElements($db->getAll('tags', []));
$conf->setSelectedElements([]);
$conf->setTaggedFrameUrl(ExampleRoutes::FRAME_TAGGED_TPL);
$conf->setTagsFrameUrl(ExampleRoutes::FRAME_TAGS_TPL);
$conf->setInputFrameUrl(ExampleRoutes::FRAME_INPUT_TPL);
$conf->setViewUrl('/gallery?tag=:tag');

if (isset($_GET['edit'])) {
    $currentMode = (int) $db->get('conf', 'edit');
    if ($currentMode !== (int) $_GET['edit']) {
        $db->set('conf', 'edit', (int) $_GET['edit']);
    }
    $conf->setEditionMode((int) $_GET['edit']);
} else {
    $conf->setEditionMode($db->get('conf', 'edit', 0));
}

$conf->setViewModeTarget('gallery');

$tagger = new \Iframous\Tagger($conf);


function pr($a){print_r($a);}
    
/* Request routing */

// Create a Router
$router = new \Iframous\ExampleRouter;

// Custom 404 Handler
$router->set404(function () {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo '404, route not found!';
});

// Before Router Middleware
$router->before('GET', '/.*', function () {
    header('X-Powered-By: bramus/router');
});

$router->get('/watch/(.*)', function ($image_path) use(&$tagger, &$conf) {
    ?>
    <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="/css/style.css">
        </head>
        <body class="show-body">
            <a href="?edit=<?= $conf->isEditionMode() ? 0 : 1 ?>" accesskey="e">
                <?= $conf->isEditionMode() ? 'Switch to View Mode' : 'Switch to edition mode' ?>
            </a>
            </br>

            <img src="/<?= $image_path ?>" style="max-width: 100%;max-height: 350px;"/>
            <?php $tagger->show($image_path); ?>
        </body>
    </html>
    <?php
});

$router->get('/', function () use(&$tagger) {
    ?>
    <html>
        <head>
            <link rel="stylesheet" href="/css/style.css">
        </head>
        <body>
            <iframe src="/gallery" style="width: 60%;height:100%;" name="gallery"></iframe>

            <input id="bigger" type="checkbox" name="bigger" value="bigger" accesskey="f">
            <div id="watch" class="watch">
                <label for="cbx-iferlay">Close</label>
                <!--<input id="cbx-iferlay" name="cbx-iferlay" type="checkbox" checked="true"/>-->
                <iframe id="iferlay" name="iferlay" src=""></iframe>
            </div>
            <input id="navshow" type="checkbox" name="navshow" value="navshow" accesskey="d">
            <div id="navigator">
                <label for="cbx-navigator">Close</label>
                <!--<iframe id="iferlaynav" name="iferlaynav" src=""></iframe>-->
            </div>
        </body>
    </html>
    <?php
});

$router->get('/gallery', function () use(&$tagger, &$db) {
    $conf = new \TagGallery\ViewerConf();
    $conf->setCache(__DIR__ . '/cache');
    $conf->setRoots([
        //    __DIR__ . '/root1',
        __DIR__ . '/wallpapers-master',
    ]);

    $folder = !empty($_GET['folder']) ? $_GET['folder'] : null;
    $gallery = new \TagGallery\Viewer($conf, $folder);
    $gallery->setBasePath('/gallery');

    if (!empty($_GET['tag'])) {
        $proper_name = str_replace('-', ' ', $_GET['tag']);
        $res = $db->get('reverse_tagged', $proper_name);
        $gallery->setInjectedFiles($res);
    }elseif(is_null ($folder)){
        $allTags = $db->getAll('tags');
        $tagCount = [];
        foreach ($allTags as $tag) {
            $tagCount[$tag] = $db->getCount('reverse_tagged', $tag);
        }
    }
    ?>

    <html>
        <head>
            <link rel="stylesheet" href="/css/style.css">
        </head>
        <body>
            <div class="list">
                <?php $gallery->view(); ?>
                <?php if(isset($allTags)): ?>
            </div>
            <div class="list tags">
                    <?php foreach($allTags as $tag): ?>
                        <a href="/gallery?tag=<?= $tag ?>" class="folder tag"><?= $tag . ' ('.$tagCount[$tag].')' ?></a>
                    <?php endforeach ?>
                <?php endif ?>
            </div>

        </body>
    </html>
    <?php
});

/* Tagger */
$router->get(ExampleRoutes::FRAME_TAGGED, function ($id) use(&$tagger, &$conf, &$db) {
    ?>
    <head>
        <link rel="stylesheet" href="/css/style.css">
    </head><body class="iframed">
        <?php
        echo 'Tagged: ';
        $conf->setSelectedElements($db->get('tagged', $id));
        $tagger->showSelectedTags($id);
        ?></body>
    <?php
});

$router->get(ExampleRoutes::FRAME_TAGS, function ($id) use(&$tagger) {
    ?>
    <head>
        <link rel="stylesheet" href="/css/style.css">
    </head><body class="iframed"><?php
        echo 'All tags: ';
        $tagger->showSelectableTags($id);
        ?></body><?php
});

$router->get(ExampleRoutes::FRAME_INPUT, function ($id) use(&$tagger) {
    ?>
    <head>
        <link rel="stylesheet" href="/css/style.css">
    </head><body class="iframed"><?php
        echo 'New tag :';
        $tagger->showNewTagInput($id);
        ?></body><?php
});

$router->get(ExampleRoutes::ADD, function ($name, $id) use(&$db) {
    $proper_name = str_replace('-', ' ', $name);
    echo "$proper_name - $id";

    $db->append('tagged', $id, $proper_name);
    $db->append('reverse_tagged', $proper_name, $id);
    header('location: ' . str_replace(':id', $id, ExampleRoutes::FRAME_TAGGED_TPL));
});

$router->get(ExampleRoutes::REMOVE, function ($name, $id) use(&$db) {
    $proper_name = str_replace('-', ' ', $name);
    $db->removeVal('tagged', $id, $proper_name);
    $db->removeVal('reverse_tagged', $proper_name, $id);
    header('location: ' . str_replace(':id', $id, ExampleRoutes::FRAME_TAGGED_TPL));
});
$router->post(ExampleRoutes::POST_ADD, function() use(&$db) {
    $proper_name = $_POST['tag'];
    $id = $_POST['id'];
    $db->appendVal('tags', $proper_name);
    $db->append('tagged', $id, $proper_name);
    $db->append('reverse_tagged', $proper_name, $id);
    header('location: ' . str_replace(':id', $id, ExampleRoutes::FRAME_TAGGED_TPL));
});

$router->get(ExampleRoutes::FILTER_TAG, function ($name) use(&$db) {
    $proper_name = str_replace('-', ' ', $name);
    $res = $db->get('reverse_tagged', $proper_name);
    print_r($res);
});

$router->run();


