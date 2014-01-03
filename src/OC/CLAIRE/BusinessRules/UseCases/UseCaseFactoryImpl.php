<?php

namespace OC\CLAIRE\BusinessRules\UseCases;

use OC\CLAIRE\BusinessRules\Requestors\InvalidUseCaseException;
use OC\CLAIRE\BusinessRules\Requestors\UseCase;
use OC\CLAIRE\BusinessRules\Requestors\UseCaseFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class UseCaseFactoryImpl implements UseCaseFactory
{
    /**
     * @var ContainerInterface
     */
    private $injector;

    /**
     * @return UseCase
     */
    public function make($useCaseName)
    {
        switch ($useCaseName) {
            // COURSE
            case 'GetPublishedCourse':
                $useCase = $this->injector->get(
                    'oc.claire.use_cases.course.course.get_published_course'
                );
                break;
            case 'GetWaitingForPublicationCourse':
                $useCase = $this->injector->get(
                    'oc.claire.use_cases.course.course.get_waiting_for_publication_course'
                );
                break;
            case 'GetDraftCourse':
                $useCase = $this->injector->get('oc.claire.use_cases.course.course.get_draft_course');
                break;
            // CONTENT
            case 'GetPublishedContent':
                $useCase = $this->injector->get(
                    'oc.claire.use_cases.course.content.get_published_content'
                );
                break;
            case 'GetWaitingForPublicationContent':
                $useCase = $this->injector->get(
                    'oc.claire.use_cases.course.content.get_waiting_for_publication_content'
                );
                break;
            case 'GetDraftContent':
                $useCase = $this->injector->get(
                    'oc.claire.use_cases.course.content.get_draft_content'
                );
                break;
            case 'SaveContent':
                $useCase = $this->injector->get(
                    'oc.claire.use_cases.course.edition.save_content'
                );
                break;
            // WORKFLOW
            case 'ChangeCourseToPublished':
                $useCase = $this->injector->get(
                    'oc.claire.use_cases.course.workflow.change_course_to_published'
                );
                break;
            case 'ChangeCourseToWaitingForPublication':
                $useCase = $this->injector->get(
                    'oc.claire.use_cases.course.workflow.change_course_to_waiting_for_publication'
                );
                break;
            // METADATA
            case 'GetCourseDifficulty':
                $useCase = $this->injector->get(
                    'oc.claire.use_cases.course.difficulty.get_course_difficulty'
                );
                break;
            case 'SaveCourseDifficulty':
                $useCase = $this->injector->get(
                    'oc.claire.use_cases.course.difficulty.save_course_difficulty'
                );
                break;
            case 'AddElementToToc':
                $useCase = $this->injector->get(
                    'oc.claire.use_cases.course.toc.add_element_to_toc'
                );
                break;
            default:
                throw new InvalidUseCaseException();
        }

        return $useCase;
    }

    public function setInjector(ContainerInterface $injector)
    {
        $this->injector = $injector;
    }
}
