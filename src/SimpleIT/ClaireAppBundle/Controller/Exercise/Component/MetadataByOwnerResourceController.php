<?php

namespace SimpleIT\ClaireAppBundle\Controller\Exercise\Component;

use SimpleIT\ApiResourcesBundle\Exercise\ExerciseResource;
use SimpleIT\ApiResourcesBundle\Exercise\ResourceResource;
use SimpleIT\AppBundle\Controller\AppController;
use SimpleIT\AppBundle\Util\RequestUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MetadataByOwnerResourceController
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class MetadataByOwnerResourceController extends AppController
{
    /**
     * Edit the metadata (GET)
     *
     * @param int $ownerResourceId Owner Resource id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editMetadataViewAction($ownerResourceId)
    {
        $ownerResource = $this->get('simple_it.claire.exercise.owner_resource')->get(
            $ownerResourceId
        );

        $misc = null;
        if (isset($ownerResource->getMetadata()['_misc'])) {
            $misc = explode(';', $ownerResource->getMetadata()['_misc']);
        }

        return $this->render(
            'SimpleITClaireAppBundle:Exercise/OwnerResource/Component:editMetadata.html.twig',
            array('ownerResource' => $ownerResource, 'misc' => $misc)
        );
    }

    /**
     * Edit the metadata (POST)
     *
     * @param Request $request         Request
     * @param int     $ownerResourceId Course id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function MetadataEditAction(Request $request, $ownerResourceId)
    {
        $resourceData = $request->request->all();
        $requiredResources = $this->get('simple_it.claire.exercise.owner_resource')->saveMetadata(
            $ownerResourceId,
            $resourceData
        );

        return new JsonResponse($requiredResources);
    }
}