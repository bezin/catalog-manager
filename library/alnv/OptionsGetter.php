<?php

namespace CatalogManager;

class OptionsGetter extends CatalogController {


    protected $strModuleID;
    protected $arrCache = [];
    protected $arrField = [];
    protected $arrCatalog = [];
    protected $arrActiveEntity = [];
    protected $arrCatalogFields = [];


    public function __construct( $arrField, $strModuleID = '' ) {

        parent::__construct();

        $this->arrField = $arrField;
        $this->strModuleID = $strModuleID;

        $this->import( 'CatalogInput' );
        $this->import( 'SQLQueryHelper' );
        $this->import( 'SQLQueryBuilder' );
        $this->import( 'CatalogDCAExtractor' );
        $this->import( 'I18nCatalogTranslator' );
    }


    public function isForeignKey() {

        if ( $this->arrField['optionsType'] && $this->arrField['optionsType'] == 'useForeignKey' ) {

            return true;
        }

        return false;
    }


    public function getForeignKey() {

        return $this->setForeignKey();
    }


    public function getOptions() {

        switch ( $this->arrField['optionsType'] ) {

            case 'useOptions':

                return $this->getKeyValueOptions();

                break;

            case 'useDbOptions':

                return $this->getDbOptions();

                break;

            case 'useActiveDbOptions':

                return $this->getActiveDbOptions();

                break;
        }

        return [];
    }


    public function getTableEntities() {

        switch ( $this->arrField['optionsType'] ) {

            case 'useDbOptions':

                if ( !$this->arrField['dbTable'] || !$this->arrField['dbTableKey'] || !$this->arrField['dbTableValue'] ) {

                    return null;
                }

                if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists( $this->arrField['dbTable'] ) ) {

                    return null;
                }

                if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( $this->arrField['dbTableKey'], $this->arrField['dbTable'] ) ) {

                    return null;
                }

