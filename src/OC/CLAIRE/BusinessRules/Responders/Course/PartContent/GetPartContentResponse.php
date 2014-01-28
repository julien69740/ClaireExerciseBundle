<?php

namespace OC\CLAIRE\BusinessRules\Responders\Course\PartContent;

use OC\CLAIRE\BusinessRules\Responders\UseCaseResponse;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
interface GetPartContentResponse extends UseCaseResponse
{
    /**
     * @return string
     */
    public function getContent();
}
