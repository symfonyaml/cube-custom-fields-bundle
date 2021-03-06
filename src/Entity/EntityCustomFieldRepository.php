<?php

namespace CubeTools\CubeCustomFieldsBundle\Entity;

use Doctrine\Common\Util\ClassUtils;

/**
 * EntityCustomFieldRepository
 */
class EntityCustomFieldRepository extends AbstractCustomFieldRepository
{
    public function addFindByObject($qb, $cfAlias, $object, $fieldId)
    {
        $objectClass = str_replace('\\', '\\\\\\\\', ClassUtils::getClass($object));
        $objectId = intval($object->getId());
        /*
         * Structure of the entityValue field.
         * 
         * SINGLE:
         * {"entityClass":"CubeTools\\CubeCustomFieldsBundle\\Entity\\TextCustomField","entityId":1}
         * 
         * MULTI:
         * {"entityClass":"AppBundle\\Entity\\User","entityId":[7]}
         * 
         */
        $objectIdComparison = array(
            'cf.entityValue LIKE \'%:' . $objectId . '}\'', // single value
            'cf.entityValue LIKE \'%[' . $objectId . ']}\'', // only one element in array
            'cf.entityValue LIKE \'%[' . $objectId . ',%\'', // first element in array
            'cf.entityValue LIKE \'%,' . $objectId . ']}\'', // last element in array
            'cf.entityValue LIKE \'%,' . $objectId . ',%\'', // one of several elements in array
        );

        if ($fieldId) {
            $qb->andWhere($cfAlias . '.fieldId = :fieldId')
            ->setParameter('fieldId', $fieldId);
        }
        $qb->andWhere($cfAlias . '.entityValue LIKE \'{"entityClass":"' . $objectClass . '"%\'') // match the class
            ->andWhere(join(' OR ', $objectIdComparison));
    }
}
