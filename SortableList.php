<?php

    namespace stankata90\yii2SortableList;

    use Yii;
    use yii\base\Exception;
    use yii\helpers\Html;
    use yii\i18n\PhpMessageSource;

    // todo: локални преводи за уиджета.

    class SortableList extends SortableListBase
    {
        /**
         * @throws Exception
         */
        public function run() : string
        {
            $this->initI18();
            $this->initGroupOptions();
            $this->initItemOptions();
            $this->initCsrf();
            $this->initWidgetOptions();
            $this->initClientOptions();
            $this->registerAssets();

            return $this->genrateUl( $this->widgetOptions );
        }

        protected function genrateUl( $config ) : string
        {

            $init       = $this->init;
            $this->init = FALSE;

            /**
             * Ако сме в начална позиция на генериране, повикваме основната заявка за листове
             */
            if ( $init ) {
                if ( isset( $this->query ) && is_callable( $this->query ) ) {
                    $config['links'] = call_user_func( $this->query, $this );
                } else {
                    $config['links'] = $this->listQuery( $config );
                }
            }

            array_map( fn( $param ) => !isset( $config[ $param ] ) ? throw new \yii\base\Exception( '"' . $param . '" params is mandatory !' ) : NULL, [
                'table', 'primaryColumn', 'sortColumn', 'links',
            ] );

            $links = $config['links'] ?? [];


            if ( isset( $config['maxDept'] ) ) {
                $config['maxDept']--;
            }


            $content = '';
            foreach ( $links as $link ) {

                $skipNested = FALSE;
                if ( !isset( $config['primaryParentColumn'] ) || $this->sort != SortableList::SORT_NESTABLE ) {
                    $skipNested = TRUE;
                }

                if ( isset( $config['maxDept'] ) && !( $config['maxDept'] > 0 ) ) {
                    $skipNested = TRUE;
                }

                $config['links'] = $skipNested ? [] : ( is_callable( $this->subQuery ) ? call_user_func( $this->subQuery, $this, $link ) : $this->listQueryNested( $config, $link ) );

                if ( isset( $config['union'] ) && $config['union'][ $link['union'] ]['primaryColumn'] ) {
                    $options = [
                        'data-' . $config['union'][ $link['union'] ]['primaryColumn'] => $link['id'],
                        'data-name'                                                   => $link[ $this->valueColumn ],
                    ];
                } else {
                    $options = [
                        'data-' . $config['primaryColumn'] => $link['id'],
                        'data-name'                        => $link[ $this->valueColumn ],
                    ];
                }


                if ( isset( $this->union ) && isset( $link['union'] ) && isset( $link['union_id'] ) ) {
                    $options['data-union']    = $link['union'];
                    $options['data-union_id'] = $link['union_id'];
                }

                $groupOptions          = $this->groupOptions;
                $groupOptions['class'] = ( !isset( $config['primaryParentColumn'] ) || $this->sort != SortableList::SORT_NESTABLE ? NULL : 'st_list_instance ' . $groupOptions['class'] );

                $subContent = Html::tag(
                    'div',
                    count( $config['links'] ) ? $this->genrateUl( $config ) : NULL,
                    array_replace( $groupOptions, $options )
                );

                $compact = compact( 'link', 'config' );

                if ( $this->renderView ) {
                    $title = $this->getView()->render( $this->renderView, $compact );
                } else if ( $this->renderFile ) {
                    $title = $this->getView()->renderFile( $this->renderFile, $compact );
                } else {
                    $title = $link[ $this->valueColumn ];
                }

                if ( isset( $config['union'] ) && $config['union'][ $link['union'] ]['primaryColumn'] ) {
                    $options = [
                        'data-' . $config['union'][ $link['union'] ]['primaryColumn'] => $link['id'],
                        'data-name'                                                   => $link[ $this->valueColumn ],
                    ];
                } else {
                    $options = [
                        'data-' . $config['primaryColumn'] => $link['id'],
                        'data-name'                        => $link[ $this->valueColumn ],
                    ];
                }


                if ( isset( $this->union ) && isset( $link['union'] ) && isset( $link['union_id'] ) ) {
                    $options['data-union']    = $link['union'];
                    $options['data-union_id'] = $link['union_id'];
                }

                $content .= Html::tag(
                    'div',
                    $title . $subContent,
                    array_replace( $this->itemOptions, $options )
                );
            }


            /**
             * Когато приключат всички вложености, тогава опаковаме вложените листове в основния HTML
             */
            if ( $init ) {
                $options = [
                    'id'          => $this->_hashWidgetOptions,
                    'data-name'   => 'ROOT',
                    'data-config' => $this->_hashWidgetOptions,
                ];

                $groupOptions          = $this->groupOptions;
                $groupOptions['class'] = 'st_list_instance ' . $groupOptions['class'];

                $content = Html::tag(
                    'div',
                    $content,
                    array_replace( $groupOptions, $options )
                );

                $content .= $this->generateButton();
            }

            return $content;
        }

        /**
         * Генерираме HTML бутон за събмитване на листа.
         *
         * @return string
         */
        protected function generateButton() : string
        {
            $button = [
                'name'    => 'submit',
                'value'   =>  $this->t( 'app', 'save' ),
                'options' => [
                    'class'   => 'btn btn-success',
                    'onclick' => "st_sortable_submit('{$this->_hashWidgetOptions}', {$this->_hashWidgetOptions}, '" . Yii::$app->request->csrfParam . "' )",
                ],
            ];

            if ( isset( $this->button['options'] ) ) {
                $button['options'] = array_replace( $button['options'], $this->button['options'] );
            }

            return Html::input( 'submit', $this->button['name'] ?? $button['name'], $this->button['value'] ?? $button['value'], $button['options'] );
        }

    }