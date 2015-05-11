<?php
namespace saada\FactoryMuffin;

use League\FactoryMuffin\Exceptions\ModelException;
use League\FactoryMuffin\Exceptions\ModelNotFoundException;

/**
 * Class FactoryMuffin
 * 
 * @package common\components
 */
class FactoryMuffin extends \League\FactoryMuffin\FactoryMuffin{
    /**
     * @param array $models ex: [ Model1::className(), Model2::className() ]
     * @throws ModelException
     * @throws ModelNotFoundException
     * @throws \League\FactoryMuffin\Exceptions\DefinitionAlreadyDefinedException
     */
    public function __construct($models = [])
    {
        parent::__construct(new ModelStoreYii());

        // map factory muffin CRUD methods to Yii2's methods
        $this->setSaveMethod('save')
            ->setDeleteMethod('delete');

        if (!empty($models))
        	$this->loadModelDefinitions($models);
    }

    /**
     * Go into each model and add its implementation of definitions() into FactoryMuffin
     * @param  array $models ex: [ Model1::className(), Model2::className() ]
     * @return void
     */
    public function loadModelDefinitions($models)
    {
        // load model definitions
        if (empty($models) || !is_array($models))
            throw new ModelNotFoundException(self::class, 'Models should be passed as an array of class names!');

        foreach ($models as $model) {
            /** @var FactoryInterface $model */
            if (in_array(FactoryInterface::class, class_implements($model))) {
                $fmModel = $this->define($model);
                $definitions = $model::definitions();
                if (!empty($definitions[0]) AND is_array($definitions[0])) {
                    $fmModel->setDefinitions($definitions[0]);
                    if (!empty($definitions[1]))
                        $fmModel->setCallback($definitions[1]);
                }
            } else {
                throw new ModelException($model, 'Could not find interface implementation: ' . FactoryInterface::class);
            }
        }
    }
}