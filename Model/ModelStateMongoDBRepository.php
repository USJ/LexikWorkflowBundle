<?php

namespace Lexik\Bundle\WorkflowBundle\Model;


use Doctrine\ODM\MongoDB\DocumentRepository;

class ModelStateMongoDBRepository extends DocumentRepository
{

    /**
     * Returns the last ModelState for the given workflow identifier.
     *
     * @param string $workflowIdentifier
     * @param string $processName
     * @param string $stepName
     *
     * @return Lexik\Bundle\WorkflowBundle\Document\ModelState
     */
    public function findLatestModelState($workflowIdentifier, $processName, $stepName = null)
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->field('workflowIdentifier')->equals($workflowIdentifier)
            ->field('processName')->equals($processName)
            ->field('successful')->equals(true)
            ->sort('createdAt', 'DESC');

        if (null !== $stepName) {
            $qb
                ->field('stepName')->equals($stepName);
        }

        return $qb->getQuery()->execute()->getSingleResult();
    }

    /**
     * Returns all model states for the given workflow identifier.
     *
     * @param  string  $workflowIdentifier
     * @param  string  $processName
     * @param  boolean $successOnly
     * @return array
     */
    public function findModelStates($workflowIdentifier, $processName, $successOnly)
    {
        $qb = $this->createQueryBuilder()
        $qb
            ->field('workflowIdentifier')->equals($workflowIdentifier)
            ->field('processName')->equals($processName)
            ->sort('createdAt', 'ASC');

        if ($successOnly) {
            $qb->field('successful')->equals(true);
        }

        return $qb->getQuery()->execute()->getResult();
    }

    /**
     * Delete all model states for the given workflowIndentifier (and process name if given).
     *
     * @param  string $workflowIdentifier
     * @param  string $processName
     * @return int
     */
    public function deleteModelStates($workflowIdentifier, $processName = null)
    {
        $qb = $this->createQueryBuilder()
            ->findAndRemove()
            ->field('workflowIdentifier')->equals($workflowIdentifier);

        if (null !== $processName) {
            $qb->field('processName')->equals($processName);
        }

        return $qb->getQuery()->execute()->getResult();
    }
} 