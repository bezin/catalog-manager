<?php

namespace CatalogManager;

class FrontendEditing extends CatalogController {


    public $strAct = '';
    public $strItemID = '';
    public $arrOptions = [];
    public $strTemplate = '';

    protected $arrValues = [];
    protected $arrCatalog = [];
    protected $arrPalettes = [];
    protected $strRedirectID = '';
    protected $objTemplate = null;
    protected $strSubmitName = '';
    protected $blnNoSubmit = false;
    protected $blnHasUpload = false;
    protected $arrCatalogFields = [];
    protected $arrPaletteLabels = [];
    protected $arrCatalogAttributes = [];


    public function __construct() {

        parent::__construct();

        $this->import( 'CatalogEvents' );
        $this->import( 'CatalogMessage' );
        $this->import( 'SQLQueryHelper' );
        $this->import( 'SQLQueryBuilder' );
        $this->import( 'DCABuilderHelper' );
        $this->import( 'CatalogFineUploader' );
        $this->import( 'I18nCatalogTranslator' );
    }


    public function initialize() {

        global $objPage;

        $this->setOptions();
        \System::loadLanguageFile('catalog_manager');

        $strPalette = 'general_legend';
        $this->strSubmitName = 'catalog_' . $this->catalogTablename;
        $arrGeneralFields = $this->DCABuilderHelper->getPredefinedFields();
        $this->arrCatalog = $this->SQLQueryHelper->getCatalogByTablename( $this->catalogTablename );
        $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogID( $this->arrCatalog['id'], function ( $arrField, $strFieldname ) use( &$strPalette ) {

            if ( $arrField['type'] == 'fieldsetStart' ) {

                $strPalette = $arrField['title'];
                $this->arrPaletteLabels[$strPalette] = $this->I18nCatalogTranslator->getLegendLabel( $arrField['title'], $arrField['label'] );
            }

            $arrField['_palette'] = $strPalette;

           return $arrField;
        });

        if ( $this->strItemID && $this->strAct && in_array( $this->strAct, [ 'copy', 'edit' ] ) ) {

            $this->setValues();
        }

        $this->arrPaletteLabels['general_legend'] = $this->I18nCatalogTranslator->getLegendLabel( 'general_legend', '' );
        $this->arrPaletteLabels['invisible_legend'] = $this->I18nCatalogTranslator->getLegendLabel( 'invisible_legend', '' );

        $this->catalogItemOperations = Toolkit::deserialize( $this->catalogItemOperations );
        $this->catalogExcludedFields = Toolkit::deserialize( $this->catalogExcludedFields );
        $this->arrCatalog['operations'] = Toolkit::deserialize( $this->arrCatalog['operations'] );

        $this->reIndexCatalogFieldsByFieldname();
        $this->arrCatalogFields = array_merge( $this->arrCatalogFields, $arrGeneralFields );
        $this->addDCFormatToCatalogFields();
        $this->setCatalogAttributes();
        $this->setPalettes();

        if ( $this->catalogFormRedirect && $this->catalogFormRedirect !== '0' ) {

            $this->strRedirectID = $this->catalogFormRedirect;
        }

        else {

            $this->strRedirectID = $objPage->id;
        }
    }