                if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( $this->arrField['dbTableValue'], $this->arrField['dbTable'] ) ) {

                    return null;
                }

                return $this->getResults( true );

                break;

            case 'useActiveDbOptions':

                $strDbColumn = $this->arrField['dbColumn'];

                if ( !$this->arrField['dbTable'] || !$strDbColumn ) {

                    return null;
                }

                if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists( $this->arrField['dbTable'] ) ) {

                    return null;
                }

                if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( $strDbColumn, $this->arrField['dbTable'] ) ) {

                    return null;
                }

                return $this->getResults( false );

                break;
        }

        return [];
    }


    protected function setValueToOption( $arrOptions, $strValue, $strLabel = '' ) {

        if ( $strValue && !in_array( $strValue, $arrOptions ) ) {

            $arrOptions[ $strValue ] = $this->I18nCatalogTranslator->get( 'option', $strValue, [ 'title' => $strLabel ] );
        }

        return $arrOptions;
    }


    protected function parseCatalogValues( $varValue, $strFieldname, $arrCatalog ) {

        $arrField = $this->arrCatalogFields[ $strFieldname ];

        switch ( $arrField['type'] ) {

            case 'select':
                
                return Select::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'checkbox':

                return Checkbox::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'radio':

                return Radio::parseValue( $varValue, $arrField, $arrCatalog );

                break;
        }

        return $varValue;
    }


    protected function getResults( $blnUseValidValue = false ) {

        $arrSQLQuery = [

            'table' => $this->arrField['dbTable'],
            'where' => []
        ];

        $this->getActiveEntityValues();
        $strOrderBy = $this->getOrderBy();
        $arrQueries = Toolkit::deserialize( $this->arrField['dbTaxonomy'] )['query'];

        $arrQueries = Toolkit::parseQueries( $arrQueries, function( $arrQuery ) use ( $blnUseValidValue ) {

            $blnValidValue = true;
            $blnIgnoreEmptyValues = $this->arrField['dbIgnoreEmptyValues'] ? true : false;
            $arrQuery['value'] = $this->getParseQueryValue( $arrQuery['value'], $arrQuery['operator'], $blnValidValue );
            $arrQuery['allowEmptyValues'] = $blnIgnoreEmptyValues ? false : true;

            if ( !$blnValidValue && $blnUseValidValue ) return null;

            return $arrQuery;
        });

        $arrSQLQuery['where'] = $arrQueries;
        $strWhereStatement = $this->SQLQueryBuilder->getWhereQuery( $arrSQLQuery );

        if ( Toolkit::isEmpty( $strOrderBy ) ) {

            $this->CatalogDCAExtractor->extract( $this->arrField['dbTable'] );

            if ( $this->CatalogDCAExtractor->hasOrderByStatement() ) {

                $strOrderBy = ' ORDER BY ' . $this->CatalogDCAExtractor->getOrderByStatement();
            }
        }
        
        $strQuery = sprintf( 'SELECT * FROM %s %s%s', $this->arrField['dbTable'], $strWhereStatement, $strOrderBy );

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerModifyOptionsGetter'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerModifyOptionsGetter'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['catalogManagerModifyOptionsGetter'] as $callback ) {

                $this->import( $callback[0] );
                $this->{$callback[0]}->{$callback[1]}( $strQuery, $arrSQLQuery, $this->arrField, $this->strModuleID );
            }
        }

        $objDbOptions = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( $strQuery )->execute( $this->SQLQueryBuilder->getValues() );

        return $objDbOptions;
    }


    protected function getDbOptions() {

        $arrOptions = [];
        $objDbOptions = $this->getTableEntities();

        if ( $objDbOptions === null ) return $arrOptions;
        if ( !$objDbOptions->numRows ) return $arrOptions;

        while ( $objDbOptions->next() ) {

            $arrOptions[ $objDbOptions->{$this->arrField['dbTableKey']} ] = $this->I18nCatalogTranslator->get( 'option', $objDbOptions->{$this->arrField['dbTableKey']}, [ 'title' => $objDbOptions->{$this->arrField['dbTableValue']} ] );
        }

        return $arrOptions;
    }

    
    protected function getActiveDbOptions() {

        $arrOptions = [];
        $objEntities = $this->getTableEntities();
        $strDbColumn = $this->arrField['dbColumn'];

        if ( $objEntities === null ) return $arrOptions;
        if ( !$objEntities->numRows ) return $arrOptions;

        $this->arrCatalog = $this->SQLQueryHelper->getCatalogByTablename( $this->arrField['dbTable'] );
        $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogTablename( $this->arrField['dbTable'] );

        while ( $objEntities->next() ) {

            $strOriginValue = $objEntities->{$strDbColumn};
            $varValue = $this->parseCatalogValues( $strOriginValue, $strDbColumn, [] );

            if ( is_array( $varValue ) ) {

                $arrLabels = array_values( $varValue );
                $arrOriginValues = array_keys( $varValue );

                if ( !empty( $arrLabels ) && is_array( $arrLabels ) ) {

                    foreach ( $arrLabels as $intPosition => $strLabel ) {

                        $arrOptions = $this->setValueToOption( $arrOptions, $arrOriginValues[ $intPosition ], $strLabel );
                    }
                }
            }

            else {

                $arrOptions = $this->setValueToOption( $arrOptions, $strOriginValue, $varValue );
            }
        }

        return $arrOptions;
    }


    protected function getParseQueryValue( $strValue = '', $strOperator = '', &$blnValidValue = true ) {

        if ( !empty( $strValue ) && is_string( $strValue ) ) {

            $strInsertTagValue = \Controller::replaceInsertTags( $strValue );

            if ( !Toolkit::isEmpty( $strInsertTagValue ) ) {

                $strValue = $strInsertTagValue;
            }
        }

        if ( !empty( $strValue ) && is_string( $strValue ) && strpos( $strValue, '{{' ) !== false ) {

            $strFieldnameValue = '';
            $arrTags = preg_split( '/{{(([^{}]*|(?R))*)}}/', $strValue, -1, PREG_SPLIT_DELIM_CAPTURE );
            $strTag = implode( '', $arrTags );

            if ( $strTag ) {

                $strFieldnameValue = $this->arrActiveEntity[ $strTag ];
            }

            if ( TL_MODE == 'FE' && ( is_null( $strFieldnameValue ) || $strFieldnameValue === '' ) ) {

                $strFieldnameValue = $this->CatalogInput->getValue( $strTag );
            }

            if ( Toolkit::isEmpty( $strFieldnameValue ) ) {

                $blnValidValue = $this->isValidValue( $strFieldnameValue );
                $strFieldnameValue = '';
            }

            $strValue = $strFieldnameValue;

        }

        if ( $strOperator == 'contain' && is_string( $strValue ) ) {

            $strValue = explode( ',', $strValue );
        }

        return Toolkit::prepareValueForQuery( $strValue );
    }


    protected function getKeyValueOptions() {

        $arrOptions = [];

        if ( $this->arrField['options'] ) {

            $arrFieldOptions = deserialize( $this->arrField['options'] );

            if ( !empty( $arrFieldOptions ) && is_array( $arrFieldOptions ) ) {

                foreach ( $arrFieldOptions as $arrOption ) {

                    $arrOptions[ $arrOption['key'] ] = $this->I18nCatalogTranslator->get( 'option', $arrOption['key'], [ 'title' => $arrOption['value'] ] );
                }
            }
        }

        return $arrOptions;
    }


    protected function setForeignKey() {

        $strLabelColumn = $this->arrField['dbTableValue'] ? $this->arrField['dbTableValue'] : $this->arrField['dbTableKey'];

        if ( !$this->arrField['dbTable'] || !$strLabelColumn ) {

            return '';
        }

        return $this->arrField['dbTable'] . '.' . $strLabelColumn;
    }


    protected function getActiveEntityValues() {

        switch ( TL_MODE ) {

            case 'BE':

                $strID = \Input::get('id');
                $strTable = \Input::get( 'table' ) ? \Input::get( 'table' ) : \Input::get('do');

                if ( !$strID || !$strTable ) {

                    return null;
                }

                if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists( $strTable ) ) {

                    return null;
                }

                $arrQuery = [

                    'table' => $strTable,

                    'pagination' => [

                        'limit' => 1
                    ],

                    'where' => [

                        [
                            'field' => 'id',
                            'value' => $strID,
                            'operator' => 'equal'
                        ]
                    ],

                    'joins' => []
                ];

                $objCatalog = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( 'SELECT * FROM tl_catalog WHERE tablename = ? LIMIT 1' )->execute( $strTable );

                if ( $objCatalog->numRows ) {

                    if ( $objCatalog->pTable && $this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( 'pid', $strTable ) ) {

                        $arrQuery['joins'][] = [

                            'field' => 'pid',
                            'onField' => 'id',
                            'multiple' => false,
                            'table' => $strTable,
                            'onTable' => $objCatalog->pTable
                        ];
                    }
                }

                $this->arrActiveEntity = $this->SQLQueryBuilder->execute( $arrQuery )->row();

                return null;

                break;

            case 'FE':

                if ( !$this->arrField['pid'] ) {

                    return null;
                }

                $objCatalog = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( 'SELECT * FROM tl_catalog WHERE id = ?' )->limit(1)->execute( $this->arrField['pid'] );

                if ( !$objCatalog->tablename || !$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists( $objCatalog->tablename ) ) {

                    return null;
                }

                $strID = $this->strModuleID ? \Input::get( 'id'. $this->strModuleID ) : \Input::get('id');

                $arrQuery = [

                    'table' => $objCatalog->tablename,

                    'pagination' => [

                        'limit' => 1
                    ],

                    'where' => [

                        [
                            'field' => 'id',
                            'value' => $strID,
                            'operator' => 'equal'
                        ]
                    ],

                    'joins' => []
                ];

                if ( $objCatalog->pTable && $this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( 'pid', $objCatalog->tablename )) {

                    $arrQuery['joins'][] = [

                        'field' => 'pid',
                        'onField' => 'id',
                        'multiple' => false,
                        'onTable' => $objCatalog->pTable,
                        'table' => $objCatalog->tablename
                    ];
                }

                $this->arrActiveEntity = $this->SQLQueryBuilder->execute( $arrQuery )->row();
                
                break;

        }

        if ( !is_array( $this->arrActiveEntity ) ) {

            $this->arrActiveEntity = [];
        }
    }


    protected function getOrderBy() {

        if ( !Toolkit::isEmpty( $this->arrField['_orderBy'] ) ) {

            return ' ' . $this->arrField['_orderBy'];
        }

        return '';
    }


    protected function isValidValue( $strValue ) {

        if ( !Toolkit::isEmpty( $strValue ) ) return true;

        switch ( TL_MODE ) {

            case 'BE':

                $strID = \Input::get('id');
                $strTable = \Input::get( 'table' ) ? \Input::get( 'table' ) : \Input::get('do');

                if ( !$strID || !$strTable ) return false;

                break;

            case 'FE':

                $strID = $this->strModuleID ? \Input::get( 'id'. $this->strModuleID ) : \Input::get('id');

                if ( !$strID ) return false;

                break;
        }

        return true;
    }
}