<?php

    namespace stankata90\yii2SortableList;


    use yii\base\Widget;
    use yii\db\Query;
    use yii\helpers\Html;
    use yii\helpers\Json;
    use yii\i18n\PhpMessageSource;
    use yii\web\JsExpression;
    use yii\web\View;

    class SortableListBase extends Widget
    {
        /**
         * The widget name
         */
        const WIDGET_NAME = 'sortable_list';

        const SORT_SINGLE = 'SORT_SINGLE';
        const SORT_NESTABLE = 'SORT_NESTABLE';

        public $sort;

        /**
         * @var $init
         */
        protected $init = TRUE;


        /**
         *  'table' => AdminMenu::tableName() // Таблица върху която ще се осъществява сортирането.
         *
         * @var string
         */
        public $table;


        /**
         *  'primaryColumn' => 'id' // основна колона чрез която ще се управлява реда.
         * @var string
         */
        public $primaryColumn;


        /**
         *  'sortColumn' => 'sort' // основна колова върху която ще се организира реалната сортировка.
         *
         * @var
         */
        public $sortColumn;


        /**
         *  'sortColumn' => 'sort' // основна колова върху която ще се организира реалната сортировка.
         *
         * @var string|null
         */
        public $primaryParentColumn;


        /**
         *  'primaryParentValue' => null // когато имаме вложено сортиране, ако е посочена сотойност тя ще се използва за начална точка при образуване на заявките.
         *
         * @var string|int|null
         */
        public $primaryParentValue;

        /**
         *  'valueColumn' => 'name' // основна колова за четене на стойснот.
         *
         * @var string|null
         */
        public $valueColumn;


        /**
         *  'sortColumn' => 'sort' // основна колова върху която ще се организира реалната сортировка.
         *
         * @var string
         */
        public $url;


        /**
         *  'maxDept' => NULL // определя дълбочината на сортиране когато имаме вложено сортиране
         *
         * @var int|null;
         */
        public $maxDept;



        /**
         * Ако има посочен вю ( асоцииран към контекста в който е инстанциран уиджета ) се използва за рендериране на съръдържанието от реда.
         * рендерирания файл ще получи $link и $config променливи
         *
         * @var string|null
         */
        public $renderView;


        /**
         * Ако има посочен файл се използва за рендериране на съръдържанието от реда.
         * рендерирания файл ще получи $link и $config @see $widgetOptions променливи
         *
         * @var string|null
         */
        public $renderFile;


        /**
         * Опции които се предават на подгрупите.
         *
         * @var array|null
         */
        public $groupOptions = [];


        /**
         * Опции които се предават на елементите.
         *
         * @var array|null
         */
        public $itemOptions = [];


        /**
         * Списък с опции които ще се препредават за вторични използване.
         *
         * @var string[]
         */
        protected $forwardOptions = [
            'sort',
            'table',
            'primaryColumn',
            'sortColumn',
            'valueColumn',
            'url',
            'primaryParentColumn',
            'primaryParentValue',
            'maxDept',
            'union',
        ];


        /**
         * Конфигурация на уиджета събрана на базата на @see $forwardOptions
         *
         * @var []
         */
        protected $widgetOptions;


        /**
         * https://github.com/SortableJS/Sortable
         *
         * Конфигурация на библиотеката. Кодира се до JSON и се предава на билиотеката като конфигурация.
         *
         * 'clientOptions' => [
         *      'jsOption1' => 'value'
         *      'jsOption2' => 'value'
         *      ...
         *  ]
         */
        public $clientOptions;


        /**
         * Анонимна фунцкия за основна заявка.
         *
         * @var callable|null
         */
        public $query;

        /**
         * Анонимна функция за подчинена заявка.
         *
         * @var callable|null
         */
        public $subQuery;


        /**
         * Необходим е когато се прилага сортиране върху вю от две таблици с обединени резултаи UNION, UNION ALL.
         * Задулжително вюта трябва да съдържа две специфични колони:
         *  "union" - която ще е таблицата от която идва реда
         *  "union_id" - която ще се явява primary key защото може да се случи стойността на primary key от таблиците да се дублира.
         *
         * примерно имаме вю menu което включва набор от таблица pages и products
         *
         * 'union' => [
         *      [
         *          'table'               => 'pages',
         *          'primaryColumn'       => 'id',
         *          'primaryParentColumn' => 'menu_in',
         *          'sortColumn'          => 'menu_sort',
         *      ],
         *      [
         *          'table'               => 'products',
         *          'primaryColumn'       => 'id',
         *          'primaryParentColumn' => 'menu_in',
         *          'sortColumn'          => 'menu_sort',
         *      ],
         * ]
         *
         * описаните клетки са реални в съответните таблици, а клетките които са описани в основната конфигурация на уиджета са клетките от вюто
         *
         * @var array|null
         */
        public $union;


        /**
         * Конфигурация на бутона
         *
         * @var array
         */
        public $button = [];


        /**
         * @var string the hashed variable to store the widgetOptions
         */
        public $_hashWidgetOptions;


        /**
         * @var PhpMessageSource
         */
        private $i18;

        /**
         * @var string the hashed variable to store the clientOptions
         */
        public $_hashClientOptions;

        protected function initI18() {
           $this->i18 = \yii::createObject( [
                'class'    => PhpMessageSource::class,
                'basePath' => __DIR__ . DIRECTORY_SEPARATOR . 'messages',
            ] );
        }

        protected function t($category, $message, $params = [], $language = null)
        {
            if ($this->i18 !== null) {
                return $this->i18->translate($category, $message, $language ?: \yii::$app->language);
            }

            $placeholders = [];
            foreach ((array) $params as $name => $value) {
                $placeholders['{' . $name . '}'] = $value;
            }

            print_r( $placeholders );
            exit();

            return ($placeholders === []) ? $message : strtr($message, $placeholders);
        }


        /**
         * Валидираме началните стойности на опциите на групи
         *
         * @return void
         */
        protected function initGroupOptions()
        {
            $this->groupOptions['class'] = 'st_list-group ' . trim( $this->groupOptions['class'] ?? '' );
        }


        /**
         * Валидираме началните стойности на опциите на групи
         *
         * @return void
         */
        protected function initItemOptions()
        {
            $this->itemOptions['class'] = 'st_list-item ' . trim( $this->itemOptions['class'] ?? '' );
        }


        /**
         * Валидираме началните стойности на уиджета JS след кото генерираме JS хеш на базата на тях.
         *
         * @return void
         */
        protected function initClientOptions()
        {
            /**
             * конфигурация по подразбиране.
             */
            $defaultOptions = [
                'group'          => 'list',
                'animation'      => 200,
                'fallbackOnBody' => TRUE,
                'swapThreshold'  => 0.65,
                'ghostClass'     => 'sort',
                'onMove'         => new JsExpression( 'function(evt) { return onMove(evt); }' ),
                'onEnd'          => new JsExpression( 'function(evt) { return onEnd(evt); }' ),
            ];

            foreach ( $defaultOptions as $key => $option ) {
                if ( !isset( $this->clientOptions[ $key ] ) ) {
                    $this->clientOptions[ $key ] = $option;
                }
            }

            $encOptions               = empty( $this->clientOptions ) ? '{}' : Json::htmlEncode( $this->clientOptions );
            $this->_hashClientOptions = self::WIDGET_NAME . '_' . hash( 'crc32', $encOptions );

            $this->getView()->registerJs( $this->render( '_js_init_client_options_pos_head', [ 'widget' => $this, 'encOptions' => $encOptions ] ), View::POS_HEAD );
            $this->getView()->registerJs( $this->render( '_js_init_client_options_pos_end', [ 'widget' => $this ] ), View::POS_END );
        }

        /**
         * Валидираме началните стойности на уиджета след кото генерираме JS хеш на базата на тях.
         *
         * @return void
         * @throws \yii\base\Exception
         */
        protected function initWidgetOptions()
        {
            foreach ( $this->forwardOptions as $option_key ) {
                if ( $this->hasProperty( $option_key ) && $this->canGetProperty( $option_key ) ) {
                    $this->widgetOptions[ $option_key ] = $this->$option_key;
                }
            }

            array_map( fn( $param ) => !isset( $this->widgetOptions[ $param ] ) ? throw new \yii\base\Exception( '"widgetOptions[' . $param . ']" params is mandatory !' ) : NULL, [
                'sort', 'table', 'primaryColumn', 'sortColumn', 'valueColumn', 'url',
            ] );

            if ( $this->union ) {

                if ( !is_array( $this->union ) ) {
                    throw new \yii\base\Exception( '"union" must be an array, and is passed ' . gettype( $this->union ) . '.' );
                }

                if ( count( $this->union ) < 2 ) {
                    throw new \yii\base\Exception( '"union" must have at least two elements.' );
                }

                $tmpUnion = [];
                foreach ( $this->union as $key => $config ) {
                    array_map( fn( $param ) => !isset( $config[ $param ] ) ? throw new \yii\base\Exception( '"union[' . $key . '][' . $param . ']" params is mandatory !' ) : NULL, [
                        'table', 'primaryColumn', 'sortColumn',
                    ] );

                    if ( $this->primaryParentColumn && !isset( $config['primaryParentColumn'] ) ) {
                        throw new \yii\base\Exception( '"union[' . $key . '][primaryParentColumn]" params is mandatory !' );
                    }

                    $tmpUnion[ $config['table'] ] = $config;
                }

                $this->union                  = $tmpUnion;
                $this->widgetOptions['union'] = $tmpUnion;
            }

            $encOptions               = empty( $this->widgetOptions ) ? '{}' : Json::htmlEncode( $this->widgetOptions );
            $this->_hashWidgetOptions = self::WIDGET_NAME . '_' . hash( 'crc32', $encOptions );
            $this->getView()->registerJs( $this->render( '_js_init_widget_options_pos_head', [ 'widget' => $this, 'encOptions' => $encOptions ] ), View::POS_HEAD );
        }


        /**
         * регистрираме променлива за csrf
         *
         * @return void
         */
        protected function initCsrf() : void
        {
            $this->getView()->registerJs( "if(!csrf_param) { var csrf_param = '" . \Yii::$app->request->csrfParam . "'; } if(!csrf_value) { var csrf_value = '" . \Yii::$app->request->getCsrfToken() . "';}", View::POS_HEAD );
        }


        /**
         * регистрираме асетите за уиджета.
         */
        protected function registerAssets()
        {
            SortableListAsset::register( $this->getView() );
        }


        /**
         * Определяме левела на основния лист за листване.
         *
         * @param $link
         * @param $config
         *
         * @return int|null
         */
        protected function initLevel( $link, $config )
        {
            if ( isset( $this->initLevel ) ) {
                return $this->initLevel;
            }

            $level = 0;

            $findLevelLink = function ( $link, &$level ) use ( &$findLevelLink, $config ) {
                if ( isset( $link[ $config['primaryParentColumn'] ] ) ) {
                    $level++;

                    $link = ( new Query() )->from( $config['table'] )
                        ->where( [ $config['primaryColumn'] => $link[ $config['primaryParentColumn'] ] ] )
                        ->one();

                    if ( $link ) {
                        $findLevelLink( $link, $level );
                    }
                }
            };

            $findLevelLink( $link, $level );

            return $level;
        }


        /**
         * Начална заявка за първо ниво лист.
         *
         * @param $config
         *
         * @return array|null
         */
        protected function listQuery( $config ) : ?array
        {
            $query = ( new Query() )->from( $config['table'] );

            if ( isset( $config['primaryParentColumn'] ) && array_key_exists( 'primaryParentValue', $config ) ) {
                $query->where( [ $config['primaryParentColumn'] => $config['primaryParentValue'] ] );
            }

            $query->orderBy( [ $config['sortColumn'] => SORT_ASC ] );

            return $query->all();
        }


        /**
         * Рекурсивна заявка за подчинени листове.
         *
         * @param $config
         * @param $parent
         *
         * @return array|null
         */
        protected function listQueryNested( $config, $parent ) : ?array
        {
            return ( new Query() )->from( $config['table'] )
                ->where( [ $config['primaryParentColumn'] => $parent[ isset( $config['union'] ) ? 'union_id' : $config['primaryColumn'] ] ] )
                ->orderBy( [ $config['sortColumn'] => SORT_ASC ] )
                ->all();
        }

    }