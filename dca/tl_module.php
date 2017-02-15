<?php

$GLOBALS['TL_DCA']['tl_module']['config']['onsubmit_callback'][] = [ 'CatalogManager\tl_module', 'generateGeoCords' ];
$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = [ 'CatalogManager\tl_module', 'disableNotRequiredFields' ];

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseMap';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogStoreFile';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseViewPage';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseRelation';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogAddMapInfoBox';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseMasterPage';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogAllowComments';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseRadiusSearch';

$GLOBALS['TL_DCA']['tl_module']['palettes']['catalogUniversalView'] = '{title_legend},name,headline,type;{catalog_legend},catalogTablename;{catalogView_legend:hide},catalogUseViewPage;{catalogTaxonomy_legend},catalogEnableParentFilter;{catalogMap_legend:hide},catalogUseMap;{orderBy_legend:hide},catalogOrderBy,catalogRandomSorting;{pagination_legend:hide},catalogAddPagination,catalogPerPage,catalogOffset;{master_legend:hide},catalogUseMasterPage,catalogMasterTemplate,catalogPreventMasterView;{join_legend:hide},catalogJoinFields,catalogJoinParentTable;{relation_legend:hide},catalogUseRelation;{frontend_editing_legend:hide},tableless,disableCaptcha,catalogNoValidate,catalogEnableFrontendPermission,catalogFormTemplate,catalogStoreFile,catalogItemOperations,catalogFormRedirect;{radiusSearch_legend:hide},catalogUseRadiusSearch;{catalog_comments_legend:hide},catalogAllowComments;{template_legend:hide},catalogTemplate,customTpl;{protected_legend:hide:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseViewPage'] = 'catalogViewPage';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseMasterPage'] = 'catalogMasterPage';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseRelation'] = 'catalogRelatedChildTables';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogAddMapInfoBox'] = 'catalogMapInfoBoxContent';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogStoreFile'] = 'catalogUploadFolder,catalogUseHomeDir,catalogDoNotOverwrite';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseRadiusSearch'] = 'catalogFieldLat,catalogFieldLng,catalogRadioSearchCountry,catalogRadioSearchZoomFactor';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogAllowComments'] = 'com_template,catalogCommentSortOrder,catalogCommentPerPage,catalogCommentModerate,catalogCommentBBCode,catalogCommentRequireLogin,catalogCommentDisableCaptcha';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseMap'] = 'catalogMapAddress,catalogMapLat,catalogMapLng,catalogFieldLat,catalogFieldLng,catalogMapViewTemplate,catalogMapTemplate,catalogMapZoom,catalogMapType,catalogMapScrollWheel,catalogMapMarker,catalogAddMapInfoBox,catalogMapStyle';

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogTablename'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogTablename'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'mandatory' => true,
        'doNotCopy' => true,
        'submitOnChange' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption'=>true,
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogs' ],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseViewPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseViewPage'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogViewPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogViewPage'],
    'inputType' => 'pageTree',

    'eval' => [

        'tl_class' => 'clr',
        'mandatory' => true,
        'fieldType' => 'radio',
    ],

    'foreignKey' => 'tl_page.title',

    'relation' => [

        'load' => 'lazy',
        'type' => 'hasOne'
    ],

    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogTemplate'],
    'inputType' => 'select',
    'default' => 'catalog_teaser',

    'eval' => [

        'chosen' => true,
        'maxlength' => 32,
        'tl_class' => 'w50',
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogTemplates' ],

    'exclude' => true,
    'sql' => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogPreventMasterView'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogPreventMasterView'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12',
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseMasterPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseMasterPage'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMasterPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMasterPage'],
    'inputType' => 'pageTree',

    'eval' => [

        'tl_class' => 'clr',
        'mandatory' => true,
        'fieldType' => 'radio',
    ],

    'foreignKey' => 'tl_page.title',

    'relation' => [

        'load' => 'lazy',
        'type' => 'hasOne'
    ],

    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMasterTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMasterTemplate'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 32,
        'tl_class' => 'w50',
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogTemplates' ],

    'exclude' => true,
    'sql' => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogOrderBy'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogOrderBy'],
    'inputType' => 'catalogOrderByWizard',

    'eval' => [

        'chosen' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption'=>true,
        'orderByTablename' => null,
        'orderByOptionsCallback' => [ 'CatalogManager\tl_module', 'getOrderByItems' ],
        'fieldOptionsCallback' => [ 'CatalogManager\tl_module', 'getSortableCatalogFieldsByTablename' ]
    ],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogRandomSorting'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogRandomSorting'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogAddPagination'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogAddPagination'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogOffset'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogOffset'],
    'inputType' => 'text',
    'default' => 1000,

    'eval' => [

        'rgxp'=>'natural',
        'tl_class'=>'w50'
    ],

    'exclude' => true,
    'sql' => "smallint(5) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogPerPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogPerPage'],
    'inputType' => 'text',
    'default' => 0,

    'eval' => [

        'rgxp'=>'natural',
        'tl_class'=>'w50'
    ],

    'exclude' => true,
    'sql' => "smallint(5) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogJoinFields'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogJoinFields'],
    'inputType' => 'checkbox',

    'eval' => [

        'multiple' => true,
        'maxlength' => 255,
        'tl_class' => 'w50',
        'doNotCopy' => true,
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getJoinAbleFields' ],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogJoinParentTable'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogJoinParentTable'],
    'inputType' => 'checkbox',

    'eval' => [

        'doNotCopy' => true,
        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseRelation'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseRelation'],
    'inputType' => 'checkbox',

    'eval' => [

        'submitOnChange' => true,
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogRelatedChildTables'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogRelatedChildTables'],
    'inputType' => 'catalogRelationWizard',

    'eval' => [

        'doNotCopy' => true
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getChildTablesByTablename' ],
    
    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogEnableParentFilter'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogEnableParentFilter'],
    'inputType' => 'checkbox',

    'eval' => [],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFormTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFormTemplate'],
    'inputType' => 'select',
    'default' => 'form_catalog_default',

    'eval' => [

        'chosen' => true,
        'maxlength' => 32,
        'tl_class' => 'w50 clr',
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogFormTemplates' ],

    'exclude' => true,
    'sql' => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogItemOperations'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogItemOperations'],
    'inputType' => 'checkbox',
    'default' => 'form_catalog_default',

    'eval' => [

        'multiple' => true,
        'maxlength' => 256,
        'tl_class' => 'w50 clr',
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogOperationItems' ],

    'exclude' => true,
    'sql' => "varchar(256) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogStoreFile'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogStoreFile'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUploadFolder'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUploadFolder'],
    'inputType' => 'fileTree',

    'eval' => [

        'fieldType' => 'radio',
        'tl_class' => 'clr',
        'mandatory' => true
    ],

    'exclude' => true,
    'sql' => "binary(16) NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseHomeDir'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseHomeDir'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogDoNotOverwrite'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogDoNotOverwrite'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogNoValidate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogNoValidate'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['disableCaptcha']['eval']['tl_class'] = 'w50';

$GLOBALS['TL_DCA']['tl_module']['fields']['tableless']['eval']['tl_class'] = 'w50';

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFormRedirect'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFormRedirect'],
    'inputType' => 'pageTree',

    'eval' => [

        'tl_class' => 'w50',
        'fieldType' => 'radio',
    ],

    'foreignKey' => 'tl_page.title',

    'relation' => [

        'load' => 'lazy',
        'type' => 'hasOne'
    ],

    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogEnableFrontendPermission'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogEnableFrontendPermission'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogAllowComments'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogAllowComments'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCommentPerPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCommentPerPage'],
    'inputType' => 'text',

    'eval' => [

        'rgxp' => 'natural',
        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "smallint(5) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCommentSortOrder'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCommentSortOrder'],
    'inputType' => 'select',
    'default' => 'ascending',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'options' => [ 'ascending', 'descending' ],

    'reference' => &$GLOBALS['TL_LANG']['MSC'],

    'exclude' => true,
    'sql' => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCommentModerate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCommentModerate'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCommentBBCode'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCommentBBCode'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCommentRequireLogin'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCommentRequireLogin'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCommentDisableCaptcha'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCommentDisableCaptcha'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseMap'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseMap'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapAddress'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapAddress'],
    'inputType' => 'text',

    'eval' => [

        'tl_class' => 'long',
    ],

    'exclude' => true,
    'sql' => "varchar(256) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapLat'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapLat'],
    'inputType' => 'text',

    'eval' => [

        'tl_class' => 'w50',
    ],

    'exclude' => true,
    'sql' => "varchar(256) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapLng'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapLng'],
    'inputType' => 'text',

    'eval' => [

        'tl_class' => 'w50',
    ],

    'exclude' => true,
    'sql' => "varchar(256) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFieldLat'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFieldLat'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'mandatory' => true,
        'tl_class' => 'w50',
        'doNotCopy' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogFieldsByTablename' ],

    'exclude' => true,
    'sql' => "char(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFieldLng'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFieldLng'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'mandatory' => true,
        'tl_class' => 'w50',
        'doNotCopy' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogFieldsByTablename' ],

    'exclude' => true,
    'sql' => "char(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapViewTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapViewTemplate'],
    'inputType' => 'select',
    'default' => 'map_catalog_default',

    'eval' => [

        'chosen' => true,
        'maxlength' => 255,
        'tl_class' => 'w50',
        'mandatory' => true
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getMapViewTemplates' ],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapTemplate'],
    'inputType' => 'select',
    'default' => 'map_catalog_default',

    'eval' => [

        'chosen' => true,
        'maxlength' => 255,
        'tl_class' => 'w50',
        'mandatory' => true
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getMapTemplates' ],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapZoom'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapZoom'],
    'inputType' => 'select',
    'default' => 10,

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50'
    ],

    'options' => [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20 ],

    'exclude' => true,
    'sql' => "smallint(5) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapType'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapType'],
    'inputType' => 'select',
    'default' => 'HYBRID',

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50'
    ],

    'options' => [ 'ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN' ],

    'reference' => &$GLOBALS['TL_LANG']['tl_module']['reference']['catalogMapType'],

    'exclude' => true,
    'sql' => "varchar(16) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapScrollWheel'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapScrollWheel'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapMarker'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapMarker'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapStyle'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapStyle'],
    'inputType' => 'textarea',

    'eval' => [

        'tl_class' => 'clr',
        'rte' => 'ace|html',
        'allowHtml' => true
    ],

    'exclude' => true,
    'sql' => "text NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogAddMapInfoBox'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogAddMapInfoBox'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapInfoBoxContent'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapInfoBoxContent'],
    'inputType' => 'textarea',

    'eval' => [

        'rte' => 'ace|html',
        'tl_class' => 'clr',
        'allowHtml' => true
    ],

    'exclude' => true,
    'sql' => "text NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseRadiusSearch'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseRadiusSearch'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogRadioSearchCountry'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogRadioSearchCountry'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'doNotCopy' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getSystemCountries' ],

    'exclude' => true,
    'sql' => "char(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogRadioSearchZoomFactor'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogRadioSearchZoomFactor'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];