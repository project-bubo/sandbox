<?php
/* version Virgin1 */

use Nette\Diagnostics\Debugger,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\SimpleRouter;

//ini_set('max_execution_time', 6);


// Load Nette Framework
require LIBS_DIR . '/autoload.php';

// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
$configurator->setDebugMode();
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR)
    //->addDirectory(PLUGINS_DIR)
	->register();


//Extras\Debug\ComponentTreePanel::register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(CONFIG_DIR . '/config.neon');
//$configurator->addConfig(CONFIG_DIR . '/config2.neon');


$loader = new Nette\Config\Loader;
$serverName = $_SERVER['SERVER_NAME'];

//dump($serverName);
//die();

$selectedModuleNs = NULL;
$selectedHost = NULL;
$selectedProjectPath = NULL;
$selectedFile = NULL;
// detect project
foreach (Nette\Utils\Finder::findDirectories('*Module')->in(APP_DIR.'/FrontModule') as $projectPath => $file) {
    // read project config
    $projectFile = $projectPath . '/config/project.neon';
    $configFile = $projectPath . '/config/config.neon';

    //dump($file->getBaseName());

    if (is_file($projectFile) && is_file($configFile)) {
        $c = $loader->load($projectFile);

        foreach ($c['modules'] as $moduleNs => $moduleData) {

            foreach ($moduleData['hosts'] as $host) {
                if (preg_match("#.*$serverName\/.*#", $host)) {
                    $selectedModuleNs = $moduleNs;
                    $selectedHost = $host;
                    $selectedProjectPath = $projectPath;
                    $selectedFile = $file;
                    break;
                }
            }

            if ($selectedModuleNs !== NULL) {
                $configurator->addConfig($configFile);
                break;
            }
        }

    }

    if ($selectedModuleNs !== NULL) break;

}

//dump($selectedModuleNs);
//die();

if ($selectedProjectPath === NULL) {
    throw new \Nette\InvalidStateException('The project does not exist!');
}

$params = array(
            'projectName'    => $selectedFile->getBaseName(),
            'projectDir'    =>  $selectedProjectPath
);
$configurator->addParameters($params);

$container = $configurator->createContainer();

//dump($container->parameters);
//die();

// Translator
$container->getService("translator")->addFile("%appDir%/lang/","core"); // add block homepage to (app/lang/{lang}.homepage.mo
//$configurator->container->getService("translator")->addFile("%appDir%/lang/","about"); // add another block, if you need to separate particular pages
NetteTranslator\Panel::register($container, $container->translator);


// Setup router using mod_rewrite detection
if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {

    $dashedCamel = array(
                            Route::FILTER_IN => array('Helpers\Inflectors', 'dash2camel'),
                            Route::FILTER_OUT => array('Helpers\Inflectors', 'camel2dash')
                        );

	$container->router = $router = new RouteList;
	$router[] = new Route('index.php', 'Admin:Default:default', Route::ONE_WAY);

    // CRON ROUTES
    $router[] = $cronRouter = new RouteList('Cron');
	$cronRouter[] = new Route('cron', 'Cron:default');


    // ADMIN ROUTES
	$router[] = $adminRouter = new RouteList('Admin');
    $adminRouter[] = new Route("[<lang [a-z]{2}>/]admin/plugin-interpreter/<plugin>/<view>",
                array(
                   'presenter'  =>  'Plugin',
                   'action'     =>  'interpret',
                   'plugin'     =>  $dashedCamel,
                   'view'       =>  $dashedCamel
                   )
    );

    $adminRouter[] = new Route("admin/copy-layout", 'Default:copyLayout');
    $adminRouter[] = new Route("admin/remove-pages", 'Default:removePages');
    $adminRouter[] = new Route("admin/repair-urls", 'Default:repairUrls');

    $adminRouter[] = new Route("show-draft/<url>", array(

                                                           'presenter' =>  'Default',
                                                           'action'    =>  'showDraft',
                                                           'url'       =>  array(
                                                                                Route::FILTER_OUT => NULL
                                                                            )
                                                           )
    );


    $adminRouter[] = new Route("[<lang [a-z]{2}>/]admin/<presenter>/<action>[/<id>]","Default:default");



	$router[] = $frontRouter = new RouteList('Front');



    $frontRouter[] = new Route("file/<url>",array(
        'presenter'=>'Image',
        'action'=>'default'
    ));
    $frontRouter[] = new Route("file/<action>",array(
        'presenter'=>'Image',
        'action'=>'default'
    ));
    $frontRouter[] = new Route("thumb/<name>",array(
        'presenter'=>'Image',
        'action'=>'thumbnail',
        'filename'=>array(Route::FILTER_OUT => NULL),
        'name'=>NULL,
        'lang'=>NULL
    ));


    $selectedModule = $selectedModuleNs;
    // make from ns module name
    $slashPos = strrpos($selectedModuleNs, '/', -1);
    if ($slashPos !== FALSE) {
        $selectedModule = substr($selectedModuleNs, $slashPos+1);
    }

    $m = explode('/', $selectedModuleNs);
    array_shift($m);


    if ($selectedModuleNs !== NULL) {

        $frontRouter[] = new Route("//".$selectedHost."[<lang [a-z]{2}>/][<url>]", array(
                                            //'module'    =>  $selectedModule,
                                            'module'    =>  implode(':', $m),
                                            'presenter' =>  'Default',
                                            'action'    =>  'default',
                                            'url'       =>  array(
                                                                Route::FILTER_OUT => NULL,
                                                                Route::PATTERN => ".*"
                                            )
        ));

    }


} else {
	$container->router = new SimpleRouter('Admin:Default:default');
}





\Kdyby\Forms\Containers\Replicator::register();

SimpleProfiler\Profiler::register();
//Extras\Debug\ComponentTreePanel::$dumps = FALSE;
//Extras\Debug\ComponentTreePanel::register();

//$container->application->catchExceptions = FALSE;
\dibi::setConnection($container->database);
MultipleFileUpload::register();
MultipleFileUpload::getUIRegistrator()
    ->clear()
//    ->register("MFUUIHTML4SingleUpload")
    ->register("MFUUIPlupload");
////            ->register("MFUUISwfupload");
////            ->register("MFUUIUploadify");
//
//// Optional step: register driver
//// As default driver is used Sqlite driver
//// @see http://addons.nettephp.com/cs/multiplefileupload#toc-drivery
//// When you want to use other driver use something like this:
if(class_exists("Dibi", true)) {
    // dibi is already connected
    MultipleFileUpload::setQueuesModel(new MFUQueuesDibi());
    MultipleFileUpload::setLifeTime(3600); // 1hour for temporarily uploaded files
}

/**
 * Extension method for FormContainer
 */
function FormContainer_addMediaFile(/*\Nette\Application\UI\Form*/ $_this, $name, $label = NULL) {
  return $_this[$name] = new \Bubo\MediaFileInput($label);
//    echo "mediaFile je připojeny";
//    die();
}


\Nette\Forms\Container::extensionMethod("\Nette\Forms\Container::addMediaFile", "FormContainer_addMediaFile");

// Run the application!
$container->application->run();

