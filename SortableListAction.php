<?php

    namespace stankata90\yii2SortableList;



    use Yii;
    use yii\base\Action;
    use yii\base\Exception;
    use yii\web\Response;

    class SortableListAction extends Action
    {
        public function run()
        {
            yii::$app->response->format = Response::FORMAT_JSON;

            $orders = json_decode( yii::$app->request->post( 'orders' ), TRUE );
            $config = json_decode( yii::$app->request->post( 'config' ), TRUE );


            if ( $orders && $config ) {
                try {

                    array_map( fn( $param ) => !isset( $config[ $param ] ) ? throw  new Exception( "'$param' params is mandatory!" ) : NULL, [
                        'table', 'primaryColumn', 'sortColumn',
                    ] );

                    $table               = $config['table'];
                    $primaryColumn       = $config['primaryColumn'];
                    $sortColumn          = $config['sortColumn'];
                    $primaryParentColumn = $config['primaryParentColumn'] ?? NULL;
                    $primaryParentValue  = $config['primaryParentValue'] ?? NULL;

                    foreach ( $orders as $item ) {

                        if ( isset( $config['union'] ) ) {
                            $union = $config['union'][ $item['union'] ];

                            $table               = $item['union'];
                            $primaryColumn       = $union['primaryColumn'];
                            $sortColumn          = $union['sortColumn'];
                            $primaryParentColumn = $union['primaryParentColumn'] ?? NULL;
                        }

                        $columnUpdate = [];

                        if ( isset( $primaryParentColumn ) && array_key_exists( $primaryParentColumn, $item ) ) {
                            $columnUpdate[ $primaryParentColumn ] = $item[ $primaryParentColumn ] ?? $primaryParentValue;
                        }

                        if ( array_key_exists( $sortColumn, $item ) ) {
                            $columnUpdate[ $sortColumn ] = intval( $item[ $sortColumn ] );
                        }

                        yii::$app->db->createCommand()->update( $table, $columnUpdate, [ $primaryColumn => $item[ $primaryColumn ] ] )->execute();
                    }

                    yii::$app->cache->flush();

                } catch ( \Throwable $e ) {

                    return $e->getMessage();
                }
            }
        }
    }