    public function render() {

        $this->objTemplate = new \FrontendTemplate( $this->strTemplate );
        $this->objTemplate->setData( $this->arrOptions );
        $arrCategories = [];

        if ( !is_array( $this->catalogExcludedFields ) ) {

            $this->catalogExcludedFields = [];
        }

        if ( !empty( $this->arrPalettes ) && is_array( $this->arrPalettes ) ) {

            foreach ( $this->arrPalettes as $strPalette => $arrFieldNames ) {

                if ( !empty( $arrFieldNames ) && is_array( $arrFieldNames ) ) {

                    $strLegend = $this->arrPaletteLabels[ $strPalette ];
                    $arrCategories[ $strLegend ] = $this->renderFieldsByPalette( $arrFieldNames, $strPalette );
                }
            }
        }

        if ( !$this->disableCaptcha ) {

            $objCaptcha = $this->getCaptcha();
            $this->objTemplate->captchaWidget = $objCaptcha->parse();
        }

        if ( !$this->blnNoSubmit && \Input::post('FORM_SUBMIT') == $this->strSubmitName  ) {

            $this->saveEntity();
        }

        $this->objTemplate->method = 'POST';
        $this->objTemplate->categories = $arrCategories;
        $this->objTemplate->formId = $this->strSubmitName;
        $this->objTemplate->submitName = $this->strSubmitName;
        $this->objTemplate->catalogAttributes = $this->arrCatalogAttributes;
        $this->objTemplate->action = \Environment::get( 'indexFreeRequest' );
        $this->objTemplate->message = $this->CatalogMessage->get( $this->id );
        $this->objTemplate->attributes = $this->catalogNoValidate ? 'novalidate' : '';
        $this->objTemplate->submit = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['submit'];
        $this->objTemplate->captchaLabel = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['captchaLabel'];
        $this->objTemplate->enctype = $this->blnHasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';

        return $this->objTemplate->parse();
    }


    protected function setCatalogAttributes() {

        if ( !empty( $this->arrCatalogFields ) && is_array( $this->arrCatalogFields  ) ) {

            $this->arrCatalogAttributes = Toolkit::parseCatalogValues( $this->arrValues, $this->arrCatalogFields, false );
        }
    }


