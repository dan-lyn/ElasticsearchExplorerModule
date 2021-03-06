<?php

namespace ElasticsearchExplorer;

return array(
    'controllers' => array(
        'factories' => array(
            'ElasticsearchExplorer\Controller\ElasticsearchExplorer' => 'ElasticsearchExplorer\Controller\Factory\ElasticsearchExplorerControllerFactory',
        ),
    ),
    'router' => array(
        'routes' => array(
            'elasticsearchexplorer' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/elasticsearchexplorer',
                    'defaults' => array(
                        'controller' => 'ElasticsearchExplorer\Controller\ElasticsearchExplorer',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'search' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/search',
                            'defaults' => array(
                                'action' => 'search',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'searchindex' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/[:searchindex][/:searchtype][/:searchfield][/:searchterm]',
                                    'defaults' => array(
                                        'searchindex' => '',
                                        'searchtype' => '',
                                        'searchfield' => '',
                                        'searchterm' => '',
                                    ),
                                    'constraints' => array(
                                        'searchindex' => '[a-zA-Z0-9_\-]+',
                                        'searchtype' => '[a-zA-Z0-9_\-]+',
                                        'searchfield' => '[a-zA-Z0-9_\-,]+',
                                        'searchterm' => '[a-zA-Z0-9_\-]+',
                                    ),
                                    'defaults' => array(
                                        'action' => 'search',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'config' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/config',
                            'defaults' => array(
                                'action' => 'config',
                            ),
                        ),
                    ),
                    'plugins' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/plugins',
                            'defaults' => array(
                                'action' => 'plugins',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'elasticsearchexplorer/layout' => __DIR__.'/../view/layout/layout.phtml',
        ),
        'template_path_stack' => array(
            'elasticsearchexplorer' => __DIR__.'/../view',
        ),
    ),
    'module_layouts' => array(
        'ElasticsearchExplorer' => 'elasticsearchexplorer/layout',
    ),
    'translator' => array(
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
                'text_domain' => __NAMESPACE__,
            ),
        ),
    ),
);
