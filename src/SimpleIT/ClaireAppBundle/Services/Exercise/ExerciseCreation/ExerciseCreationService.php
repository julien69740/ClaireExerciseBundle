<?php

namespace SimpleIT\ExerciseBundle\Service\ExerciseCreation;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use SimpleIT\ApiBundle\Exception\ApiBadRequestException;
use SimpleIT\ApiResourcesBundle\Exercise\DomainKnowledge\CommonKnowledge;
use SimpleIT\ApiResourcesBundle\Exercise\DomainKnowledge\Formula\LocalFormula;
use SimpleIT\ApiResourcesBundle\Exercise\DomainKnowledge\Formula;
use SimpleIT\ApiResourcesBundle\Exercise\Exercise\Common\CommonExercise;
use SimpleIT\ApiResourcesBundle\Exercise\Exercise\Common\CommonItem;
use SimpleIT\ApiResourcesBundle\Exercise\ExerciseModel\Common\CommonModel;
use SimpleIT\ApiResourcesBundle\Exercise\ExerciseModel\Common\ResourceBlock;
use SimpleIT\ApiResourcesBundle\Exercise\ExerciseObject\ExerciseObject;
use SimpleIT\ApiResourcesBundle\Exercise\ItemResource;
use SimpleIT\ApiResourcesBundle\Exercise\KnowledgeResource;
use SimpleIT\ApiResourcesBundle\Exercise\ModelObject\ModelDocument;
use SimpleIT\CommonBundle\Entity\User;
use SimpleIT\ExerciseBundle\Entity\CreatedExercise\Item;
use SimpleIT\ExerciseBundle\Entity\CreatedExercise\StoredExercise;
use SimpleIT\ExerciseBundle\Entity\ExerciseModel\OwnerExerciseModel;
use SimpleIT\ExerciseBundle\Entity\ItemFactory;
use SimpleIT\ExerciseBundle\Entity\StoredExerciseFactory;
use SimpleIT\ExerciseBundle\Model\ExerciseObject\ExerciseTextFactory;
use SimpleIT\ExerciseBundle\Model\Resources\KnowledgeResourceFactory;
use SimpleIT\ExerciseBundle\Service\DomainKnowledge\FormulaServiceInterface;
use SimpleIT\ExerciseBundle\Service\DomainKnowledge\OwnerKnowledgeServiceInterface;
use SimpleIT\ExerciseBundle\Service\ExerciseResource\ExerciseResourceServiceInterface;