    protected function renderFieldsByPalette( $arrFieldNames, $strPalette = '' ) {

        $arrReturn = [];

        foreach ( $arrFieldNames as $strFieldname ) {

            if ( in_array( $strFieldname, $this->catalogExcludedFields ) ) {

                continue;
            }

            $arrField = $this->arrCatalogFields[$strFieldname]['_dcFormat'];
            $arrField = $this->convertWidgetToField( $arrField );

            $strClass = $this->fieldClassExist( $arrField['inputType'] );
            $arrData = $strClass::getAttributesFromDca( $arrField, $strFieldname, $arrField['default'], '', '' );

            if ( $strClass == false ) continue;
            if ( is_bool( $arrField['_disableFEE'] ) && $arrField['_disableFEE'] == true ) continue;

            if ( $arrField['inputType'] == 'catalogFineUploader' ) {

                $arrData['configAttributes'] = [

                    'storeFile' => $this->catalogStoreFile,
                    'useHomeDir' => $this->catalogUseHomeDir,
                    'uploadFolder' => $this->catalogUploadFolder,
                    'doNotOverwrite' => $this->catalogDoNotOverwrite
                ];
            }

            $objWidget = new $strClass( $arrData );
            $objWidget->storeValues = true;
            $objWidget->id = 'id_' . $arrField['_fieldname'];
            $objWidget->value = $this->arrValues[ $arrField['_fieldname'] ];
            $objWidget->placeholder = $arrField['_placeholder'] ? $arrField['_placeholder'] : '';

            if ( is_array( $arrField['_cssID'] ) && ( $arrField['_cssID'][0] || $arrField['_cssID'][1] ) ) {

                if ( $arrField['_cssID'][0] ) $objWidget->id = 'id_' . $arrField['_cssID'][0];
                if ( $arrField['_cssID'][1] ) $objWidget->class = ' ' . $arrField['_cssID'][1];
            }

            if ( $this->strAct == 'copy' && $arrField['eval']['doNotCopy'] === true ) {

                $objWidget->value = '';
            }

            if ( $arrField['eval']['multiple'] && $arrField['eval']['csv'] && is_string( $objWidget->value ) ) {

                $objWidget->value = explode( $arrField['eval']['csv'], $objWidget->value );
            }

            if ( $arrField['eval']['submitOnChange'] ) {

                $objWidget->addAttributes([ 'onchange' => 'this.form.submit()' ]);
            }

            if ( $arrField['inputType'] == 'upload' || $arrField['inputType'] == 'catalogFineUploader' ) {

                $objWidget->storeFile = $this->catalogStoreFile;
                $objWidget->useHomeDir = $this->catalogUseHomeDir;
                $objWidget->maxlength = $arrField['eval']['maxsize'];
                $objWidget->multiple = $arrField['eval']['multiple'];
                $objWidget->uploadFolder = $this->catalogUploadFolder;
                $objWidget->extensions = $arrField['eval']['extensions'];
                $objWidget->doNotOverwrite = $this->catalogDoNotOverwrite;
                $objWidget->preview = $this->arrCatalogAttributes[ $strFieldname ];
                $this->blnHasUpload = true;
            }

            if ( $arrField['inputType'] == 'textarea' && isset( $arrField['eval']['rte'] ) ) {

                $objWidget->mandatory = false;
                $arrTextareaData = [ 'selector' => 'ctrl_' . $objWidget->id ];

                if ( version_compare( VERSION, '4.0', '>=' ) ) {

                    $strTemplate = 'be_' . $arrField['eval']['rte'];
                }

                else {

                    $strTemplate = 'ctlg_catalog_tinyMCE';
                    $arrTextareaData['tinyMCE'] = TL_ROOT . '/' . 'system/config/' . $arrField['eval']['rte'] . '.php';
                }

                $objScript = new \FrontendTemplate( $strTemplate );
                $objScript->setData( $arrTextareaData );
                $strScript = $objScript->parse();

                $GLOBALS['TL_HEAD'][] = $strScript;
            }

            if ( Toolkit::isEmpty( $objWidget->value ) && !Toolkit::isEmpty( $arrField['default'] ) ) {

                $objWidget->value = $arrField['default'];
            }

            if ( $arrField['eval']['rgxp'] && in_array( $arrField['eval']['rgxp'], [ 'date', 'time', 'datim' ] ) ) {

                $strDateFormat = \Date::getFormatFromRgxp( $arrField['eval']['rgxp'] );
                $objWidget->value = $objWidget->value ? \Date::parse( $strDateFormat, $objWidget->value ) : '';
            }

            if ( in_array( $arrField['_type'], [ 'hidden', 'date' ] ) && $this->arrCatalogFields[ $arrField['_fieldname'] ]['tstampAsDefault'] ) {

                if ( Toolkit::isEmpty( $objWidget->value ) ) {

                    $objWidget->value = time();
                }
            }

            $objWidget->catalogAttributes = $this->arrCatalogAttributes;

            if ( \Input::post('FORM_SUBMIT') == $this->strSubmitName ) {

                $objWidget->validate();
                $varValue = $objWidget->value;

                if ( $varValue && is_string( $varValue ) ) {

                    $varValue = $this->decodeValue( $varValue );
                    $varValue = $this->replaceInsertTags( $varValue );
                }

                if ( $varValue != '' && in_array( $arrField['eval']['rgxp'], [ 'date', 'time', 'datim' ] ) ) {

                    try {

                        $objDate = new \Date( $varValue, \Date::getFormatFromRgxp( $arrField['eval']['rgxp'] ) );
                        $varValue = $objDate->tstamp;

                    } catch ( \OutOfBoundsException $objError ) {

                        $objWidget->addError( sprintf( $GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue ) );
                    }
                }

                if ( $arrField['eval']['unique'] && $varValue != '' && !$this->SQLQueryHelper->SQLQueryBuilder->Database->isUniqueValue( $this->catalogTablename, $strFieldname, $varValue, ( $this->strAct == 'edit' ? $this->strItemID : null ) ) ) {

                    $objWidget->addError( sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrField['label'][0] ?: $strFieldname ) );
                }

                if ( $objWidget->submitInput() && !$objWidget->hasErrors() && is_array( $arrField['save_callback'] ) ) {

                    foreach ( $arrField['save_callback'] as $arrCallback ) {

                        try {

                            if ( is_array( $arrCallback ) ) {

                                $this->import( $arrCallback[0] );
                                $varValue = $this->{$arrCallback[0]}->{$arrCallback[1]}( $varValue, null );
                            }

                            elseif( is_callable( $arrCallback ) ) {

                                $varValue = $arrCallback( $varValue, null );
                            }
                        }

                        catch ( \Exception $objError ) {

                            $objWidget->class = 'error';
                            $objWidget->addError( $objError->getMessage() );
                        }
                    }
                }

                if ( $strFieldname == 'alias' ) {

                    $objDCACallbacks = new DCACallbacks();
                    $varValue = $objDCACallbacks->generateFEAlias( $varValue, $this->arrValues['title'], $this->catalogTablename, $this->arrValues['id'], $this->id );
                }

                if ( $objWidget->hasErrors() ) {

                    $this->blnNoSubmit = true;
                }

                elseif( $objWidget->submitInput() ) {

                    if ( $varValue === '' ) {

                        $varValue = $objWidget->getEmptyValue();
                    }

                    if ( $arrField['eval']['encrypt'] ) {

                        $varValue = \Encryption::encrypt( $varValue );
                    }

                    $this->arrValues[$strFieldname] = $varValue;
                }

                $arrFiles = $_SESSION['FILES'];

                if ( isset( $arrFiles[$strFieldname] ) && is_array( $arrFiles[$strFieldname] ) && $this->catalogStoreFile ) {

                    if ( !Toolkit::isAssoc( $arrFiles[$strFieldname] ) ) {

                        $arrUUIDValues = [];

                        foreach ( $arrFiles[$strFieldname] as $arrFile ) {

                            $arrUUIDValues[] = $this->getFileUUID( $arrFile );
                        }

                        $strUUIDValue = serialize( $arrUUIDValues );
                    }

                    else {

                        $strUUIDValue = $this->getFileUUID( $arrFiles[$strFieldname] );
                    }

                    $this->arrValues[$strFieldname] = $strUUIDValue;

                    unset( $_SESSION['FILES'][$strFieldname] );
                }
            }

            $arrReturn[] = $objWidget->parse();
        }

