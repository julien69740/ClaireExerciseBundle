<?php

namespace OC\CLAIRE\BusinessRules\UseCases\Course\CourseDifficulty\DTO;

use OC\CLAIRE\BusinessRules\Requestors\Course\CourseDifficulty\GetCourseDifficultyRequest;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class GetDraftCourseDifficultyRequestDTO implements GetCourseDifficultyRequest
{
    /**
     * @var int
     */
    public $courseId;

    public function __construct($courseId)
    {
        $this->courseId = $courseId;
    }

    /**
     * @return int
     */
    public function getCourseId()
    {
        return $this->courseId;
    }
}