/**
 * Abstract class for the services which manages the specific exercise
 * generation
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class ExerciseCreationService implements ExerciseCreationServiceInterface
{
    /**
     * @var ExerciseResourceServiceInterface
     */
    protected $exerciseResourceService;

    /**
     * @var FormulaServiceInterface
     */
    protected $formulaService;

    /**
     * @var OwnerKnowledgeServiceInterface
     */
    protected $ownerKnowledgeService;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Set serializer
     *
     * @param SerializerInterface $serializer
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Set exerciseResourceService
     *
     * @param ExerciseResourceServiceInterface $exerciseResourceService
     */
    public function setExerciseResourceService($exerciseResourceService)
    {
        $this->exerciseResourceService = $exerciseResourceService;
    }

    /**
     * Set formulaService
     *
     * @param FormulaServiceInterface $formulaService
     */
    public function setFormulaService($formulaService)
    {
        $this->formulaService = $formulaService;
    }

    /**
     * Set ownerKnowledgeService
     *
     * @param OwnerKnowledgeServiceInterface $ownerKnowledgeService
     */
    public function setKnowledgeService($ownerKnowledgeService)
    {
        $this->ownerKnowledgeService = $ownerKnowledgeService;
    }

    /**
     * Add the documents from model to the exercise
     *
     * @param CommonModel    $model
     * @param CommonExercise $exercise
     * @param User           $owner
     *
     * @throws \LogicException
     */
    protected function addDocuments(CommonModel $model, CommonExercise &$exercise, User $owner)
    {
        $modDocs = $model->getDocuments();
        foreach ($modDocs as $modDoc) {
            /** @var ModelDocument $modDoc */
            if (is_null($modDoc->getId())) {
                throw new \LogicException("Invalid document resource id.");
            }

            $exercise->addDocument(
                $this->exerciseResourceService
                    ->getExerciseObject($modDoc, $owner)
            );
        }
    }

    /**
     * Compute the values of the formula: variable instantiation and equation resolution
     *
     * @param LocalFormula $localFormula
     * @param User         $owner
     *
     * @throws \SimpleIT\ApiBundle\Exception\ApiBadRequestException
     * @return array
     */
    protected function computeFormulaVariableValues($localFormula, User $owner)
    {
        if (empty($localFormula)) {
            return array();
        }

        if (!is_null($localFormula->getFormulaId())) {
            $formula = KnowledgeResourceFactory::create(
                $this->ownerKnowledgeService->getByIdAndOwner
                    (
                        $localFormula->getFormulaId(),
                        $owner->getId()
                    )->getKnowledge()
            )->getContent();

            if (get_class($formula) != KnowledgeResource::FORMULA_CLASS) {
                throw new ApiBadRequestException(
                    'The specified formula resource is not a formula'
                );
            }

            /** @var Formula $formula */
            $textFormula = $formula->getEquation();
            $variables = $formula->getVariables();
            $unknown = $formula->getUnknown();
        } elseif (!is_null($localFormula->getEquation())) {
            $textFormula = $localFormula->getEquation();
            $unknown = $localFormula->getUnknown();
            $variables = array();
        } else {
            throw new ApiBadRequestException(
                'The equation of the formula cannot be found'
            );
        }

        $newVariables = array();
        /** @var Formula\Variable $newVar */
        foreach ($localFormula->getVariables() as $newVar) {
            $newVariables[] = $newVar->getName();
        }

        /** @var Formula\Variable $variable */
        foreach ($variables as $key => $variable) {
            if (array_search($variable->getName(), $newVariables) !== false) {
                unset($variables[$key]);
            }
        }

        $variables = array_merge($variables, $localFormula->getVariables());

        if ($localFormula->getUnknown() != null) {
            $unknown = $localFormula->getUnknown();
        }

        return $this->formulaService->resolveFormulaResource(
            $textFormula,
            $variables,
            $unknown
        );
    }

    /**
     * Inject variable values in a string
     *
     * @param string $string
     * @param array  $variables
     *
     * @return string
     */
    protected function parseStringWithVariables($string, $variables)
    {
        if (!empty($variables)) {
            preg_match_all('#\$[a-z|A-Z|0-9]+#', $string, $foundVarNames);
            foreach ($foundVarNames[0] as $varName) {
                $shortName = substr($varName, 1);
                if (isset($variables[$shortName])) {
                    $string = str_replace($varName, $variables[$shortName], $string);
                }
            }
        }

        return $string;
    }

    /**
     * Inject variable values in an array of strings
     *
     * @param $array
     * @param $variables
     *
     * @return mixed
     */
    protected function parseArrayWithVariables($array, $variables)
    {
        $result = array();
        foreach ($array as $string) {
            $result[] = $this->parseStringWithVariables($string, $variables);
        }

        return $result;
    }

    /**
     * Convert an array of ExerciseObjects into an array of ExerciseText
     * which contains the value of the field of metadata pointed by a key.
     * The metadata are copied in the new objects. The number of objects in the
     * output list can be reduced because some objects may not have the
     * metadata
     *
     * @param array  $objects An array of ExerciseObject
     * @param string $key     The key of the metadata to use to replace the object
     *
     * @return array An array of ExerciseText
     */
    protected function objectsToMetaStrings($objects, $key)
    {
        $stringObjects = array();

        foreach ($objects as $obj) {
            $textObject = $this->objectToMetaString($obj, $key);
            if (!is_null($textObject)) {
                $stringObjects[] = $textObject;
            }
        }

        return $stringObjects;
    }

    /**
     * Convert an ExerciseObject into an ExerciseText which contains the value
     * of the field of metadata pointed by a key. The metadata are copied in the
     * new object.
     *
     * @param ExerciseObject $obj The input ExerciseObject
     * @param string         $key The key of the metadata
     *
     * @return ExerciseObject The text object or null if the metadata was not found
     */
    protected function objectToMetaString(ExerciseObject $obj, $key)
    {
        $md = $obj->getMetadata();
        if (array_key_exists($key, $md)) {
            $textObj = ExerciseTextFactory::createFromText($md[$key]);
            $textObj->setMetadata($md);

            return $textObj;
        } else {
            return null;
        }
    }

    /**
     * Convert an exercise and its items into one StoredExercise entity and
     * Item entities. Schemas of the exercise and of the items are required.
     *
     * @param CommonExercise     $exercise
     * @param OwnerExerciseModel $oem
     * @param string             $typeOfItem
     * @param null               $items
     *
     * @return StoredExercise
     */
    protected function toStoredExercise(
        CommonExercise $exercise,
        OwnerExerciseModel $oem,
        $typeOfItem,
        $items = null
    )
    {
        $context = SerializationContext::create();
        $context->setGroups(array("exercise_storage", 'Default'));
        $exContent = $this->serializer->serialize(
            $exercise,
            'json',
            $context
        );
        $exerciseEntity = StoredExerciseFactory::create($exContent, $oem);

        // transform and add the items
        foreach ($items as $item) {
            $itemEnt = $this->toItemEntity($item, $typeOfItem);
            $itemEnt->setStoredExercise($exerciseEntity);
            $exerciseEntity->addItem($itemEnt);
        }

        return $exerciseEntity;
    }

    /**
     * Convert an item into an entity object
     *
     * @param $item
     * @param $typeOfItem
     *
     * @return Item
     */
    protected function toItemEntity($item, $typeOfItem)
    {
        $context = SerializationContext::create();
        $context->setGroups(array('item_storage', 'Default'));

        $itemContent = $this->serializer->serialize(
            $item,
            'json',
            $context
        );

        return ItemFactory::create($itemContent, $typeOfItem);
    }

    /**
     * Convert an item into an entity object
     *
     * @param $item
     * @param $typeOfItem
     *
     * @return Item
     */
    protected function toCorrectedItemEntity($item, $typeOfItem)
    {
        $context = SerializationContext::create();
        $context->setGroups(array('corrected', 'Default'));

        $itemContent = $this->serializer->serialize(
            $item,
            'json',
            $context
        );

        return ItemFactory::create($itemContent, $typeOfItem);
    }

    /**
     * Get Exercise object from entity
     *
     * @param Item $entityItem
     *
     * @return CommonItem
     */
    public function getItemFromEntity(Item $entityItem)
    {
        $content = $entityItem->getContent();
        $class = ItemResource::getClass($entityItem->getType());

        return $this->serializer->deserialize($content, $class, 'json');
    }

    /**
     * Select random resources from a block
     *
     * @param ResourceBlock $resourceBlock
     * @param               $numberOfOccurrences
     * @param array         $blockResources
     * @param User          $owner
     */
    public function getObjectsFromList(
        ResourceBlock $resourceBlock,
        &$numberOfOccurrences,
        array &$blockResources,
        User $owner
    )
    {
        $existingResourceIds = $resourceBlock->getResources();

        while ($numberOfOccurrences > 0 && count($existingResourceIds) > 0) {
            // select a random object
            $resIndex = array_rand($existingResourceIds);
            $resId = $existingResourceIds[$resIndex];

            // get this object in form of an ExerciseObject and add to the list
            $exObj = $this->exerciseResourceService->getExerciseObject($resId, $owner);

            // remove the object from the list (cannot be added anymore)
            unset($existingResourceIds[$resIndex]);

            $blockResources[] = $exObj;
            $numberOfOccurrences--;
        }
    }
}