        return $arrReturn;
    }


    public function isVisible() {

        if ( !\Input::get( 'auto_item' ) || !$this->catalogTablename ) {

            return false;
        }

        $arrQuery = [

            'table' => $this->catalogTablename,

            'where' => [

                [
                    [
                        'field' => 'id',
                        'operator' => 'equal',
                        'value' => \Input::get( 'auto_item' )
                    ],

                    [
                        'field' => 'alias',
                        'operator' => 'equal',
                        'value' => \Input::get( 'auto_item' )
                    ]
                ]
            ]
        ];

        if ( is_array( $this->arrCatalog['operations'] ) && in_array( 'invisible', $this->arrCatalog['operations']  ) && !BE_USER_LOGGED_IN ) {

            $dteTime = \Date::floorToMinute();

            $arrQuery['where'][] = [

                [
                    'value' => '',
                    'field' => 'start',
                    'operator' => 'equal'
                ],

                [
                    'field' => 'start',
                    'operator' => 'lte',
                    'value' => $dteTime
                ]
            ];

            $arrQuery['where'][] = [

                [
                    'value' => '',
                    'field' => 'stop',
                    'operator' => 'equal'
                ],

                [
                    'field' => 'stop',
                    'operator' => 'gt',
                    'value' => ( $dteTime + 60 )
                ]
            ];

            $arrQuery['where'][] = [

                'field' => 'invisible',
                'operator' => 'not',
                'value' => '1'
            ];
        }

        $objEntities = $this->SQLQueryBuilder->execute( $arrQuery );

        return $objEntities->numRows ? true : false;
    }


    public function checkAccess() {

        $this->import( 'FrontendEditingPermission' );

        $this->FrontendEditingPermission->blnDisablePermissions = $this->catalogEnableFrontendPermission ? false : true;
        $this->FrontendEditingPermission->initialize();

        return $this->FrontendEditingPermission->hasAccess( $this->catalogTablename );
    }


    public function checkPermission( $strMode ) {

        $this->import( 'FrontendEditingPermission' );

        if ( !is_array( $this->catalogItemOperations ) ) $this->catalogItemOperations = [];
        if ( !in_array( $strMode, $this->catalogItemOperations ) ) return false;

        $this->FrontendEditingPermission->blnDisablePermissions = $this->catalogEnableFrontendPermission ? false : true;
        $this->FrontendEditingPermission->initialize();

        if ( $strMode == 'copy' ) $strMode = 'create';

        return $this->FrontendEditingPermission->hasPermission( $strMode, $this->catalogTablename );
    }


    public function deleteEntity() {

        $this->import( 'SQLBuilder' );

        if (  $this->SQLBuilder->Database->tableExists( $this->catalogTablename ) ) {

            if ( $this->catalogNotifyDelete ) {
                $objCatalogNotification = new CatalogNotification( $this->catalogTablename, $this->strItemID );
                $objCatalogNotification->notifyOnDelete( $this->catalogNotifyDelete, [] );
            }

            $arrData = [

                'row' => [],
                'id' => $this->strItemID,
                'table' => $this->catalogTablename
            ];

            $this->CatalogMessage->set( 'deleteMessage', $arrData, $this->id );
            $this->CatalogEvents->addEventListener( 'delete', $arrData );
            $this->SQLBuilder->Database->prepare( sprintf( 'DELETE FROM %s WHERE id = ? ', $this->catalogTablename ) )->execute( $this->strItemID );
        }

        $this->redirectToFrontendPage( $this->strRedirectID );
    }


    protected function setPalettes() {

        $this->arrPalettes = [

            'general_legend' => []
        ];

        if ( !empty( $this->arrCatalogFields ) && is_array( $this->arrCatalogFields ) ) {

            foreach ( $this->arrCatalogFields as $strFieldname => $arrField ) {

                $strPalette = $arrField['_palette'];

                if ( Toolkit::isEmpty( $strPalette ) ) continue;

                if ( !is_array( $this->arrPalettes[ $strPalette ] ) ) {

                    $this->arrPalettes[ $strPalette ] = [];
                }

                $this->arrPalettes[ $strPalette ][] = $strFieldname;
            }
        }

        if ( !in_array( 'invisible', $this->arrCatalog['operations'] ) ) {

            unset( $this->arrPalettes['invisible_legend'] );
        }
    }


    protected function setOptions() {

        if ( !empty( $this->arrOptions ) && is_array( $this->arrOptions ) ) {

            foreach ( $this->arrOptions as $strKey => $varValue ) {

                $this->{$strKey} = $varValue;
            }
        }
    }


    protected function reIndexCatalogFieldsByFieldname() {

        $arrFields = [];

        if ( !empty( $this->arrCatalogFields ) && is_array( $this->arrCatalogFields ) ) {

            foreach ( $this->arrCatalogFields as $arrField ) {

                if ( Toolkit::isEmpty( $arrField['fieldname'] ) || !$this->DCABuilderHelper->isValidField( $arrField ) ) continue;

                $arrFields[ $arrField['fieldname'] ] = $arrField;
            }
        }

        $this->arrCatalogFields = $arrFields;
    }


    protected function addDCFormatToCatalogFields() {

        if ( !empty( $this->arrCatalogFields ) && is_array( $this->arrCatalogFields ) ) {

            foreach ( $this->arrCatalogFields as $strFieldname => $arrField ) {

                $arrDataContainerField = $this->DCABuilderHelper->convertCatalogField2DCA( $arrField, [], $this );
                $arrDataContainerField['_fieldname'] = $strFieldname;

                if ( $arrField['type'] == 'hidden' ) $arrDataContainerField['inputType'] = 'hidden';

                if ( $arrField['type'] == 'upload' && $arrField['useFineUploader'] ) {

                    $arrDataContainerField['inputType'] = 'catalogFineUploader';
                    $this->CatalogFineUploader->loadAssets();
                }

                $this->arrCatalogFields[$strFieldname]['_dcFormat'] = $arrDataContainerField;
            }
        }
    }


    protected function getCaptcha() {

        $arrCaptcha = [

            'id' => 'id_',
            'required' => true,
            'type' => 'captcha',
            'mandatory' => true,
            'tableless' => '1',
            'label' => $GLOBALS['TL_LANG']['MSC']['securityQuestion']
        ];

        $strClass = $GLOBALS['TL_FFL']['captcha'];

        if ( !class_exists( $strClass ) ) $strClass = 'FormCaptcha';

        $objCaptcha = new $strClass( $arrCaptcha );

        if ( \Input::post('FORM_SUBMIT') == $this->strSubmitName ) {

            $objCaptcha->validate();

            if ( $objCaptcha->hasErrors() ) $this->blnNoSubmit = true;
        }

        return $objCaptcha;
    }


    protected function convertWidgetToField( $arrField ) {

        if ( $arrField['inputType'] == 'checkboxWizard' ) {

            $arrField['inputType'] = 'checkbox';
        }

        if ( $arrField['inputType'] == 'fileTree' ) {

            $arrField['inputType'] = 'upload';
        }

        if ( $arrField['inputType'] == 'catalogMessageWidget' ) {

            $arrField['inputType'] = 'catalogMessageForm';
        }

        $arrField['eval']['tableless'] = '1';
        $arrField['eval']['required'] = $arrField['eval']['mandatory'];

        return $arrField;
    }


    protected function fieldClassExist( $strInputType ) {

        $strClass = $GLOBALS['TL_FFL'][$strInputType];
        if ( !class_exists( $strClass ) ) return false;

        return $strClass;
    }


    protected function setValues() {

        if ( $this->strItemID && $this->catalogTablename ) {

            $this->arrValues = $this->SQLQueryHelper->getCatalogTableItemByID( $this->catalogTablename, $this->strItemID );
        }

        if ( !empty( $this->arrValues ) && is_array( $this->arrValues ) ) {

            foreach ( $this->arrValues as $strFieldname => $varValue ) {

                $this->arrValues[$strFieldname] = \Input::post( $strFieldname ) !== null ? \Input::post( $strFieldname ) : $varValue;
            }
        }
    }


    protected function decodeValue( $varValue ) {

        if ( class_exists('StringUtil') ) {

            $varValue = \StringUtil::decodeEntities( $varValue );
        }

        else {

            $varValue = \Input::decodeEntities( $varValue );
        }

        return $varValue;
    }


    protected function getFileUUID( $arrFile ) {

        $strRoot = TL_ROOT . '/';
        $strUuid = $arrFile['uuid'];
        $strFile = substr( $arrFile['tmp_name'], strlen( $strRoot ) );
        $objFiles = \FilesModel::findByPath( $strFile );

        if ( $objFiles !== null ) {

            $strUuid = $objFiles->uuid;
        }

        return $strUuid;
    }


    protected function saveEntity() {

        $strQuery = '';
        $this->import( 'SQLBuilder' );

        if ( $this->arrCatalog['useGeoCoordinates'] ) {

            $this->getGeoCordValues();
        }

        if ( Toolkit::isEmpty( $this->arrValues['alias'] ) ) {

            $objDCACallbacks = new DCACallbacks();
            $this->arrValues['alias'] = $objDCACallbacks->generateFEAlias( '', $this->arrValues['title'], $this->catalogTablename, $this->arrValues['id'], $this->id );
        }

        if ( \Input::get('pid') ) {

            $strQuery = sprintf( '?pid=%s', \Input::get('pid') );
        }

        if ( isset( $this->arrValues['tstamp'] ) ) {

            $this->arrValues['tstamp'] = (string)time();
        }

        $this->prepare();

        switch ( $this->strAct ) {

            case 'create':

                if ( $this->SQLBuilder->Database->fieldExists( 'pid', $this->catalogTablename ) && $this->arrCatalog['pTable'] ) {

                    if ( !\Input::get('pid') ) return null;

                    $this->arrValues['pid'] = \Input::get('pid');
                }

                if ( $this->SQLBuilder->Database->fieldExists( 'sorting', $this->catalogTablename ) ) {

                    $intSort = $this->SQLBuilder->Database->prepare( sprintf( 'SELECT MAX(sorting) FROM %s;', $this->catalogTablename ) )->execute()->row( 'MAX(sorting)' )[0];
                    $this->arrValues['sorting'] = intval( $intSort ) + 100;
                }


                if ( $this->SQLBuilder->Database->fieldExists( 'tstamp', $this->catalogTablename ) ) {

                    $this->arrValues['tstamp'] = \Date::floorToMinute();
                }

                $this->SQLBuilder->Database->prepare( 'INSERT INTO '. $this->catalogTablename .' %s' )->set( $this->arrValues )->execute();

                if ( $this->catalogNotifyInsert ) {

                    $objCatalogNotification = new CatalogNotification( $this->catalogTablename );
                    $objCatalogNotification->notifyOnInsert( $this->catalogNotifyInsert, $this->arrValues );
                }

                $arrData = [

                    'id' => '',
                    'row' => $this->arrValues,
                    'table' => $this->catalogTablename,
                ];

                $this->CatalogMessage->set( 'insertMessage', $arrData, $this->id );
                $this->CatalogEvents->addEventListener( 'create', $arrData );

                $this->redirectAfterInsertion( $this->strRedirectID, $strQuery );

                break;

            case 'edit':

                $blnReload = true;
                $objEntity = $this->SQLBuilder->Database->prepare( 'SELECT * FROM '. $this->catalogTablename .' WHERE id = ?' )->limit(1)->execute( $this->strItemID );

                if ( $objEntity->numRows ) {

                    if ( $this->arrValues['alias'] && $this->arrValues['alias'] !== $objEntity->alias ) {

                        $blnReload =  false;

                        if ( $objEntity->pid ) {

                            $strQuery = sprintf( '?pid=%s', $objEntity->pid );
                        }
                    }

                    if ( $this->catalogNotifyUpdate ) {

                        $objCatalogNotification = new CatalogNotification( $this->catalogTablename, $this->strItemID );
                        $objCatalogNotification->notifyOnUpdate( $this->catalogNotifyUpdate, $this->arrValues );
                    }

                    $arrData = [

                        'id' => $this->strItemID,
                        'row' => $this->arrValues,
                        'table' => $this->catalogTablename,
                    ];

                    $this->CatalogMessage->set( 'updateMessage', $arrData, $this->id );
                    $this->CatalogEvents->addEventListener( 'update', $arrData );
                    $this->SQLBuilder->Database->prepare( 'UPDATE '. $this->catalogTablename .' %s WHERE id = ?' )->set( $this->arrValues )->execute( $this->strItemID );
                }

                if ( !$this->isVisible() ) $blnReload =  false;

                if ( $blnReload && ( Toolkit::isEmpty( $this->catalogFormRedirect ) || $this->catalogFormRedirect == '0'  ) ) {

                    $this->reload();
                }

                else {

                    $this->redirectAfterInsertion( $this->strRedirectID, $strQuery );
                }

                break;

            case 'copy':

                unset( $this->arrValues['id'] );

                $this->SQLBuilder->Database->prepare( 'INSERT INTO '. $this->catalogTablename .' %s' )->set( $this->arrValues )->execute();

                if ( $this->catalogNotifyInsert ) {

                    $objCatalogNotification = new CatalogNotification();
                    $objCatalogNotification->notifyOnInsert( $this->catalogNotifyInsert, $this->arrValues );
                }

                $arrData = [

                    'id' => '',
                    'row' => $this->arrValues,
                    'table' => $this->catalogTablename
                ];

                $this->CatalogMessage->set( 'insertMessage', $arrData, $this->id );
                $this->CatalogEvents->addEventListener( 'create', $arrData );
                $this->redirectAfterInsertion( $this->strRedirectID, $strQuery );

                break;
        }
    }


    protected function redirectAfterInsertion( $intPage, $strAttributes = '', $blnReturn=false ) {

        if ( ( $intPage = intval($intPage ) ) <= 0 ) return '';

        $objPage = \PageModel::findWithDetails( $intPage );
        $strUrl = $this->generateFrontendUrl( $objPage->row(), '', $objPage->language, true );

        if ( strncmp( $strUrl, 'http://', 7 ) !== 0 && strncmp( $strUrl, 'https://', 8 ) !== 0 ) $strUrl = \Environment::get( 'base' ) . $strUrl;
        if ( $strAttributes ) $strUrl .= $strAttributes;
        if ( !$blnReturn ) $this->redirect( $strUrl );

        return $strUrl;
    }


    protected function getGeoCordValues() {

        $arrCords = [];
        $objGeoCoding = new GeoCoding();
        $strGeoInputType = $this->arrCatalog['addressInputType'];

        switch ( $strGeoInputType ) {

            case 'useSingleField':

                $arrCords = $objGeoCoding->getCords( $this->arrValues[ $this->arrCatalog['geoAddress'] ], 'en', true );

                break;

            case 'useMultipleFields':

                $objGeoCoding->setCity( $this->arrValues[ $this->arrCatalog['geoCity'] ] );
                $objGeoCoding->setStreet( $this->arrValues[ $this->arrCatalog['geoCity'] ] );
                $objGeoCoding->setPostal( $this->arrValues[ $this->arrCatalog['geoPostal'] ] );
                $objGeoCoding->setCountry( $this->arrValues[ $this->arrCatalog['geoCountry'] ] );
                $objGeoCoding->setStreetNumber( $this->arrValues[ $this->arrCatalog['geoStreetNumber'] ] );
                $arrCords = $objGeoCoding->getCords( '', 'en', true );

                break;
        }

        if ( ( $arrCords['lat'] || $arrCords['lng'] ) && ( $this->arrCatalog['lngField'] && $this->arrCatalog['latField'] ) ) {

            $this->arrValues[ $this->arrCatalog['lngField'] ] = $arrCords['lng'];
            $this->arrValues[ $this->arrCatalog['latField'] ] = $arrCords['lat'];
        }
    }


    protected function prepare() {

        if ( !empty( $this->arrValues ) && is_array( $this->arrValues ) ) {

            foreach ( $this->arrValues as $strFieldname => $varValue ) {

                $arrField = $this->arrCatalogFields[ $strFieldname ];
                $varValue = Toolkit::prepareValue4Db( $varValue );

                if ( $arrField['_type'] == 'date' || in_array( $arrField['eval']['rgxp'], [ 'date', 'time', 'datim' ] ) ) {

                    $objDate = new \Date( $varValue );
                    $intTime = $objDate->timestamp;
                    $varValue = $intTime < 1 ? '' : $intTime;
                }

                if ( strpos( $arrField['sql'], 'int' ) && is_string( $varValue ) ) {

                    $varValue = intval( $varValue );
                }

                $this->arrValues[ $strFieldname ] = $varValue;
            }
        }
    }
}