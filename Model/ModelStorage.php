<?php

namespace Lexik\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;
use Lexik\Bundle\WorkflowBundle\Entity\ModelState;
use Lexik\Bundle\WorkflowBundle\Validation\ViolationList;


class ModelStorage
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * @var
     */
    protected $class;

    /**
     * Construct.
     *
     * @param ObjectManager $om
     * @param string        $class
     */
    public function __construct(ObjectManager $om, $class)
    {
        $this->om = $om;
        $this->class = $class;
        $this->repository = $this->om->getRepository($class);
    }

    /**
     * Returns the current model state.
     *
     * @param ModelInterface $model
     * @param string         $processName
     * @param string         $stepName
     *
     * @return Lexik\Bundle\WorkflowBundle\Entity\ModelState
     */
    public function findCurrentModelState(ModelInterface $model, $processName, $stepName = null)
    {
        return $this->repository->findLatestModelState(
            $model->getWorkflowIdentifier(),
            $processName,
            $stepName
        );
    }

    /**
     * Returns all model states.
     *
     * @param  ModelInterface $model
     * @param  string         $processName
     * @param  string         $successOnly
     * @return array
     */
    public function findAllModelStates(ModelInterface $model, $processName, $successOnly = true)
    {
        return $this->repository->findModelStates(
            $model->getWorkflowIdentifier(),
            $processName,
            $successOnly
        );
    }

    /**
     * Create a new invalid model state.
     *
     * @param ModelInterface  $model
     * @param string          $processName
     * @param string          $stepName
     * @param ViolationList   $violationList
     * @param null|ModelState $previous
     *
     * @return ModelState
     */
    public function newModelStateError(ModelInterface $model, $processName, $stepName, ViolationList $violationList, $previous = null)
    {
        $modelState = $this->createModelState($model, $processName, $stepName, $previous);
        $modelState->setSuccessful(false);
        $modelState->setErrors($violationList->toArray());

        $this->om->persist($modelState);
        $this->om->flush($modelState);

        return $modelState;
    }

    /**
     * Delete all model states.
     *
     * @param ModelInterface $model
     * @param string         $processName
     */
    public function deleteAllModelStates(ModelInterface $model, $processName = null)
    {
        return $this->repository->deleteModelStates(
            $model->getWorkflowIdentifier(),
            $processName
        );
    }

    /**
     * Create a new successful model state.
     *
     * @param  ModelInterface                                 $model
     * @param  string                                         $processName
     * @param  string                                         $stepName
     * @param  ModelState                                     $previous
     * @return \Lexik\Bundle\WorkflowBundle\Entity\ModelState
     */
    public function newModelStateSuccess(ModelInterface $model, $processName, $stepName, $previous = null)
    {
        $modelState = $this->createModelState($model, $processName, $stepName, $previous);
        $modelState->setSuccessful(true);

        $this->om->persist($modelState);
        $this->om->flush($modelState);

        return $modelState;
    }

    /**
     * Create a new model state.
     *
     * @param  ModelInterface                                 $model
     * @param  string                                         $processName
     * @param  string                                         $stepName
     * @param  ModelState                                     $previous
     * @return \Lexik\Bundle\WorkflowBundle\Entity\ModelState
     */
    protected function createModelState(ModelInterface $model, $processName, $stepName, $previous = null)
    {
        $modelState = $this->createModelStateObject();
        $modelState->setWorkflowIdentifier($model->getWorkflowIdentifier());
        $modelState->setProcessName($processName);
        $modelState->setStepName($stepName);
        $modelState->setData($model->getWorkflowData());

        if ($previous instanceof ModelState) {
            $modelState->setPrevious($previous);
        }

        return $modelState;
    }

    /**
     *
     */
    protected function createModelStateObject()
    {
        $class = $this->class;
        return new $class;
    }
}
