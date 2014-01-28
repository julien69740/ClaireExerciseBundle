<?php

namespace OC\CLAIRE\BusinessRules\Responders\Course\Toc;

use SimpleIT\ApiResourcesBundle\Course\PartResource;
use OC\CLAIRE\BusinessRules\Responders\UseCaseResponse;

/**
 * Interface AddElementToTocResponse
 *
 * @author Romain Kuzniak <romain.kuzniak@simple-it.fr>
 */
interface AddElementToTocResponse extends UseCaseResponse
{
    /**
     * @return PartResource
     */
    public function getToc();

    /**
     * @return PartResource
     */
    public function getNewElement();
}
